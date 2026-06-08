<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use Carbon\Carbon;

class CleanupExpiredLicenses extends Command
{
    protected $signature = 'license:cleanup-expired {--days=90 : Days after which to archive/delete expired licenses}';
    protected $description = 'Cleanup licenses that have been expired for a long period';

    public function handle()
    {
        $days = $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);

        $this->info("Cleaning up licenses expired before {$cutoffDate->toDateString()}...");

        $expiredLicenses = License::where('status', 'expired')
            ->where('expires_at', '<', $cutoffDate)
            ->get();

        foreach ($expiredLicenses as $license) {
            // In a real app, we might move to an archive table or soft-delete.
            // For this project, we will log the cleanup and mark as archived.
            $license->update(['status' => 'archived']);

            \App\Services\AuditService::log(
                'license_cleanup_archived',
                $license,
                ['status' => 'expired'],
                ['status' => 'archived']
            );
        }

        $this->info("Archived {$expiredLicenses->count()} expired licenses.");
    }
}
