<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\WooCommerceService;
use App\Services\WordPressAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

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

    public function googleRedirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Logs an existing customer in, or creates a new WooCommerce customer
     * account (the "customer" WordPress role, same as a normal storefront
     * registration), matched/linked by email address.
     */
    public function googleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            Log::error('Google sign-in failed', ['exception' => $e->getMessage()]);
            return redirect()->route('login')->withErrors(['login' => 'Google sign-in failed. Please try again.']);
        }

        $email = $googleUser->getEmail();
        if (!$email) {
            return redirect()->route('login')->withErrors(['login' => 'Your Google account has no email address to sign in with.']);
        }

        $existing = DB::connection('wordpress')->table('users')->where('user_email', $email)->first();

        if ($existing) {
            $customer = [
                'id' => (int) $existing->ID,
                'login' => $existing->user_login,
                'email' => $existing->user_email,
                'name' => $existing->display_name ?: $existing->user_login,
            ];
        } else {
            $name = trim((string) $googleUser->getName()) ?: explode('@', $email)[0];
            $nameParts = explode(' ', $name, 2);

            $result = $this->wooCommerce->createCustomer([
                'email' => $email,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'password' => Str::random(32),
                'meta_data' => [
                    ['key' => 'google_id', 'value' => $googleUser->getId()],
                ],
            ]);

            if (!$result['success']) {
                return redirect()->route('login')->withErrors(['login' => $result['error']]);
            }

            $wcCustomer = $result['customer'];
            $customer = [
                'id' => (int) $wcCustomer['id'],
                'login' => $wcCustomer['username'] ?? $email,
                'email' => $wcCustomer['email'],
                'name' => trim(($wcCustomer['first_name'] ?? '') . ' ' . ($wcCustomer['last_name'] ?? '')) ?: $name,
            ];
        }

        $request->session()->regenerate();
        $request->session()->put('customer', $customer);

        return redirect()->intended(route('account.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('customer');
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
