<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function __construct(
        private CategoryService $categories,
        private ProductService $products,
    ) {
    }

    /**
     * GET /shop — the "Big Items" bucket (all 40 top-level non-jewelry
     * categories combined), matching the mega-menu's "Big Items" entry.
     * Not literally every product — that would also pull in jewelry.
     */
    public function index(Request $request)
    {
        $page = max(1, (int) $request->query('product-page', 1));
        $listing = $this->products->paginate($this->categories->bigItemsCategoryIds(), $page);

        return view('shop.archive', [
            'categories' => $this->categories->megaMenuGroups(),
            'sidebar' => $this->categories->sidebarTree(),
            'breadcrumbs' => [],
            'title' => 'Big Items',
            'listing' => $listing,
            'baseUrl' => url('/shop'),
        ]);
    }

    /** GET /product-category/{slug} — either a subcategory tile grid or a product archive. */
    public function category(Request $request, string $slug)
    {
        $category = $this->categories->findBySlug($slug);
        abort_if(!$category, 404);

        $children = $this->categories->children($category['term_id']);
        $breadcrumbs = $this->categories->ancestors($category['term_id']);
        $menuCategories = $this->categories->megaMenuGroups();

        $isJewelry = $this->categories->isJewelryCategory($category['term_id']);

        if (!empty($children)) {
            return view('shop.category-index', [
                'categories' => $menuCategories,
                'category' => $category,
                'children' => $children,
                'breadcrumbs' => $breadcrumbs,
                'title' => $category['name'],
                'isJewelry' => $isJewelry,
            ]);
        }

        $page = max(1, (int) $request->query('product-page', 1));
        $listing = $this->products->paginate($category['term_id'], $page);

        return view('shop.archive', [
            'categories' => $menuCategories,
            'sidebar' => $this->categories->sidebarTree(),
            'breadcrumbs' => [...$breadcrumbs, ['term_id' => $category['term_id'], 'name' => $category['name'], 'slug' => $category['slug']]],
            'title' => $category['name'],
            'listing' => $listing,
            'baseUrl' => url('/product-category/' . $category['slug']),
            'isJewelry' => $isJewelry,
        ]);
    }
}
