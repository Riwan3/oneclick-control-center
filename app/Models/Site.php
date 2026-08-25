<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
