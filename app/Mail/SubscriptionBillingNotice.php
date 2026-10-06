<?php

namespace App\Mail;

use App\Models\License;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable sent to a customer when their subscription billing lifecycle changes:
 * - Payment failed + grace period started
 * - Grace period expired, license is now expired
 */
class SubscriptionBillingNotice extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly License $license,
        public readonly string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'CoreVisys Subscription Billing Notice',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.subscription_billing_notice',
        );
    }
}
