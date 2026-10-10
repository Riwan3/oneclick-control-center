<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseChangelog extends Model
{
    public const CATEGORIES = ['new', 'improvement', 'changed', 'fixed'];

    protected $fillable = ['release_id', 'category', 'title', 'description', 'position', 'created_by'];

    public function release(): BelongsTo
    {
        return $this->belongsTo(Release::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
