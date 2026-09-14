<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrassShowroomRevision extends Model
{
    public $timestamps = false;

    protected $fillable = ['brass_showroom_id', 'revision', 'config', 'note', 'published_by', 'created_at'];

    protected $casts = ['config' => 'array', 'created_at' => 'datetime'];

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(BrassShowroom::class, 'brass_showroom_id');
    }
}
