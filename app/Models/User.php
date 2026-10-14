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

#[Fillable(['username', 'name', 'email', 'avatar_path', 'password', 'role', 'status', 'site_access_scope', 'module_access_scope'])]
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

    /**
     * Resolves this user's effective site access — the single reusable
     * rule every controller/resource/future-dashboard must use instead
     * of re-deriving it independently. super_admin always resolves to
     * 'all' regardless of the stored site_access_scope column (role
     * drives authorization, not a per-row flag that could drift).
     *
     * @return array{mode: 'all'|'selected', sites: array<int, array{id: int, code: string, name: string}>}
     */
    public function effectiveSiteAccess(): array
    {
        $mode = $this->role === 'super_admin' ? 'all' : $this->site_access_scope;

        if ($mode === 'all') {
            $sites = Site::orderBy('name')->get(['id', 'code', 'name']);
        } elseif ($this->relationLoaded('sites')) {
            // Avoids re-querying the pivot when the caller already eager
            // loaded it (e.g. UserManagementController::index() listing
            // many users at once) — same result as the query below, just
            // read from the already-fetched in-memory relation instead
            // of issuing a fresh query per user.
            $sites = $this->sites->sortBy('name')->values();
        } else {
            $sites = $this->sites()->orderBy('sites.name')->get(['sites.id', 'sites.code', 'sites.name']);
        }

        return [
            'mode' => $mode,
            'sites' => $sites->map(fn (Site $site) => [
                'id' => $site->id,
                'code' => $site->code,
                'name' => $site->name,
            ])->values()->all(),
        ];
    }

    /**
     * @return array{mode: 'all'|'selected', modules: array<int, array{id: int, key: string, label: string}>}
     */
    public function effectiveModuleAccess(): array
    {
        $mode = $this->role === 'super_admin' ? 'all' : $this->module_access_scope;

        if ($mode === 'all') {
            $modules = Module::orderBy('label')->get(['id', 'key', 'label']);
        } elseif ($this->relationLoaded('modules')) {
            // See the matching comment in effectiveSiteAccess() above.
            $modules = $this->modules->sortBy('label')->values();
        } else {
            $modules = $this->modules()->orderBy('modules.label')->get(['modules.id', 'modules.key', 'modules.label']);
        }

        return [
            'mode' => $mode,
            'modules' => $modules->map(fn (Module $module) => [
                'id' => $module->id,
                'key' => $module->key,
                'label' => $module->label,
            ])->values()->all(),
        ];
    }
}
