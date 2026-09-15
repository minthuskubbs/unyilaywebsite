<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class BrassShowroomFrontendRelease extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'version', 'javascript_path', 'stylesheet_path',
        'javascript_sha256', 'stylesheet_sha256', 'javascript_bytes',
        'stylesheet_bytes', 'note', 'created_by', 'activated_by', 'activated_at',
    ];

    protected $casts = ['activated_at' => 'datetime'];
}
