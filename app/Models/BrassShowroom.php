<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BrassShowroom extends Model
{
    protected $fillable = [
        'key', 'published_config', 'draft_config', 'published_revision',
        'draft_revision', 'published_at', 'draft_updated_at', 'updated_by', 'frontend_release_id',
    ];

    protected $casts = [
        'published_config' => 'array',
        'draft_config' => 'array',
        'published_at' => 'datetime',
        'draft_updated_at' => 'datetime',
    ];

    public function revisions(): HasMany
    {
        return $this->hasMany(BrassShowroomRevision::class);
    }
}
