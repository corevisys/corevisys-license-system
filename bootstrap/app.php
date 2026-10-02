<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureAdmin::class,
        ]);

        $middleware->web(append: [
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);

        $trustedProxies = env('TRUSTED_PROXIES');

        if ($trustedProxies === '*') {
            $proxies = '*';
        } elseif (!empty($trustedProxies)) {
            $proxies = array_map('trim', explode(',', $trustedProxies));
        } else {
            // TRUSTED_PROXIES is unset. In production this means every request that
            // arrives through a load-balancer will use the LB's internal socket IP as
            // the "client" IP, causing ALL end-user clients to share one fallback-scan
            // rate-limit bucket after as few as 30 scans.
            $currentEnv = env('APP_ENV', 'production');
            if (in_array($currentEnv, ['production', 'staging'], true)) {
                if (\Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                    \Illuminate\Support\Facades\Log::error(
                        'TRUSTED_PROXIES is not configured. IP-based rate limiting WILL be incorrect in production: ' .
                        'all clients behind a load-balancer share one rate-limit bucket. ' .
                        'Set TRUSTED_PROXIES to your load-balancer CIDRs, or "*" if the network layer handles spoofing.'
                    );
                }
            } elseif (!in_array($currentEnv, ['local', 'testing'], true)) {
                if (\Illuminate\Support\Facades\Facade::getFacadeApplication()) {
                    \Illuminate\Support\Facades\Log::warning(
                        'TRUSTED_PROXIES is not set. IP-based rate limiting may be inaccurate.'
                    );
                }
            }
            $proxies = [];   // trust no proxies; use the actual socket IP
        }

        $middleware->trustProxies(
            at: $proxies,
            headers: \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_HOST |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PORT |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_PROTO |
                     \Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
