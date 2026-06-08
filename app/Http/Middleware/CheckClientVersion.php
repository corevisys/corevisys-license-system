<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckClientVersion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check Kill Switch
        $apiEnabled = SystemSetting::where('key', 'api_enabled')->value('value');
        if ($apiEnabled === 'false') {
            return response()->json(['message' => 'Service Unavailable', 'status' => false], 503);
        }

        // 2. Check Version
        $minVersion = SystemSetting::where('key', 'min_supported_version')->value('value');
        $clientVersion = $request->header('X-API-Version');

        if ($minVersion && $clientVersion) {
            if (version_compare($clientVersion, $minVersion, '<')) {
                return response()->json([
                    'message' => "Upgrade Required. Minimum version: $minVersion",
                    'status' => false
                ], 426);
            }
        }
        if ($minVersion && !$clientVersion) {
            // Allow pulse/activate without version check for backward compatibility
            if (in_array($request->path(), ['api/v1/license/pulse', 'api/v1/license/activate'])) {
                return $next($request);
            }
            return response()->json(['message' => 'X-API-Version header missing', 'status' => false], 400);
        }

        return $next($request);
    }
}
