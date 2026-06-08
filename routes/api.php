<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\LicenseController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\WebhookController;

// Public Routes (For License Checks from external apps and Product Listing)
Route::prefix('v1')->middleware([\App\Http\Middleware\CheckClientVersion::class])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::post('/webhooks/{gateway}', [WebhookController::class, 'handle']);

    Route::post('/license/activate', [LicenseController::class, 'activate'])->middleware('throttle:activation');
    Route::post('/license/check', [LicenseController::class, 'check'])->middleware('throttle:activation');
    Route::post('/license/pulse', [LicenseController::class, 'pulse'])->middleware('throttle:pulse');
    Route::post('/license/history', [LicenseController::class, 'history'])->middleware('throttle:pulse');
});

// Authenticated Routes (For User Dashboard / Checkout)
Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/notifications/preferences', [NotificationController::class, 'index']);
    Route::post('/notifications/preferences', [NotificationController::class, 'update']);
    Route::post('/orders/create', [OrderController::class, 'store']);
    Route::post('/orders/{id}/upload-receipt', [OrderController::class, 'uploadReceipt']);

    // Admin Routes
    Route::post('/admin/licenses/{id}/reset', [\App\Http\Controllers\Api\V1\Admin\LicenseResetController::class, 'reset']);
    Route::get('/admin/analytics', [\App\Http\Controllers\Api\V1\Admin\AnalyticsController::class, 'index']);
    Route::post('/admin/payments/{id}/verify', [\App\Http\Controllers\Api\V1\Admin\PaymentController::class, 'verify']);
});
