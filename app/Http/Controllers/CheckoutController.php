<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CategoryService;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cart,
        private CategoryService $categories,
        private WooCommerceService $wooCommerce,
    ) {
    }

    public function index()
    {
        $cart = $this->cart->contents();

        if (empty($cart['lines'])) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', [
            'categories' => $this->categories->megaMenuGroups(),
            'cart' => $cart,
        ]);
    }

    public function store(Request $request)
    {
        $cart = $this->cart->contents();

        if (empty($cart['lines'])) {
            return redirect()->route('cart.index');
        }

        $validated = $request->validate([
            'billing_first_name' => 'required|string|max:255',
            'billing_last_name' => 'required|string|max:255',
            'billing_address_1' => 'required|string|max:255',
            'billing_city' => 'required|string|max:255',
            'billing_phone' => 'required|string|max:40',
            'order_comments' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod',
            'terms' => 'accepted',
        ]);

        $lineItems = array_map(function ($line) {
            $item = ['product_id' => $line['product_id'], 'quantity' => $line['qty']];
            if ($line['variation_id']) {
                $item['variation_id'] = $line['variation_id'];
            }
            return $item;
        }, $cart['lines']);

        $result = $this->wooCommerce->createOrder([
            'payment_method' => 'cod',
            'payment_method_title' => 'Cash on Delivery',
            'set_paid' => false,
            // Tie the order to their WordPress account (if logged in via
            // our /login) so it shows up both in wp-admin and their own
            // "My Account" order history here — guest checkout otherwise.
            'customer_id' => $request->session()->get('customer.id', 0),
            'billing' => [
                'first_name' => $validated['billing_first_name'],
                'last_name' => $validated['billing_last_name'],
                'address_1' => $validated['billing_address_1'],
                'city' => $validated['billing_city'],
                'country' => 'MM',
                'phone' => $validated['billing_phone'],
            ],
            'line_items' => $lineItems,
            'customer_note' => $validated['order_comments'] ?? '',
        ]);

        if (!$result['success']) {
            return back()->withInput()->withErrors(['order' => $result['error']]);
        }

        $this->cart->clear();

        return redirect()->route('checkout.received', ['order' => $result['order']['id']]);
    }

    public function received(int $order)
    {
        $orderData = $this->wooCommerce->getOrder($order);

        return view('checkout.received', [
            'categories' => $this->categories->megaMenuGroups(),
            'order' => $orderData,
        ]);
    }
}
