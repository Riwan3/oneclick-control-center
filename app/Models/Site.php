<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Backs per-user site access and the site catalogue: the frontend reads
// it (plus the status pulled from each OneClick site) via
// GET /api/site-connections — see SiteConnectionController.
class Site extends Model
{
    protected $fillable = ['code', 'name', 'location'];

    // Connection fields (base_url, api_token, status, ...) are set only
    // through SiteConnectionService / sites:connect, never mass-assigned.
    protected $hidden = ['api_token'];

    protected function casts(): array
    {
        return [
            'api_token' => 'encrypted',
            'modules' => 'array',
            'health' => 'array',
            'activity' => 'array',
            'menus' => 'array',
            'deployed_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    // Has a token, so the site can talk to the Control Center in at least one direction.
    public function isConnectable(): bool
    {
        return filled($this->api_token);
    }

    // Pull mode: the Control Center can call the site (it has a URL too).
    // Without a URL the site runs in push mode and reports to us instead.
    public function canPull(): bool
    {
        return filled($this->base_url) && filled($this->api_token);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_site_access');
    }

    // Site Configuration Foundation — explicit per-site overrides
    // against the Master Baseline Registry (frontend-only baseline
    // data; these are sparse deltas, not a duplicate catalogue).
    public function moduleConfigs(): HasMany
    {
        return $this->hasMany(SiteModuleConfig::class);
    }

    public function menuConfigs(): HasMany
    {
        return $this->hasMany(SiteMenuConfig::class);
    }

    // STEP 3R-1 — immutable historical records of this site's effective
    // configuration; see App\Services\ConfigurationSnapshotService.
    public function configurationSnapshots(): HasMany
    {
        return $this->hasMany(ConfigurationSnapshot::class);
    }
}
