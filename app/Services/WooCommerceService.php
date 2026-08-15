<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceService
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl        = rtrim(config('services.woocommerce.url', ''), '/');
        $this->consumerKey    = config('services.woocommerce.key', '');
        $this->consumerSecret = config('services.woocommerce.secret', '');
    }

    private function client()
    {
        return Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                   ->acceptJson()
                   ->timeout(15);
    }

    private function endpoint(string $path): string
    {
        return $this->baseUrl . '/wp-json/wc/v3/' . ltrim($path, '/');
    }

    public function isConfigured(): bool
    {
        return !empty($this->consumerKey) && !empty($this->consumerSecret);
    }

    // ── Products ─────────────────────────────────────────────

    public function getProduct(int $id): array
    {
        $response = $this->client()->get($this->endpoint("products/{$id}"));
        return $response->successful() ? $response->json() : [];
    }

    public function createProduct(array $data): array
    {
        $response = $this->client()->post($this->endpoint('products'), $data);
        return $response->json();
    }

    public function updateProduct(int $id, array $data): array
    {
        $response = $this->client()->put($this->endpoint("products/{$id}"), $data);
        return $response->json();
    }

    public function deleteProduct(int $id): bool
    {
        $response = $this->client()->delete($this->endpoint("products/{$id}"), ['force' => true]);
        return $response->successful();
    }

    // ── Variations ───────────────────────────────────────────

    public function getVariations(int $productId): array
    {
        $response = $this->client()->get($this->endpoint("products/{$productId}/variations"), ['per_page' => 100]);
        return $response->successful() ? $response->json() : [];
    }

    public function createVariation(int $productId, array $data): array
    {
        $response = $this->client()->post($this->endpoint("products/{$productId}/variations"), $data);
        return $response->json();
    }

    public function updateVariation(int $productId, int $variationId, array $data): array
    {
        $response = $this->client()->put($this->endpoint("products/{$productId}/variations/{$variationId}"), $data);
        return $response->json();
    }

    public function deleteVariation(int $productId, int $variationId): bool
    {
        $response = $this->client()->delete($this->endpoint("products/{$productId}/variations/{$variationId}"), ['force' => true]);
        return $response->successful();
    }

    // ── Attributes ───────────────────────────────────────────

    public function getAttributes(): array
    {
        $response = $this->client()->get($this->endpoint('products/attributes'), ['per_page' => 100]);
        return $response->successful() ? $response->json() : [];
    }

    public function getAttributeTerms(int $attributeId): array
    {
        $response = $this->client()->get($this->endpoint("products/attributes/{$attributeId}/terms"), ['per_page' => 100]);
        return $response->successful() ? $response->json() : [];
    }

    // ── Orders ───────────────────────────────────────────────

    /**
     * Creates a real WooCommerce order via the REST API (not a direct DB
     * insert) so WooCommerce's own logic runs: stock reduction, order
     * numbering, order-status emails, tax/shipping calculation from
     * line_items alone (we never send client-supplied prices).
     *
     * Requires HTTPS in production — WooCommerce's REST API only accepts
     * Basic Auth over SSL; see .env comments on WC_CONSUMER_KEY for the
     * local-dev limitation (plain HTTP needs full OAuth 1.0a instead).
     *
     * @return array{success: bool, order: array|null, error: string|null}
     */
    public function createOrder(array $data): array
    {
        try {
            $response = $this->client()->post($this->endpoint('orders'), $data);
        } catch (\Throwable $e) {
            Log::error('WooCommerce order creation failed to connect', ['exception' => $e->getMessage()]);
            return ['success' => false, 'order' => null, 'error' => 'Could not reach the order system. Please try again shortly.'];
        }

        if (!$response->successful()) {
            Log::error('WooCommerce order creation failed', ['status' => $response->status(), 'body' => $response->body()]);
            $message = $response->json('message') ?? 'The order could not be placed. Please try again.';
            return ['success' => false, 'order' => null, 'error' => $message];
        }

        return ['success' => true, 'order' => $response->json(), 'error' => null];
    }

    public function getOrder(int $orderId): array
    {
        $response = $this->client()->get($this->endpoint("orders/{$orderId}"));
        return $response->successful() ? $response->json() : [];
    }

    /** Orders belonging to a specific customer (WP user ID), newest first. */
    public function getOrdersForCustomer(int $customerId, int $perPage = 20): array
    {
        $response = $this->client()->get($this->endpoint('orders'), [
            'customer' => $customerId,
            'per_page' => $perPage,
            'orderby' => 'date',
            'order' => 'desc',
        ]);

        return $response->successful() ? $response->json() : [];
    }

    // ── Customers ────────────────────────────────────────────

    public function getCustomer(int $id): array
    {
        $response = $this->client()->get($this->endpoint("customers/{$id}"));
        return $response->successful() ? $response->json() : [];
    }

    public function updateCustomer(int $id, array $data): array
    {
        $response = $this->client()->put($this->endpoint("customers/{$id}"), $data);
        return $response->successful() ? $response->json() : [];
    }

    // ── Categories ───────────────────────────────────────────

    public function getCategories(int $perPage = 100): array
    {
        $response = $this->client()->get($this->endpoint('products/categories'), ['per_page' => $perPage]);
        return $response->successful() ? $response->json() : [];
    }

    public function createCategory(array $data): array
    {
        $response = $this->client()->post($this->endpoint('products/categories'), $data);
        return $response->json();
    }

    public function updateCategory(int $id, array $data): array
    {
        $response = $this->client()->put($this->endpoint("products/categories/{$id}"), $data);
        return $response->json();
    }

    public function deleteCategory(int $id): bool
    {
        $response = $this->client()->delete($this->endpoint("products/categories/{$id}"), ['force' => true]);
        return $response->successful();
    }
}
