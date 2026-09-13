<?php

namespace App\Jobs;

use App\Models\License;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

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
        // Resolve billing period (in days) from the product's subscription price.
        $billingPeriod = 30;
        $price = $this->license->product?->prices()->where('type', 'subscription')->first();
        if ($price && $price->billing_period) {
            $billingPeriod = (int) $price->billing_period;
        }

        // Mock Payment Logic
        $paymentSuccess = true;

        if ($paymentSuccess) {
            $oldExpiry = $this->license->expires_at;

            $base = $this->license->expires_at ?? Carbon::now();
            $newExpiry = $base->copy()->addDays($billingPeriod);

            $this->license->update([
                'expires_at' => $newExpiry,
                'next_billing_at' => $newExpiry->copy()->addDays($billingPeriod),
                'last_check_at' => Carbon::now(),
                'grace_expires_at' => null
            ]);

            \App\Services\AuditService::log(
                'subscription_renewed',
                $this->license,
                ['expires_at' => $oldExpiry],
                ['expires_at' => $this->license->expires_at]
            );
        } else {
            if (is_null($this->license->grace_expires_at)) {
                $this->license->update(['grace_expires_at' => $this->license->expires_at->addDays(7)]);
            }

            \App\Services\AuditService::log(
                'subscription_renewal_failed',
                $this->license,
                ['status' => 'active'],
                ['grace_expires_at' => $this->license->grace_expires_at]
            );
        }
    }
}
