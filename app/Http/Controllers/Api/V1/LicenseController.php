<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\LicenseService;
use App\Support\DomainNormalizer;
use App\Support\OfflineLicenseVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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
            'product_code' => 'nullable|string|max:100',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $expectedSlug = $license->product?->slug;
        $providedSlug = $request->input('product_code');
        if (empty($providedSlug) || ($expectedSlug !== null && $providedSlug !== $expectedSlug)) {
            \Illuminate\Support\Facades\Log::warning('Product code mismatch or missing during license activate', [
                'license_id' => $license->id,
                'expected'   => $expectedSlug,
                'provided'   => $providedSlug,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $result = $this->licenseService->activate(
            $request->license_key,
            $request->domain,
            $request->input('ip') ?? $request->input('ip_address'),
            $request->input('fingerprint'),
            $request->input('enforcement_mode'),
            $providedSlug
        );

        if (!$result['status']) {
            $httpStatus = ($result['error_code'] ?? '') === 'activation_limit_exceeded' ? 409 : 403;
            return response()->json($result, $httpStatus);
        }

        return $this->successResponse($this->licensePayload($result['license']));
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
            'product_code' => 'nullable|string|max:100',
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            \Illuminate\Support\Facades\Log::warning('Unknown license key during check', [
                'domain' => $request->domain,
                'ip'     => $request->ip(),
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $expectedSlug = $license->product?->slug;
        $providedSlug = $request->input('product_code');
        if (empty($providedSlug) || ($expectedSlug !== null && $providedSlug !== $expectedSlug)) {
            \Illuminate\Support\Facades\Log::warning('Product code mismatch during license check', [
                'license_id' => $license->id,
                'expected'   => $expectedSlug,
                'provided'   => $providedSlug,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        if ($license->status === 'suspended') {
            return response()->json(['status' => false, 'message' => 'License has been Suspended. Contact Support.'], 403);
        }

        // Revoked and cancelled licenses must not pass any check.
        // Generic response; real reason logged for operators.
        if (in_array($license->status, ['revoked', 'cancelled'], true)) {
            \Illuminate\Support\Facades\Log::warning('Check blocked: terminal license status', [
                'license_id' => $license->id,
                'status'     => $license->status,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $requestFingerprint = $request->filled('fingerprint') ? $request->string('fingerprint')->toString() : null;
        $enforcementMode = $request->input('enforcement_mode');

        if (!$this->licenseService->validateFingerprintBinding($license, $requestFingerprint, $enforcementMode, false)) {
            \Illuminate\Support\Facades\Log::warning('Fingerprint mismatch during check', [
                'license_id' => $license->id,
                'expected'   => $license->bound_fingerprint,
                'provided'   => $requestFingerprint,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Environment Fingerprint Mismatch',
                'error_code' => 'fingerprint_mismatch',
            ], 403);
        }

        if ($license->bound_domain && $this->normalizeDomain($license->bound_domain) !== $this->normalizeDomain($request->domain)) {
            \Illuminate\Support\Facades\Log::warning('Domain mismatch during check', [
                'license_id'     => $license->id,
                'bound_domain'   => $license->bound_domain,
                'request_domain' => $request->domain,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Unauthorized Domain',
                'error_code' => 'unauthorised_domain',
            ], 403);
        }

        // Expiry / grace period (read-only)
        $isValid = true;
        if ($license->expires_at && $license->expires_at->isPast()) {
            $isValid = $license->grace_expires_at && $license->grace_expires_at->isFuture();
        }

        if (!$isValid) {
            return response()->json(['status' => false, 'message' => 'License Expired'], 403);
        }

        return $this->successResponse($this->licensePayload($license));
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
            'product_code' => 'nullable|string|max:100',
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            \Illuminate\Support\Facades\Log::warning('Unknown license key during pulse', [
                'domain' => $request->domain,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $expectedSlug = $license->product?->slug;
        $providedSlug = $request->input('product_code');
        if (empty($providedSlug) || ($expectedSlug !== null && $providedSlug !== $expectedSlug)) {
            \Illuminate\Support\Facades\Log::warning('Product code mismatch during license pulse', [
                'license_id' => $license->id,
                'expected'   => $expectedSlug,
                'provided'   => $providedSlug,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $requestFingerprint = $request->filled('fingerprint') ? $request->string('fingerprint')->toString() : null;
        $enforcementMode = $request->input('enforcement_mode');

        // --- Terminal-status guard (before any side-effect) ---
        // Suspended licenses fall through below for the 200-suspended path.
        // Revoked and cancelled must never receive a 200 or update last_check_at.
        if (in_array($license->status, ['revoked', 'cancelled'], true)) {
            \Illuminate\Support\Facades\Log::warning('Pulse blocked: terminal license status', [
                'license_id' => $license->id,
                'status'     => $license->status,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        if (!$this->licenseService->validateFingerprintBinding($license, $requestFingerprint, $enforcementMode, false)) {
            \Illuminate\Support\Facades\Log::warning('Fingerprint mismatch during pulse', [
                'license_id' => $license->id,
                'expected'   => $license->bound_fingerprint,
                'provided'   => $requestFingerprint,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Environment Fingerprint Mismatch',
                'error_code' => 'fingerprint_mismatch',
            ], 403);
        }

        if ($license->bound_domain && $this->normalizeDomain($license->bound_domain) !== $this->normalizeDomain($request->domain)) {
            $normalizedRequestDomain = $this->normalizeDomain($request->domain);
            $hasHistory = $license->activations()
                ->where('status', 'success')
                ->get()
                ->contains(fn ($act) => $this->normalizeDomain($act->request_domain) === $normalizedRequestDomain);

            // If license is SUSPENDED, still report it to enforce blocking
            // even on an unauthorized domain.
            if (!$hasHistory && $license->status !== 'suspended') {
                \Illuminate\Support\Facades\Log::warning('Unauthorized domain during pulse', [
                    'license_id'     => $license->id,
                    'bound_domain'   => $license->bound_domain,
                    'request_domain' => $request->domain,
                ]);
                return response()->json([
                    'status'     => false,
                    'message'    => 'Unauthorized Domain',
                    'error_code' => 'unauthorised_domain',
                ], 403);
            }
        }

        // Update last check (heartbeat only) — only AFTER status guards pass
        $license->update(['last_check_at' => now()]);

        if ($license->status === 'suspended') {
            // Return 200 with suspended status to avoid client wipe, just block
            return $this->successResponse($this->licensePayload($license, false));
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

        return $this->successResponse($this->licensePayload($license));
    }

    /**
     * Deactivate a license for the requesting domain.
     *
     * POST /api/v1/license/deactivate
     *
     * Required: license_key, domain, ip
     * Optional: fingerprint, product_code, reason
     */
    public function deactivate(Request $request)
    {
        $request->validate([
            'license_key'  => 'required|string',
            'domain'       => 'required|string',
            'ip'           => 'required|ip',
            'fingerprint'  => 'nullable|string|max:255',
            'product_code' => 'nullable|string|max:100',
            'reason'       => 'nullable|string|max:255',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (! $license) {
            \Illuminate\Support\Facades\Log::warning('Unknown license key during deactivate', [
                'domain' => $request->domain,
                'ip'     => $request->input('ip'),
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        // Product-code guard (same as activate/check/pulse)
        $expectedSlug = $license->product?->slug;
        $providedSlug = $request->input('product_code');
        if (empty($providedSlug) || ($expectedSlug !== null && $providedSlug !== $expectedSlug)) {
            \Illuminate\Support\Facades\Log::warning('Product code mismatch or missing during license deactivate', [
                'license_id' => $license->id,
                'expected'   => $expectedSlug,
                'provided'   => $providedSlug,
            ]);
            return response()->json([
                'status'     => false,
                'message'    => 'Invalid License Key',
                'error_code' => 'invalid_license_key',
            ], 403);
        }

        $result = $this->licenseService->deactivate(
            $license,
            $request->domain,
            $request->input('ip'),
            $request->input('fingerprint'),
            $request->input('reason'),
        );

        if (! $result['status']) {
            $httpStatus = match ($result['error_code'] ?? '') {
                'already_deactivated'   => 409,
                'fingerprint_mismatch'  => 403,
                'unauthorised_domain'   => 403,
                default                 => 403,
            };
            return response()->json($result, $httpStatus);
        }

        return response()->json([
            'success' => true,
            'status'  => 'success',
            'message' => $result['message'],
            'data'    => [],
        ]);
    }

    public function publicKey()
    {
        // 1. Check cache first
        $cached = Cache::get('license:public_key');
        if (is_array($cached) && !empty($cached['public_key'])) {
            return response()->json($cached);
        }

        // 2. Fetch fresh metadata
        // NOTE: Call Cache::forget('license:public_key') whenever LICENSE_SIGNING_PUBLIC_KEY,
        // LICENSE_SIGNING_KEY_ID, or rotation keys are updated or rotated.
        $meta = OfflineLicenseVerification::buildPublicKeyMetadata();

        if (empty($meta['public_key'])) {
            // NEVER cache the error / unconfigured state
            return response()->json(['message' => 'Public key not configured'], 503);
        }

        $payload = [
            'key_id' => $meta['key_id'],
            'active_key_id' => $meta['active_key_id'],
            'algorithm' => $meta['algorithm'],
            'public_key' => $meta['public_key'],
            'available_keys' => $meta['available_keys'],
            'rotation_overlap_days' => $meta['rotation_overlap_days'],
            'revoked_key_ids' => $meta['revoked_key_ids'],
        ];

        // 3. Cache ONLY the successful non-empty payload
        Cache::put('license:public_key', $payload, 3600);

        return response()->json($payload);
    }

    public function history(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $license = $this->licenseService->findByKey($request->license_key);

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        $query = $license->activations()
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc');

        $isPaginated = $request->has('page') || $request->has('per_page');

        if ($isPaginated) {
            $page = max(1, (int) $request->input('page', 1));
            $perPage = min(100, max(1, (int) $request->input('per_page', 15)));
            $total = (clone $query)->count();
            $activations = $query->forPage($page, $perPage)->get(['id', 'status', 'created_at']);
        } else {
            $activations = $query->limit(100)->get(['id', 'status', 'created_at']);
        }

        $history = $activations->map(fn ($activation) => [
            'id' => $activation->id,
            'status' => $activation->status,
            'created_at' => $activation->created_at,
        ])->values();

        $response = $this->successResponse([
            'license_type' => $license->type,
            'license_status' => $license->status,
            'history' => $history,
        ]);

        if ($isPaginated) {
            $response->header('X-Total-Count', (string) $total)
                ->header('X-Page', (string) $page)
                ->header('X-Per-Page', (string) $perPage)
                ->header('X-Total-Pages', (string) (ceil($total / $perPage) ?: 1));
        }

        return $response;
    }

    protected function fingerprintGraceWindowIsActive(): bool
    {
        return $this->licenseService->fingerprintGraceWindowIsActive();
    }

    protected function normalizeDomain(?string $domain): ?string
    {
        return DomainNormalizer::normalize($domain);
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
            'success' => true,
            'status' => 'success',
            'message' => null,
            'data' => $data,
            'signature' => $signed['signature'] ?? null,
            'key_id' => $signed['key_id'] ?? null,
            'algorithm' => $signed['algorithm'] ?? null,
        ])->header('Cache-Control', 'max-age=3600, private');
    }

    protected function licensePayload(License $license, bool $offlineGrant = true): array
    {
        $product = $license->product;
        $isGracePeriod = (bool) ($license->expires_at?->isPast() && $license->grace_expires_at?->isFuture());

        return [
            'status'             => $license->status,
            'license_id'         => (string) $license->id,
            'product_code'       => $product?->slug ?? (string) $license->product_id,
            'license_type'       => $license->type,
            'expires_at'         => $license->expires_at?->toIso8601String(),
            'bound_domain'       => $license->bound_domain,
            'features'           => array_values((array) $license->features),
            'issued_at'          => now()->toIso8601String(),
            'offline_valid_until' => ($offlineGrant && $license->status === 'active')
                ? now()->addDays((int) config('license.offline_validity_days', 7))->toIso8601String()
                : null,
            'is_grace_period'    => $isGracePeriod,
        ];
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

        $keyId = config('services.license.signing_key_id');
        if (empty($keyId)) {
            \Log::error('LICENSE_SIGNING_KEY_ID is missing in configuration');
            abort(503, 'License signing is temporarily unavailable.');
        }

        return [
            'signature' => base64_encode($signature),
            'key_id' => $keyId,
            'algorithm' => OfflineLicenseVerification::SIGNING_ALGORITHM,
        ];
    }
}
