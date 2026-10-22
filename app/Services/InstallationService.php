<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallationService
{
    public function lockFilePath(): string
    {
        return storage_path('installed.lock');
    }

    public function isInstalled(): bool
    {
        return File::exists($this->lockFilePath());
    }

    public function checkServerRequirements(): array
    {
        $requirements = [
            'php' => [
                'name' => 'PHP Version (>= 8.2)',
                'current' => PHP_VERSION,
                'satisfied' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'satisfied' => extension_loaded('pdo_mysql'),
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'satisfied' => extension_loaded('openssl'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'satisfied' => extension_loaded('mbstring'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'satisfied' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML Extension',
                'satisfied' => extension_loaded('xml'),
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'satisfied' => extension_loaded('curl'),
            ],
            'zip' => [
                'name' => 'Zip Extension',
                'satisfied' => extension_loaded('zip'),
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'satisfied' => extension_loaded('bcmath'),
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Writable',
                'satisfied' => is_writable(storage_path()),
            ],
            'cache_writable' => [
                'name' => 'Bootstrap Cache Writable',
                'satisfied' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        $allSatisfied = !in_array(false, array_column($requirements, 'satisfied'), true);

        return [
            'requirements' => $requirements,
            'all_satisfied' => $allSatisfied,
        ];
    }

    public function testDatabaseConnection(
        string $host,
        string|int $port,
        string $database,
        string $username,
        ?string $password
    ): array {
        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ];
            new \PDO($dsn, $username, $password ?? '', $options);

            return [
                'success' => true,
                'message' => 'Database connection successful!',
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function generateKeys(): array
    {
        $appKey = 'base64:' . base64_encode(random_bytes(32));

        $config = [
            'digest_alg' => 'sha256',
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        // Windows openssl.cnf resolution if present
        $winCnf = 'C:\\Program Files\\Common Files\\SSL\\openssl.cnf';
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' && file_exists($winCnf)) {
            $config['config'] = $winCnf;
        }

        $res = openssl_pkey_new($config);
        if ($res === false) {
            unset($config['config']);
            $res = openssl_pkey_new($config);
        }

        $privatePem = '';
        if ($res !== false) {
            if (isset($config['config'])) {
                openssl_pkey_export($res, $privatePem, null, ['config' => $config['config']]);
            } else {
                openssl_pkey_export($res, $privatePem);
            }
            $details = openssl_pkey_get_details($res);
            $publicPem = $details['key'] ?? '';
        } else {
            throw new \RuntimeException('Failed to generate RSA keys. OpenSSL error: ' . openssl_error_string());
        }

        $pepper = bin2hex(random_bytes(32));
        $keyId = 'corevisys-key-' . now()->format('Ymd');

        return [
            'app_key' => $appKey,
            'private_key' => base64_encode($privatePem),
            'public_key' => base64_encode($publicPem),
            'key_id' => $keyId,
            'pepper' => $pepper,
        ];
    }

    public function setupEnvironment(array $data, array $keys): void
    {
        $examplePath = base_path('deploy/.env.production.example');
        if (!File::exists($examplePath)) {
            $examplePath = base_path('.env.example');
        }

        $content = File::exists($examplePath) ? File::get($examplePath) : '';

        $replacements = [
            'APP_NAME' => '"' . addslashes($data['app_name'] ?? 'Corevisys License') . '"',
            'APP_ENV' => 'production',
            'APP_KEY' => $keys['app_key'],
            'APP_DEBUG' => 'false',
            'APP_URL' => rtrim($data['app_url'] ?? 'https://license.corevisys.com', '/'),
            'DB_CONNECTION' => 'mysql',
            'DB_HOST' => $data['db_host'] ?? '127.0.0.1',
            'DB_PORT' => (string) ($data['db_port'] ?? '3306'),
            'DB_DATABASE' => $data['db_database'] ?? 'corevisys_license',
            'DB_USERNAME' => $data['db_username'] ?? 'corevisys_user',
            'DB_PASSWORD' => $data['db_password'] ?? '',
            'SESSION_DRIVER' => 'database',
            'QUEUE_CONNECTION' => 'database',
            'CACHE_STORE' => 'database',
            'LICENSE_SIGNING_PRIVATE_KEY' => '"' . $keys['private_key'] . '"',
            'LICENSE_SIGNING_PUBLIC_KEY' => '"' . $keys['public_key'] . '"',
            'LICENSE_SIGNING_KEY_ID' => $keys['key_id'],
            'LICENSE_SIGNING_ALGORITHM' => 'RSA-SHA256',
            'LICENSE_PEPPER' => $keys['pepper'],
            'OFFLINE_VALIDITY_DAYS' => '7',
            'PULSE_INTERVAL_DAYS' => '30',
            'PULSE_GRACE_DAYS' => '7',
        ];

        if (!empty($data['mail_host'])) {
            $replacements['MAIL_MAILER'] = 'smtp';
            $replacements['MAIL_HOST'] = $data['mail_host'];
            $replacements['MAIL_PORT'] = (string) ($data['mail_port'] ?? '587');
            $replacements['MAIL_USERNAME'] = $data['mail_username'] ?? '';
            $replacements['MAIL_PASSWORD'] = $data['mail_password'] ?? '';
            $replacements['MAIL_ENCRYPTION'] = $data['mail_encryption'] ?? 'tls';
            $replacements['MAIL_FROM_ADDRESS'] = $data['mail_from_address'] ?? 'no-reply@corevisys.com';
        }

        foreach ($replacements as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$value}", $content);
            } else {
                $content .= "\n{$key}={$value}";
            }
        }

        File::put(base_path('.env'), $content);

        // Apply immediately to current runtime
        Config::set('app.name', $data['app_name'] ?? 'Corevisys License');
        Config::set('app.url', $data['app_url'] ?? 'https://license.corevisys.com');
        Config::set('app.key', $keys['app_key']);
        Config::set('app.license_pepper', $keys['pepper']);
        Config::set('services.license.signing_key_id', $keys['key_id']);
        Config::set('services.license.signing_private_key', $keys['private_key']);
        Config::set('services.license.signing_public_key', $keys['public_key']);
        Config::set('database.default', 'mysql');
        Config::set('database.connections.mysql.host', $data['db_host'] ?? '127.0.0.1');
        Config::set('database.connections.mysql.port', (int) ($data['db_port'] ?? 3306));
        Config::set('database.connections.mysql.database', $data['db_database'] ?? 'corevisys_license');
        Config::set('database.connections.mysql.username', $data['db_username'] ?? 'corevisys_user');
        Config::set('database.connections.mysql.password', $data['db_password'] ?? '');

        DB::purge('mysql');
    }

    public function runMigrations(): void
    {
        Artisan::call('migrate', ['--force' => true]);
    }

    public function seedSystemSettings(): void
    {
        if (class_exists(\Database\Seeders\SystemSettingsSeeder::class)) {
            Artisan::call('db:seed', [
                '--class' => 'Database\Seeders\SystemSettingsSeeder',
                '--force' => true,
            ]);
        }
    }

    public function createAdminUser(string $email, string $password, string $name = 'Administrator'): User
    {
        $existing = User::where('role', 'admin')->first();
        if ($existing) {
            return $existing;
        }

        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
    }

    public function linkStorage(): void
    {
        try {
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }
        } catch (\Throwable $e) {
            // Ignore if link fails on some restricted shared hosting
        }
    }

    public function clearAndCache(): void
    {
        try {
            Artisan::call('package:discover', ['--ansi' => false]);
        } catch (\Throwable $e) {
            // Best effort — may fail on restricted hosts
        }
        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {
            // Best effort
        }
    }

    public function markAsInstalled(string $source = 'Web'): void
    {
        File::put($this->lockFilePath(), "Installed via {$source} on " . now()->toIso8601String() . PHP_EOL);
    }
}
