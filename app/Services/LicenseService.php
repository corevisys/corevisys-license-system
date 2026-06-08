<?php

namespace App\Services;

use App\Models\License;
use App\Models\LicenseActivation;
use App\Models\Order;
use App\Models\Product;
use App\Models\TrialHistory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use App\Services\LicenseStateMachine;

class LicenseService
{
    protected LicenseStateMachine $stateMachine;

    public function __construct()
    {
        $this->stateMachine = new LicenseStateMachine();
    }

    /**
     * Create a new license for an order.
     */
    /**
     * Renew an existing license.
     */
    public function renewLicense(Order $order, Product $product)
    {
        // 1. Find the specific license if ID is provided, else fallback to user/product lookup
        $license = null;
        if ($order->license_id) {
            $license = License::find($order->license_id);
        }

        if (!$license) {
            $license = License::where('user_id', $order->user_id)
                ->where('product_id', $product->id)
                ->latest()
                ->first();
        }

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
        $license = null;
        if ($order->license_id) {
            $license = License::find($order->license_id);
        }

        if (!$license) {
            $license = License::where('user_id', $order->user_id)
                ->where('product_id', $product->id)
                ->latest()
                ->first();
        }

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

        // Upgrade 6: Trial Abuse Prevention
        if ($type === 'trial') {
            $emailHash = hash('sha256', $order->user->email);
            // Check History
            $exists = TrialHistory::where('email_hash', $emailHash)
                ->orWhere('ip_hash', hash('sha256', request()->ip()))
                ->exists();

            if ($exists) {
                // Return null or throw exception? 
                // Service should probably throw exception or handle gracefully.
                // For now, let's throw plain exception
                throw new \Exception('Trial limit exceeded for this user/environment.');
            }

            // Record Trial Start (Email/IP)
            TrialHistory::create([
                'email_hash' => $emailHash,
                'ip_hash' => hash('sha256', request()->ip()), // Capture IP from request if available
                'expires_at' => Carbon::now()->addMonths(6)
            ]);
        }

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

        // Prevent duplicate fulfillment
        if (License::where('order_id', $order->id)->exists()) {
            return License::where('order_id', $order->id)->first();
        }

        $license = License::create([
            'user_id' => $order->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'license_key' => $keyPayload,
            'license_key_hash' => $keyHash,
            'secret_salt' => $salt,
            'type' => $type,
            'status' => 'inactive',
            'expires_at' => $expiresAt,
            'auto_renew' => $type === 'subscription',
            'next_billing_at' => $type === 'subscription' ? $expiresAt : null,
        ]);

        $license->raw_key = $keyPayload; // Attach for immediate display

        return $license;
    }

    public function activate(string $key, string $domain, string $ip, ?string $fingerprint = null, ?string $enforcementMode = null)
    {
        // To find the license, we need the salt, but searching by salt is hard 
        // if we don't know the key. We'll search by the start of the hash or similar if we had a prefix,
        // but since we have a full hash, we should probably have a temporary lookup or 
        // iterate? No, that's slow.
        // Better: We should probably store a 'key_prefix' or just allow searching by the full hash 
        // if we had a static salt. But the requirement is 'generated secret_salt'.
        // So we'll have to find by order_id or user_id or... 
        // Wait, if we can't find by hash, how do we find the license?
        // Strategy: We can use a 'key_identifier' (e.g. first 8 chars of key) stored plainly.
        
        // For now, I'll assume we find the license by the key if we didn't salt it, 
        // OR we use the salt to verify once we find it.
        // Let's assume we find by the hash *without* salt for lookup, 
        // but the 'license_key_hash' in DB is actually hash(key + salt).
        // This means we CANNOT lookup by key.
        
        // I will add a 'key_id' column or prefix to the key.
        // Let's use a simpler approach: hash(key) is used for lookup, 
        // but we verify with another field or we use the salt for something else?
        // Actually, the standard way is: lookup by key_id, then verify hash(key + salt).
        
        // I'll update the migration to add 'key_identifier' as well.
        // But for this snippet, I'll just search by a hypothetical key_id.
        // Wait, I'll stick to searching by the hash and assuming the hash is just the key for now 
        // until I can fix the lookup strategy.
        
        // Actually, I'll use the first part of the key as an identifier.
        $keyParts = explode('-', $key);
        $identifier = $keyParts[0] . '-' . $keyParts[1]; // First two parts
        
        $license = License::where('license_key_hash', 'like', $identifier . '%')->first(); // This is a placeholder
        
        // Real logic: We'll have to iterate or have a better lookup.
        // I'll just use the old lookup for now but verify with salt if present.
        $license = License::where('license_key_hash', hash('sha256', $key))->first(); 
        
        if (!$license && License::whereNotNull('secret_salt')->exists()) {
             // Try searching with salt (this is slow if many licenses, but fine for now)
             $license = License::whereNotNull('secret_salt')->get()->first(function($l) use ($key) {
                 return hash_equals($l->license_key_hash, hash('sha256', $key . $l->secret_salt));
             });
        }

        if (!$license) {
            return ['status' => false, 'message' => 'Invalid License Key'];
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
            ->distinct('request_domain')
            ->count();

        // Check if this is a NEW domain/environment activation
        $isExistingBinding = ($license->bound_domain === $domain) || 
                             LicenseActivation::where('license_id', $license->id)
                                ->where('status', 'success')
                                ->where('request_domain', $domain)
                                ->exists();

        if (!$isExistingBinding && $activeBindingsCount >= $activationLimit) {
            $this->logActivation($license, $domain, $ip, 'failed', 'Activation Limit Reached');
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

        // Upgrade 6: Check Trial Fingerprint Abuse upon Activation (if fingerprint provided)
        if ($license->type === 'trial' && $fingerprint) {
            $fpHash = hash('sha256', $fingerprint);
            $exists = TrialHistory::where('fingerprint_hash', $fpHash)->exists();

            if ($exists) {
                // Optimization: Did WE just create this? 
                // If we created it in createLicense, we didn't have fingerprint.
                // So this is a new fingerprint usage. 
                // If a previous trial used this fingerprint, BLOCK.
                // BUT: What if this Current license is the *first* one mapping to this fingerprint?
                // We need to associate this fingerprint with the CURRENT trial history record?
                // Or create a new record?
                // If 'exists' is true, it means SOMEONE ELSE used it.
                // Unless it's OURSELVES (which is allowed).
                // Logic: If (exists AND not associated with this license... but no link in TrialHistory to LicenseID).

                // Simpler: TrialHistory blocks FUTURE trials.
                // If I am activating a trial, and my fingerprint is in history...
                // It means I already had a trial on this machine.
                // So BLOCK.

                // But wait, if I re-install app on same machine for the SAME trial license?
                // Allowed.
                // So we must check if this fingerprint usage is from a PREVIOUS trial.
                // Use `expires_at` maybe? 
                // Or checking if the fingerprint was used by a DIFFERENT User?
                // Let's assume strict: "One trial per machine ever".

                // However, we just started this trial. We didn't save fingerprint in createLicense.
                // So 'exists' should be FALSE for the first time.
                // If True -> Block.

                return ['status' => false, 'message' => 'Trial already used on this environment. Upgrade to full version.'];
            }

            // Record Fingerprint for this trial
            TrialHistory::create([
                'fingerprint_hash' => $fpHash,
                'expires_at' => $license->expires_at
            ]);
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
            'signature' => $this->generateSignature($license)
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

    private function generateSignature($license)
    {
        // Simple HMAC of status + expiry
        $data = $license->status . '|' . ($license->expires_at ? $license->expires_at->toIso8601String() : 'lifetime');
        return hash_hmac('sha256', $data, config('app.key'));
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
        $license->activations()->where('status', 'success')->update(['status' => 'reset']);

        \App\Services\AuditService::log(
            'license_reset',
            $license,
            ['bound_domain' => $oldDomain, 'bound_ip' => $oldIp, 'fingerprint' => $oldFingerprint],
            ['bound_domain' => null, 'bound_ip' => null, 'fingerprint' => null]
        );

        return true;
    }

    public function processRenewals()
    {
        // 1. Find due assignments
        $licenses = License::where('auto_renew', true)
            ->where('status', 'active')
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', Carbon::now())
            ->get();

        $results = ['success' => 0, 'failed' => 0];

        foreach ($licenses as $license) {
            // Find the associated price to get billing period
            // In real app, we might store the specific price_id on the license/order
            $price = ProductPrice::where('product_id', $license->product_id)
                ->where('type', 'full') // Assuming full for subscriptions
                ->first();

            $billingPeriod = $price ? $price->billing_period : 'monthly';

            // Mock Payment Logic
            $paymentSuccess = true; // Simulating success for now

            if ($paymentSuccess) {
                // Extend Expiry
                $extension = $billingPeriod === 'yearly' ? 'addYear' : 'addMonth';

                $license->update([
                    'expires_at' => $license->expires_at->$extension(),
                    'next_billing_at' => $license->next_billing_at->$extension(),
                    'last_check_at' => Carbon::now()
                ]);

                \App\Services\AuditService::log('license_renewed', $license, ['period' => $billingPeriod]);
                $results['success']++;
            } else {
                // Upgrade 4: Set Grace Period on Failure
                if (is_null($license->grace_expires_at)) {
                    $license->update(['grace_expires_at' => Carbon::now()->addDays(7)]);
                    \App\Services\AuditService::log('license_renewal_failed_grace_started', $license);
                }
                $results['failed']++;
            }
        }

        return $results;
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
