<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * Resolves WordPress attachment IDs to public media URLs.
 * Local dev's unyilaywp uploads/ folder isn't synced (too large), so
 * WP_UPLOADS_URL points at production for images — see .env.
 */
class ProductImageResolver
{
    public function urlForAttachment(int $attachmentId): ?string
    {
        $path = DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $attachmentId)
            ->where('meta_key', '_wp_attached_file')
            ->value('meta_value');

        return $path ? $this->baseUrl() . '/' . ltrim($path, '/') : null;
    }

    /**
     * Batched: product_id => listing-size image URL, for a list of product
     * IDs. Avoids N+1 when resolving images for a product listing/grid.
     * Prefers WordPress's "medium" size (400x400) over the full-size
     * original so listing pages load faster, falling back to WooCommerce's
     * own "woocommerce_thumbnail" (247x296, generated for every product
     * image, unlike "medium" which isn't guaranteed on older uploads) and
     * finally the full-size original if neither variant exists.
     *
     * @param  int[]  $productIds
     * @return array<int, string|null>
     */
    public function urlsForProducts(array $productIds): array
    {
        $productIds = array_values(array_unique(array_filter($productIds)));
        if (empty($productIds)) {
            return [];
        }

        $thumbMeta = DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $productIds)
            ->where('meta_key', '_thumbnail_id')
            ->pluck('meta_value', 'post_id');

        $attachmentIds = $thumbMeta->map(fn ($v) => (int) $v)->filter()->unique()->values()->all();
        if (empty($attachmentIds)) {
            return [];
        }

        $pathMap = DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $attachmentIds)
            ->where('meta_key', '_wp_attached_file')
            ->pluck('meta_value', 'post_id');

        $metaMap = DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $attachmentIds)
            ->where('meta_key', '_wp_attachment_metadata')
            ->pluck('meta_value', 'post_id');

        $base = $this->baseUrl();

        return $thumbMeta->mapWithKeys(function ($attachmentId, $productId) use ($pathMap, $metaMap, $base) {
            $attachmentId = (int) $attachmentId;
            $fullPath = $pathMap->get($attachmentId) ?? $pathMap->get((string) $attachmentId);
            if (!$fullPath) {
                return [(int) $productId => null];
            }

            $listingPath = $this->preferListingSize($fullPath, $metaMap->get($attachmentId) ?? $metaMap->get((string) $attachmentId));

            return [(int) $productId => $base . '/' . ltrim($listingPath, '/')];
        })->all();
    }

    /**
     * Swaps a full-size attachment path for the closest-to-400px generated
     * size WordPress actually recorded for it — sizes aren't guaranteed to
     * exist uniformly (older uploads are often missing "medium" entirely),
     * so this tries several roughly-listing-appropriate sizes in order of
     * preference before finally settling for the small 247x296
     * "woocommerce_thumbnail" (present on virtually every product image).
     */
    private function preferListingSize(string $fullPath, ?string $serializedMeta): string
    {
        if (!$serializedMeta) {
            return $fullPath;
        }

        $meta = @unserialize($serializedMeta);
        if (!is_array($meta)) {
            return $fullPath;
        }

        $sizeFile = $meta['sizes']['medium']['file']
            ?? $meta['sizes']['shop_catalog']['file']
            ?? $meta['sizes']['woocommerce_single']['file']
            ?? $meta['sizes']['woocommerce_thumbnail']['file']
            ?? null;

        if (!$sizeFile) {
            return $fullPath;
        }

        $dir = dirname($fullPath);
        return ($dir === '.' ? '' : $dir . '/') . $sizeFile;
    }

    /**
     * Batched: attachment_id => URL, for an arbitrary list of attachment IDs
     * (e.g. a product's gallery, where each ID is already known).
     *
     * @param  int[]  $attachmentIds
     * @return array<int, string>
     */
    public function urlsForAttachments(array $attachmentIds): array
    {
        $attachmentIds = array_values(array_unique(array_filter($attachmentIds)));
        if (empty($attachmentIds)) {
            return [];
        }

        $base = $this->baseUrl();

        return DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $attachmentIds)
            ->where('meta_key', '_wp_attached_file')
            ->pluck('meta_value', 'post_id')
            ->mapWithKeys(fn ($path, $id) => [(int) $id => $base . '/' . ltrim($path, '/')])
            ->all();
    }

    private function baseUrl(): string
    {
        return rtrim(config('woocommerce.uploads_url'), '/');
    }
}
