<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One explicit per-site override of a Master Baseline Registry LEAF
// menu item's enabled state. Absence of a row means "use the baseline
// default" (enabled). Parent menu items (with children) are never
// expected to have a row here — their effective visibility is derived
// from their children, not independently stored — see
// composables/useSiteConfiguration.js.
class SiteMenuConfig extends Model
{
    protected $fillable = ['site_id', 'module_key', 'menu_key', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
