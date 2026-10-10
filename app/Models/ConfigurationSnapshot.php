<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RuntimeException;

/**
 * Immutable historical record of one site's effective configuration at
 * a point in time — see App\Services\ConfigurationSnapshotService, the
 * only place that creates these. Never updated after creation: no
 * controller route exists for it, and the `updating` guard below is a
 * defense-in-depth backstop against that ever changing by accident.
 */
class ConfigurationSnapshot extends Model
{
    protected $fillable = [
        'uuid', 'site_id', 'snapshot_number', 'source_baseline_version',
        'module_count', 'menu_count', 'status', 'created_by',
    ];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Configuration snapshots are immutable and cannot be updated once created.');
        });
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modules(): HasMany
    {
        return $this->hasMany(ConfigurationSnapshotModule::class, 'snapshot_id');
    }

    public function menus(): HasMany
    {
        return $this->hasMany(ConfigurationSnapshotMenu::class, 'snapshot_id');
    }
}
