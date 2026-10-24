<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case CLOSED = 'CLOSED';
    case EXPIRED = 'EXPIRED';

    public function isSuccessful(): bool
    {
        return $this === self::PAID;
    }
}
