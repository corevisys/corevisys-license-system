<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
use App\Support\OfflineLicenseVerification;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $licenseService)
    {
        $this->licenseService = $licenseService;
    }

    public function activate(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain' => 'required|string',
            'ip' => 'required|ip', // Client should send their server IP
            'fingerprint' => 'nullable|string|max:255',
        ]);

        $result = $this->licenseService->activate(
            $request->license_key,
            $request->domain,
            $request->input('ip') ?? $request->input('ip_address'),
            $request->input('fingerprint'),
            $request->input('enforcement_mode')
        );

        if (!$result['status']) {
            return response()->json($result, 403);
        }

        return $this->successResponse([
            'license_status' => $result['license']->status,
            'type' => $result['license']->type,
            'license_type' => $result['license']->type,
            'expires_at' => $result['license']->expires_at ? $result['license']->expires_at->toIso8601String() : null,
            'offline_valid_until' => now()->addHours(24)->toIso8601String(),
            'issued_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Lightweight validity check. Read-only: performs NO side effects
     * (no activation logs, no binding updates, no limit changes).
     */
    public function check(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain' => 'required|string',
            'ip' => 'required|ip',
            'fingerprint' => 'nullable|string|max:255',
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['status' => false, 'message' => 'Invalid License Key'], 403);
        }

        if ($license->status === 'suspended') {
            return response()->json(['status' => false, 'message' => 'License has been Suspended. Contact Support.'], 403);
        }

        $requestFingerprint = $request->filled('fingerprint') ? $request->string('fingerprint')->toString() : null;

        if (!$this->licenseService->validateFingerprintBinding($license, $requestFingerprint)) {
            return response()->json(['status' => false, 'message' => 'Environment Fingerprint Required or Mismatched'], 403);
        }

        if ($license->bound_domain && $this->normalizeDomain($license->bound_domain) !== $this->normalizeDomain($request->domain)) {
            return response()->json(['status' => false, 'message' => 'Invalid Domain. Bound to: ' . $license->bound_domain], 403);
        }

        // Expiry / grace period (read-only)
        $isValid = true;
        if ($license->expires_at && $license->expires_at->isPast()) {
            $isValid = $license->grace_expires_at && $license->grace_expires_at->isFuture();
        }

        if (!$isValid) {
            return response()->json(['status' => false, 'message' => 'License Expired'], 403);
        }

        return $this->successResponse([
            'license_status' => $license->status,
            'type' => $license->type,
            'license_type' => $license->type,
            'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            'is_grace_period' => (bool) ($license->expires_at && $license->expires_at->isPast()),
        ]);
    }

    /**
     * Pulse Endpoint: Lightweight heartbeat. No side effects besides
     * updating `last_check_at` (no activation rows, no log noise).
     */
    public function pulse(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'domain' => 'required|string',
            'fingerprint' => 'nullable|string|max:255',
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['status' => false, 'message' => 'License Inactive/Invalid'], 403);
        }

        $requestFingerprint = $request->filled('fingerprint') ? $request->string('fingerprint')->toString() : null;

        if (!$this->licenseService->validateFingerprintBinding($license, $requestFingerprint)) {
            return response()->json(['status' => false, 'message' => 'Environment Fingerprint Required or Mismatched'], 403);
        }

        if ($license->bound_domain && $this->normalizeDomain($license->bound_domain) !== $this->normalizeDomain($request->domain)) {
            $hasHistory = $license->activations()
                ->where(function ($q) use ($request) {
                    $q->where('request_domain', $request->domain);
                    if (in_array($request->domain, ['localhost', '127.0.0.1'])) {
                        $q->orWhere('request_domain', 'localhost');
                    }
                })
                ->exists();

            // If license is SUSPENDED, still report it to enforce blocking
            // even on an unauthorized domain.
            if (!$hasHistory && $license->status !== 'suspended') {
                return response()->json(['status' => false, 'message' => 'Unauthorized Domain'], 403);
            }
        }

        // Update last check (heartbeat only)
        $license->update(['last_check_at' => now()]);

        if ($license->status === 'suspended') {
            // Return 200 with suspended status to avoid client wipe, just block
            return $this->successResponse([
                'license_status' => 'SUSPENDED',
                'license_type' => $license->type,
                'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
                'is_grace_period' => (bool) ($license->expires_at && $license->expires_at->isPast()),
            ]);
        }

        if ($license->status !== 'active') {
            return response()->json(['status' => false, 'message' => 'License ' . strtoupper($license->status)], 403);
        }

        // Check expiry (considering grace period)
        $isValid = true;
        if ($license->expires_at && $license->expires_at->isPast()) {
            $isValid = $license->grace_expires_at && $license->grace_expires_at->isFuture();
        }

        if (!$isValid) {
            return response()->json(['status' => false, 'message' => 'License Expired'], 403);
        }

        return $this->successResponse([
            'license_status' => $license->status,
            'license_type' => $license->type,
            'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            'is_grace_period' => (bool) ($license->expires_at && $license->expires_at->isPast()),
        ]);
    }

    public function publicKey()
    {
        $meta = OfflineLicenseVerification::buildPublicKeyMetadata();

        if (empty($meta['public_key'])) {
            return response()->json(['message' => 'Public key not configured'], 503);
        }

        return response()->json([
            'key_id' => $meta['key_id'],
            'active_key_id' => $meta['active_key_id'],
            'algorithm' => $meta['algorithm'],
            'public_key' => $meta['public_key'],
            'available_keys' => $meta['available_keys'],
            'rotation_overlap_days' => $meta['rotation_overlap_days'],
            'revoked_key_ids' => $meta['revoked_key_ids'],
        ]);
    }

    public function history(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        $history = $license->activations()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'request_ip', 'request_domain', 'status', 'failure_reason', 'created_at']);

        $history = $history->map(fn ($activation) => [
            'id' => $activation->id,
            'status' => $activation->status,
            'created_at' => $activation->created_at,
        ]);

        return $this->successResponse([
            'license_type' => $license->type,
            'license_status' => $license->status,
            'history' => $history,
        ]);
    }

    protected function fingerprintGraceWindowIsActive(): bool
    {
        if (!(bool) config('services.license.fingerprint_grace_mode', true)) {
            return false;
        }

        $storedDeadline = \App\Models\SystemSetting::where('key', 'fingerprint_enforcement_deadline')->value('value');
        $deadline = $storedDeadline
            ? \Carbon\Carbon::parse($storedDeadline)
            : \Carbon\Carbon::parse(config('services.license.fingerprint_enforcement_deadline', now()->addDays(90)->format('Y-m-d')));

        return now()->lt($deadline);
    }

    protected function normalizeDomain(?string $domain): ?string
    {
        return in_array($domain, ['localhost', '127.0.0.1']) ? '127.0.0.1' : $domain;
    }

    protected function shouldEnforceFingerprint(Request $request): bool
    {
        $mode = strtolower((string) ($request->input('enforcement_mode') ?? 'standard'));

        return in_array($mode, ['strict', 'active'], true);
    }

    protected function successResponse(array $data)
    {
        $signed = $this->signResponse($data);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'payload' => $signed['payload'] ?? null,
            'server_signature' => $signed['server_signature'] ?? null,
            'key_id' => $signed['key_id'] ?? null,
            'algorithm' => $signed['algorithm'] ?? null,
        ])->header('Cache-Control', 'max-age=3600, private');
    }

    protected function signResponse(array $data)
    {
        $payload = OfflineLicenseVerification::canonicalizePayload($data);
        $privateKeyStr = config('services.license.signing_private_key');

        if (!$privateKeyStr) {
            \Log::error('LICENSE_SIGNING_PRIVATE_KEY is missing in configuration');
            abort(503, 'License signing is temporarily unavailable.');
        }

        $decoded = base64_decode($privateKeyStr, true);
        $privateKey = openssl_get_privatekey($decoded !== false ? $decoded : $privateKeyStr);

        if (!$privateKey) {
            \Log::error('OpenSSL failed to parse license signing key.');
            abort(503, 'License signing is temporarily unavailable.');
        }

        $signature = '';
        if (!openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            \Log::error('OpenSSL signing failed.');
            abort(503, 'License signing is temporarily unavailable.');
        }

        return [
            'payload' => base64_encode($payload),
            'server_signature' => base64_encode($signature),
            'key_id' => config('services.license.signing_key_id', 'corevisys-key-1'),
            'algorithm' => config('services.license.signing_algorithm', 'RSA-SHA256'),
        ];
    }
}
