<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CPanelSetupController extends Controller
{
    private string $lockFile;

    public function __construct()
    {
        $this->lockFile = storage_path('installed.lock');
    }

    public function index()
    {
        $isInstalled = File::exists($this->lockFile);

        // System checks
        $checks = [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'status' => version_compare(PHP_VERSION, '8.2.0', '>='),
                'current' => PHP_VERSION,
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'status' => extension_loaded('pdo_mysql'),
                'current' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Missing',
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'status' => extension_loaded('openssl'),
                'current' => extension_loaded('openssl') ? 'Enabled' : 'Missing',
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'status' => extension_loaded('mbstring'),
                'current' => extension_loaded('mbstring') ? 'Enabled' : 'Missing',
            ],
            'fileinfo' => [
                'name' => 'Fileinfo Extension',
                'status' => extension_loaded('fileinfo'),
                'current' => extension_loaded('fileinfo') ? 'Enabled' : 'Missing',
            ],
            'storage_writable' => [
                'name' => 'storage/ Writable',
                'status' => is_writable(storage_path()),
                'current' => is_writable(storage_path()) ? 'Writable' : 'Not Writable (Check 755/775 permissions)',
            ],
            'bootstrap_writable' => [
                'name' => 'bootstrap/cache/ Writable',
                'status' => is_writable(base_path('bootstrap/cache')),
                'current' => is_writable(base_path('bootstrap/cache')) ? 'Writable' : 'Not Writable (Check 755/775 permissions)',
            ],
        ];

        // DB Connection Check
        $dbConnected = false;
        $dbError = null;
        $tablesCount = 0;
        $adminExists = false;

        try {
            DB::connection()->getPdo();
            $dbConnected = true;
            if (Schema::hasTable('migrations')) {
                $tablesCount = count(DB::select('SHOW TABLES'));
            }
            if (Schema::hasTable('users')) {
                $adminExists = User::where('role', 'admin')->exists();
            }
        } catch (\Throwable $e) {
            $dbError = $e->getMessage();
        }

        $allRequirementsPassed = collect($checks)->every(fn($c) => $c['status']);

        return view('cpanel-setup', [
            'checks' => $checks,
            'allRequirementsPassed' => $allRequirementsPassed,
            'dbConnected' => $dbConnected,
            'dbError' => $dbError,
            'tablesCount' => $tablesCount,
            'adminExists' => $adminExists,
            'isInstalled' => $isInstalled,
            'dbConfig' => [
                'database' => config('database.connections.mysql.database'),
                'username' => config('database.connections.mysql.username'),
                'host' => config('database.connections.mysql.host'),
            ],
        ]);
    }

    public function runSetup(Request $request)
    {
        if (File::exists($this->lockFile) && !$request->has('force')) {
            return back()->with('error', 'Setup is locked because installation is already complete. Delete storage/installed.lock if you need to rerun.');
        }

        $output = [];

        try {
            // 1. Verify DB
            DB::connection()->getPdo();
            $output[] = '✓ Database connection verified.';

            // 2. Run Migrations
            Artisan::call('migrate', ['--force' => true]);
            $output[] = '✓ Database migrations executed successfully.';

            // 3. Run System Settings Seeder
            if (class_exists(\Database\Seeders\SystemSettingsSeeder::class)) {
                Artisan::call('db:seed', ['--class' => 'Database\Seeders\SystemSettingsSeeder', '--force' => true]);
                $output[] = '✓ Default system settings seeded.';
            }

            // 4. Check or Create Admin User
            if (!User::where('role', 'admin')->exists()) {
                User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@corevisys.com',
                    'password' => Hash::make('admin123456'),
                    'role' => 'admin',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $output[] = '✓ Created initial Admin account: admin@corevisys.com (Password: admin123456)';
            } else {
                $output[] = '✓ Existing Admin account retained.';
            }

            // 5. Storage Symlink
            try {
                if (!file_exists(public_path('storage'))) {
                    Artisan::call('storage:link');
                    $output[] = '✓ Storage symlink created in public/storage.';
                } else {
                    $output[] = '✓ Storage symlink already exists.';
                }
            } catch (\Throwable $ex) {
                $output[] = '! Storage link notice: ' . $ex->getMessage();
            }

            // 6. Clear Optimizations
            Artisan::call('optimize:clear');
            $output[] = '✓ Application caches cleared and refreshed.';

            // 7. Write lock file
            File::put($this->lockFile, 'Installed on ' . now()->toIso8601String());
            $output[] = '✓ Installation lock file created (storage/installed.lock).';

            return back()->with('setup_success', $output);
        } catch (\Throwable $e) {
            return back()->with('error', 'Setup failed: ' . $e->getMessage());
        }
    }
}
