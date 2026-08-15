<?php

namespace App\Http\Controllers;

use App\Services\CategoryService;
use App\Services\WordPressAuthService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private WordPressAuthService $auth,
        private CategoryService $categories,
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

    public function logout(Request $request)
    {
        $request->session()->forget('customer');
        $request->session()->regenerate();

        return redirect()->route('home');
    }
}
