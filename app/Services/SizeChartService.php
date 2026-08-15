<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Reads size charts from the YITH WooCommerce Product Size Charts plugin's
 * own tables (custom post type `yith-wcpsc-wc-chart`), assigned to a product
 * via the `yith_wcpsc_product_charts` postmeta the plugin writes in wp-admin.
 * We only ever read this data — the plugin itself isn't installed here.
 */
class SizeChartService
{
    private const CACHE_TTL = 900; // 15 minutes

    /**
     * @return array<int, array{
     *     id: int, title: string, button_text: string,
     *     tab_title: string, desc_tab_title: string,
     *     content: string, table: array<int, array<int, string>>
     * }>
     */
    public function chartsForProduct(int $productId): array
    {
        return Cache::remember("sizecharts.product.{$productId}", self::CACHE_TTL, function () use ($productId) {
            $raw = DB::connection('wordpress')
                ->table('postmeta')
                ->where('post_id', $productId)
                ->where('meta_key', 'yith_wcpsc_product_charts')
                ->value('meta_value');

            if (!$raw) {
                return [];
            }

            $chartIds = @unserialize($raw);
            if (!is_array($chartIds) || empty($chartIds)) {
                return [];
            }
            $chartIds = array_values(array_unique(array_map('intval', $chartIds)));

            $posts = DB::connection('wordpress')
                ->table('posts')
                ->whereIn('ID', $chartIds)
                ->where('post_type', 'yith-wcpsc-wc-chart')
                ->where('post_status', 'publish')
                ->get(['ID', 'post_title', 'post_content'])
                ->keyBy('ID');

            if ($posts->isEmpty()) {
                return [];
            }

            $meta = DB::connection('wordpress')
                ->table('postmeta')
                ->whereIn('post_id', $posts->keys())
                ->whereIn('meta_key', ['_table_meta', 'button_text', 'tab_title', 'title_of_desc_tab'])
                ->get(['post_id', 'meta_key', 'meta_value'])
                ->groupBy('post_id')
                ->map(fn ($rows) => $rows->pluck('meta_value', 'meta_key'));

            $uploadsBase = rtrim(config('woocommerce.uploads_url'), '/');

            return collect($chartIds)
                ->map(function ($id) use ($posts, $meta, $uploadsBase) {
                    $post = $posts->get($id);
                    if (!$post) {
                        return null;
                    }

                    $m = $meta->get($id, collect());

                    $table = [];
                    $tableRaw = $m->get('_table_meta');
                    if ($tableRaw) {
                        $decoded = json_decode($tableRaw, true);
                        if (is_array($decoded)) {
                            // Drop rows that are entirely blank (some charts are
                            // image-only and store a placeholder empty row).
                            $table = array_values(array_filter($decoded, function ($row) {
                                return is_array($row) && collect($row)->contains(fn ($cell) => trim((string) $cell) !== '');
                            }));
                        }
                    }

                    $content = (string) $post->post_content;
                    if ($content !== '') {
                        // Chart content stores absolute media URLs (often the
                        // local WP dev host) — repoint to wherever uploads
                        // actually resolve, same as product images.
                        $content = preg_replace('#https?://[^\s"\']+/wp-content/uploads#', $uploadsBase, $content);
                    }

                    return [
                        'id' => (int) $id,
                        'title' => $post->post_title,
                        'button_text' => $m->get('button_text') ?: 'Size Help',
                        'tab_title' => $m->get('tab_title') ?: 'Size Chart',
                        'desc_tab_title' => $m->get('title_of_desc_tab') ?: 'Description',
                        'content' => $content,
                        'table' => $table,
                    ];
                })
                ->filter()
                ->values()
                ->all();
        });
    }
}
