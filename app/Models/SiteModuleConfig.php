<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// One explicit per-site override of a Master Baseline Registry module's
// enabled state. Absence of a row means "use the baseline default"
// (enabled) — see the migration for the full rationale.
class SiteModuleConfig extends Model
{
    protected $fillable = ['site_id', 'module_key', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
