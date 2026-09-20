<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Queue\Events\JobFailed;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        Queue::failing(function (JobFailed $event) {
            $context = [
                'connection' => $event->connectionName,
                'queue' => $event->job->getQueue(),
                'job' => $event->job->resolveName(),
                'exception' => $event->exception?->getMessage(),
            ];

            Log::error('Queue job failed', $context);

            if (config('logging.channels.alert')) {
                Log::channel('alert')->critical('Queue job failed', $context);
            }
        });

        // Rate Limiters
        \Illuminate\Support\Facades\RateLimiter::for('pulse', function (\Illuminate\Http\Request $request) {
            $licenseKey = (string) $request->input('license_key', '');
            $key = $licenseKey !== '' ? hash('sha256', $licenseKey . '|' . $request->ip()) : $request->ip();

            return \Illuminate\Cache\RateLimiting\Limit::perHour(5)->by($key);
        });

        \Illuminate\Support\Facades\RateLimiter::for('activation', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(5)->by($request->ip());
        });
    }
}
