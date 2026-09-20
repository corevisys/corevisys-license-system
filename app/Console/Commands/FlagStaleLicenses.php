<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FlagStaleLicenses extends Command
{
    protected $signature = 'license:flag-stale
                            {--dry-run : Preview without writing logs}';

    protected $description = 'Report active licenses whose last heartbeat exceeds pulse_interval_days + pulse_grace_days';

    public function handle(): int
    {
        $intervalDays = (int) config('license.pulse_interval_days', 30);
        $graceDays    = (int) config('license.pulse_grace_days', 7);
        $windowDays   = $intervalDays + $graceDays;
        $cutoff       = Carbon::now()->subDays($windowDays);
        $dryRun       = $this->option('dry-run');

        $this->info(sprintf(
            'Overdue threshold: %d days (interval %d + grace %d). Cutoff: %s%s',
            $windowDays, $intervalDays, $graceDays,
            $cutoff->toDateTimeString(),
            $dryRun ? ' [DRY RUN]' : ''
        ));

        // Overdue = active license where last_check_at is older than cutoff,
        // or null past that period since creation
        $stale = License::where('status', 'active')
            ->where(function ($q) use ($cutoff) {
                $q->where('last_check_at', '<', $cutoff)
                  ->orWhere(function ($sub) use ($cutoff) {
                      $sub->whereNull('last_check_at')
                          ->where('created_at', '<', $cutoff);
                  });
            })->get();

        if ($stale->isEmpty()) {
            $this->info('No overdue licenses found.');
            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Type', 'Last Check At', 'Created At', 'Domain'],
            $stale->map(fn ($l) => [
                $l->id, $l->type,
                $l->last_check_at?->toDateTimeString() ?? 'never',
                $l->created_at?->toDateTimeString() ?? 'unknown',
                $l->bound_domain ?? '(unbound)',
            ])->toArray()
        );

        if ($dryRun) {
            $this->warn(sprintf('Dry run complete. %d overdue license(s) reported (no logs recorded).', $stale->count()));
            return self::SUCCESS;
        }

        foreach ($stale as $license) {
            Log::info(sprintf(
                'License %s overdue for heartbeat. Status: %s, Last check: %s, Created: %s',
                $license->id,
                $license->status,
                $license->last_check_at?->toIso8601String() ?? 'never',
                $license->created_at?->toIso8601String() ?? 'unknown'
            ));
        }

        $this->info(sprintf('Reported %d overdue license(s).', $stale->count()));
        return self::SUCCESS;
    }
}

