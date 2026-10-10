<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Minimal reference model backing per-user site access (see the
// create_sites_table migration). Not consumed by the frontend Sites /
// Site Detail pages yet — those still read data/mock/sites.js.
class Site extends Model
{
    protected $fillable = ['code', 'name'];

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
