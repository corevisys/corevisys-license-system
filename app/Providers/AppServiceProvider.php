<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
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
        // Prohibit destructive database commands (migrate:fresh, migrate:refresh, migrate:reset, db:wipe) in production
        DB::prohibitDestructiveCommands($this->app->isProduction());

        $this->registerSlowQueryLogger();

        if ($this->app->isProduction() && empty(config('services.license.signing_key_id'))) {
            throw new \RuntimeException('LICENSE_SIGNING_KEY_ID is missing or not configured in production.');
        }

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

        // Deactivation is infrequent (uninstall/transfer) — 10 per hour per IP is generous
        // but still prevents abuse (e.g. looping deactivations to exhaust activations).
        \Illuminate\Support\Facades\RateLimiter::for('deactivation', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perHour(10)->by($request->ip());
        });

        \Illuminate\Support\Facades\RateLimiter::for('public-key', function (\Illuminate\Http\Request $request) {
            return \Illuminate\Cache\RateLimiting\Limit::perMinute(60)->by($request->ip());
        });
    }

    /**
     * Log queries exceeding the configured threshold to the `slow_query` channel.
     *
     * Bound parameters are intentionally NOT logged: they can contain license
     * keys or password material. Only the SQL shape is recorded for profiling.
     */
    protected function registerSlowQueryLogger(): void
    {
        if (!config('database.slow_query_log', false)) {
            return;
        }

        $thresholdMs = (int) config('database.slow_query_threshold_ms', 1000);
        if ($thresholdMs <= 0) {
            return;
        }

        DB::listen(function ($query) use ($thresholdMs) {
            if ($query->time < $thresholdMs) {
                return;
            }

            Log::channel('slow_query')->warning('Slow query detected', [
                'connection'     => $query->connectionName,
                'time_ms'        => $query->time,
                'sql'            => $query->sql,
                'bindings_count' => count($query->bindings),
            ]);
        });
    }
}
