<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use App\Services\LicenseService;

class LicenseResetController extends Controller
{
    protected $licenseService;

    public function __construct(LicenseService $service)
    {
        $this->licenseService = $service;
    }

    public function reset(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|min:5'
        ]);

        $license = License::findOrFail($id);

        // Authorization check: Ensure user is admin
        // This is usually handled by Middleware, but checking role here just in case/or assuming middleware 'auth:sanctum' + 'admin'
        if ($request->user()->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $this->licenseService->resetLicense($license, $request->user(), $request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'License domain reset successfully.',
            'reset_count' => $license->refresh()->reset_count
        ]);
    }
}