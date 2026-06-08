<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Get the rate from local currency TO base currency (USD).
     * e.g. BDT -> USD: rate is 1/120 = 0.0083
     * The table stores rate_to_base.
     */
    public function getRate(string $currency): float
    {
        if ($currency === 'USD') {
            return 1.0;
        }

        return Cache::remember("exchange_rate_{$currency}", 3600, function () use ($currency) {
            $rate = ExchangeRate::where('currency', $currency)->first();
            return $rate ? (float) $rate->rate_to_base : 1.0;
        });
    }

    public function convertToBase(float $amount, string $currency): float
    {
        $rate = $this->getRate($currency);
        return round($amount * $rate, 2);
    }

    public function forgetRate(string $currency): void
    {
        Cache::forget("exchange_rate_{$currency}");
    }
}
