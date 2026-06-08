<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'products' => \App\Models\Product::where('is_active', true)->with('prices')->get(),
        'gatewaySettings' => \App\Models\SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key'),
    ]);
});

Route::get('/company', function () {
    return Inertia::render('Company');
})->name('company');

Route::get('/services/custom-software', function () {
    return Inertia::render('CustomSoftwareDevelopment');
})->name('services.custom-software');

Route::get('/services/web-applications', function () {
    return Inertia::render('WebApplicationDevelopment');
})->name('services.web-applications');

Route::get('/services/mobile-apps', function () {
    return Inertia::render('MobileAppDevelopment');
})->name('services.mobile-apps');

Route::get('/services/ai-ml', function () {
    return Inertia::render('AiMachineLearning');
})->name('services.ai-ml');

Route::get('/services/cloud-solutions', function () {
    return Inertia::render('CloudArchitecture');
})->name('services.cloud');



Route::get('/privacy-policy', function () {
    return Inertia::render('PrivacyPolicy');
})->name('privacy-policy');

Route::get('/terms-of-service', function () {
    return Inertia::render('TermsOfService');
})->name('terms-of-service');

Route::get('/cookie-policy', function () {
    return Inertia::render('CookiePolicy');
})->name('cookie-policy');

Route::get('/insights', function () {
    return Inertia::render('Insights');
})->name('insights');

Route::get('/case-study', function () {
    return redirect('/#portfolio');
})->name('case-study');

Route::get('/case-study/{slug}', function ($slug) {
    $caseStudies = [
        'corevisys-analytics' => [
            'title' => 'CoreVisys Analytics',
            'category' => 'SaaS',
            'description' => 'A scalable SaaS analytics platform that processes millions of data points in real-time, providing actionable insights for enterprise clients.',
            'industry' => 'Data Analytics',
            'duration' => '6 Months',
            'services' => 'Next.js, Python, AWS',
            'problems' => [
                ['title' => 'Data Processing Lag', 'desc' => 'Legacy systems struggled with high data volume, causing significant delays in report generation.'],
                ['title' => 'Complex UI', 'desc' => 'Users found the existing dashboard unintuitive and difficult to navigate.'],
                ['title' => 'Scalability Issues', 'desc' => 'The platform crashed during peak traffic due to poor architecture.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'Architecture Revamp', 'desc' => 'Designed a serverless AWS architecture for dynamic scaling.'],
                ['phase' => 'Phase 2', 'title' => 'Data Pipeline', 'desc' => 'Implemented Python microservices to handle stream processing.'],
                ['phase' => 'Phase 3', 'title' => 'UI Overhaul', 'desc' => 'Built a modern, responsive Next.js frontend.']
            ],
            'features' => [
                ['title' => 'Real-time Dashboards'],
                ['title' => 'Predictive Analytics'],
                ['title' => 'Custom Reports'],
                ['title' => 'Automated Alerts']
            ],
            'results' => [
                ['value' => '10x', 'label' => 'Faster Processing'],
                ['value' => '99.9%', 'label' => 'Uptime'],
                ['value' => '50%', 'label' => 'Cost Reduction'],
                ['value' => '300+', 'label' => 'Enterprise Clients']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80', 'alt' => 'Analytics Dashboard'],
                ['src' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80', 'alt' => 'Data Visualization'],
                ['src' => 'https://images.unsplash.com/photo-1555421689-d68471e189f2?auto=format&fit=crop&w=600&q=80', 'alt' => 'Reporting Tool']
            ]
        ],
        'finflow-banking-app' => [
            'title' => 'FinFlow Banking App',
            'category' => 'Mobile Apps',
            'description' => 'A secure, high-performance banking application offering seamless cross-border transactions and real-time balance updates.',
            'industry' => 'FinTech',
            'duration' => '9 Months',
            'services' => 'React Native, Node.js',
            'problems' => [
                ['title' => 'Security Vulnerabilities', 'desc' => 'The old app lacked biometric authentication and end-to-end encryption.'],
                ['title' => 'Slow Transactions', 'desc' => 'Cross-border transfers took days to settle.'],
                ['title' => 'Poor UX', 'desc' => 'The app was clunky and not optimized for modern smartphones.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'Security Audit', 'desc' => 'Identified and patched vulnerabilities in the legacy API.'],
                ['phase' => 'Phase 2', 'title' => 'Mobile Dev', 'desc' => 'Built a cross-platform app using React Native.'],
                ['phase' => 'Phase 3', 'title' => 'Payment Gateway', 'desc' => 'Integrated a high-speed ledger for instant settlements.']
            ],
            'features' => [
                ['title' => 'Biometric Login'],
                ['title' => 'Instant Transfers'],
                ['title' => 'Expense Tracking'],
                ['title' => 'Virtual Cards']
            ],
            'results' => [
                ['value' => '2M+', 'label' => 'Active Users'],
                ['value' => '0', 'label' => 'Security Breaches'],
                ['value' => '80%', 'label' => 'Faster Transfers'],
                ['value' => '4.8', 'label' => 'App Store Rating']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=600&q=80', 'alt' => 'Mobile Banking'],
                ['src' => 'https://images.unsplash.com/photo-1555421689-d68471e189f2?auto=format&fit=crop&w=600&q=80', 'alt' => 'Transaction History'],
                ['src' => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=600&q=80', 'alt' => 'Security Settings']
            ]
        ],
        'global-logistics-pro' => [
            'title' => 'Global Logistics Pro',
            'category' => 'ERP',
            'description' => 'A comprehensive enterprise resource planning system tailored for global supply chain and logistics management.',
            'industry' => 'Logistics',
            'duration' => '12 Months',
            'services' => 'Laravel, Vue, PostgreSQL',
            'problems' => [
                ['title' => 'Fragmented Systems', 'desc' => 'Different departments used isolated software, causing data silos.'],
                ['title' => 'Manual Tracking', 'desc' => 'Fleet tracking relied on manual updates and spreadsheets.'],
                ['title' => 'Inefficient Routing', 'desc' => 'Delivery routes were not optimized, wasting fuel and time.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'System Integration', 'desc' => 'Unified 5 different legacy systems into a central Laravel backend.'],
                ['phase' => 'Phase 2', 'title' => 'IoT Fleet Tracking', 'desc' => 'Integrated real-time GPS tracking for the entire fleet.'],
                ['phase' => 'Phase 3', 'title' => 'AI Routing', 'desc' => 'Deployed machine learning models to optimize delivery routes.']
            ],
            'features' => [
                ['title' => 'Real-time Tracking'],
                ['title' => 'Inventory Management'],
                ['title' => 'Automated Dispatch'],
                ['title' => 'AI Route Optimization']
            ],
            'results' => [
                ['value' => '30%', 'label' => 'Fuel Saved'],
                ['value' => '100%', 'label' => 'Fleet Visibility'],
                ['value' => '40%', 'label' => 'Efficiency Gain'],
                ['value' => '$2M', 'label' => 'Annual Savings']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=600&q=80', 'alt' => 'Fleet Dashboard'],
                ['src' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=600&q=80', 'alt' => 'Warehouse Management'],
                ['src' => 'https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?auto=format&fit=crop&w=600&q=80', 'alt' => 'Route Map']
            ]
        ],
        'healthtech-portal' => [
            'title' => 'HealthTech Portal',
            'category' => 'Web Apps',
            'description' => 'A HIPAA-compliant patient management platform connecting doctors and patients via telemedicine and secure messaging.',
            'industry' => 'Healthcare',
            'duration' => '7 Months',
            'services' => 'React, TypeScript, Node.js',
            'problems' => [
                ['title' => 'Compliance Risks', 'desc' => 'The old portal did not fully comply with new HIPAA regulations.'],
                ['title' => 'No Telemedicine', 'desc' => 'Patients had to use third-party apps for video consultations.'],
                ['title' => 'Poor Scheduling', 'desc' => 'The appointment booking system was prone to double-booking.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'Security Hardening', 'desc' => 'Implemented robust encryption and audit logging.'],
                ['phase' => 'Phase 2', 'title' => 'WebRTC Integration', 'desc' => 'Built native video conferencing directly into the portal.'],
                ['phase' => 'Phase 3', 'title' => 'Smart Scheduling', 'desc' => 'Developed a conflict-free, time-zone-aware booking calendar.']
            ],
            'features' => [
                ['title' => 'HIPAA Compliance'],
                ['title' => 'Video Consultations'],
                ['title' => 'Secure Messaging'],
                ['title' => 'E-Prescriptions']
            ],
            'results' => [
                ['value' => '1M+', 'label' => 'Consultations'],
                ['value' => '100%', 'label' => 'Compliance'],
                ['value' => '60%', 'label' => 'Less No-shows'],
                ['value' => '500+', 'label' => 'Clinics Using It']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=600&q=80', 'alt' => 'Patient Dashboard'],
                ['src' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=600&q=80', 'alt' => 'Video Call'],
                ['src' => 'https://images.unsplash.com/photo-1555421689-d68471e189f2?auto=format&fit=crop&w=600&q=80', 'alt' => 'Medical Records']
            ]
        ],
        'autocrm-ai' => [
            'title' => 'AutoCRM AI',
            'category' => 'AI Systems',
            'description' => 'An intelligent CRM that leverages LLMs to automate customer interactions, email drafting, and lead scoring.',
            'industry' => 'Sales & Marketing',
            'duration' => '5 Months',
            'services' => 'OpenAI, Python, Vue',
            'problems' => [
                ['title' => 'Manual Follow-ups', 'desc' => 'Sales reps spent 40% of their time writing routine emails.'],
                ['title' => 'Poor Lead Scoring', 'desc' => 'High-value leads were often missed in the noise.'],
                ['title' => 'Data Entry', 'desc' => 'Updating CRM records manually led to inaccurate data.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'LLM Integration', 'desc' => 'Fine-tuned OpenAI models on past successful sales emails.'],
                ['phase' => 'Phase 2', 'title' => 'Lead AI', 'desc' => 'Created predictive models to score leads based on behavior.'],
                ['phase' => 'Phase 3', 'title' => 'Workflow Automation', 'desc' => 'Built an intuitive Vue interface to manage automated workflows.']
            ],
            'features' => [
                ['title' => 'AI Email Drafting'],
                ['title' => 'Predictive Lead Scoring'],
                ['title' => 'Automated Data Entry'],
                ['title' => 'Chatbot Integration']
            ],
            'results' => [
                ['value' => '40%', 'label' => 'Time Saved'],
                ['value' => '25%', 'label' => 'Higher Conversion'],
                ['value' => '10k+', 'label' => 'Emails Automated'],
                ['value' => '95%', 'label' => 'Data Accuracy']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1535223289827-42f1e9919769?auto=format&fit=crop&w=600&q=80', 'alt' => 'CRM Dashboard'],
                ['src' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=600&q=80', 'alt' => 'AI Insights'],
                ['src' => 'https://images.unsplash.com/photo-1504868584819-f8e8b4b6d7e3?auto=format&fit=crop&w=600&q=80', 'alt' => 'Lead Pipeline']
            ]
        ],
        'cloudsync-workspace' => [
            'title' => 'CloudSync Workspace',
            'category' => 'SaaS',
            'description' => 'A unified collaborative workspace offering real-time document editing, project management, and team communication.',
            'industry' => 'Productivity',
            'duration' => '8 Months',
            'services' => 'Vue, Tailwind, Supabase',
            'problems' => [
                ['title' => 'Tool Fatigue', 'desc' => 'Teams were paying for and juggling 5 different apps.'],
                ['title' => 'Sync Conflicts', 'desc' => 'Offline edits caused major document versioning issues.'],
                ['title' => 'Slow Search', 'desc' => 'Finding files across the organization was painfully slow.']
            ],
            'timeline' => [
                ['phase' => 'Phase 1', 'title' => 'Supabase Backend', 'desc' => 'Set up real-time database subscriptions for instant updates.'],
                ['phase' => 'Phase 2', 'title' => 'Editor Dev', 'desc' => 'Built a robust collaborative rich-text editor using CRDTs.'],
                ['phase' => 'Phase 3', 'title' => 'Global Search', 'desc' => 'Implemented an Algolia-powered instant search engine.']
            ],
            'features' => [
                ['title' => 'Real-time Editing'],
                ['title' => 'Kanban Boards'],
                ['title' => 'Instant Search'],
                ['title' => 'Team Chat']
            ],
            'results' => [
                ['value' => '500k', 'label' => 'Active Workspaces'],
                ['value' => '10ms', 'label' => 'Sync Latency'],
                ['value' => '5-in-1', 'label' => 'Tools Replaced'],
                ['value' => '4.9', 'label' => 'User Rating']
            ],
            'gallery' => [
                ['src' => 'https://images.unsplash.com/photo-1600132806370-bf17e65e942f?auto=format&fit=crop&w=600&q=80', 'alt' => 'Workspace UI'],
                ['src' => 'https://images.unsplash.com/photo-1526628953301-3e589a6a8b74?auto=format&fit=crop&w=600&q=80', 'alt' => 'Collaborative Editor'],
                ['src' => 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?auto=format&fit=crop&w=600&q=80', 'alt' => 'Project Board']
            ]
        ]
    ];

    if (!array_key_exists($slug, $caseStudies)) {
        abort(404);
    }

    return Inertia::render('CaseStudy', [
        'caseStudy' => $caseStudies[$slug]
    ]);
})->name('case-study.show');

Route::get('/careers', function () {
    return Inertia::render('Careers');
})->name('careers');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

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

    $price = \App\Models\ProductPrice::findOrFail($request->price_id);
    $product = \App\Models\Product::findOrFail($request->product_id);

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

                // If result is returned, it means we just fulfilled it. Flash credentials.
                if ($result) {
                    $license = $result['license'];
                    $apiToken = $result['api_token'];
                    
                    if (isset($license->raw_key)) {
                        session()->flash('new_license_key', $license->raw_key);
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
    Route::get('/licenses', function () {
        $user = auth()->user();
        $licenses = \App\Models\License::with('product')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn($l) => [
                'id' => $l->id,
                'product_name' => $l->product->name,
                'link_renew' => route('licenses.renew', $l->id),
                'type' => ucfirst($l->type),
                'key_preview' => 'XXXX-XXXX-' . substr($l->license_key_hash, -4),
                'full_key' => $l->license_key ?? 'Contact Admin for Key', 
                'status' => $l->status,
                'expires_at' => $l->expires_at ? $l->expires_at->format('Y-m-d') : null,
                'last_check_at' => $l->last_check_at ? $l->last_check_at->toDateTimeString() : null,
                'is_running' => $l->last_check_at && $l->last_check_at->isAfter(now()->subHours(25)),
                'is_expiring_soon' => $l->expires_at && $l->expires_at->isFuture() && $l->expires_at->diffInDays(now()) <= 30,
                'can_renew' => $l->type === 'trial' || ($l->type === 'subscription' && $l->status !== 'active') || $l->is_expiring_soon,
                'bound_domain' => $l->bound_domain,
                'bound_ip' => $l->bound_ip,
                'upgrade_plans' => $l->type === 'full' ? [] : \App\Models\ProductPrice::where('product_id', $l->product_id)
                    ->get()
                    ->map(fn($p) => [
                        'id' => $p->id,
                        'name' => ($p->type === 'subscription' ? ($p->billing_period >= 365 ? 'Yearly' : 'Monthly') : ucfirst($p->type)) . ' Plan',
                        'amount' => $p->amount,
                        'currency' => $p->currency,
                        'type' => $p->type
                    ]),
            ]);

        return Inertia::render('Licenses', ['licenses' => $licenses]);
    })->name('licenses');

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
                'license_key' => $license->license_key ?? 'Contact Admin for Key',
                'status' => $license->status,
                'bound_domain' => $license->bound_domain,
                'bound_ip' => $license->bound_ip,
                'activation_limit' => $license->activation_limit ?? 1,
                'current_usage' => $license->activations()->where('status', 'success')->count(),
                'expires_at' => $license->expires_at ? $license->expires_at->format('Y-m-d') : 'Lifetime',
            ],
            'api_base_url' => url('/api/v1')
        ]);
    })->name('licenses.config');

    Route::post('/licenses/{id}/renew', function ($id) {
        $license = \App\Models\License::where('user_id', auth()->id())->findOrFail($id);
        
        // Find the original price plan to renew
        // We look at the original order item to find the product_price_id
        $originalOrder = $license->order;
        $originalItem = $originalOrder ? $originalOrder->items()->where('product_id', $license->product_id)->first() : null;
        
        $price = null;
        if ($originalItem && $originalItem->product_price_id) {
            $price = \App\Models\ProductPrice::find($originalItem->product_price_id);
        }

        // Fallback: If no linked price, try to find a valid 'full' or 'subscription' price for the product
        if (!$price) {
             $price = \App\Models\ProductPrice::where('product_id', $license->product_id)
                 ->where('type', $license->type === 'trial' ? 'subscription' : $license->type) // If trial, renew as sub? Or let them choose. Assuming sub/full match.
                 ->first();
        }

        if (!$price) {
            return back()->with('error', 'Renewal price not found. Please purchase a new license from the store.');
        }

        // Create Renewal Order
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-REN-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => 'awaiting_payment',
            'payment_method' => 'online', // Auto-assume online for auto-redirect
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
        
        // Initiate Payment
        $stripeService = new \App\Services\StripePaymentService();
        $session = $stripeService->createCheckoutSession($order, $price);

        if ($session && $session->url) {
            return \Inertia\Inertia::location($session->url);
        }

        return back()->with('error', 'Failed to initiate renewal payment.');
    })->name('licenses.renew');

    Route::post('/licenses/{id}/upgrade', function ($id, \Illuminate\Http\Request $request) {
        $license = \App\Models\License::where('user_id', auth()->id())->findOrFail($id);
        $newPriceId = $request->input('product_price_id');
        
        if (!$newPriceId) {
            return back()->with('error', 'Please select a plan to upgrade to.');
        }

        $price = \App\Models\ProductPrice::findOrFail($newPriceId);

        // Create Upgrade Order
        $order = \App\Models\Order::create([
            'order_number' => 'ORD-UPG-' . strtoupper(\Illuminate\Support\Str::random(10)),
            'user_id' => auth()->id(),
            'license_id' => $license->id,
            'total_amount' => $price->amount,
            'currency' => $price->currency,
            'status' => 'awaiting_payment',
            'payment_method' => 'online',
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
        
        // Associate license ID in some way? 
        // We'll use the License ID in OrderFulfillmentService to find the one to upgrade
        // For now, LicenseService::upgradeLicense finds the latest license for that product/user.

        // Initiate Payment
        $stripeService = new \App\Services\StripePaymentService();
        $session = $stripeService->createCheckoutSession($order, $price);

        if ($session && $session->url) {
            return \Inertia\Inertia::location($session->url);
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
        } elseif ($order->payment_method === 'offline' && $order->payment?->payment_proof_path) {
             return response()->download(storage_path('app/' . $order->payment->payment_proof_path));
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
            $order = \App\Models\Order::findOrFail($id);
            if ($order->status === 'pending') {
                $order->update(['status' => 'completed']);
                
                // Update payment status as well
                if ($order->payment) {
                    $order->payment->update(['status' => 'verified', 'verified_by' => auth()->id()]);
                }

                // Activate licenses associated with this order
                // ... (Logic to activate licenses would go here, assuming License model linking)
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
                'is_running' => $l->last_check_at && $l->last_check_at->isAfter(now()->subHours(1)),
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
                    'license_key' => $license->license_key ?? 'Contact Admin for Key',
                    'type' => ucfirst($license->type),
                    'status' => $license->status,
                    'enforcement_mode' => $license->enforcement_mode ?? 'active',
                    'bound_domain' => $license->bound_domain,
                    'bound_ip' => $license->bound_ip,
                    'activation_limit' => $license->activation_limit ?? 1,
                    'current_usage' => $license->activations()->where('status', 'success')->count(),
                    'expires_at' => $license->expires_at ? $license->expires_at->format('Y-m-d') : 'Lifetime',
                    'last_check_at' => $license->last_check_at ? $license->last_check_at->toDateTimeString() : null,
                    'is_running' => $license->last_check_at && $license->last_check_at->isAfter(now()->subHours(1)),
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
            $license->update(['status' => $request->status]);
            return back()->with('success', 'License status updated.');
        })->name('admin.licenses.status');

        Route::get('/licenses/{id}/history', function ($id) {
            $license = \App\Models\License::findOrFail($id);
            return $license->activations()->latest()->limit(50)->get();
        })->name('admin.licenses.history');

        Route::post('/licenses/{id}/reset-binding', function ($id) {
            $license = \App\Models\License::findOrFail($id);
            $license->update([
                'bound_ip' => null,
                'bound_domain' => null
            ]);
            return back()->with('success', 'License bindings reset successfully.');
        })->name('admin.licenses.reset-binding');

        Route::get('/products', function () {
            $products = \App\Models\Product::with('prices')->get();
            return Inertia::render('Admin/Products', ['products' => $products]);
        })->name('admin.products');

        Route::post('/products/save', function (\Illuminate\Http\Request $request) {
            $data = $request->validate([
                'id' => 'nullable|exists:products,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'is_active' => 'required|boolean',
                'prices' => 'required|array',
                'prices.*.currency' => 'required|string|size:3',
                'prices.*.amount' => 'required|numeric|min:0',
                'prices.*.type' => 'required|string|in:trial,full,subscription',
                'prices.*.billing_period' => 'nullable|integer',
            ]);

            $product = \App\Models\Product::updateOrCreate(
                ['id' => $data['id'] ?? null],
                [
                    'name' => $data['name'],
                    'slug' => \Illuminate\Support\Str::slug($data['name']),
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
            return Inertia::render('Admin/Settings', [
                'currentSettings' => $settings
            ]);
        })->name('admin.settings');

        Route::post('/settings/save', function (\Illuminate\Http\Request $request) {
            $data = $request->all();
            foreach ($data as $key => $value) {
                \App\Models\SystemSetting::updateOrCreate(
                    ['key' => $key],
                    ['value' => $value]
                );
            }
            return back()->with('success', 'Settings updated successfully.');
        })->name('admin.settings.save');
    });
});

require __DIR__ . '/auth.php';
