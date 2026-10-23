<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $defaultTheme = 'terminal';
        try {
            $defaultTheme = \App\Models\SystemSetting::getCached('default_theme', 'terminal');
        } catch (\Throwable $e) {
            // Database not connected or table does not exist yet
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'settings' => [
                'default_theme' => $defaultTheme,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
                'new_license_key' => fn () => $request->session()->get('new_license_key'),
                'new_api_token' => fn () => $request->session()->get('new_api_token'),
            ],
        ];
    }
}
