<?php

namespace App\Support;

final class OrderStatus
{
    public const PENDING = 'pending';
    public const AWAITING_PAYMENT = 'awaiting_payment';
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    /**
     * Single source of truth for the order lifecycle.
     */
    public static function values(): array
    {
        return [
            self::PENDING,
            self::AWAITING_PAYMENT,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }
}
