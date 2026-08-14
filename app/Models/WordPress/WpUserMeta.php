<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpUserMeta extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'usermeta';
    protected $primaryKey = 'umeta_id';
    public    $timestamps = false;

    protected $fillable = ['user_id', 'meta_key', 'meta_value'];
}
