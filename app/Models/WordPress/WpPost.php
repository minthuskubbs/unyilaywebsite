<?php

namespace App\Models\WordPress;

use Illuminate\Database\Eloquent\Model;

class WpPost extends Model
{
    protected $connection = 'wordpress';
    protected $table      = 'posts';
    protected $primaryKey = 'ID';
    public    $timestamps = false;

    protected $fillable = [
        'post_title', 'post_name', 'post_status', 'post_type',
        'post_content', 'post_excerpt', 'post_parent', 'menu_order',
        'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt',
        'post_author', 'comment_status', 'ping_status', 'post_password',
        'to_ping', 'pinged', 'post_content_filtered', 'guid',
        'post_mime_type', 'comment_count',
    ];

    public function meta()
    {
        return $this->hasMany(WpPostMeta::class, 'post_id', 'ID');
    }

    public function getMeta(string $key, $default = null)
    {
        $meta = $this->meta->firstWhere('meta_key', $key);
        return $meta ? $meta->meta_value : $default;
    }

    // Scope: products only
    public function scopeProducts($query)
    {
        return $query->where('post_type', 'product')
                     ->whereIn('post_status', ['publish', 'draft', 'private']);
    }

    // Scope: variations
    public function scopeVariations($query)
    {
        return $query->where('post_type', 'product_variation')
                     ->where('post_status', 'publish');
    }
}
