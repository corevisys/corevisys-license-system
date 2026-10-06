<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\LicenseService;

class RenewSubscriptions extends Command
{
    protected $signature = 'license:renew-subscriptions';
    protected $description = 'Process auto-renewal for due subscriptions';

    public function handle()
    {
        $this->info('Finding subscriptions due for renewal...');

        $licenses = \App\Models\License::where('auto_renew', true)
            ->where('status', 'active')
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', \Carbon\Carbon::now())
            ->get();

        foreach ($licenses as $license) {
            \App\Jobs\ProcessLicenseRenewal::dispatch($license);
        }

        $this->info("Dispatched renewal jobs for {$licenses->count()} licenses.");
    }
}
