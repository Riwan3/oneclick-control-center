<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// A module promoted into the Master Baseline from the Control Center — see
// the migration. The 9 SKS modules are not rows here; they live in code.
class BaselineRegisteredModule extends Model
{
    protected $fillable = ['module_key', 'label', 'description', 'menus', 'source_site_code', 'registered_by'];

    protected function casts(): array
    {
        return ['menus' => 'array'];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }
}
