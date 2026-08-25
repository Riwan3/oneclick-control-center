<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(private AuditLogger $auditLogger)
    {
    }
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            $this->auditLogger->log(
                action: 'login_failed',
                description: 'Invalid credentials',
                metadata: ['username' => $credentials['username']],
                entityType: 'Authentication'
            );

            throw ValidationException::withMessages([
                'username' => 'Invalid username or password.',
            ]);
        }

        $user = Auth::user();

        if (! $user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();

            $this->auditLogger->log(
                action: 'login_failed',
                description: 'Account deactivated',
                metadata: ['username' => $credentials['username']],
                entityType: 'Authentication'
            );

            // Only revealed after the password has already been verified,
            // so this does not leak account existence to a guesser.
            throw ValidationException::withMessages([
                'username' => 'This account has been deactivated. Contact your administrator.',
            ]);
        }

        $request->session()->regenerate();

        $this->auditLogger->log(
            action: 'login',
            description: 'Successful login',
            entityType: 'Authentication'
        );

        return new UserResource($user->loadCount(['sites', 'modules']));
    }

    public function destroy(Request $request)
    {
        $this->auditLogger->log(
            action: 'logout',
            description: 'Successful logout',
            entityType: 'Authentication'
        );

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
