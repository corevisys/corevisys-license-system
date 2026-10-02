<?php

namespace App\Services;

use App\Jobs\ProcessLicenseRenewal;
use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductPrice;
use App\Models\TrialHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\LicenseStateMachine;

class LicenseService
{
    protected LicenseStateMachine $stateMachine;

    public function __construct()
    {
        $this->stateMachine = new LicenseStateMachine();
    }

    /**
     * Retrieve the configured license pepper or throw a clear exception.
     *
     * @throws \RuntimeException
     */
    public static function getLicensePepper(): string
    {
        $pepper = config('app.license_pepper');
        if (!is_string($pepper) || trim($pepper) === '') {
            throw new \RuntimeException('License pepper is missing or empty. Please set LICENSE_PEPPER in your .env file.');
        }

        return $pepper;
    }

    /**
     * Create a new license for an order.
     */
    /**
     * Resolve existing license for renewal or upgrade.
     */
    protected function resolveLicenseForOrder(Order $order, Product $product): ?License
    {
        $license = $order->license_id ? License::find($order->license_id) : null;

        return $license ?? License::where('user_id', $order->user_id)
            ->where('product_id', $product->id)
            ->latest()
            ->first();
    }

    /**
     * Renew an existing license from a completed order (order-fulfillment path).
     * Called by OrderFulfillmentService for manual/admin renewal orders.
     * Do NOT call from the cron/job renewal path — use renewLicense(License) instead.
     */
    public function renewLicenseFromOrder(Order $order, Product $product)
    {
        // 1. Find the specific license if ID is provided, else fallback to user/product lookup
        $license = $this->resolveLicenseForOrder($order, $product);

        if (!$license) {
            // Log::warning("Attempted renewal but no license found for Order #{$order->order_number}");
            // Optional: Fallback to create if no license exists? 
            // For now, strict renewal means we expect a license.
            // Actually, better user experience: if missing, create new. 
            return $this->createLicense($order, $product, 'full'); // Fallback
        }

        // 2. Calculate New Expiry
        // Get billing period from the current order items
        $orderItem = $order->items()->where('product_id', $product->id)->first();
        $billingPeriod = 30; // Default
        if ($orderItem && $orderItem->price) {
             $billingPeriod = $orderItem->price->billing_period ?? 30;
        }

        // If currently valid, add to expires_at. If expired, start from now.
        $startDate = ($license->expires_at && $license->expires_at->isFuture()) 
            ? $license->expires_at 
            : \Carbon\Carbon::now();
            
        $newExpiry = $startDate->copy()->addDays($billingPeriod);

        // 3. Update License
        $license->update([
            'expires_at' => $newExpiry,
            'status' => 'active', // Reactivate if was expired
            'last_check_at' => now(), // Optional: mark activity
        ]);
        
        // Return existing license
        return $license;
    }

    /**
     * Upgrade an existing license.
     */
    public function upgradeLicense(Order $order, Product $product)
    {
        // 1. Find the specific license
        $license = $this->resolveLicenseForOrder($order, $product);

        $orderItem = $order->items()->where('product_id', $product->id)->first();
        $newType = $orderItem->license_type ?? 'full'; // usage: 'full', 'subscription'

        if (!$license) {
            return $this->createLicense($order, $product, $newType);
        }

        // Calculate New Expiry for Upgrade
        $billingPeriod = 30; // Default
        if ($orderItem && $orderItem->price) {
             $billingPeriod = $orderItem->price->billing_period ?? 30;
        }

        // Upgrades typically start a fresh period from Now
        $newExpiry = \Carbon\Carbon::now()->addDays($billingPeriod);
        if ($newType === 'full' || $newType === 'lifetime') {
            $newExpiry = null; // Lifetime
        }

        $license->update([
            'type' => $newType,
            'status' => 'active',
            'expires_at' => $newExpiry,
            'last_check_at' => now(),
        ]);
        
        return $license;
    }

    public function createLicense(Order $order, Product $product, string $type = 'full')
    {
        $emailHash = $type === 'trial' ? hash('sha256', $order->user?->email ?? '') : null;
        $requestIp = request()->ip();
        $normalizedIp = is_string($requestIp) ? trim($requestIp) : null;
        $isLocalIp = in_array($normalizedIp, [null, '127.0.0.1', '::1', 'localhost'], true);
        $ipHash = $type === 'trial' && !$isLocalIp ? hash('sha256', $normalizedIp) : null;

        // Upgrade 6: Trial Abuse Prevention
        if ($type === 'trial') {
            $exists = TrialHistory::where('email_hash', $emailHash)->exists();

            if (!$isLocalIp && $ipHash) {
                $exists = $exists || TrialHistory::where('ip_hash', $ipHash)->exists();
            }

            if ($exists) {
                throw new \Exception('Trial limit exceeded for this user/environment.');
            }
        }

        // Prevent duplicate fulfillment
        if (License::where('order_id', $order->id)->exists()) {
            return License::where('order_id', $order->id)->first();
        }

        // Generate a unique license key
        // Format: [PROD_SLUG]-[RANDOM]-[RANDOM]-[RANDOM]
        $prefix = strtoupper(substr($product->slug ?? $product->name, 0, 4));
        $keyPayload = $prefix . '-' . 
                      strtoupper(Str::random(4)) . '-' . 
                      strtoupper(Str::random(4)) . '-' . 
                      strtoupper(Str::random(4));
        
        // Generate a 32-char secret salt
        $salt = Str::random(32);
        
        // Store SHA-256 Hash with salt
        $keyHash = hash('sha256', $keyPayload . $salt);

        // Calculate Expiry
        $expiresAt = null;
        if ($type === 'trial') {
            // Default to 6 months
            $billingPeriod = 180; 
            
            // Check if there is a specific trial price configured in the order
            $orderItem = $order->items()->where('product_id', $product->id)->first();
            if ($orderItem && $orderItem->price && $orderItem->price->type === 'trial') {
                $billingPeriod = $orderItem->price->billing_period ?? 180;
            }

            $expiresAt = Carbon::now()->addDays($billingPeriod);
        } elseif ($type === 'subscription') {
            // Find the billing period from the Order Item's linked price
            $orderItem = $order->items()->where('product_id', $product->id)->first();
            
            $billingPeriod = 30; // Default Monthly
            if ($orderItem && $orderItem->price) {
                 $billingPeriod = $orderItem->price->billing_period ?? 30;
            }
            
            $expiresAt = Carbon::now()->addDays($billingPeriod);
        }

        $pepper = static::getLicensePepper();
        $lookupHash = hash_hmac('sha256', $keyPayload, $pepper);

        $license = License::create([
            'user_id' => $order->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key_hash' => $keyHash,
            'lookup_hash' => $lookupHash,
            'key_encrypted' => $keyPayload,
            'secret_salt' => $salt,
            'type' => $type,
            'status' => 'inactive',
            'expires_at' => $expiresAt,
            'auto_renew' => $type === 'subscription',
            'next_billing_at' => $type === 'subscription' ? $expiresAt : null,
        ]);

        if ($type === 'trial') {
            TrialHistory::create([
                'user_id' => $order->user_id,
                'license_id' => $license->id,
                'email_hash' => $emailHash,
                'ip_hash' => $ipHash,
                'expires_at' => $license->expires_at ?? Carbon::now()->addMonths(6),
            ]);
        }

        $license->raw_key = $keyPayload; // Attach for immediate display

        return $license;
    }

    /**
     * Resolve a license by key using lookup_hash with timing-safe salted fallback.
     *
     * Legacy plaintext `license_key` values must be migrated first via
     * `php artisan license:migrate-legacy-keys`; rows that only have a raw
     * SHA-256 hash and no `secret_salt` are rejected by design.
     */
    /**
     * Resolve a license by key using lookup_hash with abuse-protected legacy fallback.
     *
     * Legacy plaintext `license_key` values must be migrated first via
     * `php artisan license:migrate-legacy-keys`; rows that only have a raw
     * SHA-256 hash and no `secret_salt` are rejected by design.
     */
    public function findByKey(string $key, ?string $clientIp = null): ?License
    {
        $pepper = static::getLicensePepper();
        $lookupHash = hash_hmac('sha256', $key, $pepper);

        $license = License::where('lookup_hash', $lookupHash)->first();
        if ($license) {
            if ($license->secret_salt && $license->license_key_hash && hash_equals($license->license_key_hash, hash('sha256', $key . $license->secret_salt))) {
                return $license;
            }
            return null;
        }

        // 1. Check if there are any NULL-lookup_hash rows left (cached for 60s)
        $hasLegacyRows = Cache::remember('license:has_legacy_lookup_rows', 60, function () {
            return License::whereNull('lookup_hash')
                ->whereNotNull('secret_salt')
                ->whereNotNull('license_key_hash')
                ->exists();
        });

        if (!$hasLegacyRows) {
            return null;
        }

        // 2. Per-IP rate limiting on fallback scans to prevent abuse on wrong keys.
        //
        // When there is no HTTP request context (console commands, queue jobs) the
        // resolved IP is null.  We deliberately skip the per-IP counter in that case
        // rather than falling back to '127.0.0.1', which would create a single shared
        // bucket that throttles all queue workers together after only 30 fallback scans.
        $resolvedIp     = $clientIp ?? request()?->ip();   // null in console/queue
        $fallbackLimit  = (int) config('app.license_fallback_rate_limit', 30);
        $fallbackWindow = (int) config('app.license_fallback_rate_limit_window', 60);

        if (app()->isProduction()) {
            $driver = Cache::getDefaultDriver();
            if (in_array($driver, ['array', 'file'], true)) {
                Log::warning("Fallback rate limiting is using non-shared cache driver '{$driver}' in production; rate limits are not shared across server instances. Consider configuring redis or memcached.");
            }
        }

        if ($resolvedIp !== null) {
            $cacheKey        = "license_fallback_count:{$resolvedIp}";
            $currentAttempts = (int) Cache::get($cacheKey, 0);

            if ($currentAttempts >= $fallbackLimit) {
                // Abuse protection: limit exceeded, skip fallback scan and return null
                return null;
            }

            Cache::put($cacheKey, $currentAttempts + 1, $fallbackWindow);
        }

        // 3. Fallback for legacy licenses (lookup_hash NULL)
        $matchedCandidate = null;
        License::whereNull('lookup_hash')
            ->whereNotNull('secret_salt')
            ->whereNotNull('license_key_hash')
            ->select(['id', 'license_key_hash', 'secret_salt'])
            ->chunkById(500, function ($licenses) use ($key, &$matchedCandidate) {
                foreach ($licenses as $candidate) {
                    if (hash_equals($candidate->license_key_hash, hash('sha256', $key . $candidate->secret_salt))) {
                        $matchedCandidate = $candidate;
                        return false;
                    }
                }
            });

        if ($matchedCandidate) {
            License::where('id', $matchedCandidate->id)->update(['lookup_hash' => $lookupHash]);
            Cache::forget('license:has_legacy_lookup_rows');
            return License::find($matchedCandidate->id);
        }

        return null;
    }

    /**
     * Rotate a license's key and update lookup and salted hashes.
     */
    public function rotateLicenseKey(License $license, ?string $newKeyPayload = null): License
    {
        $product = $license->product;
        if (!$newKeyPayload) {
            $prefix = strtoupper(substr($product?->slug ?? $product?->name ?? 'CORE', 0, 4));
            $newKeyPayload = $prefix . '-' . 
                          strtoupper(Str::random(4)) . '-' . 
                          strtoupper(Str::random(4)) . '-' . 
                          strtoupper(Str::random(4));
        }

        $salt = Str::random(32);
        $keyHash = hash('sha256', $newKeyPayload . $salt);
        $pepper = static::getLicensePepper();
        $lookupHash = hash_hmac('sha256', $newKeyPayload, $pepper);

        $license->update([
            'license_key_hash' => $keyHash,
            'lookup_hash' => $lookupHash,
            'key_encrypted' => $newKeyPayload,
            'secret_salt' => $salt,
        ]);

        $license->raw_key = $newKeyPayload;

        return $license;
    }

    public function activate(string $key, string $domain, string $ip, ?string $fingerprint = null, ?string $enforcementMode = null, ?string $productCode = null)
    {
        $license = $this->findByKey($key);

        if (!$license) {
            return ['status' => false, 'message' => 'Invalid License Key'];
        }

        if ($productCode !== null) {
            $expectedSlug = $license->product?->slug;
            if ($expectedSlug !== null && $expectedSlug !== $productCode) {
                return [
                    'status' => false,
                    'message' => 'License is not valid for product: ' . $productCode,
                    'error_code' => 'product_code_mismatch',
                ];
            }
        }

        if ($license->status === 'suspended') {
            return ['status' => false, 'message' => 'License has been Suspended. Contact Support.'];
        }

        if ($license->status !== 'active') {
            // allow activation if 'inactive' (initial state) -> set to active
            try {
                $this->stateMachine->transition($license, 'active');
            } catch (\Exception $e) {
                return ['status' => false, 'message' => 'License is ' . $license->status . ' and cannot be activated.'];
            }
        }

        if ($license->expires_at && $license->expires_at->isPast()) {
            // Upgrade 4: Grace Period Check
            if ($license->grace_expires_at && $license->grace_expires_at->isFuture()) {
                // In Grace Period - Return Active but with warning?
                // For now, let's treat it as valid but maybe return a flag (handled in response below is better, 
                // but we need to prevent 'expired' status update here).
                // Do Nothing, continue.
            } else {
                try {
                    $this->stateMachine->transition($license, 'expired');
                } catch (\Exception $e) {
                    // already expired or suspended
                }
                return ['status' => false, 'message' => 'License Expired'];
            }
        }

        if ($license->status === 'revoked') {
            return ['status' => false, 'message' => 'License Revoked'];
        }

        // Restore limit if it was reset to 0
        if ($license->activation_limit === 0) {
            $license->update(['activation_limit' => 1]);
        }

        // Activation Limit Guardrails
        $activationLimit = $license->activation_limit ?? 1;
        $activeBindingsCount = LicenseActivation::where('license_id', $license->id)
            ->where('status', 'success')
            ->distinct()
            ->count('request_domain');

        // Check if this is a NEW domain/environment activation
        $isExistingBinding = ($license->bound_domain === $domain) || 
                             LicenseActivation::where('license_id', $license->id)
                                ->where('status', 'success')
                                ->where('request_domain', $domain)
                                ->exists();

        if (!$isExistingBinding && $activeBindingsCount >= $activationLimit) {
            $this->logActivation($license, $domain, $ip, 'failed', 'Activation Limit Reached');
            // A brand-new domain that conflicts with the bound domain is reported as
            // a domain mismatch (clearer for the client), otherwise as a limit error.
            if ($license->bound_domain && $license->bound_domain !== $domain) {
                return ['status' => false, 'message' => 'Invalid Domain. Bound to: ' . $license->bound_domain];
            }
            return ['status' => false, 'message' => "Activation limit reached ({$activationLimit}). Please upgrade or reset licenses."];
        }

        // Domain Binding Logic (TOFU for primary, plus additional tracking)
        if (is_null($license->bound_domain)) {
            // First time (Primary Binding)
            $license->update([
                'bound_domain' => $domain,
                'bound_ip' => $ip,
                'bound_fingerprint' => $fingerprint,
                'activated_at' => Carbon::now(),
            ]);
        } else if (!$isExistingBinding) {
             // Additional binding allowed within limit - update last known if needed or just log
             // For simplicity, we track secondary bindings via LicenseActivation logs
        } else {
            // Validate Domain for primary (or allow any within limit)
            // If we want to be strict even within limit, we'd check against a list.
            // For now, if it's an existing binding, it's fine.
        }

        if (!$this->validateFingerprintBinding($license, $fingerprint, $enforcementMode, true)) {
            $this->logActivation($license, $domain, $ip, 'failed', 'Environment Fingerprint Mismatch');
            return ['status' => false, 'message' => 'Environment Fingerprint Mismatch'];
        }

        // Upgrade 6: Check Trial Fingerprint Abuse upon Activation (if fingerprint provided)
        if ($license->type === 'trial' && $fingerprint) {
            $fpHash = hash('sha256', $fingerprint);
            $existingHistory = TrialHistory::where('fingerprint_hash', $fpHash)
                ->where(function ($query) use ($license) {
                    $query->whereNull('license_id')
                        ->orWhere('license_id', '!=', $license->id);
                })
                ->first();

            if ($existingHistory) {
                return ['status' => false, 'message' => 'Trial already used on this environment. Upgrade to full version.'];
            }

            // Record fingerprint usage in a dedicated row so email/IP history can be deleted
            // without erasing the actual device-level abuse evidence.
            TrialHistory::firstOrCreate(
                [
                    'license_id' => $license->id,
                    'fingerprint_hash' => $fpHash,
                ],
                [
                    'user_id' => $license->user_id,
                    'expires_at' => $license->expires_at,
                ]
            );
        }

        // Success
        $license->update([
            'last_check_at' => Carbon::now(),
            'enforcement_mode' => $enforcementMode,
        ]);
        $this->logActivation($license, $domain, $ip, 'success');

        return [
            'status' => true,
            'license' => $license,
        ];
    }

    private function logActivation($license, $domain, $ip, $status, $reason = null)
    {
        LicenseActivation::create([
            'license_id' => $license->id,
            'request_ip' => $ip,
            'request_domain' => $domain,
            'status' => $status,
            'failure_reason' => $reason
        ]);
    }

    public function validateFingerprintBinding(License $license, ?string $fingerprint, ?string $enforcementMode = null, bool $persistGrace = true): bool
    {
        if ($license->bound_fingerprint === null) {
            return true;
        }

        if ($fingerprint !== null && !hash_equals($license->bound_fingerprint, $fingerprint)) {
            if ($persistGrace) {
                $license->update(['fingerprint_missing_grace' => false]);
            }
            return false;
        }

        if ($fingerprint === null) {
            $allowsMissing = $this->fingerprintGraceWindowIsActive();
            if ($persistGrace) {
                $license->update(['fingerprint_missing_grace' => $allowsMissing]);
            }
            return $allowsMissing;
        }

        if ($persistGrace) {
            $license->update(['fingerprint_missing_grace' => false]);
        }
        return true;
    }

    protected function shouldEnforceFingerprint(?string $mode): bool
    {
        $normalizedMode = strtolower((string) ($mode ?? 'active'));

        return !in_array($normalizedMode, ['disabled', 'none', 'off'], true);
    }

    public function fingerprintGraceWindowIsActive(): bool
    {
        $storedDeadline = \App\Models\SystemSetting::getCached('fingerprint_enforcement_deadline');
        $configuredDeadline = config('services.license.fingerprint_enforcement_deadline');

        $rawDeadline = $storedDeadline ?: $configuredDeadline;
        if (!$rawDeadline) {
            return false;
        }

        try {
            $deadline = Carbon::parse($rawDeadline);

            return now()->lt($deadline);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Deactivate a license for a specific domain/fingerprint combination.
     *
     * Rules:
     * - The request MUST come from the bound domain (or a domain with a
     *   successful activation history). Prevents a rogue third party from
     *   deactivating someone else's license.
     * - If the license has a bound_fingerprint, the submitted fingerprint MUST
     *   match (strict equality — no grace window on deactivation).
     * - On success: clears the primary binding fields, marks all active
     *   activation rows for the domain as 'deactivated', writes an audit entry,
     *   and sets status to 'inactive' only when no other active bindings remain.
     *
     * @return array{status: bool, message: string, error_code?: string}
     */
    public function deactivate(
        License $license,
        string  $domain,
        string  $ip,
        ?string $fingerprint = null,
        ?string $reason      = null,
    ): array {
        // 1. Verify domain is authorised (has a successful activation history)
        $domainIsAuthorised = ($license->bound_domain && $this->normalizeDomain($license->bound_domain) === $this->normalizeDomain($domain))
            || LicenseActivation::where('license_id', $license->id)
                ->where('status', 'success')
                ->where('request_domain', $domain)
                ->exists();

        if (! $domainIsAuthorised) {
            $this->logActivation($license, $domain, $ip, 'failed', 'Deactivation: unauthorised domain');
            return [
                'status'     => false,
                'message'    => 'This domain is not authorised to deactivate the license.',
                'error_code' => 'unauthorised_domain',
            ];
        }

        // 2. Strict fingerprint check (no grace window on deactivation)
        if ($license->bound_fingerprint !== null && $fingerprint !== null) {
            if (! hash_equals($license->bound_fingerprint, $fingerprint)) {
                $this->logActivation($license, $domain, $ip, 'failed', 'Deactivation: fingerprint mismatch');
                return [
                    'status'     => false,
                    'message'    => 'Fingerprint mismatch. Deactivation denied.',
                    'error_code' => 'fingerprint_mismatch',
                ];
            }
        }

        // 3. Mark all successful activation rows for this domain as deactivated
        LicenseActivation::where('license_id', $license->id)
            ->where('request_domain', $domain)
            ->where('status', 'success')
            ->update(['status' => 'failed', 'failure_reason' => 'Deactivated by client']);

        // 4. Clear primary binding if this domain IS the primary binding
        $isPrimary = $license->bound_domain && $this->normalizeDomain($license->bound_domain) === $this->normalizeDomain($domain);
        $updates   = [];

        if ($isPrimary) {
            $updates['bound_domain']      = null;
            $updates['bound_ip']          = null;
            $updates['bound_fingerprint'] = null;
        }

        // 5. If no remaining active bindings, set license to 'inactive'
        $remainingBindings = LicenseActivation::where('license_id', $license->id)
            ->where('status', 'success')
            ->count();

        if ($remainingBindings === 0) {
            try {
                $this->stateMachine->transition($license, 'inactive');
            } catch (\Exception) {
                // already inactive / state-machine won't allow the transition
            }
        }

        if (! empty($updates)) {
            $license->update($updates);
        }

        // 6. Audit trail
        AuditService::log(
            'license_deactivated',
            $license,
            ['domain' => $domain, 'fingerprint_submitted' => $fingerprint !== null ? '[present]' : null],
            ['reason' => $reason ?? 'client_request', 'remaining_bindings' => $remainingBindings]
        );

        return ['status' => true, 'message' => 'License deactivated successfully.'];
    }

    private function normalizeDomain(?string $domain): ?string
    {
        return in_array($domain, ['localhost', '127.0.0.1'], true) ? '127.0.0.1' : $domain;
    }

    public function resetLicense(License $license, $admin, string $reason)
    {
        $oldDomain = $license->bound_domain;
        $oldIp = $license->bound_ip;
        $oldFingerprint = $license->bound_fingerprint;

        // 1. Log Reset
        \App\Models\LicenseReset::create([
            'license_id' => $license->id,
            'admin_id' => $admin->id,
            'reason' => $reason,
            'previous_domain' => $license->bound_domain,
            'previous_fingerprint' => $license->bound_fingerprint,
            'ip_address' => request()->ip(), // Capture Admin IP
        ]);

        // 2. Perform Reset
        $license->update([
            'bound_domain' => null,
            'bound_ip' => null,
            'bound_fingerprint' => null,
            'activation_limit' => 0, // Reset limit to 0 as requested
            'reset_count' => $license->reset_count + 1
        ]);

        // 3. Clear Activation History (Reset Limits)
        // Note: 'reset' is not a valid DB enum value (schema allows success/failed),
        // so mark them 'failed' with an explanatory reason.
        $license->activations()
            ->where('status', 'success')
            ->update(['status' => 'failed', 'failure_reason' => 'Reset by admin']);

        \App\Services\AuditService::log(
            'license_reset',
            $license,
            ['bound_domain' => $oldDomain, 'bound_ip' => $oldIp, 'fingerprint' => $oldFingerprint],
            ['bound_domain' => null, 'bound_ip' => null, 'fingerprint' => null]
        );

        return true;
    }

    /**
     * Process due license renewals in batch.
     * Delegates each license renewal to the single shared renewLicense() implementation.
     */
    public function processRenewals(): array
    {
        $dueQuery = License::where('auto_renew', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('gateway_subscription_id')
                  ->orWhere('gateway_subscription_id', 'not like', 'sub_%');
            })
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', Carbon::now());

        $results = ['success' => 0, 'failed' => 0];

        $dueQuery->with('product.prices')->chunkById(500, function ($licenses) use (&$results) {
            foreach ($licenses as $license) {
                if ($this->renewLicense($license)) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            }
        });

        return $results;
    }

    /**
     * Single shared implementation for renewing a license.
     * Used by both the ProcessLicenseRenewal job and batch processRenewals().
     */
    public function renewLicense(License $license): bool
    {
        // 1. Status whitelist guard (1c):
        // Suspended, revoked, or cancelled licenses must NEVER be renewed or reactivated.
        if (in_array($license->status, ['suspended', 'revoked', 'cancelled'], true)) {
            \App\Services\AuditService::log('license_renewal_blocked_disallowed_status', $license, [
                'current_status' => $license->status,
            ]);
            return false;
        }

        // Only active licenses or licenses within an active grace window may be renewed
        $isGraceActive = $license->grace_expires_at && $license->grace_expires_at->isFuture();
        if ($license->status !== 'active' && !$isGraceActive) {
            \App\Services\AuditService::log('license_renewal_blocked_disallowed_status', $license, [
                'current_status' => $license->status,
            ]);
            return false;
        }

        // 2. Stripe-managed guard (1e):
        // Stripe subscriptions (sub_...) are managed by Stripe webhooks and must not be renewed by cron/job.
        if ($license->gateway_subscription_id && str_starts_with($license->gateway_subscription_id, 'sub_')) {
            return false;
        }

        // 3. Resolve billing period from subscription price (default 30 days).
        // Use the already-loaded prices collection when available (eager-load path) to avoid
        // N+1 queries when processRenewals() iterates over a chunk of licenses.
        $product = $license->product;
        if ($product && $product->relationLoaded('prices')) {
            $prices = $product->prices;   // Eloquent Collection (no new query)
            $price  = $prices->firstWhere('type', 'subscription')
                   ?? $prices->firstWhere('type', 'full');
        } else {
            $price = $product?->prices()->where('type', 'subscription')->first()
                  ?? $product?->prices()->where('type', 'full')->first();
        }
        $billingPeriod = $price && $price->billing_period ? (int) $price->billing_period : 30;

        // 4. Payment qualification / charge (1d)
        $paymentSuccess = $this->qualifyOrChargeRenewalPayment($license, $price);

        if ($paymentSuccess) {
            // 5. Success path (1f):
            // next_billing_at is aligned with newExpiry (not two periods ahead)
            $base = $license->expires_at ?? Carbon::now();
            $newExpiry = $base->copy()->addDays($billingPeriod);

            $license->update([
                'expires_at' => $newExpiry,
                'next_billing_at' => $newExpiry,
                'last_check_at' => Carbon::now(),
                'grace_expires_at' => null,
                'status' => 'active',
                'auto_renew' => true,
            ]);

            \App\Services\AuditService::log('license_renewed', $license, ['period' => $billingPeriod]);
            return true;
        }

        // 6. Failure path (1a - Infinite grace bug fix):
        // Grace period is granted ONCE, only when grace_expires_at is currently NULL.
        if (is_null($license->grace_expires_at)) {
            $license->update([
                'grace_expires_at' => Carbon::now()->addDays(7),
                'status' => 'active',
            ]);
            \App\Services\AuditService::log('license_renewal_failed_grace_started', $license);
            $this->notifyCustomer($license, 'your recurring payment failed and a 7-day grace period has started');
        } elseif ($license->grace_expires_at->isPast()) {
            if ($license->status !== 'expired') {
                $license->update([
                    'status' => 'expired',
                    'auto_renew' => false,
                ]);
                \App\Services\AuditService::log('license_expired_grace_ended', $license);
                $this->notifyCustomer($license, 'your subscription grace period has ended and the license has expired');
            }
        }
        // If grace is currently active (future), do not re-grant and do not re-notify

        return false;
    }

    /**
     * Charge recurring subscription or qualify an unconsumed verified payment record for this renewal cycle.
     * Enforces explicit linkage to license_id, unique consumption via applied_at, and amount sufficiency (FIX-005 / 1d).
     */
    public function qualifyOrChargeRenewalPayment(License $license, ?ProductPrice $price = null): bool
    {
        // 1. If bKash recurring subscription, delegate to bKash recurring charge
        if ($license->gateway_subscription_id && str_starts_with($license->gateway_subscription_id, 'bkash_')) {
            $renewalJob = new ProcessLicenseRenewal($license);
            if ($renewalJob->chargeRecurringSubscription()) {
                return true;
            }
        }

        // 2. Stripe subscription ID: handled by webhooks only
        if ($license->gateway_subscription_id && str_starts_with($license->gateway_subscription_id, 'sub_')) {
            return false;
        }

        // 3. Strict qualification of renewal payment records (1d):
        // Must be explicitly linked to this specific license: payments.license_id == $license->id
        // Must NOT have been consumed: payments.applied_at IS NULL
        // Must have status 'verified'
        // Amount must be >= required license price for the period (currency-aware)
        $requiredAmount = $price ? (float) $price->amount : 0.0;
        $requiredCurrency = $price ? $price->currency : null;

        $candidatePayment = Payment::where('license_id', $license->id)
            ->whereNull('applied_at')
            ->where('status', 'verified')
            ->orderBy('id', 'asc')
            ->first();

        if ($candidatePayment) {
            if ((float) $candidatePayment->amount < $requiredAmount) {
                \Illuminate\Support\Facades\Log::warning('Renewal payment amount insufficient', [
                    'license_id' => $license->id,
                    'payment_id' => $candidatePayment->id,
                    'payment_amount' => $candidatePayment->amount,
                    'required_amount' => $requiredAmount,
                ]);
                return false;
            }

            // Mark consumed uniquely for this cycle
            $candidatePayment->update([
                'applied_at' => Carbon::now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * Backward-compatible alias for qualifyOrChargeRenewalPayment.
     */
    public function chargeRecurringSubscription(License $license): bool
    {
        return $this->qualifyOrChargeRenewalPayment($license);
    }

    /**
     * Notify customer on subscription renewal lifecycle events (failure / grace / expiration).
     * Uses a proper Mailable so Mail::fake() intercepts it correctly in tests.
     */
    public function notifyCustomer(License $license, string $reason): void
    {
        $user = $license->user;
        if (!$user || empty($user->email)) {
            return;
        }

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)
                ->send(new \App\Mail\SubscriptionBillingNotice($license, $reason));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Subscription billing notification failed', [
                'license_id' => $license->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get or create a Sanctum API token for the user.
     */
    public function getOrCreateApiToken($user)
    {
        $tokenName = 'LMS-API-TOKEN';
        $token = $user->tokens()->where('name', $tokenName)->first();

        if (!$token) {
            $newToken = $user->createToken($tokenName);
            return $newToken->plainTextToken;
        }

        return null;
    }
}
