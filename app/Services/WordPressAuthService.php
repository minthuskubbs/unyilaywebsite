<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Verifies customer login credentials against the real WordPress site
 * itself (POSTing to wp-login.php server-side) rather than reimplementing
 * WordPress's own password hashing here — WP 6.8+ uses a bcrypt-based
 * scheme ($wp$2y$...) that's risky to reverse-engineer, so we let WP be
 * the source of truth for "is this password correct" and only read our
 * own `wordpress` DB connection afterwards to fetch the profile to display.
 */
class WordPressAuthService
{
    /**
     * @return array{id: int, login: string, email: string, name: string}|null
     */
    public function attempt(string $login, string $password): ?array
    {
        $login = trim($login);
        if ($login === '' || $password === '') {
            return null;
        }

        $siteUrl = rtrim(config('services.woocommerce.url', ''), '/');
        if ($siteUrl === '') {
            return null;
        }

        try {
            $response = Http::asForm()
                ->withOptions(['allow_redirects' => false])
                ->timeout(15)
                ->post($siteUrl . '/wp-login.php', [
                    'log' => $login,
                    'pwd' => $password,
                    'wp-submit' => 'Log In',
                    'redirect_to' => $siteUrl . '/wp-admin/',
                    'testcookie' => '1',
                ]);
        } catch (\Throwable $e) {
            return null;
        }

        // A successful WP login always issues a wordpress_logged_in_* cookie
        // on this response (regardless of whether the redirect target is
        // actually reachable for this user) — that's the one thing a wrong
        // password can never produce, so it's a reliable success signal.
        $cookies = $response->headers()['Set-Cookie'] ?? [];
        $loggedIn = collect($cookies)->contains(fn ($c) => str_contains($c, 'wordpress_logged_in_'));

        if (!$loggedIn) {
            return null;
        }

        $user = DB::connection('wordpress')
            ->table('users')
            ->where('user_login', $login)
            ->orWhere('user_email', $login)
            ->first();

        if (!$user) {
            return null;
        }

        return [
            'id' => (int) $user->ID,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'name' => $user->display_name ?: $user->user_login,
        ];
    }
}
