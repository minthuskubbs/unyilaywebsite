<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\WishlistService;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlist,
        private CategoryService $categories,
    ) {
    }

    public function index()
    {
        return view('wishlist.index', [
            'categories' => $this->categories->megaMenuGroups(),
            'items' => $this->wishlist->contents(),
        ]);
    }

    /** POST /wishlist/toggle — AJAX from the heart icon on product cards/detail pages. */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
        ]);

        $inWishlist = $this->wishlist->toggle((int) $validated['product_id']);

        return response()->json([
            'in_wishlist' => $inWishlist,
            'count' => $this->wishlist->count(),
        ]);
    }

    public function remove(int $productId)
    {
        $this->wishlist->remove($productId);

        return redirect()->route('wishlist.index');
    }
}
