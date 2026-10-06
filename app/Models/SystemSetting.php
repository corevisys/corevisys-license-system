<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    /**
     * Sentinel value stored in cache to represent "key does not exist in DB".
     *
     * Problem: Cache::remember('setting:foo', ttl, fn) returns whatever the
     * closure returns.  If the closure returns null (key missing or value IS NULL),
     * the result is cached as null.  On the next call Cache::remember sees a
     * non-null (a hit) only if the cached value is truthy — but null is not
     * truthy, so it re-queries the DB every time for missing keys.
     *
     * Fix: store this private sentinel object for "key not in DB" so cache hits
     * are always distinguishable from cache misses, and the $default is only
     * substituted when we return to the caller.
     */
    private static string $MISSING = '__COREVISYS_SETTING_MISSING__';

    protected static function booted(): void
    {
        static::saved(function (SystemSetting $setting) {
            static::forgetCache($setting->key);
        });

        static::deleted(function (SystemSetting $setting) {
            static::forgetCache($setting->key);
        });
    }

    /**
     * Get a cached setting value.
     *
     * Returns $default when the key does not exist in the database.
     * A DB value of NULL is treated the same as "not found" and also
     * returns $default.
     *
     * Missing keys are cached via a sentinel so a second call within
     * the TTL window does not query the database again.
     *
     * If the cache store is unavailable this falls back to a direct DB
     * query (Cache::remember raises no exception; it simply executes the
     * closure and returns the result without caching).
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public static function getCached(string $key, mixed $default = null): mixed
    {
        $cached = Cache::remember("setting:{$key}", 3600, function () use ($key) {
            $value = static::where('key', $key)->value('value');

            // Use sentinel for missing / null values so the cache entry is
            // non-null and will be returned as a hit on subsequent calls.
            return $value ?? static::$MISSING;
        });

        if ($cached === static::$MISSING) {
            return $default;
        }

        return $cached;
    }

    /**
     * Explicitly forget the cache entry for a given setting key.
     */
    public static function forgetCache(string $key): void
    {
        Cache::forget("setting:{$key}");
    }
}
