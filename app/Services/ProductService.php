<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProductService
{
    private const CACHE_TTL = 900; // 15 minutes

    public function __construct(private ProductImageResolver $images)
    {
    }

    /**
     * Full single-product read for the product detail page: gallery,
     * description, categories, and — for variable products — the
     * attribute selectors + per-variation price/stock data needed to
     * drive the add-to-cart form client-side.
     */
    public function findBySlug(string $slug): ?array
    {
        return Cache::remember("products.detail.{$slug}", self::CACHE_TTL, function () use ($slug) {
            $post = DB::connection('wordpress')
                ->table('posts')
                ->where('post_name', $slug)
                ->where('post_type', 'product')
                ->where('post_status', 'publish')
                ->first();

            if (!$post) {
                return null;
            }

            $meta = DB::connection('wordpress')
                ->table('postmeta')
                ->where('post_id', $post->ID)
                ->pluck('meta_value', 'meta_key');

            $type = DB::connection('wordpress')
                ->table('term_relationships as tr')
                ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->join('terms as t', 'tt.term_id', '=', 't.term_id')
                ->where('tr.object_id', $post->ID)
                ->where('tt.taxonomy', 'product_type')
                ->value('t.slug') ?? 'simple';

            $categories = DB::connection('wordpress')
                ->table('term_relationships as tr')
                ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->join('terms as t', 'tt.term_id', '=', 't.term_id')
                ->where('tr.object_id', $post->ID)
                ->where('tt.taxonomy', 'product_cat')
                ->select('t.term_id', 't.name', 't.slug')
                ->get()
                ->map(fn ($c) => ['term_id' => $c->term_id, 'name' => $c->name, 'slug' => $c->slug])
                ->all();

            $galleryIds = array_filter(array_map('intval', array_filter(explode(',', $meta->get('_product_image_gallery', '')))));
            $thumbnailId = (int) $meta->get('_thumbnail_id', 0);
            $allImageIds = array_values(array_unique(array_filter([$thumbnailId, ...$galleryIds])));
            $imageUrls = $this->images->urlsForAttachments($allImageIds);
            $images = array_values(array_filter(array_map(fn ($id) => $imageUrls[$id] ?? null, $allImageIds)));

            $attributes = [];
            $variations = [];
            $price = null;

            if ($type === 'variable') {
                $attributes = $this->resolveAttributes($post->ID, $meta->get('_product_attributes'));
                $variations = $this->resolveVariations($post->ID);
                $prices = collect($variations)->pluck('price')->filter(fn ($p) => $p !== null)->map(fn ($p) => (float) $p);
                if ($prices->isNotEmpty()) {
                    $min = $prices->min();
                    $max = $prices->max();
                    $price = ['min' => (string) $min, 'max' => (string) $max, 'is_range' => $min != $max];
                }
            } else {
                $priceValue = $meta->get('_price', $meta->get('_regular_price'));
                if ($priceValue !== null && $priceValue !== '') {
                    $price = ['min' => $priceValue, 'max' => $priceValue, 'is_range' => false];
                }
            }

            return [
                'id' => $post->ID,
                'name' => $post->post_title,
                'slug' => $post->post_name,
                'sku' => $meta->get('_sku', ''),
                'description' => $post->post_content,
                'short_description' => $post->post_excerpt,
                'images' => $images,
                'type' => $type,
                'price' => $price,
                'stock_status' => $meta->get('_stock_status', 'instock'),
                'categories' => $categories,
                'attributes' => $attributes,
                'variations' => $variations,
            ];
        });
    }

    /**
     * Related products: other published products sharing the primary category.
     */
    public function related(int $productId, int $categoryId, int $limit = 8): array
    {
        return Cache::remember("products.related.{$productId}.{$limit}", self::CACHE_TTL, function () use ($productId, $categoryId, $limit) {
            $rows = DB::connection('wordpress')
                ->table('posts as p')
                ->join('term_relationships as tr', 'tr.object_id', '=', 'p.ID')
                ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->where('tt.term_id', $categoryId)
                ->where('tt.taxonomy', 'product_cat')
                ->where('p.post_type', 'product')
                ->where('p.post_status', 'publish')
                ->where('p.ID', '!=', $productId)
                ->select('p.ID', 'p.post_title', 'p.post_name')
                ->distinct()
                ->limit($limit)
                ->get();

            $ids = $rows->pluck('ID')->all();
            $images = $this->images->urlsForProducts($ids);
            $typeMap = $this->productTypes($ids);
            $priceMap = $this->priceRanges($ids, $typeMap);

            return $rows->map(fn ($row) => [
                'id' => $row->ID,
                'name' => $row->post_title,
                'slug' => $row->post_name,
                'image' => $images[$row->ID] ?? null,
                'type' => $typeMap[$row->ID] ?? 'simple',
                'price' => $priceMap[$row->ID] ?? null,
            ])->all();
        });
    }

    /**
     * Parses the serialized _product_attributes meta into taxonomy attributes
     * with their selectable terms, for rendering the variation selector UI.
     */
    private function resolveAttributes(int $productId, ?string $serialized): array
    {
        if (!$serialized) {
            return [];
        }

        $raw = @unserialize($serialized);
        if (!is_array($raw)) {
            return [];
        }

        $attributes = [];

        foreach ($raw as $taxonomy => $attr) {
            if (empty($attr['is_taxonomy'])) {
                // Custom (non-taxonomy) attribute: value is a pipe-separated string.
                $values = array_values(array_filter(array_map('trim', explode('|', $attr['value'] ?? ''))));
                $attributes[$taxonomy] = [
                    'label' => $attr['name'] ?? $taxonomy,
                    'is_variation' => !empty($attr['is_variation']),
                    'terms' => array_map(fn ($v) => ['slug' => $v, 'name' => $v], $values),
                ];
                continue;
            }

            $terms = DB::connection('wordpress')
                ->table('term_relationships as tr')
                ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                ->join('terms as t', 'tt.term_id', '=', 't.term_id')
                ->where('tr.object_id', $productId)
                ->where('tt.taxonomy', $taxonomy)
                ->select('t.term_id', 't.name', 't.slug')
                ->orderBy('t.name')
                ->get();

            $label = ucwords(str_replace(['-', '_'], ' ', preg_replace('/^pa_/', '', $taxonomy)));

            $attributes[$taxonomy] = [
                'label' => $label,
                'is_variation' => !empty($attr['is_variation']),
                'terms' => $terms->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->all(),
            ];
        }

        return $attributes;
    }

    /**
     * Each variation's price/stock + its selected attribute values
     * (attribute_pa_xxx meta keys), for the client-side variation picker.
     */
    private function resolveVariations(int $productId): array
    {
        $variations = DB::connection('wordpress')
            ->table('posts')
            ->where('post_parent', $productId)
            ->where('post_type', 'product_variation')
            ->where('post_status', 'publish')
            ->orderBy('menu_order')
            ->get(['ID']);

        if ($variations->isEmpty()) {
            return [];
        }

        $variationIds = $variations->pluck('ID')->all();

        $meta = DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $variationIds)
            ->get(['post_id', 'meta_key', 'meta_value'])
            ->groupBy('post_id');

        // Variations only sometimes have their own distinct image (WooCommerce
        // stores it as a normal _thumbnail_id on the variation post) — most
        // fall back to the parent product's gallery instead.
        $thumbIds = $meta->map(fn ($rows) => (int) $rows->firstWhere('meta_key', '_thumbnail_id')?->meta_value)
            ->filter()
            ->unique()
            ->values()
            ->all();
        $variationImages = $this->images->urlsForAttachments($thumbIds);

        return $variations->map(function ($v) use ($meta, $variationImages) {
            $rows = $meta->get($v->ID, collect())->pluck('meta_value', 'meta_key');
            $attributes = [];

            foreach ($rows as $key => $value) {
                if (str_starts_with($key, 'attribute_')) {
                    $attributes[substr($key, strlen('attribute_'))] = $value;
                }
            }

            $thumbId = (int) $rows->get('_thumbnail_id', 0);

            return [
                'id' => $v->ID,
                'price' => $rows->get('_price', $rows->get('_regular_price')),
                'stock_status' => $rows->get('_stock_status', 'instock'),
                'attributes' => $attributes,
                'image' => $thumbId ? ($variationImages[$thumbId] ?? null) : null,
            ];
        })->all();
    }

    /**
     * Resolves cart/checkout line items (name, image, live price, variation
     * label) for a set of {product_id, variation_id, qty} entries. Always
     * reads fresh from the DB (not cached) so prices/stock never go stale.
     *
     * @param  array<int, array{product_id: int, variation_id: int, qty: int}>  $items
     */
    public function cartLines(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        $productIds = array_values(array_unique(array_column($items, 'product_id')));
        $variationIds = array_values(array_filter(array_unique(array_column($items, 'variation_id'))));

        $products = DB::connection('wordpress')
            ->table('posts')
            ->whereIn('ID', $productIds)
            ->select('ID', 'post_title', 'post_name')
            ->get()
            ->keyBy('ID');

        $productImages = $this->images->urlsForProducts($productIds);

        $simplePrices = DB::connection('wordpress')
            ->table('postmeta')
            ->whereIn('post_id', $productIds)
            ->where('meta_key', '_price')
            ->pluck('meta_value', 'post_id');

        $variationMeta = [];
        if (!empty($variationIds)) {
            $variationMeta = DB::connection('wordpress')
                ->table('postmeta')
                ->whereIn('post_id', $variationIds)
                ->get(['post_id', 'meta_key', 'meta_value'])
                ->groupBy('post_id')
                ->map(fn ($rows) => $rows->pluck('meta_value', 'meta_key'))
                ->all();
        }

        $lines = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                continue;
            }

            $variationId = $item['variation_id'];
            $price = $simplePrices->get($item['product_id']);
            $variationLabel = null;

            if ($variationId && isset($variationMeta[$variationId])) {
                $vMeta = $variationMeta[$variationId];
                $price = $vMeta->get('_price', $vMeta->get('_regular_price', $price));
                $variationLabel = $this->variationLabel($vMeta);
            }

            $unitPrice = (float) ($price ?? 0);
            $qty = max(1, (int) $item['qty']);

            $lines[] = [
                'key' => $item['product_id'] . ':' . $variationId,
                'product_id' => $item['product_id'],
                'variation_id' => $variationId,
                'name' => $product->post_title,
                'slug' => $product->post_name,
                'variation_label' => $variationLabel,
                'image' => $productImages[$item['product_id']] ?? null,
                'price' => $unitPrice,
                'qty' => $qty,
                'line_total' => $unitPrice * $qty,
            ];
        }

        return $lines;
    }

    /** Human-readable "Length: 16'', Color: Silver" from a variation's attribute_* meta. */
    private function variationLabel($vMeta): ?string
    {
        $parts = [];

        foreach ($vMeta as $key => $value) {
            if (!str_starts_with($key, 'attribute_') || $value === '') {
                continue;
            }

            $taxonomy = substr($key, strlen('attribute_'));
            $label = ucwords(str_replace(['-', '_'], ' ', preg_replace('/^pa_/', '', $taxonomy)));

            $termName = str_starts_with($taxonomy, 'pa_')
                ? DB::connection('wordpress')->table('terms')->where('slug', $value)->value('name')
                : null;

            $parts[] = $label . ': ' . ($termName ?? $value);
        }

        return empty($parts) ? null : implode(', ', $parts);
    }

    /**
     * Best-selling published products (by WooCommerce's _total_sales meta), for the
     * homepage "Popular Products" carousel.
     */
    public function popular(int $limit = 8): array
    {
        return Cache::remember("products.popular.{$limit}", self::CACHE_TTL, function () use ($limit) {
            $rows = DB::connection('wordpress')
                ->table('posts as p')
                ->join('postmeta as sales', function ($j) {
                    $j->on('sales.post_id', '=', 'p.ID')->where('sales.meta_key', 'total_sales');
                })
                ->where('p.post_type', 'product')
                ->where('p.post_status', 'publish')
                ->select('p.ID', 'p.post_title', 'p.post_name')
                ->orderByDesc(DB::raw(DB::connection('wordpress')->getTablePrefix() . 'sales.meta_value + 0'))
                ->limit($limit)
                ->get();

            $ids = $rows->pluck('ID')->all();
            $images = $this->images->urlsForProducts($ids);

            return $rows->map(fn ($row) => [
                'id' => $row->ID,
                'name' => $row->post_title,
                'slug' => $row->post_name,
                'image' => $images[$row->ID] ?? null,
            ])->all();
        });
    }

    /**
     * Paginated product grid for a category archive (or the whole shop if
     * $categoryId is null). Mirrors the reference site: 12 per page,
     * ordered by menu_order then title (WooCommerce's default), variable
     * products show a min–max price range.
     */
    /**
     * @param  int|int[]|null  $categoryId  A single category, a set of categories
     *      (e.g. the "Big Items" bucket), or null for genuinely every product.
     */
    public function paginate(int|array|null $categoryId, int $page = 1, int $perPage = 12, ?string $search = null): array
    {
        $query = DB::connection('wordpress')
            ->table('posts as p')
            ->where('p.post_type', 'product')
            ->where('p.post_status', 'publish');

        if ($categoryId !== null) {
            $categoryIds = is_array($categoryId) ? $categoryId : [$categoryId];
            $query->whereIn('p.ID', function ($q) use ($categoryIds) {
                $q->select('tr.object_id')
                    ->from('term_relationships as tr')
                    ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
                    ->whereIn('tt.term_id', $categoryIds)
                    ->where('tt.taxonomy', 'product_cat');
            });
        }

        if ($search !== null && $search !== '') {
            $query->where('p.post_title', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%');
        }

        $total = (clone $query)->count();

        $rows = $query
            ->select('p.ID', 'p.post_title', 'p.post_name')
            ->orderBy('p.menu_order')
            ->orderBy('p.post_title')
            ->forPage($page, $perPage)
            ->get();

        $productIds = $rows->pluck('ID')->all();
        $images = $this->images->urlsForProducts($productIds);
        $typeMap = $this->productTypes($productIds);
        $priceMap = $this->priceRanges($productIds, $typeMap);

        $items = $rows->map(fn ($row) => [
            'id' => $row->ID,
            'name' => $row->post_title,
            'slug' => $row->post_name,
            'image' => $images[$row->ID] ?? null,
            'type' => $typeMap[$row->ID] ?? 'simple',
            'price' => $priceMap[$row->ID] ?? null,
        ])->all();

        return [
            'items' => $items,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'lastPage' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    /**
     * Live-search preview: a handful of matching products (name + image +
     * price) for the header search dropdown, as the user types.
     */
    public function search(string $term, int $limit = 6): array
    {
        $term = trim($term);
        if ($term === '') {
            return [];
        }

        $rows = DB::connection('wordpress')
            ->table('posts as p')
            ->where('p.post_type', 'product')
            ->where('p.post_status', 'publish')
            ->where('p.post_title', 'like', '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%')
            ->select('p.ID', 'p.post_title', 'p.post_name')
            ->orderBy('p.post_title')
            ->limit($limit)
            ->get();

        $ids = $rows->pluck('ID')->all();
        $images = $this->images->urlsForProducts($ids);
        $typeMap = $this->productTypes($ids);
        $priceMap = $this->priceRanges($ids, $typeMap);

        return $rows->map(fn ($row) => [
            'id' => $row->ID,
            'name' => $row->post_title,
            'slug' => $row->post_name,
            'image' => $images[$row->ID] ?? null,
            'price' => $priceMap[$row->ID] ?? null,
        ])->all();
    }

    /**
     * Resolves a set of product IDs (e.g. from the wishlist session) into
     * display data, preserving the given order and silently dropping any
     * that no longer exist / aren't published.
     */
    public function productsByIds(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        $rows = DB::connection('wordpress')
            ->table('posts')
            ->whereIn('ID', $productIds)
            ->where('post_type', 'product')
            ->where('post_status', 'publish')
            ->select('ID', 'post_title', 'post_name')
            ->get()
            ->keyBy('ID');

        $ids = array_values(array_intersect($productIds, $rows->keys()->all()));
        $images = $this->images->urlsForProducts($ids);
        $typeMap = $this->productTypes($ids);
        $priceMap = $this->priceRanges($ids, $typeMap);

        return array_values(array_filter(array_map(function ($id) use ($rows, $images, $typeMap, $priceMap) {
            $row = $rows->get($id);
            if (!$row) {
                return null;
            }

            return [
                'id' => $row->ID,
                'name' => $row->post_title,
                'slug' => $row->post_name,
                'image' => $images[$row->ID] ?? null,
                'type' => $typeMap[$row->ID] ?? 'simple',
                'price' => $priceMap[$row->ID] ?? null,
            ];
        }, $productIds)));
    }

    /** @return array<int, string> product_id => 'simple'|'variable' etc. */
    private function productTypes(array $productIds): array
    {
        if (empty($productIds)) {
            return [];
        }

        return DB::connection('wordpress')
            ->table('term_relationships as tr')
            ->join('term_taxonomy as tt', 'tr.term_taxonomy_id', '=', 'tt.term_taxonomy_id')
            ->join('terms as t', 'tt.term_id', '=', 't.term_id')
            ->whereIn('tr.object_id', $productIds)
            ->where('tt.taxonomy', 'product_type')
            ->pluck('t.slug', 'tr.object_id')
            ->all();
    }

    /**
     * @return array<int, array{min: string, max: string, is_range: bool}>
     */
    private function priceRanges(array $productIds, array $typeMap): array
    {
        if (empty($productIds)) {
            return [];
        }

        $simpleIds = array_keys(array_filter($typeMap, fn ($t) => $t !== 'variable'));
        $variableIds = array_keys(array_filter($typeMap, fn ($t) => $t === 'variable'));
        // Any product not in $typeMap (no product_type term row) is treated as simple.
        $simpleIds = array_values(array_unique(array_merge($simpleIds, array_diff($productIds, $simpleIds, $variableIds))));

        $result = [];

        if (!empty($simpleIds)) {
            $prices = DB::connection('wordpress')
                ->table('postmeta')
                ->whereIn('post_id', $simpleIds)
                ->where('meta_key', '_price')
                ->pluck('meta_value', 'post_id');

            foreach ($simpleIds as $id) {
                $price = $prices->get($id);
                if ($price !== null && $price !== '') {
                    $result[$id] = ['min' => $price, 'max' => $price, 'is_range' => false];
                }
            }
        }

        if (!empty($variableIds)) {
            $variationRows = DB::connection('wordpress')
                ->table('posts as v')
                ->join('postmeta as pm', function ($j) {
                    $j->on('pm.post_id', '=', 'v.ID')->where('pm.meta_key', '_price');
                })
                ->whereIn('v.post_parent', $variableIds)
                ->where('v.post_type', 'product_variation')
                ->select('v.post_parent', 'pm.meta_value')
                ->get()
                ->groupBy('post_parent');

            foreach ($variableIds as $id) {
                $prices = ($variationRows->get($id) ?? collect())
                    ->pluck('meta_value')
                    ->filter(fn ($p) => $p !== null && $p !== '')
                    ->map(fn ($p) => (float) $p);

                if ($prices->isNotEmpty()) {
                    $min = $prices->min();
                    $max = $prices->max();
                    $result[$id] = [
                        'min' => (string) $min,
                        'max' => (string) $max,
                        'is_range' => $min != $max,
                    ];
                }
            }
        }

        return $result;
    }
}
