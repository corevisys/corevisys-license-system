<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use App\Jobs\SendExpiryNotification;
use Carbon\Carbon;

class NotifyExpiringLicenses extends Command
{
    protected $signature = 'license:notify-expiring';
    protected $description = 'Scan and notify users of licenses expiring soon';

    public function handle()
    {
        $this->info('Scanning for expiring licenses...');

        $intervals = [7, 3, 1];
        $notifiedCount = 0;

        foreach ($intervals as $days) {
            $targetDate = Carbon::now()->addDays($days)->toDateString();

            $licenses = License::where('status', 'active')
                ->whereDate('expires_at', $targetDate)
                ->get();

            foreach ($licenses as $license) {
                SendExpiryNotification::dispatch($license, $days);
                $notifiedCount++;
            }

            $this->info("Found {$licenses->count()} licenses expiring in {$days} days.");
        }

        $this->info("Dispatched {$notifiedCount} expiry notifications.");
    }
}
