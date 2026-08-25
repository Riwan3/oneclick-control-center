<?php

namespace App\Support;

use App\Models\User;
use RuntimeException;

/**
 * Enforces "at least one active Super Admin must always exist". Wired
 * into UserManagementController@update and @destroy — any other future
 * endpoint that can delete a user, change their role, or change their
 * status MUST also call ensureNotLastActiveSuperAdmin() before
 * persisting the change.
 */
class SuperAdminGuard
{
    public static function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('role', 'super_admin')
            ->where('status', 'active')
            ->count();
    }

    /**
     * @throws RuntimeException if $user is the last active Super Admin.
     */
    public static function ensureNotLastActiveSuperAdmin(User $user): void
    {
        if ($user->role === 'super_admin' && $user->status === 'active' && self::activeSuperAdminCount() <= 1) {
            throw new RuntimeException('At least one active Super Admin must remain in the Control Center.');
        }
    }
}
