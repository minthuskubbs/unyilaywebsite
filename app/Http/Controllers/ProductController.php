<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;
use App\Services\SizeChartService;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $products,
        private CategoryService $categories,
        private SizeChartService $sizeCharts,
    ) {
    }

    public function show(string $slug)
    {
        $product = $this->products->findBySlug($slug);
        abort_if(!$product, 404);

        $primaryCategory = $product['categories'][0] ?? null;
        $breadcrumbs = $primaryCategory ? $this->categories->ancestors($primaryCategory['term_id']) : [];
        if ($primaryCategory) {
            $breadcrumbs[] = $primaryCategory;
        }

        $related = $primaryCategory
            ? $this->products->related($product['id'], $primaryCategory['term_id'])
            : [];

        // Any of the product's categories being jewelry is enough — a product
        // could technically be filed under both a jewelry and non-jewelry term.
        $isJewelry = collect($product['categories'])
            ->contains(fn ($cat) => $this->categories->isJewelryCategory($cat['term_id']));

        $sizeCharts = $isJewelry ? $this->sizeCharts->chartsForProduct($product['id']) : [];

        return view('shop.product', [
            'categories' => $this->categories->megaMenuGroups(),
            'product' => $product,
            'breadcrumbs' => $breadcrumbs,
            'related' => $related,
            'isJewelry' => $isJewelry,
            'sizeCharts' => $sizeCharts,
        ]);
    }
}
