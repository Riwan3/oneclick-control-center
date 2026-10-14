<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

/**
 * Self-service profile for the signed-in user: display name, password
 * and profile photo. Role, status, username and access are not editable
 * here — those stay with user management (super_admin).
 */
class ProfileController extends Controller
{
    public function __construct(private AuditLogger $auditLogger) {}

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = $request->user();
        $user->update($data);

        $this->auditLogger->log(
            action: 'profile_updated',
            description: "{$user->username} changed their display name",
            entityType: 'user',
            entityId: $user->id,
        );

        return new UserResource($user);
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::min(8), 'different:current_password'],
        ]);

        $user = $request->user();
        $user->update(['password' => $data['password']]);

        $this->auditLogger->log(
            action: 'password_changed',
            description: "{$user->username} changed their password",
            entityType: 'user',
            entityId: $user->id,
        );

        return response()->json(['message' => 'Password updated.']);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();
        $old = $user->avatar_path;

        $user->update(['avatar_path' => $request->file('avatar')->store('avatars')]);

        if ($old) {
            Storage::delete($old);
        }

        $this->auditLogger->log(
            action: 'avatar_changed',
            description: "{$user->username} changed their profile photo",
            entityType: 'user',
            entityId: $user->id,
        );

        return new UserResource($user);
    }

    public function destroyAvatar(Request $request)
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::delete($user->avatar_path);
            $user->update(['avatar_path' => null]);
        }

        return new UserResource($user);
    }

    public function avatar(User $user)
    {
        abort_unless($user->avatar_path && Storage::exists($user->avatar_path), 404);

        return response()->file(Storage::path($user->avatar_path), [
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
