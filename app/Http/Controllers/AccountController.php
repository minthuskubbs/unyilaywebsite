<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __construct(
        private WooCommerceService $wooCommerce,
        private CategoryService $categories,
    ) {
    }

    public function dashboard(Request $request)
    {
        $customer = $request->session()->get('customer');
        $orders = $this->wooCommerce->getOrdersForCustomer($customer['id'], 5);

        return view('account.dashboard', [
            'categories' => $this->categories->megaMenuGroups(),
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function orders(Request $request)
    {
        $customer = $request->session()->get('customer');
        $orders = $this->wooCommerce->getOrdersForCustomer($customer['id'], 50);

        return view('account.orders', [
            'categories' => $this->categories->megaMenuGroups(),
            'customer' => $customer,
            'orders' => $orders,
        ]);
    }

    public function orderShow(Request $request, int $id)
    {
        $customer = $request->session()->get('customer');
        $order = $this->wooCommerce->getOrder($id);

        // Only ever show an order that actually belongs to the logged-in
        // customer — WooCommerce's API has no per-customer scoping on the
        // single-order endpoint, so this check is what enforces it.
        abort_if(empty($order) || (int) ($order['customer_id'] ?? 0) !== $customer['id'], 404);

        return view('account.order-show', [
            'categories' => $this->categories->megaMenuGroups(),
            'customer' => $customer,
            'order' => $order,
        ]);
    }

    public function editAddress(Request $request)
    {
        $customer = $request->session()->get('customer');
        $wcCustomer = $this->wooCommerce->getCustomer($customer['id']);

        return view('account.address', [
            'categories' => $this->categories->megaMenuGroups(),
            'customer' => $customer,
            'billing' => $wcCustomer['billing'] ?? [],
        ]);
    }

    public function updateAddress(Request $request)
    {
        $customer = $request->session()->get('customer');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'address_1' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'phone' => 'required|string|max:40',
            'email' => 'nullable|email|max:255',
        ]);

        $this->wooCommerce->updateCustomer($customer['id'], [
            'billing' => [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'address_1' => $validated['address_1'],
                'city' => $validated['city'],
                'phone' => $validated['phone'],
                'email' => $validated['email'] ?? $customer['email'],
                'country' => 'MM',
            ],
        ]);

        return redirect()->route('account.address')->with('success', 'Your billing details have been updated.');
    }
}
