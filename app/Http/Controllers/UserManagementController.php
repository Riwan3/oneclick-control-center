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
use RuntimeException;

/**
 * User Management: list, create, update, delete. Destructive/role-
 * changing actions are protected by App\Support\SuperAdminGuard so the
 * Control Center can never end up with zero active Super Admins.
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
        $users = User::withCount(['sites', 'modules'])->orderBy('name')->get();

        return UserResource::collection($users);
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'username' => $request->validated('username'),
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => $request->validated('role'),
            'status' => $request->validated('status'),
            'site_access_scope' => 'all',
        ])->loadCount(['sites', 'modules']);

        $this->auditLogger->log(
            action: 'user_created',
            description: "Created user {$user->username}",
            metadata: [
                'username' => $user->username,
                'role' => $user->role,
                'status' => $user->status,
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
        $isCurrentlyActiveSuperAdmin = $user->role === 'super_admin' && $user->status === 'active';
        $willRemainActiveSuperAdmin = $request->validated('role') === 'super_admin'
            && $request->validated('status') === 'active';

        if ($isCurrentlyActiveSuperAdmin && ! $willRemainActiveSuperAdmin) {
            $this->guardLastSuperAdmin($user);
        }

        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role' => $request->validated('role'),
            'status' => $request->validated('status'),
        ]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();
        $user->loadCount(['sites', 'modules']);

        $this->auditLogger->log(
            action: 'user_updated',
            description: "Updated user {$user->username}",
            metadata: ['role' => $user->role, 'status' => $user->status],
            entityType: 'User',
            entityId: $user->id
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
