<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ResetLookupHashes extends Command
{
    protected $signature = 'license:reset-lookup-hashes {--force : Force the operation}';

    protected $description = 'Set all license lookup_hash values to NULL for pepper rotation';

    public function handle(): int
    {
        $hasPlaintextColumn = DB::getSchemaBuilder()->hasColumn('licenses', 'license_key');
        $totalWithLookup = DB::table('licenses')->whereNotNull('lookup_hash')->count();
        $recomputableCount = 0;

        if ($hasPlaintextColumn) {
            $recomputableCount = DB::table('licenses')
                ->whereNotNull('lookup_hash')
                ->whereNotNull('license_key')
                ->where('license_key', '!=', '')
                ->count();
        }

        $unrecoverableExceptLazy = $totalWithLookup - $recomputableCount;

        $this->warn('--- Preflight Assessment for Pepper Rotation ---');
        $this->line("Total active lookup_hash records: {$totalWithLookup}");
        $this->line("Recomputable offline via license:migrate-legacy-keys (plaintext key available): {$recomputableCount}");
        $this->line("Rows unrecoverable except via lazy fallback (no plaintext key stored): {$unrecoverableExceptLazy}");

        if ($totalWithLookup === 0) {
            $this->info('No licenses with active lookup_hash found. Nothing to reset.');
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            $this->error('The --force option is required to run this command.');
            return self::FAILURE;
        }

        $confirmation = $this->ask('Type "RESET" to confirm resetting lookup_hash for all licenses:');
        if ($confirmation !== 'RESET') {
            $this->warn('Confirmation mismatched. Operation cancelled.');
            return self::FAILURE;
        }

        $affected = DB::table('licenses')->whereNotNull('lookup_hash')->update(['lookup_hash' => null]);
        Cache::forget('license:has_legacy_lookup_rows');

        $this->info("Successfully reset {$affected} license lookup_hash records to NULL.");
        return self::SUCCESS;
    }
}
