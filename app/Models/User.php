<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'name', 'email', 'password', 'role', 'status', 'site_access_scope'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Control Center roles. Must stay in sync with
     * resources/js/config/roles.js — this is the backend mirror of that
     * single frontend source of truth, not a second independent list.
     */
    public const ROLES = ['super_admin', 'management', 'view'];

    public const STATUSES = ['active', 'inactive'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Sites this user has explicit access to. Only meaningful when
     * site_access_scope is 'selected' — when it is 'all', this relation
     * is not consulted for access decisions.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'user_site_access');
    }

    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'user_module_access');
    }
}
