<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Public marketing routes
|--------------------------------------------------------------------------
| Compact, frontend-only public site. These routes render Inertia pages
| and never touch business logic, services, payments, or license state.
*/

// Corevisys Installation Routes (Disabled once installed.lock exists)
Route::middleware('web')->group(function () {
    Route::get('/install', [\App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/install/test-db', [\App\Http\Controllers\InstallController::class, 'testDb'])->name('install.testDb');
    Route::post('/install', [\App\Http\Controllers\InstallController::class, 'install'])->name('install.process');
    Route::get('/install/success', [\App\Http\Controllers\InstallController::class, 'success'])->name('install.success');
});

Route::get('/', function () {
    if (!app()->environment('testing') && !file_exists(storage_path('installed.lock'))) {
        return redirect()->route('install.index');
    }

    try {
        if (!app()->environment('testing') && !\Illuminate\Support\Facades\Schema::hasTable('products')) {
            return redirect()->route('install.index');
        }
    } catch (\Throwable $e) {
        if (!app()->environment('testing')) {
            return redirect()->route('install.index');
        }
    }

    return Inertia::render('Public/Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'featuredProducts' => \App\Models\Product::where('is_active', true)
            ->orderBy('name')
            ->take(3)
            ->get(['id', 'name', 'slug', 'description']),
    ]);
})->name('home');

Route::get('/pricing', function () {
    return Inertia::render('Public/Pricing', [
        'products' => \App\Models\Product::where('is_active', true)->with('prices')->get(),
        'gatewaySettings' => \App\Models\SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key'),
    ]);
})->name('pricing');

// Per-product page. Only active products resolve; unknown or inactive slugs
// render a friendly 404 inside the public layout.
Route::get('/pricing/{slug}', function (string $slug) {
    $product = \App\Models\Product::where('slug', $slug)
        ->where('is_active', true)
        ->with('prices')
        ->first();

    if (! $product) {
        return Inertia::render('Public/NotFound', [
            'message' => 'That product is not available. It may not exist, or it is no longer on sale.',
        ])->toResponse(request())->setStatusCode(404);
    }

    return Inertia::render('Public/Product', [
        'product' => $product,
        'otherProducts' => \App\Models\Product::where('is_active', true)
            ->where('id', '!=', $product->id)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description']),
        'gatewaySettings' => \App\Models\SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key'),
    ]);
})->where('slug', '[A-Za-z0-9._-]+')->name('pricing.product');

Route::get('/developers', function () {
    return Inertia::render('Public/Developers');
})->name('developers');

Route::get('/contact', function () {
    return Inertia::render('Public/Contact');
})->name('contact');

Route::get('/privacy', function () {
    return Inertia::render('Public/Privacy');
})->name('privacy');

Route::get('/terms', function () {
    return Inertia::render('Public/Terms');
})->name('terms');

Route::get('/cookies', function () {
    return Inertia::render('Public/Cookies');
})->name('cookies');

/*
|--------------------------------------------------------------------------
| Legacy URL redirects (301 permanent)
|--------------------------------------------------------------------------
| Old marketing URLs are permanently redirected so inbound links,
| bookmarks, and search rankings keep working.
*/

Route::redirect('/company', '/', 301);
Route::redirect('/insights', '/', 301);
Route::redirect('/careers', '/', 301);
Route::redirect('/case-study', '/', 301);
Route::get('/case-study/{slug}', fn () => redirect('/', 301));
Route::redirect('/services/custom-software', '/', 301);
Route::redirect('/services/web-applications', '/', 301);
Route::redirect('/services/mobile-apps', '/', 301);
Route::redirect('/services/ai-ml', '/', 301);
Route::redirect('/services/cloud-solutions', '/', 301);
Route::redirect('/privacy-policy', '/privacy', 301);
Route::redirect('/terms-of-service', '/terms', 301);
Route::redirect('/cookie-policy', '/cookies', 301);

Route::post('/order/create', function (\Illuminate\Http\Request $request) {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $request->validate([
        'product_id' => 'required|exists:products,id',
        'price_id' => 'required|exists:product_prices,id',
        'payment_method' => 'required|string|in:online,offline',
        // For offline payments, transaction_id might be manually entered or generated.
        // For online, it comes from the gateway response later, but here we initiate.
        'transaction_id' => 'nullable|string',
        'gateway' => 'nullable|string', 
    ]);

    // Ensure the selected price actually belongs to the selected product,
    // otherwise a user could pair a cheap price with an expensive product.
    $price = \App\Models\ProductPrice::where('id', $request->price_id)
        ->where('product_id', $request->product_id)
        ->firstOrFail();
    $product = \App\Models\Product::findOrFail($request->product_id);

    abort_unless($product->is_active, 422, 'Product Unavailable');

    // Create Order
    $order = \App\Models\Order::create([
        'order_number' => 'ORD-' . strtoupper(\Illuminate\Support\Str::random(10)),
        'user_id' => auth()->id(),
        'total_amount' => $price->amount,
        'currency' => $price->currency,
        'status' => $request->payment_method === 'offline' ? 'pending' : 'awaiting_payment', // Pending verification vs waiting for gateway
        'payment_method' => $request->payment_method,
    ]);

    // Create Order Item
    if (class_exists(\App\Models\OrderItem::class)) {
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => in_array($price->type, ['trial', 'full', 'subscription']) ? $price->type : 'full',
        ]);
    }

    \Illuminate\Support\Facades\Log::info('Order Create initiated', $request->all());

    // Create Initial Payment Record
    $payment = \App\Models\Payment::create([
        'order_id' => $order->id,
        'user_id' => auth()->id(),
        'gateway' => $request->payment_method === 'offline' ? 'manual' : ($request->gateway ?? 'stripe'),
        'transaction_id' => $request->transaction_id ?? null,
        'amount' => $price->amount,
        'status' => 'pending',
    ]);

    // Handle Stripe Online Payment
    if ($request->payment_method === 'online' && ($request->gateway === 'stripe' || !$request->gateway)) {
        try {
            $stripeService = new \App\Services\StripePaymentService();
            $checkout_session = $stripeService->createCheckoutSession($order, $price);

            \Illuminate\Support\Facades\Log::info('Stripe Session Created: ' . $checkout_session->id);
            return Inertia::location($checkout_session->url);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe Exception: ' . $e->getMessage());
            return back()->with('error', 'Payment Initialization Failed: ' . $e->getMessage());
        }
    }

    // Handle bKash Online Payment (Tokenized Checkout)
    if ($request->payment_method === 'online' && $request->gateway === 'bkash') {
        try {
            $bkashService = new \App\Services\BKashPaymentService();
            $bkashResponse = $bkashService->createPayment($order, $price);

            $payment->update([
                'transaction_id' => $bkashResponse['paymentID'],
                'gateway_response' => $bkashResponse,
            ]);

            \Illuminate\Support\Facades\Log::info('bKash Payment Created: ' . $bkashResponse['paymentID']);
            return Inertia::location($bkashResponse['bkashURL']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('bKash Exception: ' . $e->getMessage());
            return back()->with('error', 'Payment Initialization Failed: ' . $e->getMessage());
        }
    }

    return redirect()->route('orders')->with('success', 'Order created successfully!');
})->name('order.create');

Route::get('/orders/stripe/success', function (\Illuminate\Http\Request $request) {
    try {
        $stripeService = new \App\Services\StripePaymentService();
        $session = $stripeService->retrieveSession($request->get('session_id'));
        
        if ($session->payment_status === 'paid') {
            $orderId = $session->metadata->order_id ?? $session->client_reference_id;
            $order = \App\Models\Order::with('payment')->findOrFail($orderId);
            
            // Fulfillment Fallback (if webhook hasn't processed it yet)
            // Use Centralized Service
            try {
                $fulfillmentService = app(\App\Services\OrderFulfillmentService::class);
                $result = $fulfillmentService->fulfillOrder($order, [
                    'transaction_id' => $session->payment_intent ?? $session->id,
                    'gateway_response' => $session->toArray()
                ]);

                // If result is returned, it means we just fulfilled it. Flash only non-sensitive references.
                if ($result) {
                    $license = $result['license'];
                    $apiToken = $result['api_token'];

                    if ($license) {
                        session()->flash('new_license_key', 'XXXX-XXXX-' . substr($license->license_key_hash ?? '', -4));
                    }
                    if ($apiToken) {
                        session()->flash('new_api_token', $apiToken);
                    }
                }
            } catch (\Exception $e) {
                 \Illuminate\Support\Facades\Log::error("Success Callback: Fulfillment Error", ['order_id' => $orderId, 'error' => $e->getMessage()]);
            }

            return redirect()->route('dashboard')->with('success', 'Payment successful! Your license and API Key have been generated.');
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Success Callback Fallback Error: " . $e->getMessage());
        return redirect()->route('orders')->with('error', 'Payment verification failed.');
    }

    return redirect()->route('orders')->with('error', 'Payment was not completed.');
})->name('orders.stripe.success');

Route::get('/orders/stripe/cancel', function () {
    return redirect()->route('orders')->with('info', 'Payment was cancelled.');
})->name('orders.stripe.cancel');

Route::get('/orders/bkash/callback', function (\Illuminate\Http\Request $request) {
    $paymentID = is_string($request->get('paymentID')) ? trim($request->get('paymentID')) : null;

    if (!$paymentID) {
        return redirect()->route('orders')->with('error', 'Payment was not completed.');
    }

    $payment = \App\Models\Payment::where('gateway', 'bkash')
        ->where(function ($query) use ($paymentID) {
            $query->where('transaction_id', $paymentID)
                ->orWhere('gateway_response->paymentID', $paymentID);
        })
        ->with(['order.licenses', 'order.items.product', 'order.license'])
        ->first();

    if (!$payment || !$payment->order) {
        \Illuminate\Support\Facades\Log::error('bKash Callback: Payment not found.', ['payment_id' => $paymentID]);
        return redirect()->route('orders')->with('error', 'Payment verification failed.');
    }

    if (auth()->check() && (int) $payment->user_id !== (int) auth()->id()) {
        \Illuminate\Support\Facades\Log::warning('bKash Callback: User mismatch.', [
            'payment_id' => $paymentID,
            'payment_user_id' => $payment->user_id,
            'auth_user_id' => auth()->id(),
        ]);
        return redirect()->route('orders')->with('error', 'Unauthorized payment callback.');
    }

    $lock = \Illuminate\Support\Facades\Cache::lock("bkash_payment_process:{$paymentID}", 15);
    try {
        $lock->block(5);
    } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
        \Illuminate\Support\Facades\Log::warning('bKash Callback: Lock timeout.', ['payment_id' => $paymentID]);
        return redirect()->route('orders')->with('info', 'Your payment is currently being processed. Please refresh in a moment.');
    }

    try {
        $payment->refresh();
        $order = $payment->order;

        if ($order && $order->type === 'renewal' && $order->renewal_cycle_at
            && !app(\App\Services\BkashRenewalCheckoutService::class)->isMatchingRenewalPayment($payment)) {
            \Illuminate\Support\Facades\Log::warning('bKash Callback: Renewal payment/order/license mismatch.', [
                'payment_id' => $paymentID,
                'payment_record_id' => $payment->id,
                'order_id' => $order->id,
            ]);
            return redirect()->route('orders')->with('error', 'Payment verification failed.');
        }

        if ($order && ($order->status === \App\Support\OrderStatus::COMPLETED || $payment->status === 'verified')) {
            \Illuminate\Support\Facades\Log::info('bKash Callback: Payment/order already completed. Idempotent return.', ['payment_id' => $paymentID]);
            $existingLicense = $order->licenses()->first()
                ?? ($order->license_id ? \App\Models\License::find($order->license_id) : null)
                ?? $order->license;

            if ($existingLicense) {
                session()->flash('new_license_key', 'XXXX-XXXX-' . substr($existingLicense->license_key_hash ?? '', -4));
            }
            return redirect()->route('dashboard')->with('success', 'Payment successful! Your license is active.');
        }

        $bkashService = app(\App\Services\BKashPaymentService::class);
        $result = $bkashService->executeOrQueryPayment($paymentID);

        if (($result['transactionStatus'] ?? '') === 'Completed') {
            if ($order->type === 'renewal' && $order->renewal_cycle_at && !$bkashService->isPaymentAmountValid($order, $result)) {
                \Illuminate\Support\Facades\Log::warning('bKash Callback: Renewal amount mismatch.', [
                    'payment_id' => $paymentID,
                    'order_id' => $order->id,
                ]);
                return redirect()->route('orders')->with('error', 'Payment verification failed.');
            }

            try {
                $fulfillmentService = app(\App\Services\OrderFulfillmentService::class);
                $trxID = $result['trxID'] ?? $result['paymentID'];
                $fulfillment = $fulfillmentService->fulfillOrder($order, [
                    'transaction_id' => $trxID,
                    'gateway_response' => array_merge($payment->gateway_response ?? [], $result, ['paymentID' => $paymentID]),
                ]);

                if ($fulfillment) {
                    $license = $fulfillment['license'];
                    $apiToken = $fulfillment['api_token'];

                    if ($license) {
                        session()->flash('new_license_key', 'XXXX-XXXX-' . substr($license->license_key_hash ?? '', -4));
                    }
                    if ($apiToken) {
                        session()->flash('new_api_token', $apiToken);
                    }
                }

                return redirect()->route('dashboard')->with('success', 'Payment successful! Your license and API Key have been generated.');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('bKash Callback: Fulfillment error.', ['payment_id' => $paymentID, 'error' => $e->getMessage()]);
                return redirect()->route('orders')->with('error', 'Payment verification failed.');
            }
        }

        \Illuminate\Support\Facades\Log::warning('bKash Callback: Payment not completed.', [
            'payment_id' => $paymentID,
            'status' => $result['transactionStatus'] ?? 'unknown',
        ]);
        return redirect()->route('orders')->with('error', 'Payment was not completed.');
    } finally {
        optional($lock)->release();
    }
})->name('orders.bkash.callback');

Route::get('/dashboard', function () {
    $user = auth()->user();

    return Inertia::render('Dashboard', [
        'stats' => [
            'active_count' => \App\Models\License::where('user_id', $user->id)->where('status', 'active')->count(),
            'expiring_count' => \App\Models\License::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expires_at', '<=', now()->addDays(30))
                ->count(),
            'total_spent' => \App\Models\Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount'),
        ],
        'recentLicenses' => \App\Models\License::with('product')
            ->where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/store', function () {
        return Inertia::render('Store', [
            'products' => \App\Models\Product::where('is_active', true)->with('prices')->get(),
            'gatewaySettings' => \App\Models\SystemSetting::where('key', 'like', 'gateway_%_active')
                ->pluck('value', 'key'),
        ]);
    })->name('store');

    Route::get('/licenses', function () {
        $user = auth()->user();

        // Which renewal gateways are enabled (stripe/bkash toggled in admin settings,
        // offline/manual always available). Passed to the page so the renew modal can
        // offer an explicit choice instead of blindly POSTing an empty request.
        $gatewaySettings = \App\Models\SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key');
        $availableGateways = [];
        if (($gatewaySettings['gateway_stripe_active'] ?? '0') === '1') {
            $availableGateways[] = 'stripe';
        }
        if (($gatewaySettings['gateway_bkash_active'] ?? '0') === '1') {
            $availableGateways[] = 'bkash';
        }
        $availableGateways[] = 'offline';

        $licenses = \App\Models\License::with('product')
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
                    ? \App\Models\OrderItem::where('order_id', $l->order_id)
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
                    'renew_plans' => \App\Models\ProductPrice::where('product_id', $l->product_id)
                        ->whereIn('type', ['subscription', 'full', 'trial'])
                        ->get()
                        ->map(fn($p) => [
                            'id' => $p->id,
                            'name' => ($p->type === 'subscription' ? ($p->billing_period >= 365 ? 'Yearly' : 'Monthly') : ucfirst($p->type)) . ' Plan',
                            'amount' => $p->amount,
                            'currency' => $p->currency,
                            'type' => $p->type,
                            'billing_period' => $p->billing_period,
                        ]),
                    'last_gateway' => \App\Models\Payment::where('license_id', $l->id)->latest()->value('gateway'),
                    'bound_domain' => $l->bound_domain,
                    'bound_ip' => $l->bound_ip,
                    // Full/lifetime editions are the top tier — no upgrade plans.
                    // Otherwise offer non-trial plans excluding the current one.
                    'upgrade_plans' => $l->type === 'full'
                        ? []
                        : \App\Models\ProductPrice::where('product_id', $l->product_id)
                            ->where('type', '!=', 'trial')
                            ->when($currentPriceId, fn ($q) => $q->where('id', '!=', $currentPriceId))
                            ->get()
                            ->map(fn($p) => [
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
    })->name('licenses');

    Route::post('/licenses/{id}/reveal', function ($id) {
        $license = \App\Models\License::where('user_id', auth()->id())->findOrFail($id);

        try {
            $key = $license->key_encrypted;
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            \Illuminate\Support\Facades\Log::warning('Unable to decrypt stored license key', [
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

        \Illuminate\Support\Facades\Log::info('License key revealed', [
            'license_id' => $license->id,
            'user_id' => auth()->id(),
        ]);

        return response()->json(['license_key' => $key]);
    })->middleware('throttle:5,1')->name('licenses.reveal');

    Route::get('/licenses/{id}/config', function ($id) {
        $license = \App\Models\License::with('product')->findOrFail($id);

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
    })->name('licenses.config');

    Route::post('/licenses/{id}/renew', function ($id, \Illuminate\Http\Request $request) {
        $license = \App\Models\License::where('user_id', auth()->id())->findOrFail($id);

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
            $price = \App\Models\ProductPrice::where('id', $requestedPriceId)
                ->where('product_id', $license->product_id)
                ->first();
        }

        if (! $price) {
            $originalOrder = $license->order;
            $originalItem = $originalOrder
                ? $originalOrder->items()->where('product_id', $license->product_id)->first()
                : null;

            if ($originalItem && $originalItem->product_price_id) {
                $price = \App\Models\ProductPrice::where('id', $originalItem->product_price_id)
                    ->where('product_id', $license->product_id)
                    ->first();
            }
        }

        if (! $price) {
            $price = \App\Models\ProductPrice::where('product_id', $license->product_id)
                ->where('type', 'subscription')
                ->first();
        }

        if (! $price) {
            $price = \App\Models\ProductPrice::where('product_id', $license->product_id)
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
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-REN-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $computedTotal,
            'currency' => $price->currency,
            'status' => $isOffline ? 'pending' : 'awaiting_payment',
            'payment_method' => $isOffline ? 'offline' : 'online',
            'type' => 'renewal',
        ]);

        // Create Order Item
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $license->product_id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => $price->type,
        ]);

        // Ensure a payment record exists so the callback can resolve + fulfill.
        $payment = \App\Models\Payment::create([
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
                $bkashService = new \App\Services\BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $payment->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                return \Inertia\Inertia::location($bkashResponse['bkashURL']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('bKash Renewal Exception: ' . $e->getMessage());
                return back()->with('error', 'Failed to initiate renewal payment. Please try another payment method or contact support.');
            }
        }

        // Stripe: guard against a missing/invalid key so the user gets a friendly
        // message instead of an unhandled 500 from a failed API call.
        if (empty(\App\Services\StripePaymentService::resolveSecret())) {
            \Illuminate\Support\Facades\Log::error('Stripe renewal aborted: no Stripe secret configured in settings or services config.');
            return back()->with('error', 'Card payments are currently unavailable. Please choose bKash or offline payment, or contact support.');
        }

        try {
            $stripeService = new \App\Services\StripePaymentService();
            $session = $stripeService->createCheckoutSession($order, $price);

            if ($session && $session->url) {
                return \Inertia\Inertia::location($session->url);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe Renewal Exception: ' . $e->getMessage());
            return back()->with('error', 'Unable to start the card payment session. Please choose another payment method or contact support.');
        }

        return back()->with('error', 'Failed to initiate renewal payment.');
    })->name('licenses.renew');

    Route::post('/licenses/{id}/upgrade', function ($id, \Illuminate\Http\Request $request) {
        $license = \App\Models\License::where('user_id', auth()->id())->findOrFail($id);
        $newPriceId = $request->input('product_price_id');

        if (!$newPriceId) {
            return back()->with('error', 'Please select a plan to upgrade to.');
        }

        // The target plan must belong to this license's product, otherwise a user
        // could pair a cheap price from another product with this license.
        $price = \App\Models\ProductPrice::where('id', $newPriceId)
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
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-UPG-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => $isOffline ? 'pending' : 'awaiting_payment',
            'payment_method' => $isOffline ? 'offline' : 'online',
            'type' => 'upgrade',
        ]);

        // Create Order Item
        \App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $license->product_id,
            'product_price_id' => $price->id,
            'price' => $price->amount,
            'license_type' => $price->type,
        ]);

        // Ensure a payment record exists so the callback can resolve + fulfill.
        $payment = \App\Models\Payment::create([
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
                $bkashService = new \App\Services\BKashPaymentService();
                $bkashResponse = $bkashService->createPayment($order, $price);

                $payment->update([
                    'transaction_id' => $bkashResponse['paymentID'],
                    'gateway_response' => $bkashResponse,
                ]);

                return \Inertia\Inertia::location($bkashResponse['bkashURL']);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('bKash Upgrade Exception: ' . $e->getMessage());
                return back()->with('error', 'Failed to initiate upgrade payment. Please try another payment method or contact support.');
            }
        }

        // Stripe: guard against a missing/invalid key so the user gets a friendly
        // message instead of an unhandled 500 from a failed API call.
        if (empty(\App\Services\StripePaymentService::resolveSecret())) {
            \Illuminate\Support\Facades\Log::error('Stripe upgrade aborted: no Stripe secret configured in settings or services config.');
            return back()->with('error', 'Card payments are currently unavailable. Please choose bKash or offline payment, or contact support.');
        }

        try {
            $stripeService = new \App\Services\StripePaymentService();
            $session = $stripeService->createCheckoutSession($order, $price);

            if ($session && $session->url) {
                return \Inertia\Inertia::location($session->url);
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Stripe Upgrade Exception: ' . $e->getMessage());
            return back()->with('error', 'Unable to start the card payment session. Please choose another payment method or contact support.');
        }

        return back()->with('error', 'Failed to initiate upgrade payment.');
    })->name('licenses.upgrade');



    Route::get('/orders', function () {
        $user = auth()->user();
        $orders = \App\Models\Order::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn($o) => [
                'id' => 'ORD-' . str_pad($o->id, 4, '0', STR_PAD_LEFT),
                'created_at' => $o->created_at->format('Y-m-d'),
                'amount' => $o->total_amount,
                'currency' => $o->currency,
                'status' => $o->status,
                'payment_method' => $o->payment_method,
                'gateway' => $o->payment?->gateway,
            ]);

        return Inertia::render('Orders', ['orders' => $orders]);
    })->name('orders');

    Route::get('/orders/{id}/invoice', function ($id) {
        $order = \App\Models\Order::where('user_id', auth()->id())->findOrFail($id);
        
        if ($order->payment_method === 'online' || $order->payment?->gateway === 'stripe') {
            if ($order->payment && $order->payment->transaction_id) {
                try {
                    $stripeService = new \App\Services\StripePaymentService();
                    $url = $stripeService->getReceiptUrl($order->payment->transaction_id);
                    if ($url) {
                        return redirect()->away($url);
                    }
                } catch (\Exception $e) {
                    // Log error
                }
            }
            return back()->with('error', 'Receipt not available yet from Stripe.');
        } elseif ($order->payment?->gateway === 'bkash') {
            return back()->with('info', 'bKash does not provide a hosted receipt. Please use the bKash app / SMS for your transaction details.');
        } elseif ($order->payment_method === 'offline' && $order->payment?->payment_proof_path) {
             return \Illuminate\Support\Facades\Storage::disk(config('receipt.storage_disk', 'local'))->download($order->payment->payment_proof_path);
        }

        return back()->with('error', 'Invoice not found.');
    })->name('orders.invoice');

    Route::get('/analytics', function () {
        $user = auth()->user();

        // 1. License Distribution by Product
        $licenseDistribution = \App\Models\License::where('user_id', $user->id)
            ->with('product')
            ->get()
            ->groupBy('product.name')
            ->map(fn($group) => $group->count());

        // 2. Spending Trend (Last 6 Months)
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i)->format('M'));
        $spendingTrend = collect(range(5, 0))->map(function ($i) use ($user) {
            $date = now()->subMonths($i);
            return \App\Models\Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        });

        // 3. Status Summary
        $statusSummary = \App\Models\License::where('user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Analytics', [
            'stats' => [
                'active_licenses' => $statusSummary['active'] ?? 0,
                'total_revenue' => \App\Models\Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount'),
                'avg_uptime' => 99.99,
                'server_load' => rand(15, 45), // Mocked load but slightly more realistic range
            ],
            'charts' => [
                'distribution' => $licenseDistribution,
                'orders_trend' => $spendingTrend,
                'months' => $months,
                'status_summary' => $statusSummary,
            ]
        ]);
    })->name('analytics');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/theme', function (\Illuminate\Http\Request $request) {
        $request->validate(['theme' => 'required|string']);
        $user = auth()->user();
        $user->theme_preference = $request->theme;
        $user->save();
        return back();
    })->name('profile.update-theme');

    // Admin Routes
    Route::middleware('can:admin')->prefix('admin')->group(function () {
        Route::get('/orders', function () {
            $orders = \App\Models\Order::with(['user', 'payment'])->latest()->get()->map(fn($o) => [
                'id' => $o->id,
                'order_number' => $o->order_number,
                'user_name' => $o->user->name,
                'user_email' => $o->user->email,
                'total_amount' => $o->total_amount,
                'currency' => $o->currency,
                'status' => $o->status,
                'payment_method' => $o->payment_method,
                'created_at' => $o->created_at->format('Y-m-d H:i'),
                'transaction_id' => $o->payment?->transaction_id ?? 'N/A', // Assuming one payment per order for now
            ]);

            return Inertia::render('Admin/Orders', ['orders' => $orders]);
        })->name('admin.orders');

        Route::post('/orders/{id}/verify', function ($id) {
            $order = \App\Models\Order::with('payment')->findOrFail($id);

            try {
                if (!$order->payment) {
                    throw new \RuntimeException('Order has no payment record.');
                }

                app(\App\Services\OrderFulfillmentService::class)->fulfillOrder($order, [
                    'verified_by' => auth()->id(),
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('Admin order fulfillment failed', [
                    'order_id' => $order->id,
                    'exception' => get_class($e),
                ]);

                $message = 'Could not confirm this order. Please review it and try again.';
                return back()->with('error', $message)->withErrors(['fulfillment' => $message]);
            }

            return back()->with('success', 'Order verified successfully.');
        })->name('admin.orders.verify');

        Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Admin\AnalyticsController::class, 'dashboard'])
            ->name('admin.dashboard');

        Route::get('/analytics', [\App\Http\Controllers\Api\V1\Admin\AnalyticsController::class, 'analytics'])
            ->name('admin.analytics');

        Route::get('/teams', function () {
            $teams = \App\Models\Team::with('owner')->get()->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'owner_name' => $t->owner->name,
                'member_count' => $t->users()->count(),
                'active_licenses' => $t->licenses()->where('status', 'active')->count(),
            ]);
            return Inertia::render('Admin/Teams', ['teams' => $teams]);
        })->name('admin.teams');

        Route::get('/licenses', function () {
            $licenses = \App\Models\License::with(['user', 'product'])->latest()->get()->map(fn($l) => [
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
        })->name('admin.licenses');

        Route::get('/licenses/{id}', function ($id) {
            $license = \App\Models\License::with(['user', 'product', 'activations' => function($q) {
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
                        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                            \Illuminate\Support\Facades\Log::warning('Unable to decrypt stored license key for admin details', [
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
                    'history' => $license->activations->map(fn($a) => [
                        'id' => $a->id,
                        'created_at' => $a->created_at->toDateTimeString(),
                        'request_domain' => $a->request_domain,
                        'request_ip' => $a->request_ip,
                        'status' => $a->status,
                    ])
                ]
            ]);
        })->name('admin.licenses.show');

        Route::post('/licenses/{id}/status', function ($id, \Illuminate\Http\Request $request) {
            $license = \App\Models\License::findOrFail($id);

            $request->validate([
                'status' => 'required|string|in:active,inactive,expired,suspended,revoked',
            ]);

            $oldStatus = $license->status;
            $license->update(['status' => $request->status]);

            \App\Services\AuditService::log(
                'license_status_updated',
                $license,
                ['status' => $oldStatus],
                ['status' => $request->status]
            );

            return back()->with('success', 'License status updated.');
        })->name('admin.licenses.status');

        Route::post('/licenses/{id}/features', function ($id, \Illuminate\Http\Request $request) {
            $data = $request->validate([
                'features' => 'present|array|max:100',
                'features.*' => ['required', 'string', 'max:100', 'distinct', 'regex:/^[A-Za-z0-9._:-]+$/'],
            ]);

            $license = \App\Models\License::findOrFail($id);
            $oldFeatures = $license->features ?? [];
            $newFeatures = array_values($data['features']);
            $license->update(['features' => $newFeatures]);

            \App\Services\AuditService::log(
                'license_features_updated',
                $license,
                ['features' => $oldFeatures],
                ['features' => $newFeatures]
            );

            return back()->with('success', 'License features updated.');
        })->name('admin.licenses.features');

        Route::get('/licenses/{id}/history', function ($id) {
            $license = \App\Models\License::findOrFail($id);
            return $license->activations()->latest()->limit(50)->get();
        })->name('admin.licenses.history');

        Route::post('/licenses/{id}/reset-binding', function ($id) {
            $license = \App\Models\License::findOrFail($id);

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

            \App\Services\AuditService::log('license_binding_reset', $license);

            return back()->with('success', 'License environment bindings reset successfully. The license can now be activated on a new domain.');
        })->name('admin.licenses.reset-binding');

        Route::get('/products', function () {
            $products = \App\Models\Product::with('prices')->get();
            return Inertia::render('Admin/Products', ['products' => $products]);
        })->name('admin.products');

        Route::post('/products/save', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'id' => 'nullable|exists:products,id',
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean',
                'prices' => 'required|array',
                'prices.*.currency' => 'required|string|size:3',
                'prices.*.amount' => 'required|numeric|min:0',
                'prices.*.type' => 'required|string|in:trial,full,subscription',
                'prices.*.billing_period' => 'nullable|integer',
            ]);

            $targetSlug = isset($data['slug']) && trim($data['slug']) !== ''
                ? \Illuminate\Support\Str::slug($data['slug'])
                : \Illuminate\Support\Str::slug($data['name']);

            if (!empty($data['id'])) {
                $existing = \App\Models\Product::findOrFail($data['id']);
                if ($existing->slug !== $targetSlug && $existing->licenses()->exists()) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'slug' => ['The product slug cannot be changed while licenses exist for this product.'],
                    ]);
                }
            }

            $product = \App\Models\Product::updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name' => $data['name'],
                    'slug' => $targetSlug,
                    'description' => $data['description'],
                    'is_active' => $data['is_active'],
                ]
            );

            // Sync prices
            $product->prices()->delete();
            foreach ($data['prices'] as $priceData) {
                $product->prices()->create($priceData);
            }

            return back()->with('success', 'Product updated successfully.');
        })->name('admin.products.save');

        Route::post('/products/delete', function (\Illuminate\Http\Request $request) {
            $product = \App\Models\Product::findOrFail($request->id);
            $product->delete();
            return back()->with('success', 'Product deleted.');
        })->name('admin.products.delete');

        Route::get('/settings', function () {
            $settings = \App\Models\SystemSetting::all()->pluck('value', 'key');

            $maskedSettings = $settings->mapWithKeys(function ($value, $key) {
                if (str_contains($key, 'secret') || str_contains($key, 'token') || str_contains($key, 'password') || str_contains($key, 'key')) {
                    return [$key => $value === '' ? '' : '******'];
                }

                return [$key => $value];
            });

            if (! $maskedSettings->has('api_enabled')) {
                $maskedSettings['api_enabled'] = 'true';
            }
            if (! $maskedSettings->has('min_supported_version')) {
                $maskedSettings['min_supported_version'] = '1.0.0';
            }

            // RSA signing architecture metadata (algorithm, active key id, public key).
            $signingMeta = \App\Support\OfflineLicenseVerification::buildPublicKeyMetadata();

            return Inertia::render('Admin/Settings', [
                'currentSettings' => $maskedSettings,
                'signing' => [
                    'algorithm' => $signingMeta['algorithm'] ?? 'RSA-SHA256',
                    'active_key_id' => $signingMeta['active_key_id'] ?? null,
                    'public_key_configured' => ! empty($signingMeta['public_key']),
                    'public_key_url' => url('/api/v1/license/public-key'),
                ],
                'runtime' => [
                    'queue_driver' => (string) config('queue.default'),
                    'session_driver' => (string) config('session.driver'),
                    'cache_driver' => (string) config('cache.default'),
                    'scheduler_command' => 'license:renew-subscriptions',
                ],
                'protocol' => [
                    'offline_validity_days' => (int) config('license.offline_validity_days', 7),
                    'pulse_interval_days' => (int) config('license.pulse_interval_days', 30),
                ],
            ]);
        })->name('admin.settings');

        Route::post('/settings/save', function (\Illuminate\Http\Request $request) {
            $data = $request->all();

            // Secret fields that must never be clobbered by the masked placeholder.
            $secretKeys = ['gateway_stripe_key', 'gateway_stripe_secret', 'gateway_bkash_password', 'gateway_bkash_app_key', 'gateway_bkash_app_secret'];

            foreach ($data as $key => $value) {
                if (in_array($key, ['_token', '_method'], true)) {
                    continue;
                }

                $raw = is_array($value) ? json_encode($value) : (string) $value;

                // Catastrophic-overwrite guard: the settings page renders secrets
                // masked ('******') and sends them back on save. Never persist a
                // mask/empty/placeholder over a real stored secret.
                if (in_array($key, $secretKeys, true)) {
                    $trimmed = trim($raw);
                    if ($trimmed === '' || $trimmed === '******' || str_starts_with($trimmed, '***')) {
                        continue; // keep existing value untouched
                    }
                }

                // Store a clean ISO code for the base currency, not the long label.
                if ($key === 'base_currency') {
                    $raw = trim(explode('—', str_replace('---', '—', $raw))[0]);
                }

                \App\Models\SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $raw]
                );

                \Illuminate\Support\Facades\Cache::forget("setting:{$key}");
            }

            // Keep the exchange_rates table in sync with the scalar setting and
            // flush the cached lookup so BDT conversion uses the fresh rate.
            if ($request->filled('exchange_rate')) {
                $rate = (float) $request->input('exchange_rate');

                if ($rate > 0) {
                    $baseCurrency = \App\Models\SystemSetting::getCached('base_currency', 'USD');

                    // The exchange rate is expressed as units of base (USD) per 1 local unit,
                    // so a displayed "USD/BDT" of 114.50 means rate_to_base = 1 / 114.50.
                    $rateToBase = $baseCurrency === 'BDT' && $rate > 1 ? (1 / $rate) : $rate;
                    $currency = $baseCurrency === 'BDT' ? 'BDT' : 'USD';

                    \App\Models\ExchangeRate::updateOrCreate(
                        ['currency' => 'BDT'],
                        ['rate_to_base' => $rateToBase]
                    );

                    \Illuminate\Support\Facades\Cache::forget('exchange_rate_BDT');
                    \Illuminate\Support\Facades\Cache::forget("exchange_rate_{$currency}");
                }
            }

            return back()->with('success', 'Settings updated successfully.');
        })->name('admin.settings.save');
    });
});

require __DIR__ . '/auth.php';
