<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Two named rate-limit buckets:
        //   - 'auth' is tight, to slow brute-force attempts on login/register
        //   - 'api'  is per-user once authenticated, generous enough to feel
        //            instant during normal use of the SPA
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->id ?: $request->ip();
            return Limit::perMinute(120)->by($key);
        });
    }
}
