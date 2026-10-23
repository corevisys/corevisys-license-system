<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class PublicController extends Controller
{
    /**
     * Public landing page. When the application is not yet installed (or the
     * product schema is missing) uninstalled visitors are guided to the installer.
     */
    public function home()
    {
        if (!app()->environment('testing') && !file_exists(storage_path('installed.lock'))) {
            return redirect()->route('install.index');
        }

        try {
            if (!app()->environment('testing') && !Schema::hasTable('products')) {
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
            'featuredProducts' => Product::where('is_active', true)
                ->orderBy('name')
                ->take(3)
                ->get(['id', 'name', 'slug', 'description']),
        ]);
    }

    public function pricing()
    {
        return Inertia::render('Public/Pricing', [
            'products' => Product::where('is_active', true)->with('prices')->get(),
            'gatewaySettings' => SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key'),
        ]);
    }

    /**
     * Per-product page. Only active products resolve; unknown or inactive slugs
     * render a friendly 404 inside the public layout.
     */
    public function product(string $slug)
    {
        $product = Product::where('slug', $slug)
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
            'otherProducts' => Product::where('is_active', true)
                ->where('id', '!=', $product->id)
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'description']),
            'gatewaySettings' => SystemSetting::where('key', 'like', 'gateway_%_active')->pluck('value', 'key'),
        ]);
    }

    public function developers()
    {
        return Inertia::render('Public/Developers');
    }

    public function contact()
    {
        return Inertia::render('Public/Contact');
    }

    public function privacy()
    {
        return Inertia::render('Public/Privacy');
    }

    public function terms()
    {
        return Inertia::render('Public/Terms');
    }

    public function cookies()
    {
        return Inertia::render('Public/Cookies');
    }

    /**
     * Legacy case study URLs are permanently redirected to the home page so
     * inbound links, bookmarks, and search rankings keep working.
     */
    public function caseStudy(string $slug)
    {
        return redirect('/', 301);
    }
}