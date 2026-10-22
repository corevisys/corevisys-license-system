<?php

namespace App\Http\Controllers;

use App\Services\InstallationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallController extends Controller
{
    public function __construct(
        protected InstallationService $installer
    ) {}

    public function index(): View|RedirectResponse
    {
        if ($this->installer->isInstalled()) {
            abort(404);
        }

        $serverCheck = $this->installer->checkServerRequirements();
        $suggestedUrl = rtrim(request()->root(), '/');

        return view('install.index', [
            'requirements' => $serverCheck['requirements'],
            'allSatisfied' => $serverCheck['all_satisfied'],
            'suggestedUrl' => $suggestedUrl,
        ]);
    }

    public function testDb(Request $request): JsonResponse
    {
        if ($this->installer->isInstalled()) {
            return response()->json(['success' => false, 'message' => 'Already installed.'], 403);
        }

        $validated = $request->validate([
            'db_host' => 'required|string',
            'db_port' => 'required|numeric',
            'db_database' => 'required|string',
            'db_username' => 'required|string',
            'db_password' => 'nullable|string',
        ]);

        $result = $this->installer->testDatabaseConnection(
            $validated['db_host'],
            $validated['db_port'],
            $validated['db_database'],
            $validated['db_username'],
            $validated['db_password'] ?? null
        );

        return response()->json($result);
    }

    public function install(Request $request): View|RedirectResponse
    {
        if ($this->installer->isInstalled()) {
            abort(404);
        }

        $serverCheck = $this->installer->checkServerRequirements();
        $suggestedUrl = rtrim($request->root(), '/');

        $viewData = [
            'requirements' => $serverCheck['requirements'],
            'allSatisfied' => $serverCheck['all_satisfied'],
            'suggestedUrl' => $suggestedUrl,
        ];

        // Manual validation so we can return view() directly (no session flash needed)
        $rules = [
            'app_name'                  => 'required|string|max:100',
            'app_url'                   => 'required|url',
            'db_host'                   => 'required|string',
            'db_port'                   => 'required|numeric',
            'db_database'               => 'required|string',
            'db_username'               => 'required|string',
            'db_password'               => 'nullable|string',
            'admin_name'                => 'required|string|max:100',
            'admin_email'               => 'required|email|max:191',
            'admin_password'            => 'required|string|min:8|confirmed',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return view('install.index', array_merge($viewData, [
                'errors'   => $validator->errors(),
                'oldInput' => $request->all(),
            ]));
        }

        $validated = $validator->validated();

        // 1. Verify database connection
        $test = $this->installer->testDatabaseConnection(
            $validated['db_host'],
            $validated['db_port'],
            $validated['db_database'],
            $validated['db_username'],
            $validated['db_password'] ?? null
        );

        if (!$test['success']) {
            return view('install.index', array_merge($viewData, [
                'errors'   => new \Illuminate\Support\MessageBag(['db_connection' => [$test['message']]]),
                'oldInput' => $request->all(),
            ]));
        }

        try {
            @ini_set('max_execution_time', '300');
            @set_time_limit(300);

            // 2. Generate RSA keypair, LICENSE_PEPPER, APP_KEY
            $keys = $this->installer->generateKeys();

            // 3. Write .env and configure runtime
            $this->installer->setupEnvironment($validated, $keys);

            // 4. Run migrations
            $this->installer->runMigrations();

            // 5. Seed default system settings
            $this->installer->seedSystemSettings();

            // 6. Create Admin User
            $this->installer->createAdminUser(
                $validated['admin_email'],
                $validated['admin_password'],
                $validated['admin_name']
            );

            // 7. Symlink storage
            $this->installer->linkStorage();

            // 8. Refresh & Discover
            $this->installer->clearAndCache();

            // 9. Mark installed
            $this->installer->markAsInstalled('Web Setup Wizard');

            // Pass admin email via query string — no session flash needed
            return redirect()->route('install.success', ['email' => $validated['admin_email']]);

        } catch (\Throwable $e) {
            return view('install.index', array_merge($viewData, [
                'errors'   => new \Illuminate\Support\MessageBag(['install_error' => ['Installation failed: ' . $e->getMessage()]]),
                'oldInput' => $request->all(),
            ]));
        }
    }

    public function success(Request $request): View|RedirectResponse
    {
        if (!$this->installer->isInstalled()) {
            return redirect()->route('install.index');
        }

        return view('install.success', [
            'adminEmail' => $request->query('email', ''),
        ]);
    }
}
