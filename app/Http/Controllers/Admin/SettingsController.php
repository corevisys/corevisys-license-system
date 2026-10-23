<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExchangeRate;
use App\Models\SystemSetting;
use App\Support\OfflineLicenseVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');

        $maskedSettings = $settings->mapWithKeys(function ($value, $key) {
            if (str_contains($key, 'secret') || str_contains($key, 'token') || str_contains($key, 'password') || str_contains($key, 'key')) {
                return [$key => $value === '' ? '' : '******'];
            }

            return [$key => $value];
        });

        if (! $maskedSettings->has('api_enabled')) {
            $maskedSettings['api_enabled'] = 'true';
        }
        if (! $maskedSettings->has('min_supported_version')) {
            $maskedSettings['min_supported_version'] = '1.0.0';
        }

        // RSA signing architecture metadata (algorithm, active key id, public key).
        $signingMeta = OfflineLicenseVerification::buildPublicKeyMetadata();

        return Inertia::render('Admin/Settings', [
            'currentSettings' => $maskedSettings,
            'signing' => [
                'algorithm' => $signingMeta['algorithm'] ?? 'RSA-SHA256',
                'active_key_id' => $signingMeta['active_key_id'] ?? null,
                'public_key_configured' => ! empty($signingMeta['public_key']),
                'public_key_url' => url('/api/v1/license/public-key'),
            ],
            'runtime' => [
                'queue_driver' => (string) config('queue.default'),
                'session_driver' => (string) config('session.driver'),
                'cache_driver' => (string) config('cache.default'),
                'scheduler_command' => 'license:renew-subscriptions',
            ],
            'protocol' => [
                'offline_validity_days' => (int) config('license.offline_validity_days', 7),
                'pulse_interval_days' => (int) config('license.pulse_interval_days', 30),
            ],
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->all();

        // Secret fields that must never be clobbered by the masked placeholder.
        $secretKeys = ['gateway_stripe_key', 'gateway_stripe_secret', 'gateway_bkash_password', 'gateway_bkash_app_key', 'gateway_bkash_app_secret'];

        foreach ($data as $key => $value) {
            if (in_array($key, ['_token', '_method'], true)) {
                continue;
            }

            $raw = is_array($value) ? json_encode($value) : (string) $value;

            // Catastrophic-overwrite guard: the settings page renders secrets
            // masked ('******') and sends them back on save. Never persist a
            // mask/empty/placeholder over a real stored secret.
            if (in_array($key, $secretKeys, true)) {
                $trimmed = trim($raw);
                if ($trimmed === '' || $trimmed === '******' || str_starts_with($trimmed, '***')) {
                    continue; // keep existing value untouched
                }
            }

            // Store a clean ISO code for the base currency, not the long label.
            if ($key === 'base_currency') {
                $raw = trim(explode('—', str_replace('---', '—', $raw))[0]);
            }

            SystemSetting::updateOrCreate(
                ['key' => $key],
                ['value' => $raw]
            );

            Cache::forget("setting:{$key}");
        }

        // Keep the exchange_rates table in sync with the scalar setting and
        // flush the cached lookup so BDT conversion uses the fresh rate.
        if ($request->filled('exchange_rate')) {
            $rate = (float) $request->input('exchange_rate');

            if ($rate > 0) {
                $baseCurrency = SystemSetting::getCached('base_currency', 'USD');

                // The exchange rate is expressed as units of base (USD) per 1 local unit,
                // so a displayed "USD/BDT" of 114.50 means rate_to_base = 1 / 114.50.
                $rateToBase = $baseCurrency === 'BDT' && $rate > 1 ? (1 / $rate) : $rate;
                $currency = $baseCurrency === 'BDT' ? 'BDT' : 'USD';

                ExchangeRate::updateOrCreate(
                    ['currency' => 'BDT'],
                    ['rate_to_base' => $rateToBase]
                );

                Cache::forget('exchange_rate_BDT');
                Cache::forget("exchange_rate_{$currency}");
            }
        }

        return back()->with('success', 'Settings updated successfully.');
    }
}