<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BkashRenewalPaymentLink extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $paymentUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Action required: complete your CoreVisys renewal');
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.bkash_renewal_payment_link',
            with: ['paymentUrl' => $this->paymentUrl],
        );
    }
}
