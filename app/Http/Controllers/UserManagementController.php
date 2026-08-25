<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\NotificationService;
use App\Support\SuperAdminGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * User Management: list, create, update, delete — including site/module
 * access configuration. Destructive/role-changing actions are protected
 * by App\Support\SuperAdminGuard so the Control Center can never end up
 * with zero active Super Admins.
 *
 * Access resolution (mode + effective list) always goes through
 * App\Models\User::effectiveSiteAccess()/effectiveModuleAccess() — this
 * controller never re-derives "all vs selected" logic itself.
 */
class UserManagementController extends Controller
{
    public function __construct(
        private AuditLogger $auditLogger,
        private NotificationService $notifications,
    ) {
    }

    public function index()
    {
        $users = User::orderBy('name')->get();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $role = $request->validated('role');

        // super_admin is always full access, regardless of what (if
        // anything) the client submitted for these fields — the frontend
        // hides the picker for this role, but the backend never trusts
        // that alone. For every other role, StoreUserRequest already
        // guarantees these are present and equal to 'selected' with a
        // non-empty id list — there is no safe "missing" fallback here,
        // so none is applied.
        $siteMode = $role === 'super_admin' ? 'all' : $request->input('site_access_mode');
        $moduleMode = $role === 'super_admin' ? 'all' : $request->input('module_access_mode');
        $siteIds = $siteMode === 'selected' ? array_map('intval', $request->input('site_ids', [])) : [];
        $moduleIds = $moduleMode === 'selected' ? array_map('intval', $request->input('module_ids', [])) : [];

        $user = DB::transaction(function () use ($request, $role, $siteMode, $moduleMode, $siteIds, $moduleIds) {
            $user = User::create([
                'username' => $request->validated('username'),
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
                'role' => $role,
                'status' => $request->validated('status'),
                'site_access_scope' => $siteMode,
                'module_access_scope' => $moduleMode,
            ]);

            if ($siteMode === 'selected') {
                $user->sites()->sync($siteIds);
            }

            if ($moduleMode === 'selected') {
                $user->modules()->sync($moduleIds);
            }

            return $user;
        });

        $this->auditLogger->log(
            action: 'user_created',
            description: "Created user {$user->username}",
            metadata: [
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
                'site_access_mode' => $siteMode,
                'site_codes' => $siteMode === 'selected' ? $user->sites()->pluck('code')->all() : null,
                'module_access_mode' => $moduleMode,
                'module_keys' => $moduleMode === 'selected' ? $user->modules()->pluck('key')->all() : null,
            ],
            entityType: 'User',
            entityId: $user->id
        );

        // Notifies the Super Admin who performed the action — not the
        // newly created user (that is a future capability).
        $this->notifications->send(
            recipient: $request->user(),
            type: 'user_created',
            title: 'New User Created',
            message: "User {$user->username} has been successfully created.",
            severity: 'success',
            url: '/users',
        );

        return (new UserResource($user))->response()->setStatusCode(201);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $originalRole = $user->role;
        $originalStatus = $user->status;
        $originalSiteMode = $user->site_access_scope;
        $originalModuleMode = $user->module_access_scope;
        $originalSiteIds = $user->sites()->pluck('sites.id')->sort()->values()->all();
        $originalModuleIds = $user->modules()->pluck('modules.id')->sort()->values()->all();

        $role = $request->validated('role');
        $status = $request->validated('status');

        // SECURITY CORRECTION: an omitted access field on a partial update
        // must PRESERVE the user's existing access, never silently reset
        // it to 'all'. A field is only changed when the request explicitly
        // includes it (UpdateUserRequest already validated that an
        // explicitly-present value is safe for a non-super-admin). The one
        // case "preserve existing" cannot cover is demoting a user AWAY
        // FROM super_admin without also supplying new explicit access —
        // there is no prior non-admin access to fall back to, so that is
        // rejected below instead of silently becoming 'all'.
        if ($role === 'super_admin') {
            $siteMode = 'all';
            $moduleMode = 'all';
            $siteIds = [];
            $moduleIds = [];
        } else {
            $siteMode = $request->has('site_access_mode') ? $request->input('site_access_mode') : $originalSiteMode;
            $moduleMode = $request->has('module_access_mode') ? $request->input('module_access_mode') : $originalModuleMode;
            $siteIds = $request->has('site_ids')
                ? array_map('intval', $request->input('site_ids', []))
                : ($siteMode === $originalSiteMode ? $originalSiteIds : []);
            $moduleIds = $request->has('module_ids')
                ? array_map('intval', $request->input('module_ids', []))
                : ($moduleMode === $originalModuleMode ? $originalModuleIds : []);

            if ($siteMode !== 'selected' || empty($siteIds) || $moduleMode !== 'selected' || empty($moduleIds)) {
                throw ValidationException::withMessages(array_filter([
                    'site_access_mode' => ($siteMode !== 'selected' || empty($siteIds))
                        ? ['Non-Super-Admin users must have explicit selected site access.']
                        : null,
                    'module_access_mode' => ($moduleMode !== 'selected' || empty($moduleIds))
                        ? ['Non-Super-Admin users must have explicit selected module access.']
                        : null,
                ]));
            }
        }

        $sortedSiteIds = collect($siteIds)->sort()->values()->all();
        $sortedModuleIds = collect($moduleIds)->sort()->values()->all();

        $isCurrentlyActiveSuperAdmin = $originalRole === 'super_admin' && $originalStatus === 'active';
        $willRemainActiveSuperAdmin = $role === 'super_admin' && $status === 'active';
        if ($isCurrentlyActiveSuperAdmin && ! $willRemainActiveSuperAdmin) {
            $this->guardLastSuperAdmin($user);
        }

        DB::transaction(function () use ($request, $user, $role, $status, $siteMode, $moduleMode, $siteIds, $moduleIds) {
            $user->fill([
                'name' => $request->validated('name'),
                'email' => $request->validated('email'),
                'role' => $role,
                'status' => $status,
                'site_access_scope' => $siteMode,
                'module_access_scope' => $moduleMode,
            ]);

            if ($request->filled('password')) {
                $user->password = $request->validated('password');
            }

            $user->save();

            $user->sites()->sync($siteMode === 'selected' ? $siteIds : []);
            $user->modules()->sync($moduleMode === 'selected' ? $moduleIds : []);
        });

        $this->logUpdateAudit(
            user: $user,
            originalRole: $originalRole,
            originalStatus: $originalStatus,
            newStatus: $status,
            originalSiteMode: $originalSiteMode,
            newSiteMode: $siteMode,
            originalSiteIds: $originalSiteIds,
            newSiteIds: $sortedSiteIds,
            originalModuleMode: $originalModuleMode,
            newModuleMode: $moduleMode,
            originalModuleIds: $originalModuleIds,
            newModuleIds: $sortedModuleIds,
        );

        return new UserResource($user);
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            abort(422, 'You cannot delete your own account.');
        }

        $this->guardLastSuperAdmin($user);

        $username = $user->username;
        $userId = $user->id;
        $user->delete();

        $this->auditLogger->log(
            action: 'user_deleted',
            description: "Deleted user {$username}",
            metadata: ['username' => $username],
            entityType: 'User',
            entityId: $userId
        );

        return response()->noContent();
    }

    /**
     * Emits one audit event per dimension that actually changed, using
     * the most specific action name for each — rather than a single
     * generic 'user_updated' for everything, so "who changed this
     * user's site access and when" is a directly answerable audit
     * query. No event is logged for a no-op save.
     */
    private function logUpdateAudit(
        User $user,
        string $originalRole,
        string $originalStatus,
        string $newStatus,
        string $originalSiteMode,
        string $newSiteMode,
        array $originalSiteIds,
        array $newSiteIds,
        string $originalModuleMode,
        string $newModuleMode,
        array $originalModuleIds,
        array $newModuleIds,
    ): void {
        $role = $user->role;

        if ($originalRole !== $role || $originalStatus !== $newStatus) {
            $this->auditLogger->log(
                action: 'user_updated',
                description: "Updated user {$user->username}",
                metadata: ['role' => $role, 'status' => $newStatus],
                entityType: 'User',
                entityId: $user->id,
            );
        }

        if ($originalStatus !== $newStatus) {
            $this->auditLogger->log(
                action: $newStatus === 'active' ? 'user_activated' : 'user_deactivated',
                description: ($newStatus === 'active' ? 'Activated' : 'Deactivated')." user {$user->username}",
                metadata: ['status' => $newStatus],
                entityType: 'User',
                entityId: $user->id,
            );
        }

        if ($originalSiteMode !== $newSiteMode || $originalSiteIds !== $newSiteIds) {
            $this->auditLogger->log(
                action: 'user_site_access_updated',
                description: "Updated site access for user {$user->username}",
                metadata: [
                    'site_access_mode' => $newSiteMode,
                    'site_codes' => $newSiteMode === 'selected' ? $user->sites()->pluck('code')->all() : null,
                ],
                entityType: 'User',
                entityId: $user->id,
            );
        }

        if ($originalModuleMode !== $newModuleMode || $originalModuleIds !== $newModuleIds) {
            $this->auditLogger->log(
                action: 'user_module_access_updated',
                description: "Updated module access for user {$user->username}",
                metadata: [
                    'module_access_mode' => $newModuleMode,
                    'module_keys' => $newModuleMode === 'selected' ? $user->modules()->pluck('key')->all() : null,
                ],
                entityType: 'User',
                entityId: $user->id,
            );
        }
    }

    /**
     * Converts the SuperAdminGuard invariant into a proper 422 JSON error
     * instead of an uncaught 500.
     */
    private function guardLastSuperAdmin(User $user): void
    {
        try {
            SuperAdminGuard::ensureNotLastActiveSuperAdmin($user);
        } catch (RuntimeException $e) {
            abort(422, $e->getMessage());
        }
    }
}
