<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpUser extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'users';
    protected $primaryKey = 'ID';
    public    $timestamps = false;

    protected $fillable = [
        'user_login', 'user_pass', 'user_nicename', 'user_email',
        'user_url', 'user_registered', 'display_name',
    ];

    protected $hidden = ['user_pass', 'user_activation_key'];

    public function meta()
    {
        return $this->hasMany(WpUserMeta::class, 'user_id', 'ID');
    }

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : $default;
    }
}
