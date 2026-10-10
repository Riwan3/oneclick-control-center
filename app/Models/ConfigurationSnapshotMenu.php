<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

// One menu item's frozen effective state within a ConfigurationSnapshot
// (already resolved for parent-derived-from-children visibility).
// Immutable — see ConfigurationSnapshot's class doc comment.
class ConfigurationSnapshotMenu extends Model
{
    protected $fillable = ['snapshot_id', 'module_key', 'menu_key', 'enabled'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Configuration snapshot rows are immutable and cannot be updated once created.');
        });
    }

    public function snapshot(): BelongsTo
    {
        return $this->belongsTo(ConfigurationSnapshot::class, 'snapshot_id');
    }
}
