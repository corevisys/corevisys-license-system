<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\ProductPrice;
use App\Models\SystemSetting;
use App\Services\BKashPaymentService;
use App\Services\StripePaymentService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Which renewal gateways are enabled (stripe/bkash toggled in admin settings,
        // offline/manual always available). Passed to the page so the renew modal can
        // offer an explicit choice instead of blindly POSTing an empty request.
        $gatewaySettings = SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key');
        $availableGateways = [];
        if (($gatewaySettings['gateway_stripe_active'] ?? '0') === '1') {
            $availableGateways[] = 'stripe';
        }
        if (($gatewaySettings['gateway_bkash_active'] ?? '0') === '1') {
            $availableGateways[] = 'bkash';
        }
        $availableGateways[] = 'offline';

        $licenses = License::with('product')
            ->where('user_id', $user->id)
            ->get()
            ->map(function ($l) {
                // Renew is offered when the license is expired, expiring within 30
                // days (or has no expiry), is inside an active grace window, or is a
                // trial. Suspended/revoked/cancelled licenses may NEVER be renewed.
                $blockedFromRenewal = in_array($l->status, ['suspended', 'revoked', 'cancelled'], true);
                $canRenew = ! $blockedFromRenewal && (
                    $l->status === 'expired'
                    || ! $l->expires_at
                    || $l->expires_at->diffInDays(now(), false) <= 30
                    || ($l->grace_expires_at && $l->grace_expires_at->isFuture())
                    || $l->type === 'trial'
                );

                // The plan the license was originally purchased on — excluded from
                // the upgrade list so users are not offered the plan they already have.
                $currentPriceId = $l->order_id
                    ? OrderItem::where('order_id', $l->order_id)
                        ->where('product_id', $l->product_id)
                        ->value('product_price_id')
                    : null;

                return [
                    'id' => $l->id,
                    'product_name' => $l->product->name,
                    'link_renew' => route('licenses.renew', $l->id),
                    'type' => ucfirst($l->type),
                    'key_preview' => 'XXXX-XXXX-' . substr($l->license_key_hash, -4),
                    'status' => $l->status,
                    'expires_at' => $l->expires_at ? $l->expires_at->format('Y-m-d') : null,
                    'last_check_at' => $l->last_check_at ? $l->last_check_at->toDateTimeString() : null,
                    'is_running' => $l->last_check_at && $l->last_check_at->isAfter(now()->subHours(25)),
                    'is_expiring_soon' => $l->expires_at && $l->expires_at->isFuture() && $l->expires_at->diffInDays(now()) <= 30,
                    'can_renew' => $canRenew,
                    'renew_plans' => ProductPrice::where('product_id', $l->product_id)
                        ->whereIn('type', ['subscription', 'full', 'trial'])
                        ->get()
                        ->map(fn ($p) => [
                            'id' => $p->id,
                            'name' => ($p->type === 'subscription' ? ($p->billing_period >= 365 ? 'Yearly' : 'Monthly') : ucfirst($p->type)) . ' Plan',
                            'amount' => $p->amount,
                            'currency' => $p->currency,
                            'type' => $p->type,
                            'billing_period' => $p->billing_period,
                        ]),
                    'last_gateway' => Payment::where('license_id', $l->id)->latest()->value('gateway'),
                    'bound_domain' => $l->bound_domain,
                    'bound_ip' => $l->bound_ip,
                    // Full/lifetime editions are the top tier — no upgrade plans.
                    // Otherwise offer non-trial plans excluding the current one.
                    'upgrade_plans' => $l->type === 'full'
                        ? []
                        : ProductPrice::where('product_id', $l->product_id)
                            ->where('type', '!=', 'trial')
                            ->when($currentPriceId, fn ($q) => $q->where('id', '!=', $currentPriceId))
                            ->get()
                            ->map(fn ($p) => [
                                'id' => $p->id,
                                'name' => ($p->type === 'subscription' ? ($p->billing_period >= 365 ? 'Yearly' : 'Monthly') : ucfirst($p->type)) . ' Plan',
                                'amount' => $p->amount,
                                'currency' => $p->currency,
                                'type' => $p->type
                            ]),
                ];
            });

        return Inertia::render('Licenses', [
            'licenses' => $licenses,
            'availableGateways' => $availableGateways,
        ]);
    }

    public function reveal($id)
    {
        $license = License::where('user_id', auth()->id())->findOrFail($id);

        try {
            $key = $license->key_encrypted;
        } catch (DecryptException $e) {
            Log::warning('Unable to decrypt stored license key', [
                'license_id' => $license->id,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'License key unavailable. Contact support to request a reissue.',
            ], 404);
        }

        if (!$key) {
            return response()->json([
                'message' => 'License key unavailable. Contact support to request a reissue.',
            ], 404);
        }

        Log::info('License key revealed', [
            'license_id' => $license->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['license_key' => $key]);
    }

    public function config($id)
    {
        $license = License::with('product')->findOrFail($id);

        // Ensure user owns the license
        if ($license->user_id !== auth()->id()) {
            abort(403);
        }

        return Inertia::render('LicenseConfig', [
            'license' => [
                'id' => $license->id,
                'product_name' => $license->product->name,
                'product_code' => $license->product?->slug,
                'license_type' => $license->type,
                'status' => $license->status,
                'bound_domain' => $license->bound_domain,
                'bound_ip' => $license->bound_ip,
                'bound_fingerprint' => $license->bound_fingerprint,
                'enforcement_mode' => $license->enforcement_mode ?? 'standard',
                'features' => is_array($license->features) ? $license->features : [],
                'activation_limit' => $license->activation_limit ?? 1,
                // Distinct active domains (not raw activation rows). A bound domain
                // with no recorded activations still occupies at least one seat.
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
            ],
            'api_base_url' => url('/api/v1'),
            'public_key_url' => url('/api/v1/license/public-key'),
            'offline_validity_days' => (int) config('license.offline_validity_days', 7),
            'pulse_interval_days' => (int) config('license.pulse_interval_days', 30),
        ]);
    }

    public function renew($id, Request $request)
    {
        $license = License::where('user_id', auth()->id())->findOrFail($id);

        // Terminal states can never be renewed, irrespective of the request payload.
        if (in_array($license->status, ['suspended', 'revoked', 'cancelled'], true)) {
            return back()->with('error', 'This license cannot be renewed while it is ' . $license->status . '. Please contact support.');
        }

        $gateway = $request->input('gateway');
        if (! in_array($gateway, ['stripe', 'bkash', 'offline'], true)) {
            return back()->with('error', 'Please choose a payment method to continue.');
        }

        // Resolve the price plan in priority order:
        //   1. Explicit product_price_id chosen by the user (monthly vs yearly),
        //      but only if it belongs to this license's product.
        //   2. The price originally purchased on the source order item.
        //   3. Any active subscription price for the product.
        //   4. Any active full price for the product (guarantees we never dead-end).
        $price = null;

        $requestedPriceId = $request->input('product_price_id');
        if ($requestedPriceId) {
            $price = ProductPrice::where('id', $requestedPriceId)
                ->where('product_id', $license->product_id)
                ->first();
        }

        if (! $price) {
            $originalOrder = $license->order;
            $originalItem = $originalOrder
                ? $originalOrder->items()->where('product_id', $license->product_id)->first()
                : null;

            if ($originalItem && $originalItem->product_price_id) {
                $price = ProductPrice::where('id', $originalItem->product_price_id)
                    ->where('product_id', $license->product_id)
                    ->first();
            }
        }

        if (! $price) {
            $price = ProductPrice::where('product_id', $license->product_id)
                ->where('type', 'subscription')
                ->first();
        }

        if (! $price) {
            $price = ProductPrice::where('product_id', $license->product_id)
                ->where('type', 'full')
                ->first();
        }

        if (! $price) {
            return back()->with('error', 'Renewal price not found. Please purchase a new license from the store.');
        }

        // Validate coupon and compute total server-side (never from request input)
        $coupon = $request->input('coupon');
        $discountPercent = 0;
        if ($coupon) {
            $validCoupons = config('services.coupons', [
                'SAVE20' => 20,
                'SAVE50' => 50,
                'FREE100' => 100,
            ]);

            if (!isset($validCoupons[$coupon])) {
                return back()->with('error', 'Invalid or expired coupon code.');
            }

            $discountPercent = (float) $validCoupons[$coupon];
        }

        $computedTotal = round((float) $price->amount * (1 - ($discountPercent / 100)), 2);

        // Policy for total 0 (100% coupon): require explicit admin approval or reject; no automatic free renewal
        if ($computedTotal <= 0) {
            return back()->with('error', '100% discount renewal orders require explicit administrator approval.');
        }

        $isOffline = $gateway === 'offline';

        // Create Renewal Order
        $order = Order::create([
            'order_number' => 'ORD-REN-' . strtoupper(Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $computedTotal,
            'currency' => $price->currency,
            'status' => $isOffline ? 'pending' : 'awaiting_payment',
            'payment_method' => $isOffline ? 'offline' : 'online',
            'type' => 'renewal',
        ]);

        // Create Order Item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $license->product_id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => $price->type,
        ]);

        // Ensure a payment record exists so the callback can resolve + fulfill.
        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => auth()->id(),
            'gateway' => $isOffline ? 'manual' : $gateway,
            'amount' => $computedTotal,
            'status' => 'pending',
        ]);

        // Offline / manual: no hosted checkout. Park the order as pending and let
        // the user submit a receipt for admin verification.
        if ($isOffline) {
            return back()->with('success', 'Renewal order created. Please upload your payment receipt from the Orders page for verification.');
        }

        if ($gateway === 'bkash') {
            try {
                $bkashService = new BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $payment->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                return Inertia::location($bkashResponse['bkashURL']);
            } catch (\Exception $e) {
                Log::error('bKash Renewal Exception: ' . $e->getMessage());
                return back()->with('error', 'Failed to initiate renewal payment. Please try another payment method or contact support.');
            }
        }

        // Stripe: guard against a missing/invalid key so the user gets a friendly
        // message instead of an unhandled 500 from a failed API call.
        if (empty(StripePaymentService::resolveSecret())) {
            Log::error('Stripe renewal aborted: no Stripe secret configured in settings or services config.');
            return back()->with('error', 'Card payments are currently unavailable. Please choose bKash or offline payment, or contact support.');
        }

        try {
            $stripeService = new StripePaymentService();
            $session = $stripeService->createCheckoutSession($order, $price);

            if ($session && $session->url) {
                return Inertia::location($session->url);
            }
        } catch (\Exception $e) {
            Log::error('Stripe Renewal Exception: ' . $e->getMessage());
            return back()->with('error', 'Unable to start the card payment session. Please choose another payment method or contact support.');
        }

        return back()->with('error', 'Failed to initiate renewal payment.');
    }

    public function upgrade($id, Request $request)
    {
        $license = License::where('user_id', auth()->id())->findOrFail($id);
        $newPriceId = $request->input('product_price_id');

        if (!$newPriceId) {
            return back()->with('error', 'Please select a plan to upgrade to.');
        }

        // The target plan must belong to this license's product, otherwise a user
        // could pair a cheap price from another product with this license.
        $price = ProductPrice::where('id', $newPriceId)
            ->where('product_id', $license->product_id)
            ->first();

        if (!$price) {
            return back()->with('error', 'That upgrade plan is not available for this license.');
        }

        $gateway = $request->input('gateway');
        if (! in_array($gateway, ['stripe', 'bkash', 'offline'], true)) {
            return back()->with('error', 'Please choose a payment method to continue.');
        }

        $isOffline = $gateway === 'offline';

        // Create Upgrade Order
        $order = Order::create([
            'order_number' => 'ORD-UPG-' . strtoupper(Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => $isOffline ? 'pending' : 'awaiting_payment',
            'payment_method' => $isOffline ? 'offline' : 'online',
            'type' => 'upgrade',
        ]);

        // Create Order Item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $license->product_id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => $price->type,
        ]);

        // Ensure a payment record exists so the callback can resolve + fulfill.
        $payment = Payment::create([
            'order_id' => $order->id,
            'license_id' => $license->id,
            'user_id' => auth()->id(),
            'gateway' => $isOffline ? 'manual' : $gateway,
            'amount' => $price->amount,
            'status' => 'pending',
        ]);

        // Offline / manual: no hosted checkout. Park the order as pending and let
        // the user submit a receipt for admin verification.
        if ($isOffline) {
            return back()->with('success', 'Upgrade order created. Please upload your payment receipt from the Orders page for verification.');
        }

        if ($gateway === 'bkash') {
            try {
                $bkashService = new BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $payment->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                return Inertia::location($bkashResponse['bkashURL']);
            } catch (\Exception $e) {
                Log::error('bKash Upgrade Exception: ' . $e->getMessage());
                return back()->with('error', 'Failed to initiate upgrade payment. Please try another payment method or contact support.');
            }
        }

        // Stripe: guard against a missing/invalid key so the user gets a friendly
        // message instead of an unhandled 500 from a failed API call.
        if (empty(StripePaymentService::resolveSecret())) {
            Log::error('Stripe upgrade aborted: no Stripe secret configured in settings or services config.');
            return back()->with('error', 'Card payments are currently unavailable. Please choose bKash or offline payment, or contact support.');
        }

        try {
            $stripeService = new StripePaymentService();
            $session = $stripeService->createCheckoutSession($order, $price);

            if ($session && $session->url) {
                return Inertia::location($session->url);
            }
        } catch (\Exception $e) {
            Log::error('Stripe Upgrade Exception: ' . $e->getMessage());
            return back()->with('error', 'Unable to start the card payment session. Please choose another payment method or contact support.');
        }

        return back()->with('error', 'Failed to initiate upgrade payment.');
    }
}