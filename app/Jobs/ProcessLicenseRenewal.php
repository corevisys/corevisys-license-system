<?php

namespace App\Jobs;

use App\Models\License;
use App\Models\Payment;
use App\Services\BKashPaymentService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
        // Stripe billing is managed by the native subscription lifecycle and webhook events.
        // The renewal job should not query Stripe for status-only success as a substitute for real billing.
        $subscriptionId = $this->license->gateway_subscription_id;
        if ($subscriptionId && str_starts_with($subscriptionId, 'sub_')) {
            return;
        }

        // Resolve billing period (in days) from the product's subscription price.
        $billingPeriod = 30;
        $price = $this->license->product?->prices()->where('type', 'subscription')->first();
        if ($price && $price->billing_period) {
            $billingPeriod = (int) $price->billing_period;
        }

        $paymentSuccess = $this->chargeRecurringSubscription();

        if ($paymentSuccess) {
            $oldExpiry = $this->license->expires_at;

            $base = $this->license->expires_at ?? Carbon::now();
            $newExpiry = $base->copy()->addDays($billingPeriod);

            $this->license->update([
                'expires_at' => $newExpiry,
                'next_billing_at' => $newExpiry->copy()->addDays($billingPeriod),
                'last_check_at' => Carbon::now(),
                'grace_expires_at' => null,
                'status' => 'active',
                'auto_renew' => true,
            ]);

            \App\Services\AuditService::log(
                'subscription_renewed',
                $this->license,
                ['expires_at' => $oldExpiry],
                ['expires_at' => $this->license->expires_at]
            );
        } else {
            $shouldGrantGrace = is_null($this->license->grace_expires_at) || $this->license->grace_expires_at->isPast();

            if ($shouldGrantGrace) {
                $this->license->update([
                    'grace_expires_at' => Carbon::now()->addDays(7),
                    'status' => 'active',
                ]);
                $this->notifyCustomer('your recurring payment failed and a 7-day grace period has started');
            }

            if ($this->license->grace_expires_at && $this->license->grace_expires_at->isPast()) {
                $this->license->update([
                    'status' => 'expired',
                    'auto_renew' => false,
                ]);
            }

            \App\Services\AuditService::log(
                'subscription_renewal_failed',
                $this->license,
                ['status' => 'active'],
                ['grace_expires_at' => $this->license->grace_expires_at, 'status' => $this->license->status]
            );
        }
    }

    protected function chargeRecurringSubscription(): bool
    {
        $subscriptionId = $this->license->gateway_subscription_id;

        if (!$subscriptionId) {
            return false;
        }

        if (str_starts_with($subscriptionId, 'bkash_') && (config('services.bkash.app_key') || config('services.bkash.app_secret'))) {
            return $this->chargeBkashRecurringSubscription();
        }

        return false;
    }

    protected function chargeBkashRecurringSubscription(): bool
    {
        $order = $this->license->order;
        if (!$order || !$order->user) {
            return false;
        }

        $price = $this->license->product?->prices()->where('type', 'subscription')->first()
            ?? $this->license->product?->prices()->where('type', 'full')->first();

        if (!$price) {
            return false;
        }

        $idempotencyKey = $this->buildBkashIdempotencyKey();
        if ($this->hasExistingVerifiedBkashAttempt($idempotencyKey)) {
            return true;
        }

        try {
            $bkashService = app(BKashPaymentService::class);
            $createResult = $bkashService->createPayment($order, $price);
            $paymentId = $createResult['paymentID'] ?? null;

            if (!$paymentId) {
                return false;
            }

            $executeResult = $bkashService->executePayment($paymentId);
            $completed = (($executeResult['transactionStatus'] ?? '') === 'Completed')
                || (($executeResult['statusCode'] ?? '') === '0000' && (($executeResult['status'] ?? '') === 'Completed'));

            $gatewayResponse = array_merge($createResult, $executeResult, ['idempotency_key' => $idempotencyKey]);
            $order->payments()->updateOrCreate(
                ['gateway' => 'bkash', 'transaction_id' => $paymentId],
                [
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'amount' => $price->amount,
                    'status' => $completed ? 'verified' : 'failed',
                    'gateway_response' => $gatewayResponse,
                ]
            );

            return $completed;
        } catch (\Throwable $e) {
            \Log::error('bKash recurring charge failed', [
                'license_id' => $this->license->id,
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    protected function hasExistingVerifiedBkashAttempt(string $idempotencyKey): bool
    {
        $order = $this->license->order;

        if (!$order) {
            return false;
        }

        foreach ($order->payments()->where('gateway', 'bkash')->get() as $payment) {
            $response = $payment->gateway_response ?? [];
            if (($response['idempotency_key'] ?? null) === $idempotencyKey && $payment->status === 'verified') {
                return true;
            }
        }

        return false;
    }

    protected function buildBkashIdempotencyKey(): string
    {
        $billingReference = $this->license->next_billing_at ? $this->license->next_billing_at->format('Y-m-d H:i:s') : Carbon::now()->format('Y-m-d H:i:s');

        return 'bkash-renewal:' . $this->license->id . ':' . $billingReference;
    }

    protected function notifyCustomer(string $reason): void
    {
        $user = $this->license->user;

        if (!$user) {
            return;
        }

        try {
            Mail::raw(
                "Your CoreVisys subscription billing update requires attention: {$reason}.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('CoreVisys Subscription Billing Notice');
                }
            );
        } catch (\Throwable $e) {
            \Log::error('Subscription billing notification failed', [
                'license_id' => $this->license->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
