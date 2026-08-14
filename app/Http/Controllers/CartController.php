<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CategoryService $categories,
    ) {
    }

    public function index()
    {
        return view('cart.index', [
            'categories' => $this->categories->megaMenuGroups(),
            'cart' => $this->cart->contents(),
        ]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer',
            'variation_id' => 'nullable|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $this->cart->add(
            (int) $validated['product_id'],
            (int) ($validated['variation_id'] ?? 0),
            (int) ($validated['quantity'] ?? 1),
        );

        return redirect()->route('cart.index')->with('success', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $quantities = $request->input('qty', []);

        foreach ($quantities as $key => $qty) {
            $this->cart->updateQty($key, (int) $qty);
        }

        return redirect()->route('cart.index');
    }

    public function remove(string $key)
    {
        $this->cart->remove($key);

        return redirect()->route('cart.index');
    }
}
