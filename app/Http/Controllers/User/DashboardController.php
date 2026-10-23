<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Order;
use App\Models\Product;
use App\Models\SystemSetting;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'stats' => [
                'active_count' => License::where('user_id', $user->id)->where('status', 'active')->count(),
                'expiring_count' => License::where('user_id', $user->id)
                    ->where('status', 'active')
                    ->where('expires_at', '<=', now()->addDays(30))
                    ->count(),
                'total_spent' => Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount'),
            ],
            'recentLicenses' => License::with('product')
                ->where('user_id', $user->id)
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }

    /**
     * The storefront listing of active products / prices (legacy `/store`).
     */
    public function store()
    {
        return Inertia::render('Store', [
            'products' => Product::where('is_active', true)->with('prices')->get(),
            'gatewaySettings' => SystemSetting::where('key', 'like', 'gateway_%_active')
                ->pluck('value', 'key'),
        ]);
    }

    public function orders()
    {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => 'ORD-' . str_pad($o->id, 4, '0', STR_PAD_LEFT),
                'created_at' => $o->created_at->format('Y-m-d'),
                'amount' => $o->total_amount,
                'currency' => $o->currency,
                'status' => $o->status,
                'payment_method' => $o->payment_method,
                'gateway' => $o->payment?->gateway,
            ]);

        return Inertia::render('Orders', ['orders' => $orders]);
    }

    public function invoice($id)
    {
        $order = Order::where('user_id', auth()->id())->findOrFail($id);

        if ($order->payment_method === 'online' || $order->payment?->gateway === 'stripe') {
            if ($order->payment && $order->payment->transaction_id) {
                try {
                    $stripeService = new StripePaymentService();
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
            return Storage::disk(config('receipt.storage_disk', 'local'))->download($order->payment->payment_proof_path);
        }

        return back()->with('error', 'Invoice not found.');
    }

    public function analytics()
    {
        $user = auth()->user();

        // 1. License Distribution by Product
        $licenseDistribution = License::where('user_id', $user->id)
            ->with('product')
            ->get()
            ->groupBy('product.name')
            ->map(fn ($group) => $group->count());

        // 2. Spending Trend (Last 6 Months)
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('M'));
        $spendingTrend = collect(range(5, 0))->map(function ($i) use ($user) {
            $date = now()->subMonths($i);
            return Order::where('user_id', $user->id)
                ->where('status', 'completed')
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->sum('total_amount');
        });

        // 3. Status Summary
        $statusSummary = License::where('user_id', $user->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return Inertia::render('Analytics', [
            'stats' => [
                'active_licenses' => $statusSummary['active'] ?? 0,
                'total_revenue' => Order::where('user_id', $user->id)->where('status', 'completed')->sum('total_amount'),
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
    }

    public function updateTheme(Request $request)
    {
        $request->validate(['theme' => 'required|string']);
        $user = auth()->user();
        $user->theme_preference = $request->theme;
        $user->save();
        return back();
    }
}