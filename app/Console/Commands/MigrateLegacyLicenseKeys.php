<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateLegacyLicenseKeys extends Command
{
    protected $signature = 'license:migrate-legacy-keys {--dry-run : Preview updates without writing data}';

    protected $description = 'Backfill sha256 license_key_hash values from legacy plaintext license_key rows';

    public function handle()
    {
        if (!DB::getSchemaBuilder()->hasColumn('licenses', 'license_key')) {
            $this->info('No legacy plaintext license_key column found. Nothing to do.');
            return self::SUCCESS;
        }

        $records = DB::table('licenses')
            ->whereNotNull('license_key')
            ->where(function ($query) {
                $query->whereNull('license_key_hash')
                    ->orWhere('license_key_hash', '');
            })
            ->get();

        if ($records->isEmpty()) {
            $this->info('No legacy plaintext license keys require migration.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($records as $license) {
            $salt = $license->secret_salt ?: Str::random(32);
            $hash = hash('sha256', $license->license_key . $salt);

            $this->line("Migrating license {$license->id} from plaintext key to salted hash.");

            if (!$this->option('dry-run')) {
                DB::table('licenses')
                    ->where('id', $license->id)
                    ->update([
                        'license_key_hash' => $hash,
                        'secret_salt' => $salt,
                    ]);
            }

            $count++;
        }

        $this->info("{$count} legacy license keys processed." . ($this->option('dry-run') ? ' Dry run only.' : ''));

        return self::SUCCESS;
    }
}
