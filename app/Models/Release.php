<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// A formal Control Center Release catalog entry. Independent from the
// Master Baseline's own version, any per-module version, the
// Deployments page's per-site platform version, and
// ConfigurationSnapshot.source_baseline_version — see the
// create_releases_table migration's doc comment for the full account.
class Release extends Model
{
    public const STATUSES = ['draft', 'ready', 'released', 'archived'];

    protected $fillable = ['version', 'title', 'status', 'release_date', 'description', 'created_by'];

    protected function casts(): array
    {
        return ['release_date' => 'date'];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function changelogs(): HasMany
    {
        return $this->hasMany(ReleaseChangelog::class)->orderBy('position');
    }
}
