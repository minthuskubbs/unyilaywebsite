<?php

namespace App\Http\Controllers;

use App\Mail\ResetPasswordMail;
use App\Services\CategoryService;
use App\Services\WooCommerceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Resets a real WordPress customer's password (via the WooCommerce REST
 * API, so WP's own password hashing is used — same reasoning as
 * WordPressAuthService/register()). The one-time code lives in this app's
 * own `password_reset_tokens` table (Laravel's default), keyed by the
 * WordPress account's email — WordPress is never the code's store.
 *
 * Uses a plain 6-digit emailed code rather than a link: a styled HTML
 * email with a reset-link button was landing nowhere (not even spam) on
 * the production mail server, while a plain-text email delivered fine —
 * a short numeric code keeps the email about as plain as that.
 */
class PasswordResetController extends Controller
{
    private const CODE_TTL_MINUTES = 15;

    public function __construct(
        private CategoryService $categories,
        private WooCommerceService $wooCommerce,
    ) {
    }

    public function showRequestForm(Request $request)
    {
        return view('auth.forgot-password', [
            'categories' => $this->categories->megaMenuGroups(),
        ]);
    }

    public function sendResetLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $user = DB::connection('wordpress')->table('users')->where('user_email', $validated['email'])->first();

        Log::info('Password reset requested', ['email' => $validated['email'], 'account_found' => (bool) $user]);

        // Always show the same success message whether or not the email is a
        // real account, so this form can't be used to check who has an
        // account here (standard practice for any "forgot password" form).
        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->user_email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            try {
                Mail::to($user->user_email)->send(new ResetPasswordMail($code, $user->display_name ?: $user->user_login));
                Log::info('Password reset email sent', ['email' => $user->user_email]);
            } catch (\Throwable $e) {
                Log::error('Password reset email failed to send', ['email' => $user->user_email, 'exception' => $e->getMessage()]);
            }
        }

        return redirect()->route('password.reset', ['email' => $validated['email']])
            ->with('status', 'If an account exists for that email, we\'ve sent a 6-digit code — enter it below.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', [
            'categories' => $this->categories->megaMenuGroups(),
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        $invalid = !$record
            || !Hash::check($validated['code'], $record->token)
            || now()->diffInMinutes($record->created_at) > self::CODE_TTL_MINUTES;

        if ($invalid) {
            return back()->withErrors(['code' => 'This code is invalid or has expired. Please request a new one.']);
        }

        $wpUser = DB::connection('wordpress')->table('users')->where('user_email', $validated['email'])->first();
        if (!$wpUser) {
            return back()->withErrors(['code' => 'This account could not be found.']);
        }

        $result = $this->wooCommerce->updateCustomer((int) $wpUser->ID, ['password' => $validated['password']]);

        if (empty($result)) {
            return back()->withErrors(['password' => 'Could not reset your password right now. Please try again shortly.']);
        }

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now log in.');
    }
}
