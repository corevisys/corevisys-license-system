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

        return response()->json($this->signResponse([
            'license_status' => $result['license']->status,
            'type' => $result['license']->type,
            'expires_at' => $result['license']->expires_at ? $result['license']->expires_at->toIso8601String() : null,
            'signature' => $result['signature'], // Hardware binding signature
            'offline_valid_until' => now()->addHours(24)->toIso8601String(),
        ]))->header('Cache-Control', 'private, max-age=3600');
    }

    public function check(Request $request)
    {
        return $this->activate($request);
    }

    /**
     * Pulse Endpoint: Lightweight validity check without side effects (no log noise).
     * Used for frequent heartbeats.
     */
    public function pulse(Request $request)
    {
        \Log::info('===== PULSE METHOD CALLED =====');
        
        $request->validate([
            'license_key' => 'required|string',
            'domain' => 'required|string',
            'enforcement_mode' => 'nullable|string|in:standard,strict,active',
        ]);

        \Log::debug('Pulse request received', ['key' => $request->license_key, 'domain' => $request->domain]);

        // Normalize domain
        $requestDomain = $request->domain;
        if (in_array($requestDomain, ['localhost', '127.0.0.1'])) { $requestDomain = '127.0.0.1'; }

        $license = \App\Models\License::where('license_key', $request->license_key)->first();

        if (!$license) {
            \Log::error('Pulse license completely not found', ['key' => $request->license_key]);
            return response()->json(['status' => false, 'message' => 'License Inactive/Invalid'], 403);
        }

        $boundDomain = $license->bound_domain;
        if (in_array($boundDomain, ['localhost', '127.0.0.1'])) { $boundDomain = '127.0.0.1'; }

        if ($boundDomain !== $requestDomain) {
             // Check history
             $hasHistory = \App\Models\LicenseActivation::where('license_id', $license->id)
                ->where(function($q) use ($requestDomain) {
                    $q->where('request_domain', $requestDomain);
                    if ($requestDomain === '127.0.0.1') {
                        $q->orWhere('request_domain', 'localhost');
                    }
                })
                ->exists();
             
             if (!$hasHistory) {
                 \Log::error('Pulse domain mismatch and no history found', ['domain' => $request->domain, 'bound' => $license->bound_domain]);
                 // If license is SUSPENDED, we should still return SUSPENDED to enforce it even on unauthorized domain
                 if ($license->status !== 'suspended') {
                    return response()->json(['status' => false, 'message' => 'Unauthorized Domain'], 403);
                 }
             }
        }

        \Log::info('LICENSE FOUND', ['id' => $license->id, 'status' => $license->status]);

        // Log this pulse for history tracking
        \App\Models\LicenseActivation::create([
            'license_id' => $license->id,
            'request_ip' => $request->ip(),
            'request_domain' => $request->domain,
            'status' => 'success', // Unified status for heartbeat (enum: success, failed)
        ]);

        // Update last check
        $license->update(['last_check_at' => now()]);

        if ($license->status === 'suspended') {
             \Log::warning('SUSPENDED LICENSE DETECTED', ['license_id' => $license->id]);
             // Return 200 with suspended status to avoid client wipe, just block
             $responseData = $this->signResponse([
                 'license_status' => 'SUSPENDED',
                 'license_type' => $license->type,
                 'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
                 'is_grace_period' => ($license->expires_at && $license->expires_at->isPast()),
             ]);
             \Log::warning('SUSPENDED RESPONSE GENERATED', ['response' => $responseData]);
             return response()->json($responseData);
        }

        if ($license->status !== 'active') {
            return response()->json(['status' => false, 'message' => 'License ' . strtoupper($license->status)], 403);
        }

        // Check Expiry (considering grace period)
        $isValid = true;
        if ($license->expires_at && $license->expires_at->isPast()) {
            $isValid = $license->grace_expires_at && $license->grace_expires_at->isFuture();
        }

        if (!$isValid) {
            return response()->json(['status' => false, 'message' => 'License Expired'], 403);
        }

        return response()->json($this->signResponse([
            'license_status' => $license->status,
            'license_type' => $license->type,
            'expires_at' => $license->expires_at ? $license->expires_at->toIso8601String() : null,
            'is_grace_period' => ($license->expires_at && $license->expires_at->isPast()),
        ]));
    }

    public function history(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
        ]);

        $license = \App\Models\License::where('license_key', $request->license_key)->first();

        if (!$license) {
            return response()->json(['message' => 'License not found'], 404);
        }

        $history = $license->activations()
            ->orderBy('created_at', 'desc')
            ->get(['id', 'request_ip', 'request_domain', 'status', 'failure_reason', 'created_at']);

        return response()->json($this->signResponse([
            'license_type' => $license->type,
            'license_status' => $license->status,
            'history' => $history
        ]));
    }

    protected function signResponse(array $data)
    {
        \Log::debug('signResponse called', ['data' => $data]);
        
        $payload = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $privateKeyStr = env('LICENSE_SIGNING_PRIVATE_KEY');
        
        \Log::debug('Private key length', ['length' => strlen($privateKeyStr ?? '')]);
        
        if (!$privateKeyStr) {
            \Log::error('LICENSE_SIGNING_PRIVATE_KEY is missing in .env');
            return ['data' => $data, 'server_signature' => 'MISSING_KEY'];
        }

        // Aggressively strip any whitespace/newlines
        $privateKeyStr = str_replace(["\r", "\n", " ", "\t"], "", $privateKeyStr);
        $decoded = base64_decode($privateKeyStr);
        
        \Log::debug('Decoded key length', ['length' => strlen($decoded)]);

        $privateKey = openssl_get_privatekey($decoded);
        if (!$privateKey) {
             // Fallback: try raw string if it wasn't base64 encoded
             \Log::debug('First attempt failed, trying raw key');
             $privateKey = openssl_get_privatekey($privateKeyStr);
        }
        
        if (!$privateKey) {
             $error = openssl_error_string();
             \Log::error('OpenSSL failed to parse private key: ' . $error);
             return ['data' => $data, 'server_signature' => 'INVALID_KEY: ' . $error];
        }
        
        \Log::debug('Private key loaded successfully');
        
        $signature = '';
        if (!openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
             $error = openssl_error_string();
             \Log::error('OpenSSL signing failed: ' . $error);
             return ['data' => $data, 'server_signature' => 'SIGNING_FAILED: ' . $error];
        }
        
        \Log::debug('Signature created successfully', ['sig_length' => strlen($signature)]);
        
        $result = [
            'payload' => base64_encode($payload),
            'server_signature' => base64_encode($signature)
        ];
        
        \Log::debug('signResponse returning', ['result_keys' => array_keys($result)]);
        
        return $result;
    }
}
