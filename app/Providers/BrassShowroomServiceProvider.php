<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

final class BrassShowroomServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        RateLimiter::for('mcp-showroom', function (Request $request): Limit {
            return Limit::perMinute(30)->by(
                (string) ($request->user()?->getAuthIdentifier() ?? $request->ip())
            );
        });
    }
}
