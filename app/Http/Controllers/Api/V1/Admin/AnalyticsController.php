<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\License;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1. Core Stats
        $activeLicenses = License::where('status', 'active')->count();
        $trialLicenses = License::where('status', 'active')->where('type', 'trial')->count();
        $subLicenses = License::where('status', 'active')->where('type', 'subscription')->count();
        $liveLicenses = License::where('status', 'active')
            ->where('last_check_at', '>=', now()->subHours(1))
            ->count();
        
        // 2. Revenue Trend (30 Days)
        $endDate = now();
        $startDate = now()->subDays(29);
        $revenueData = \App\Models\Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');
        
        // Fill missing dates with 0
        $revenueTrend = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');
            $revenueTrend[] = $revenueData[$dayStr] ?? 0;
        }

        // 3. Recent Activity (License Activations)
        $recentActivities = \App\Models\LicenseActivation::with('license.product')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'product_name' => $a->license->product->name ?? 'Unknown Product',
                'domain' => $a->request_domain ?? 'Direct API',
                'status' => $a->status,
                'created_at' => $a->created_at->diffForHumans(),
            ]);

        return \Inertia\Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_users' => \App\Models\User::count(),
                'total_products' => \App\Models\Product::count(),
                'total_orders' => \App\Models\Order::where('status', 'completed')->count(),
                'total_revenue' => \App\Models\Order::where('status', 'completed')->sum('total_amount'),
                'running_projects' => $liveLicenses,
                'active_licenses' => $activeLicenses,
                'trial_licenses' => $trialLicenses,
                'subscription_licenses' => $subLicenses,
                'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            ],
            'revenue_trend' => $revenueTrend,
            'recent_activities' => $recentActivities,
        ]);
    }

    public function analytics(Request $request)
    {
        // 1. Core Stats
        $activeLicenses = License::where('status', 'active')->count();
        $trialLicenses = License::where('status', 'active')->where('type', 'trial')->count();
        $subLicenses = License::where('status', 'active')->where('type', 'subscription')->count();
        $liveLicenses = License::where('status', 'active')
            ->where('last_check_at', '>=', now()->subHours(1))
            ->count();
        
        // 2. Revenue Trend (30 Days)
        $endDate = now();
        $startDate = now()->subDays(29);
        $revenueData = \App\Models\Order::where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date');
        
        // Fill missing dates with 0
        $revenueTrend = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');
            $revenueTrend[] = $revenueData[$dayStr] ?? 0;
        }

        // 3. Recent Activity
        $recentActivities = \App\Models\LicenseActivation::with('license.product')
            ->latest()
            ->paginate(5) // Show 5 per page with pagination
            ->through(fn($a) => [
                'id' => $a->id,
                'product_name' => $a->license->product->name ?? 'Unknown Product',
                'domain' => $a->request_domain ?? 'Direct API',
                'status' => $a->status,
                'created_at' => $a->created_at->diffForHumans(),
            ]);

        return \Inertia\Inertia::render('Admin/Analytics', [
            'stats' => [
                'total_users' => \App\Models\User::count(),
                'total_products' => \App\Models\Product::count(),
                'total_orders' => \App\Models\Order::where('status', 'completed')->count(),
                'total_revenue' => \App\Models\Order::where('status', 'completed')->sum('total_amount'),
                'running_projects' => $liveLicenses,
                'active_licenses' => $activeLicenses,
                'trial_licenses' => $trialLicenses,
                'subscription_licenses' => $subLicenses,
                'pending_orders' => \App\Models\Order::where('status', 'pending')->count(),
            ],
            'revenue_trend' => $revenueTrend,
            'recent_activities' => $recentActivities,
        ]);
    }

    public function index(Request $request)
    {
        // Basic JSON Analytics if needed (or we can deprecate this)
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // ... existing logic ...
        return response()->json(['status' => 'ok']);
    }
}
