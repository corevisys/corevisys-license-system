<?php

namespace App\Services;

use App\Mail\BkashRenewalPaymentLink;
use App\Models\License;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Support\OrderStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BkashRenewalCheckoutService
{
    public function isBkashBackedLicense(License $license): bool
    {
        if ($license->gateway_subscription_id && str_starts_with($license->gateway_subscription_id, 'bkash_')) {
            return true;
        }

        return Payment::query()
            ->where('order_id', $license->order_id)
            ->where('gateway', 'bkash')
            ->exists();
    }

    public function isMatchingRenewalPayment(Payment $payment): bool
    {
        $order = Order::query()->find($payment->order_id);
        $license = $order ? License::query()->find($order->license_id) : null;

        if (! $order || $order->type !== 'renewal' || ! $license
            || ! $order->license_id || $order->license_id !== $license->id
            || $payment->license_id !== $license->id
            || (int) $payment->user_id !== (int) $order->user_id
            || (int) $license->user_id !== (int) $order->user_id
            || ! OrderItem::query()->where('order_id', $order->id)
                ->where('product_id', $license->product_id)->exists()
            || abs((float) $payment->amount - (float) $order->total_amount) >= 0.005) {
            return false;
        }

        if ($order->status === OrderStatus::COMPLETED && $payment->status === 'verified') {
            return true;
        }

        if (! in_array($order->status, [OrderStatus::PENDING, OrderStatus::AWAITING_PAYMENT], true)
            || $payment->status !== 'pending') {
            return false;
        }

        if ($order->renewal_cycle_at) {
            return $license->next_billing_at
                && $order->renewal_cycle_at->getTimestamp() === $license->next_billing_at->getTimestamp();
        }

        return true;
    }

    public function createOrReuse(License $license): ?Order
    {
        $license->refresh();
        $cycle = $license->next_billing_at;
        $user = User::query()->find($license->user_id);
        $product = Product::query()->find($license->product_id);

        if (! $cycle || ! $user || ! $product || ! $this->isBkashBackedLicense($license)) {
            Log::warning('bKash renewal checkout skipped because license billing data is incomplete', [
                'license_id' => $license->id,
            ]);

            return null;
        }

        $price = $product->prices()->where('type', 'subscription')->first()
            ?? $product->prices()->where('type', 'full')->first();
        if (! $price || (float) $price->amount <= 0 || empty($price->currency)) {
            Log::error('bKash renewal checkout cannot resolve a valid product price', [
                'license_id' => $license->id,
            ]);

            return null;
        }

        $cycleAt = $cycle->copy()->startOfSecond();
        if ($cycleAt->isFuture()) {
            return null;
        }

        [$order, $payment, $shouldCreate] = DB::transaction(function () use ($license, $cycleAt, $user, $price) {
            $lockedLicense = License::whereKey($license->id)->lockForUpdate()->first();
            if (! $lockedLicense || ! $lockedLicense->next_billing_at
                || $lockedLicense->next_billing_at->getTimestamp() !== $cycleAt->getTimestamp()
                || $lockedLicense->next_billing_at->isFuture()) {
                return [null, null, false];
            }

            $existing = Order::where('license_id', $license->id)
                ->where('type', 'renewal')
                ->where('renewal_cycle_at', $cycleAt)
                ->first();

            $order = $existing ?? Order::create([
                'order_number' => 'ORD-REN-'.strtoupper(Str::random(10)),
                'user_id' => $user->id,
                'license_id' => $license->id,
                'total_amount' => $price->amount,
                'currency' => $price->currency,
                'status' => OrderStatus::AWAITING_PAYMENT,
                'payment_method' => 'bkash',
                'type' => 'renewal',
                'renewal_cycle_at' => $cycleAt,
            ]);

            if (! $existing) {
                $order->items()->create([
                    'product_id' => $license->product_id,
                    'product_price_id' => $price->id,
                    'price' => $price->amount,
                    'license_type' => 'subscription',
                ]);
            }

            $payment = $order->payments()->where('gateway', 'bkash')->lockForUpdate()->first();
            if (! $payment) {
                $payment = $order->payments()->create([
                    'user_id' => $user->id,
                    'license_id' => $license->id,
                    'gateway' => 'bkash',
                    'amount' => $price->amount,
                    'status' => 'pending',
                    'gateway_response' => [
                        'renewal_cycle' => $cycleAt->toDateTimeString(),
                        'checkout_state' => 'not_started',
                    ],
                ]);
            }

            $gatewayResponse = $payment->gateway_response ?? [];
            if ($payment->status !== 'pending' || ($payment->transaction_id && ! empty($gatewayResponse['bkashURL']))) {
                return [$order, $payment, false];
            }

            if (($gatewayResponse['checkout_state'] ?? null) === 'creating') {
                $startedAt = isset($gatewayResponse['checkout_started_at'])
                    ? \Carbon\Carbon::parse($gatewayResponse['checkout_started_at'])
                    : null;
                if ($startedAt && $startedAt->diffInSeconds(now()) < 900) {
                    return [$order, $payment, false];
                }

                $this->failCheckout($order, $payment, 'previous checkout attempt did not finish');

                return [$order, $payment, false];
            }

            if (! in_array($gatewayResponse['checkout_state'] ?? 'not_started', ['not_started'], true)) {
                $this->failCheckout($order, $payment, 'previous checkout attempt is not reusable');

                return [$order, $payment, false];
            }

            $gatewayResponse['checkout_state'] = 'creating';
            $gatewayResponse['checkout_started_at'] = now()->toIso8601String();
            $payment->update(['gateway_response' => $gatewayResponse]);

            return [$order, $payment, true];
        });

        if (! $order || ! $payment || $payment->status !== 'pending') {
            return $order;
        }

        $gatewayResponse = $payment->gateway_response ?? [];
        if ($payment->transaction_id && ! empty($gatewayResponse['bkashURL'])) {
            $this->emailPaymentLinkOnce($order, $payment, $gatewayResponse['bkashURL']);

            return $order;
        }

        if (! $shouldCreate) {
            return $order;
        }

        try {
            $response = app(BKashPaymentService::class)->createPayment($order, $price);
            $payment->update([
                'transaction_id' => $response['paymentID'],
                'gateway_response' => array_merge($response, [
                    'renewal_cycle' => $cycleAt->toDateTimeString(),
                    'checkout_state' => 'ready',
                ]),
            ]);

            $this->emailPaymentLinkOnce($order, $payment->fresh(), $response['bkashURL']);
        } catch (\Throwable $e) {
            $this->failCheckout($order, $payment, 'provider checkout creation failed');
            Log::error('bKash renewal checkout creation failed', [
                'license_id' => $license->id,
                'order_id' => $order->id,
                'exception' => get_class($e),
            ]);
        }

        return $order->fresh();
    }

    protected function emailPaymentLinkOnce(Order $order, Payment $payment, string $url): void
    {
        $claimedAt = now()->startOfSecond();
        $claimed = Order::whereKey($order->id)
            ->whereNull('renewal_link_email_sent_at')
            ->update(['renewal_link_email_sent_at' => $claimedAt]);

        if (! $claimed) {
            return;
        }

        $user = User::query()->find($order->user_id);
        if (! $user || empty($user->email)) {
            Log::error('bKash renewal payment link email has no recipient', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
            ]);
            $this->releaseEmailClaim($order, $claimedAt);

            return;
        }

        try {
            Mail::to($user->email)->send(new BkashRenewalPaymentLink($order, $url));
        } catch (\Throwable $e) {
            Log::error('bKash renewal payment link email failed', [
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'exception' => get_class($e),
            ]);
            $this->releaseEmailClaim($order, $claimedAt);
        }
    }

    protected function releaseEmailClaim(Order $order, \Carbon\Carbon $claimedAt): void
    {
        Order::whereKey($order->id)
            ->where('renewal_link_email_sent_at', $claimedAt)
            ->update(['renewal_link_email_sent_at' => null]);
    }

    protected function failCheckout(Order $order, Payment $payment, string $reason): void
    {
        $payment->update([
            'status' => 'failed',
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'checkout_state' => 'failed',
                'failure_reason' => $reason,
            ]),
        ]);

        if ($order->status !== OrderStatus::COMPLETED) {
            $order->update(['status' => OrderStatus::CANCELLED]);
        }
    }
}
