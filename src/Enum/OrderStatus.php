<?php

namespace App\Enum;

enum OrderStatus: string
{
    case PENDING   = 'pending';
    case VALIDATED = 'validated';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING   => 'En attente',
            self::VALIDATED => 'Validée',
            self::CANCELLED => 'Annulée',
        };
    }
}
