<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\WooCommerceService;
use App\Services\WordPressAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private WordPressAuthService $auth,
        private CategoryService $categories,
        private WooCommerceService $wooCommerce,
    ) {
    }

    public function showLogin(Request $request)
    {
        if ($request->session()->has('customer')) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.login', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        $user = $this->auth->attempt($validated['login'], $validated['password']);

        if (!$user) {
            return back()->withInput(['login' => $validated['login']])->withErrors([
                'login' => 'Incorrect username/email or password.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('customer', $user);

        return redirect()->intended(route('account.dashboard'));
    }

    public function showRegister(Request $request)
    {
        if ($request->session()->has('customer')) {
            return redirect()->route('account.dashboard');
        }

        return view('auth.register', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $result = $this->wooCommerce->createCustomer([
            'email' => $validated['email'],
            'first_name' => $validated['name'],
            'password' => $validated['password'],
        ]);

        if (!$result['success']) {
            return back()->withInput($request->only('name', 'email'))->withErrors([
                'email' => $result['error'],
            ]);
        }

        $customer = $result['customer'];
        $request->session()->regenerate();
        $request->session()->put('customer', [
            'id' => (int) $customer['id'],
            'login' => $customer['username'] ?? $customer['email'],
            'email' => $customer['email'],
            'name' => trim(($customer['first_name'] ?? '') . ' ' . ($customer['last_name'] ?? '')) ?: ($customer['username'] ?? $customer['email']),
        ]);

        return redirect()->route('account.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('customer');
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
