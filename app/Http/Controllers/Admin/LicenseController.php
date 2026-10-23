<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Services\AuditService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $licenses = License::with(['user', 'product'])->latest()->get()->map(fn ($l) => [
            'id' => $l->id,
            'user_name' => $l->user->name,
            'user_email' => $l->user->email,
            'product_name' => $l->product->name,
            'key_preview' => 'XXXX-XXXX-' . substr($l->license_key_hash, -4),
            'type' => ucfirst($l->type),
            'status' => $l->status,
            'expires_at' => $l->expires_at ? $l->expires_at->format('Y-m-d') : 'Lifetime',
            'last_check_at' => $l->last_check_at ? $l->last_check_at->toDateTimeString() : null,
            'is_running' => $l->isRunning(),
        ]);
        return Inertia::render('Admin/Licenses', ['licenses' => $licenses]);
    }

    public function show($id)
    {
        $license = License::with(['user', 'product', 'activations' => function ($q) {
            $q->latest()->limit(50);
        }])->findOrFail($id);

        return Inertia::render('Admin/LicenseDetails', [
            'license' => [
                'id' => $license->id,
                'user_name' => $license->user->name,
                'user_email' => $license->user->email,
                'product_name' => $license->product->name,
                'license_key' => (function () use ($license) {
                    try {
                        return $license->key_encrypted ?: null;
                    } catch (DecryptException $e) {
                        Log::warning('Unable to decrypt stored license key for admin details', [
                            'license_id' => $license->id,
                        ]);

                        return null;
                    }
                })(),
                'type' => ucfirst($license->type),
                'status' => $license->status,
                'features' => $license->features ?? [],
                'enforcement_mode' => $license->enforcement_mode ?? 'active',
                'bound_domain' => $license->bound_domain,
                'bound_ip' => $license->bound_ip,
                'bound_fingerprint' => $license->bound_fingerprint,
                'activation_limit' => $license->activation_limit ?? 1,
                // Count distinct active domains rather than raw activation rows,
                // so repeated pulses from one domain don't inflate usage.
                // A bound domain with no recorded activations still uses 1 seat.
                'current_usage' => max(
                    (int) $license->activations()
                        ->where('status', 'success')
                        ->whereNotNull('request_domain_normalized')
                        ->distinct()
                        ->count('request_domain_normalized'),
                    $license->bound_domain ? 1 : 0
                ),
                'expires_at' => $license->expires_at ? $license->expires_at->format('Y-m-d') : 'Lifetime',
                'last_check_at' => $license->last_check_at ? $license->last_check_at->toDateTimeString() : null,
                'next_expected_heartbeat' => $license->last_check_at ? $license->last_check_at->copy()->addDays((int) config('license.pulse_interval_days', 30))->toDateTimeString() : null,
                'is_running' => $license->isRunning(),
                'history' => $license->activations->map(fn ($a) => [
                    'id' => $a->id,
                    'created_at' => $a->created_at->toDateTimeString(),
                    'request_domain' => $a->request_domain,
                    'request_ip' => $a->request_ip,
                    'status' => $a->status,
                ])
            ]
        ]);
    }

    public function updateStatus($id, Request $request)
    {
        $license = License::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:active,inactive,expired,suspended,revoked',
        ]);

        $oldStatus = $license->status;
        $license->update(['status' => $request->status]);

        AuditService::log(
            'license_status_updated',
            $license,
            ['status' => $oldStatus],
            ['status' => $request->status]
        );

        return back()->with('success', 'License status updated.');
    }

    public function updateFeatures($id, Request $request)
    {
        $data = $request->validate([
            'features' => 'present|array|max:100',
            'features.*' => ['required', 'string', 'max:100', 'distinct', 'regex:/^[A-Za-z0-9._:-]+$/'],
        ]);

        $license = License::findOrFail($id);
        $oldFeatures = $license->features ?? [];
        $newFeatures = array_values($data['features']);
        $license->update(['features' => $newFeatures]);

        AuditService::log(
            'license_features_updated',
            $license,
            ['features' => $oldFeatures],
            ['features' => $newFeatures]
        );

        return back()->with('success', 'License features updated.');
    }

    public function history($id)
    {
        $license = License::findOrFail($id);
        return $license->activations()->latest()->limit(50)->get();
    }

    public function resetBinding($id)
    {
        $license = License::findOrFail($id);

        $license->update([
            'bound_ip' => null,
            'bound_domain' => null,
            'bound_fingerprint' => null,
            'activated_at' => null,
        ]);

        // Mark previous successful activations as failed so they no longer
        // count toward the activation limit and don't block reactivation.
        $license->activations()
            ->where('status', 'success')
            ->update([
                'status' => 'failed',
                'failure_reason' => 'Binding reset by admin',
            ]);

        AuditService::log('license_binding_reset', $license);

        return back()->with('success', 'License environment bindings reset successfully. The license can now be activated on a new domain.');
    }
}