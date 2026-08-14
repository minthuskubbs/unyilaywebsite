<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Session-backed cart (guest checkout, no customer accounts built yet).
 * Only stores {product_id, variation_id, qty} — display data (name,
 * image, live price) is resolved fresh from the WordPress DB on every
 * cart/checkout render via ProductService::cartLines(), so prices never
 * go stale even if the session is long-lived.
 */
class CartService
{
    private const SESSION_KEY = 'cart.items';

    public function __construct(private ProductService $products)
    {
    }

    public function add(int $productId, int $variationId, int $qty = 1): void
    {
        $items = $this->rawItems();
        $key = $this->key($productId, $variationId);

        $items[$key] = [
            'product_id' => $productId,
            'variation_id' => $variationId,
            'qty' => ($items[$key]['qty'] ?? 0) + max(1, $qty),
        ];

        Session::put(self::SESSION_KEY, $items);
    }

    public function updateQty(string $key, int $qty): void
    {
        $items = $this->rawItems();

        if (!isset($items[$key])) {
            return;
        }

        if ($qty < 1) {
            unset($items[$key]);
        } else {
            $items[$key]['qty'] = $qty;
        }

        Session::put(self::SESSION_KEY, $items);
    }

    public function remove(string $key): void
    {
        $items = $this->rawItems();
        unset($items[$key]);
        Session::put(self::SESSION_KEY, $items);
    }

    public function clear(): void
    {
        Session::forget(self::SESSION_KEY);
    }

    public function isEmpty(): bool
    {
        return empty($this->rawItems());
    }

    public function count(): int
    {
        return array_sum(array_column($this->rawItems(), 'qty'));
    }

    /**
     * Enriched cart lines (name/image/price resolved live) + totals.
     */
    public function contents(): array
    {
        $raw = $this->rawItems();
        if (empty($raw)) {
            return ['lines' => [], 'subtotal' => 0.0];
        }

        $lines = $this->products->cartLines(array_values($raw));
        $subtotal = array_sum(array_column($lines, 'line_total'));

        return ['lines' => $lines, 'subtotal' => $subtotal];
    }

    private function key(int $productId, int $variationId): string
    {
        return $productId . ':' . $variationId;
    }

    private function rawItems(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }
}
