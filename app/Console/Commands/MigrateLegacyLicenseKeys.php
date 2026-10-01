<?php

namespace App\Console\Commands;

use App\Models\License;
use App\Services\LicenseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateLegacyLicenseKeys extends Command
{
    protected $signature = 'license:migrate-legacy-keys {--dry-run : Preview updates without writing data}';

    protected $description = 'Backfill lookup_hash and license_key_hash from legacy plaintext or key_encrypted rows';

    public function handle()
    {
        $hasPlaintextColumn = DB::getSchemaBuilder()->hasColumn('licenses', 'license_key');
        $hasKeyEncryptedColumn = DB::getSchemaBuilder()->hasColumn('licenses', 'key_encrypted');

        if (!$hasPlaintextColumn && !$hasKeyEncryptedColumn) {
            $remainingNull = DB::table('licenses')->whereNull('lookup_hash')->count();
            $this->info('No plaintext license_key or key_encrypted column found. Nothing to do.');
            $this->info('Backfilled: 0 rows.');
            $this->info("Remaining licenses with NULL lookup_hash: {$remainingNull}.");
            return self::SUCCESS;
        }

        $pepper = LicenseService::getLicensePepper();

        // Find candidate licenses that need lookup_hash backfilled
        $query = License::query()->where(function ($q) {
            $q->whereNull('lookup_hash')
                ->orWhere('lookup_hash', '');
        });

        // Only query rows that have some key material to recover from
        $query->where(function ($q) use ($hasPlaintextColumn, $hasKeyEncryptedColumn) {
            $hasAny = false;
            if ($hasKeyEncryptedColumn) {
                $q->whereNotNull('key_encrypted')->where('key_encrypted', '!=', '');
                $hasAny = true;
            }
            if ($hasPlaintextColumn) {
                if ($hasAny) {
                    $q->orWhere(function ($sub) {
                        $sub->whereNotNull('license_key')->where('license_key', '!=', '');
                    });
                } else {
                    $q->whereNotNull('license_key')->where('license_key', '!=', '');
                    $hasAny = true;
                }
            }
        });

        $records = $query->get();

        if ($records->isEmpty()) {
            $remainingNull = DB::table('licenses')->whereNull('lookup_hash')->count();
            $this->info('No legacy plaintext or recoverable encrypted license keys require migration.');
            $this->info('Backfilled: 0 rows.');
            $this->info("Remaining licenses with NULL lookup_hash: {$remainingNull}.");
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($records as $license) {
            $rawKey = null;

            // Priority 1: Decrypt key_encrypted using Eloquent 'encrypted' cast (uses APP_KEY)
            if (!empty($license->key_encrypted)) {
                try {
                    $rawKey = (string) $license->key_encrypted;
                } catch (\Throwable $e) {
                    $this->warn("Failed to decrypt key_encrypted for license {$license->id}: {$e->getMessage()}");
                }
            }

            // Priority 2: Fall back to legacy plaintext license_key column if present
            if (empty($rawKey) && $hasPlaintextColumn && !empty($license->license_key)) {
                $rawKey = (string) $license->license_key;
            }

            if (empty($rawKey)) {
                continue;
            }

            $salt = $license->secret_salt ?: Str::random(32);
            $hash = (!empty($license->license_key_hash)) ? $license->license_key_hash : hash('sha256', $rawKey . $salt);
            $lookupHash = hash_hmac('sha256', $rawKey, $pepper);

            $this->line("Migrating license {$license->id} to compute lookup_hash under current pepper.");

            if (!$this->option('dry-run')) {
                DB::table('licenses')
                    ->where('id', $license->id)
                    ->update([
                        'license_key_hash' => $hash,
                        'secret_salt'      => $salt,
                        'lookup_hash'      => $lookupHash,
                    ]);
            }

            $count++;
        }

        Cache::forget('license:has_legacy_lookup_rows');

        $remainingNull = DB::table('licenses')
            ->whereNull('lookup_hash')
            ->count();

        $this->info("Backfilled: {$count} rows." . ($this->option('dry-run') ? ' (Dry run only).' : ''));
        $this->info("Remaining licenses with NULL lookup_hash: {$remainingNull}.");

        return self::SUCCESS;
    }
}
