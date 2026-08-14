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
     * Batched: product_id => featured image URL, for a list of product IDs.
     * Avoids N+1 when resolving images for a product listing/grid.
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

        $base = $this->baseUrl();

        return $thumbMeta->mapWithKeys(function ($attachmentId, $productId) use ($pathMap, $base) {
            $path = $pathMap->get((int) $attachmentId) ?? $pathMap->get((string) $attachmentId);
            return [(int) $productId => $path ? $base . '/' . ltrim($path, '/') : null];
        })->all();
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
