<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CorevisysInstallCommand extends Command
{
    protected $signature = 'corevisys:install
                            {--force : Overwrite existing lock file and rerun installation}
                            {--admin-email= : Email address for the administrator}
                            {--admin-password= : Initial password for the administrator (leave empty to prompt or generate randomly)}';

    protected $description = 'Install and initialize CoreVisys License Server (database migrations, default settings, and secure admin account)';

    private string $lockFile;

    public function __construct()
    {
        parent::__construct();
        $this->lockFile = storage_path('installed.lock');
    }

    public function handle(): int
    {
        $this->info('=== CoreVisys License Server Installer ===');

        if (File::exists($this->lockFile) && !$this->option('force')) {
            $this->error('CoreVisys is already installed. Use --force to rerun setup.');
            return self::FAILURE;
        }

        // 1. Verify Database Connection
        $this->line('1. Verifying database connection...');
        try {
            DB::connection()->getPdo();
            $this->info('   [OK] Database connection verified.');
        } catch (\Throwable $e) {
            $this->error('   [FAIL] Database connection failed: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 2. Run Database Migrations
        $this->line('2. Running database migrations...');
        try {
            Artisan::call('migrate', ['--force' => true], $this->output);
            $this->info('   [OK] Migrations completed.');
        } catch (\Throwable $e) {
            $this->error('   [FAIL] Migration error: ' . $e->getMessage());
            return self::FAILURE;
        }

        // 3. Seed Default System Settings
        $this->line('3. Seeding default system settings...');
        if (class_exists(\Database\Seeders\SystemSettingsSeeder::class)) {
            try {
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\SystemSettingsSeeder',
                    '--force' => true,
                ], $this->output);
                $this->info('   [OK] System settings seeded.');
            } catch (\Throwable $e) {
                $this->warn('   [WARN] Seeder notice: ' . $e->getMessage());
            }
        }

        // Show payment gateway mode warning
        $bkashSandbox = \App\Models\SystemSetting::getCached('gateway_bkash_sandbox', '?');
        $stripeActive  = \App\Models\SystemSetting::getCached('gateway_stripe_active', '0');
        $bkashActive   = \App\Models\SystemSetting::getCached('gateway_bkash_active', '0');
        $this->newLine();
        $this->warn('================================================================');
        $this->warn('  PAYMENT GATEWAY STATUS — verify before accepting real payments');
        $this->warn('================================================================');
        $this->line('  Stripe  : ' . ($stripeActive === '1' ? 'ENABLED (live)' : 'DISABLED'));
        $this->line('  bKash   : ' . ($bkashActive === '1' ? 'ENABLED' : 'DISABLED') .
            ' | Mode: ' . ($bkashSandbox === '0' ? 'PRODUCTION (live payments)' : 'SANDBOX (test only)'));
        if ($bkashSandbox !== '0') {
            $this->error('  *** bKash is in SANDBOX mode. Real payments will NOT be processed. ***');
            $this->line('  To use live payments: set gateway_bkash_sandbox=0 in system_settings.');
        }
        $this->warn('================================================================');
        $this->newLine();

        // 4. Admin User Creation
        $this->line('4. Checking administrator account...');
        if (!User::where('role', 'admin')->exists()) {
            $email = $this->option('admin-email');
            if (empty($email)) {
                $email = $this->input->isInteractive()
                    ? $this->ask('Enter admin email address', 'admin@corevisys.com')
                    : 'admin@corevisys.com';
            }

            $password = $this->option('admin-password');
            $generated = false;

            if (empty($password)) {
                if ($this->input->isInteractive()) {
                    $password = $this->secret('Enter admin password (leave empty to auto-generate a secure random password)');
                }
            }

            if (empty($password)) {
                // Generate secure 16-character random password
                $password = Str::password(16, true, true, false, false);
                $generated = true;
            }

            $admin = User::create([
                'name' => 'Administrator',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $this->info('   [OK] Administrator account created successfully.');
            $this->newLine();
            $this->warn('****************************************************************');
            $this->warn('  ADMIN CREDENTIALS (STORE SAFELY - SHOWN ONCE):');
            $this->line("  Email:    {$email}");
            if ($generated) {
                $this->line("  Password: {$password} (Auto-generated secure password)");
            } else {
                $this->line("  Password: [Set as specified by user]");
            }
            $this->warn('****************************************************************');
            $this->newLine();
        } else {
            $this->info('   [OK] Administrator account already exists. Keeping existing admin.');
        }

        // 5. Storage Symlink
        $this->line('5. Linking storage disk...');
        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
                $this->info('   [OK] Storage symlink created.');
            } else {
                $this->info('   [OK] Storage symlink already exists.');
            }
        } catch (\Throwable $e) {
            $this->warn('   [WARN] Storage link: ' . $e->getMessage());
        }

        // 6. Clear Optimizations
        $this->line('6. Refreshing application cache...');
        Artisan::call('optimize:clear');
        $this->info('   [OK] Caches cleared.');

        // 7. Write Installation Lock File
        File::put($this->lockFile, 'Installed via CLI corevisys:install on ' . now()->toIso8601String() . PHP_EOL);
        $this->info("   [OK] Installation lock file written to {$this->lockFile}.");

        $this->newLine();
        $this->info('CoreVisys License Server installation completed successfully!');

        return self::SUCCESS;
    }
}
