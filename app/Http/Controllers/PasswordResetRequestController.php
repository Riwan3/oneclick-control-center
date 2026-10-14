<?php

namespace App\Http\Controllers;

use App\Models\PasswordResetRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * "Lupa Password" flow. store() is public (login page) and answers the
 * same way whether or not the username exists, so it cannot be used to
 * probe for valid usernames. The rest is super_admin only.
 */
class PasswordResetRequestController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'position' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,}$/'],
        ]);

        $user = User::where('username', $data['username'])->first();

        $alreadyOpen = PasswordResetRequest::where('username', $data['username'])
            ->whereIn('status', [PasswordResetRequest::STATUS_PENDING, PasswordResetRequest::STATUS_VERIFIED])
            ->exists();

        if (! $alreadyOpen) {
            $entry = PasswordResetRequest::create($data + ['user_id' => $user?->id]);

            $this->auditLogger->log(
                action: 'password_reset_requested',
                description: "Password reset requested for {$data['username']}",
                metadata: ['request_id' => $entry->id, 'user_found' => (bool) $user],
                entityType: 'password_reset_request',
                entityId: $entry->id,
            );
        }

        return response()->json([
            'message' => 'Permintaan reset password telah dikirim. Super Admin akan memverifikasi data Anda.',
        ], 201);
    }

    public function index(): JsonResponse
    {
        $rows = PasswordResetRequest::with(['user:id,username,name', 'handler:id,name'])
            ->orderByRaw("case status when 'pending' then 0 when 'verified' then 1 else 2 end")
            ->latest()
            ->limit(100)
            ->get()
            ->map(fn (PasswordResetRequest $r) => [
                'id' => $r->id,
                'username' => $r->username,
                'fullName' => $r->full_name,
                'position' => $r->position,
                'phone' => $r->phone,
                'status' => $r->status,
                'userFound' => $r->user_id !== null,
                'userName' => $r->user?->name,
                'handledBy' => $r->handler?->name,
                'handledAt' => $r->handled_at?->toIso8601String(),
                'createdAt' => $r->created_at->toIso8601String(),
            ]);

        return response()->json(['data' => $rows]);
    }

    public function verify(Request $request, PasswordResetRequest $passwordRequest): JsonResponse
    {
        abort_unless($passwordRequest->status === PasswordResetRequest::STATUS_PENDING, 422, 'Request is not pending.');

        $this->markHandled($request, $passwordRequest, PasswordResetRequest::STATUS_VERIFIED);
        $this->audit('password_reset_verified', $passwordRequest, "Verified password reset request for {$passwordRequest->username}");

        return response()->json(['ok' => true]);
    }

    public function reject(Request $request, PasswordResetRequest $passwordRequest): JsonResponse
    {
        abort_unless($passwordRequest->isOpen(), 422, 'Request is already closed.');

        $this->markHandled($request, $passwordRequest, PasswordResetRequest::STATUS_REJECTED);
        $this->audit('password_reset_rejected', $passwordRequest, "Rejected password reset request for {$passwordRequest->username}");

        return response()->json(['ok' => true]);
    }

    /**
     * Resets the account's password back to its username / NRP. Only a
     * verified request for an existing user can be reset.
     */
    public function reset(Request $request, PasswordResetRequest $passwordRequest): JsonResponse
    {
        abort_unless($passwordRequest->status === PasswordResetRequest::STATUS_VERIFIED, 422, 'Verify the request first.');

        $user = $passwordRequest->user;
        abort_unless($user, 422, 'No user matches this username.');

        $user->update(['password' => $user->username]);

        $this->markHandled($request, $passwordRequest, PasswordResetRequest::STATUS_RESET);
        $this->audit('password_reset_done', $passwordRequest, "Reset password of {$user->username} to default");

        return response()->json(['ok' => true]);
    }

    private function markHandled(Request $request, PasswordResetRequest $entry, string $status): void
    {
        $entry->update([
            'status' => $status,
            'handled_by' => $request->user()->id,
            'handled_at' => now(),
        ]);
    }

    private function audit(string $action, PasswordResetRequest $entry, string $description): void
    {
        $this->auditLogger->log(
            action: $action,
            description: $description,
            metadata: ['request_id' => $entry->id, 'username' => $entry->username],
            entityType: 'password_reset_request',
            entityId: $entry->id,
        );
    }
}
