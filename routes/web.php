<?php

use App\Http\Controllers\Admin\LicenseController as AdminLicenseController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\LicenseController as UserLicenseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Corevisys Installation Routes (Disabled once installed.lock exists)
|--------------------------------------------------------------------------
*/
Route::middleware('web')->group(function () {
    Route::get('/install', [InstallController::class, 'index'])->name('install.index');
    Route::post('/install/test-db', [InstallController::class, 'testDb'])->name('install.testDb');
    Route::post('/install', [InstallController::class, 'install'])->name('install.process');
    Route::get('/install/success', [InstallController::class, 'success'])->name('install.success');
});

/*
|--------------------------------------------------------------------------
| Public Marketing Routes
|--------------------------------------------------------------------------
| Compact, frontend-only public site. These routes delegate to
| PublicController and never touch business logic, services, payments,
| or license state.
*/
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');

// Per-product page. Only active products resolve; unknown or inactive slugs
// render a friendly 404 inside the public layout.
Route::get('/pricing/{slug}', [PublicController::class, 'product'])
    ->where('slug', '[A-Za-z0-9._-]+')
    ->name('pricing.product');

Route::get('/developers', [PublicController::class, 'developers'])->name('developers');
Route::get('/contact', [PublicController::class, 'contact'])->name('contact');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
Route::get('/cookies', [PublicController::class, 'cookies'])->name('cookies');
Route::get('/case-study/{slug}', [PublicController::class, 'caseStudy']);

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
Route::redirect('/services/custom-software', '/', 301);
Route::redirect('/services/web-applications', '/', 301);
Route::redirect('/services/mobile-apps', '/', 301);
Route::redirect('/services/ai-ml', '/', 301);
Route::redirect('/services/cloud-solutions', '/', 301);
Route::redirect('/privacy-policy', '/privacy', 301);
Route::redirect('/terms-of-service', '/terms', 301);
Route::redirect('/cookie-policy', '/cookies', 301);

/*
|--------------------------------------------------------------------------
| Checkout & Gateway Callbacks
|--------------------------------------------------------------------------
*/
Route::post('/order/create', [CheckoutController::class, 'createOrder'])->name('order.create');
Route::get('/orders/stripe/success', [CheckoutController::class, 'stripeSuccess'])->name('orders.stripe.success');
Route::get('/orders/stripe/cancel', [CheckoutController::class, 'stripeCancel'])->name('orders.stripe.cancel');
Route::get('/orders/bkash/callback', [CheckoutController::class, 'bkashCallback'])->name('orders.bkash.callback');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/store', [DashboardController::class, 'store'])->name('store');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}/invoice', [DashboardController::class, 'invoice'])->name('orders.invoice');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::post('/profile/theme', [DashboardController::class, 'updateTheme'])->name('profile.update-theme');

    // User Licenses
    Route::get('/licenses', [UserLicenseController::class, 'index'])->name('licenses');
    Route::post('/licenses/{id}/reveal', [UserLicenseController::class, 'reveal'])
        ->middleware('throttle:5,1')
        ->name('licenses.reveal');
    Route::get('/licenses/{id}/config', [UserLicenseController::class, 'config'])->name('licenses.config');
    Route::post('/licenses/{id}/renew', [UserLicenseController::class, 'renew'])->name('licenses.renew');
    Route::post('/licenses/{id}/upgrade', [UserLicenseController::class, 'upgrade'])->name('licenses.upgrade');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Api\V1\Admin\AnalyticsController::class, 'dashboard'])->name('dashboard');
    Route::get('/analytics', [\App\Http\Controllers\Api\V1\Admin\AnalyticsController::class, 'analytics'])->name('analytics');

    // Admin Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders');
    Route::post('/orders/{id}/verify', [AdminOrderController::class, 'verify'])->name('orders.verify');

    // Admin Teams
    Route::get('/teams', [AdminTeamController::class, 'index'])->name('teams');

    // Admin Licenses
    Route::get('/licenses', [AdminLicenseController::class, 'index'])->name('licenses');
    Route::get('/licenses/{id}', [AdminLicenseController::class, 'show'])->name('licenses.show');
    Route::post('/licenses/{id}/status', [AdminLicenseController::class, 'updateStatus'])->name('licenses.status');
    Route::post('/licenses/{id}/features', [AdminLicenseController::class, 'updateFeatures'])->name('licenses.features');
    Route::get('/licenses/{id}/history', [AdminLicenseController::class, 'history'])->name('licenses.history');
    Route::post('/licenses/{id}/reset-binding', [AdminLicenseController::class, 'resetBinding'])->name('licenses.reset-binding');

    // Admin Products
    Route::get('/products', [AdminProductController::class, 'index'])->name('products');
    Route::post('/products/save', [AdminProductController::class, 'save'])->name('products.save');
    Route::post('/products/delete', [AdminProductController::class, 'delete'])->name('products.delete');

    // Admin Settings
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/save', [AdminSettingsController::class, 'save'])->name('settings.save');
});

require __DIR__.'/auth.php';
