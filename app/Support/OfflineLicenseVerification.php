<?php

namespace App\Support;

final class OfflineLicenseVerification
{
    public const SIGNING_ALGORITHM = 'RSA-SHA256';

    public static function canonicalizePayload(array $payload): string
    {
        $normalized = self::normalizeValue($payload);

        $json = json_encode($normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        if ($json === false) {
            throw new \RuntimeException('Unable to canonicalize offline license payload.');
        }

        return $json;
    }

    public static function buildPublicKeyMetadata(): array
    {
        $keyMap = self::availableKeys();
        $activeKeyId = config('services.license.signing_key_id', 'corevisys-key-1');

        $activePublicKey = $keyMap[$activeKeyId] ?? config('services.license.signing_public_key');

        return [
            'key_id' => $activeKeyId,
            'active_key_id' => $activeKeyId,
            'public_key' => $activePublicKey,
            'algorithm' => self::SIGNING_ALGORITHM,
            'available_keys' => array_map(
                fn (string $keyId, string $publicKey) => ['key_id' => $keyId, 'public_key' => $publicKey],
                array_keys($keyMap),
                array_values($keyMap)
            ),
            'rotation_overlap_days' => (int) config('services.license.rotation_overlap_days', config('services.license.signing_rotation_overlap_days', 30)),
            'revoked_key_ids' => array_values(array_filter(array_map('trim', (array) config('services.license.signing_revoked_key_ids', [])))),
        ];
    }

    public static function verifySignature(string $payload, string $signature, string $keyId): bool
    {
        if (self::isRevokedKey($keyId)) {
            return false;
        }

        $key = self::publicKeyForKeyId($keyId);

        if ($key === null) {
            return false;
        }

        $decodedSignature = base64_decode($signature, true);

        if ($decodedSignature === false) {
            return false;
        }

        $publicKey = openssl_get_publickey(self::normalizeKeyMaterial($key));

        if ($publicKey === false) {
            return false;
        }

        return openssl_verify($payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256) === 1;
    }

    protected static function availableKeys(): array
    {
        $configuredKeys = config('services.license.signing_public_keys', []);

        if (is_array($configuredKeys) && !empty($configuredKeys)) {
            return $configuredKeys;
        }

        $activeKeyId = config('services.license.signing_key_id', 'corevisys-key-1');
        $publicKey = config('services.license.signing_public_key');

        if (!$publicKey) {
            return [];
        }

        return [$activeKeyId => $publicKey];
    }

    protected static function publicKeyForKeyId(string $keyId): ?string
    {
        return self::availableKeys()[$keyId] ?? null;
    }

    protected static function isRevokedKey(string $keyId): bool
    {
        $revoked = array_values(array_filter(array_map('trim', (array) config('services.license.signing_revoked_key_ids', [])))) ;

        return in_array($keyId, $revoked, true);
    }

    protected static function normalizeKeyMaterial(string $keyMaterial): string
    {
        $decoded = base64_decode($keyMaterial, true);

        return $decoded !== false ? $decoded : $keyMaterial;
    }

    protected static function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            if (array_is_list($value)) {
                $normalized = [];
                foreach ($value as $item) {
                    $normalized[] = self::normalizeValue($item);
                }

                return $normalized;
            }

            $normalized = [];
            foreach ($value as $key => $item) {
                $normalized[$key] = self::normalizeValue($item);
            }

            ksort($normalized);

            return $normalized;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        return $value;
    }
}
