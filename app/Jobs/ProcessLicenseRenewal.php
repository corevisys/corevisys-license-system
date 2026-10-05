<?php

namespace App\Jobs;

use App\Models\License;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLicenseRenewal implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $license;

    public function __construct(License $license)
    {
        $this->license = $license;
    }

    public function handle(): void
    {
        if (app(\App\Services\BkashRenewalCheckoutService::class)->isBkashBackedLicense($this->license)) {
            try {
                app(\App\Services\BkashRenewalCheckoutService::class)->createOrReuse($this->license);
            } catch (\Throwable $e) {
                Log::error('bKash renewal checkout could not be created', [
                    'license_id' => $this->license->id,
                    'exception' => get_class($e),
                ]);
            }
        }

        app(\App\Services\LicenseService::class)->renewLicense($this->license);
    }
}
