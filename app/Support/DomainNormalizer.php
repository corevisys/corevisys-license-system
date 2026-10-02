<?php

namespace App\Support;

class DomainNormalizer
{
    /**
     * Normalize a domain/URL string according to the canonical specification:
     * 1. Trim whitespace
     * 2. Extract host only (strip scheme, userinfo, port, path, query, fragment)
     * 3. Lowercase host
     * 4. Strip ONE leading www.
     * 5. Map localhost and 127.0.0.1 (and ::1) to 127.0.0.1
     */
    public static function normalize(?string $domain): ?string
    {
        if ($domain === null) {
            return null;
        }

        $raw = trim($domain);
        if ($raw === '') {
            return '';
        }

        if (str_contains($raw, '://')) {
            $parsedHost = parse_url($raw, PHP_URL_HOST);
        } elseif (str_starts_with($raw, '//')) {
            $parsedHost = parse_url('http:' . $raw, PHP_URL_HOST);
        } else {
            $parsedHost = parse_url('http://' . ltrim($raw, '/'), PHP_URL_HOST);
        }

        $host = (string) ($parsedHost ?: $raw);

        // If parse_url didn't strip a port (e.g. fallback), strip it
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        // If path remained in fallback, strip it
        if (str_contains($host, '/')) {
            $host = explode('/', $host)[0];
        }

        $host = strtolower(trim($host));

        // Strip ONE leading www.
        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        // Localhost mapping
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '[::1]'], true)) {
            $host = '127.0.0.1';
        }

        return $host;
    }
}
