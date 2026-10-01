<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * FIX-001 — Key Rotation Command
 *
 * Generates a fresh RSA-2048 keypair and a new LICENSE_PEPPER value,
 * then prints exactly the .env lines to paste in.  It deliberately does NOT
 * write to .env automatically so that the operator controls the rotation
 * window (old responses signed with the previous key are still accepted
 * during the LICENSE_SIGNING_PUBLIC_KEYS overlap period).
 *
 * Usage:
 *   php artisan license:generate-keys
 *   php artisan license:generate-keys --key-id=corevisys-key-2
 *   php artisan license:generate-keys --bits=4096
 */
class GenerateLicenseKeysCommand extends Command
{
    protected $signature = 'license:generate-keys
        {--key-id=  : Key ID to embed (default: corevisys-key-<timestamp>)}
        {--bits=2048 : RSA key size in bits (2048 or 4096)}
        {--force    : Suppress the confirmation prompt}';

    protected $description = 'Generate a new RSA keypair and LICENSE_PEPPER for license signing. Prints .env lines only — does NOT write files.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirmRotation()) {
            $this->line('Aborted.');
            return self::SUCCESS;
        }

        $bits = (int) $this->option('bits');
        if (! in_array($bits, [2048, 4096], true)) {
            $this->error("--bits must be 2048 or 4096. Got: {$bits}");
            return self::FAILURE;
        }

        $keyId = $this->option('key-id') ?: 'corevisys-key-' . now()->format('Ymd');

        // -----------------------------------------------------------------------
        // 1. Generate RSA keypair
        // -----------------------------------------------------------------------
        $config = [
            'digest_alg'       => 'sha256',
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        $cnf = $this->resolveOpenSslConfig();
        if ($cnf) {
            $config['config'] = $cnf;
        }

        $resource = openssl_pkey_new($config);
        if ($resource === false) {
            $this->error('openssl_pkey_new() failed: ' . openssl_error_string());
            return self::FAILURE;
        }

        if ($cnf) {
            openssl_pkey_export($resource, $privatePem, null, ['config' => $cnf]);
        } else {
            openssl_pkey_export($resource, $privatePem);
        }
        $details   = openssl_pkey_get_details($resource);
        $publicPem = $details['key'];

        $privateB64 = base64_encode($privatePem);
        $publicB64  = base64_encode($publicPem);

        // -----------------------------------------------------------------------
        // 2. Generate new LICENSE_PEPPER (32 random bytes → hex)
        // -----------------------------------------------------------------------
        $pepper = bin2hex(random_bytes(32));

        // -----------------------------------------------------------------------
        // 3. Print instructions — NEVER log/display the private key value itself
        // -----------------------------------------------------------------------
        $this->newLine();
        $this->line('<fg=yellow;options=bold>══════════════════════════════════════════════════════════════════</>');
        $this->line('<fg=yellow;options=bold>  FIX-001 · Key Rotation Output — TREAT AS SECRET</>');
        $this->line('<fg=yellow;options=bold>══════════════════════════════════════════════════════════════════</>');
        $this->newLine();

        $this->line('<options=bold>STEP 1 — Capture the old public key into the overlap list.</>');
        $this->line('  Read LICENSE_SIGNING_PUBLIC_KEY from your current .env, then set:');
        $this->newLine();
        $this->line('  LICENSE_SIGNING_PUBLIC_KEYS=<json-array-of-old-base64-public-keys>');
        $this->newLine();
        $this->line('  Example (one old key):');
        $this->line('  LICENSE_SIGNING_PUBLIC_KEYS=["<old-public-key-base64>"]');
        $this->newLine();

        $this->line('<options=bold>STEP 2 — Replace the active signing key pair in your .env:</>');
        $this->newLine();
        // Print the actual new values
        $this->line("LICENSE_SIGNING_KEY_ID={$keyId}");
        $this->line("LICENSE_SIGNING_ALGORITHM=RSA-SHA256");
        // We DO output the keys — they must be pasted by the operator.
        // We mask what goes into logs by using output only (no Log::).
        $this->line("LICENSE_SIGNING_PRIVATE_KEY=\"{$privateB64}\"");
        $this->line("LICENSE_SIGNING_PUBLIC_KEY=\"{$publicB64}\"");
        $this->newLine();

        $this->line('<options=bold>STEP 3 — Rotate APP_KEY (re-encrypts all Eloquent encrypted columns):</>');
        $this->newLine();
        $this->line('  # Move the current APP_KEY to APP_PREVIOUS_KEYS first:');
        $this->line('  # APP_PREVIOUS_KEYS=<current-APP_KEY-value>');
        $this->line('  # Then generate a new APP_KEY:');
        $this->line('  php artisan key:generate');
        $this->newLine();
        $this->line('  After key:generate, run:');
        $this->line('  php artisan license:reset-lookup-hashes');
        $this->newLine();

        $this->line('<options=bold>STEP 4 — Replace LICENSE_PEPPER (invalidates lookup_hash for all licenses):</>');
        $this->newLine();
        $this->line("LICENSE_PEPPER={$pepper}");
        $this->newLine();
        $this->line('  After updating, run:');
        $this->line('  php artisan license:reset-lookup-hashes');
        $this->newLine();

        $this->line('<options=bold>STEP 5 — After the overlap window (LICENSE_ROTATION_OVERLAP_DAYS, default 30d):</>');
        $this->newLine();
        $this->line('  Remove old key IDs from LICENSE_SIGNING_PUBLIC_KEYS and add them to');
        $this->line('  LICENSE_SIGNING_REVOKED_KEY_IDS to hard-reject any lingering old signatures.');
        $this->newLine();

        $this->line('<fg=yellow;options=bold>══════════════════════════════════════════════════════════════════</>');
        $this->line('<fg=red>  ⚠ Do NOT commit these values. Store them in a secrets manager.</>');
        $this->line('<fg=yellow;options=bold>══════════════════════════════════════════════════════════════════</>');
        $this->newLine();

        return self::SUCCESS;
    }

    private function confirmRotation(): bool
    {
        $this->newLine();
        $this->warn('  This command outputs new signing secrets.');
        $this->warn('  Copy and store the output securely before closing this terminal.');
        $this->newLine();

        return $this->confirm('Generate new license signing keys?');
    }

    private function resolveOpenSslConfig(): ?string
    {
        $env = env('OPENSSL_CONF') ?: getenv('OPENSSL_CONF');
        if ($env && file_exists($env)) {
            return $env;
        }

        $candidates = [
            'C:\\xampp\\php\\extras\\openssl\\openssl.cnf',
            'C:\\xampp\\apache\\conf\\openssl.cnf',
            'C:\\xampp\\php\\extras\\ssl\\openssl.cnf',
        ];

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
