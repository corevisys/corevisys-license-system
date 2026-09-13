<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\LicenseService;
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
            'signature' => $result['signature'], // Hardware binding signature
            'offline_valid_until' => now()->addHours(24)->toIso8601String(),
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
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['status' => false, 'message' => 'Invalid License Key'], 403);
        }

        if ($license->status === 'suspended') {
            return response()->json(['status' => false, 'message' => 'License has been Suspended. Contact Support.'], 403);
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
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['status' => false, 'message' => 'License Inactive/Invalid'], 403);
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

        return response()->json($this->signResponse([
            'license_type' => $license->type,
            'license_status' => $license->status,
            'history' => $history,
        ]));
    }

    protected function normalizeDomain(?string $domain): ?string
    {
        return in_array($domain, ['localhost', '127.0.0.1']) ? '127.0.0.1' : $domain;
    }

    protected function successResponse(array $data)
    {
        $signed = $this->signResponse($data);

        return response()->json([
            'status' => 'success',
            'data' => $data,
            'payload' => $signed['payload'] ?? null,
            'server_signature' => $signed['server_signature'] ?? null,
        ])->header('Cache-Control', 'max-age=3600, private');
    }

    protected function signResponse(array $data)
    {
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyStr = config('services.license.signing_private_key');

        if (!$privateKeyStr) {
            \Log::error('LICENSE_SIGNING_PRIVATE_KEY is missing in configuration');
            return ['payload' => base64_encode($payload), 'server_signature' => 'MISSING_KEY'];
        }

        // Aggressively strip any whitespace/newlines
        $privateKeyStr = str_replace(["\r", "\n", " ", "\t"], "", $privateKeyStr);
        $decoded = base64_decode($privateKeyStr);

        $privateKey = openssl_get_privatekey($decoded);
        if (!$privateKey) {
            // Fallback: try raw string if it wasn't base64 encoded
            $privateKey = openssl_get_privatekey($privateKeyStr);
        }

        if (!$privateKey) {
            \Log::error('OpenSSL failed to parse private key: ' . openssl_error_string());
            return ['payload' => base64_encode($payload), 'server_signature' => 'INVALID_KEY'];
        }

        $signature = '';
        if (!openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            \Log::error('OpenSSL signing failed: ' . openssl_error_string());
            return ['payload' => base64_encode($payload), 'server_signature' => 'SIGNING_FAILED'];
        }

        return [
            'payload' => base64_encode($payload),
            'server_signature' => base64_encode($signature),
        ];
    }
}
