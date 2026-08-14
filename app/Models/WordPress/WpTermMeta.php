<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpTermMeta extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'termmeta';
    protected $primaryKey = 'meta_id';
    public    $timestamps = false;

    protected $fillable = ['term_id', 'meta_key', 'meta_value'];
}
