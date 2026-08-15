<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

/**
 * Session-backed wishlist (guest, no customer accounts built yet) — a
 * simple in-house replacement for the old site's YITH WooCommerce
 * Wishlist plugin. Only stores product IDs; display data is resolved
 * fresh from the WordPress DB on render via ProductService::productsByIds().
 */
class WishlistService
{
    private const SESSION_KEY = 'wishlist.items';

    public function __construct(private ProductService $products)
    {
    }

    public function toggle(int $productId): bool
    {
        $ids = $this->rawIds();

        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_diff($ids, [$productId]));
            Session::put(self::SESSION_KEY, $ids);
            return false;
        }

        $ids[] = $productId;
        Session::put(self::SESSION_KEY, $ids);
        return true;
    }

    public function remove(int $productId): void
    {
        $ids = array_values(array_diff($this->rawIds(), [$productId]));
        Session::put(self::SESSION_KEY, $ids);
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->rawIds(), true);
    }

    public function count(): int
    {
        return count($this->rawIds());
    }

    /** Enriched wishlist items (name/image/price resolved live). */
    public function contents(): array
    {
        return $this->products->productsByIds($this->rawIds());
    }

    private function rawIds(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }
}
