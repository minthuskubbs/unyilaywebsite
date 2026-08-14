<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpPostMeta extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'postmeta';
    protected $primaryKey = 'meta_id';
    public    $timestamps = false;

    protected $fillable = ['post_id', 'meta_key', 'meta_value'];
}
