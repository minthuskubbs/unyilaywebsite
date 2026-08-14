<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class WordPressHelper
{
    /**
     * Get WordPress product permalink
     */
    public static function getPermalink($postId)
    {
        $post = DB::connection('wordpress')
            ->table('posts')
            ->where('ID', $postId)
            ->select('post_name', 'post_type')
            ->first();
        
        if (!$post) {
            return '#';
        }
        
        $siteUrl = config('woocommerce.site_url', env('WC_SITE_URL', 'https://unyilaysilver.com'));
        
        // Build URL based on post type
        if ($post->post_type === 'product') {
            return rtrim($siteUrl, '/') . '/product/' . $post->post_name . '/';
        }
        
        return rtrim($siteUrl, '/') . '/' . $post->post_name . '/';
    }
    
    /**
     * Get product edit link in WordPress admin
     */
    public static function getAdminEditLink($productId)
    {
        $siteUrl = config('woocommerce.site_url', env('WC_SITE_URL', 'https://unyilaysilver.com'));
        return rtrim($siteUrl, '/') . '/wp-admin/post.php?post=' . $productId . '&action=edit';
    }
}