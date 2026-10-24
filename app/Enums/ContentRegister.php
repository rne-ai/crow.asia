<?php

namespace App\Enums;

enum ContentRegister: string
{
    case CASUAL = 'casual';
    case STANDARD = 'standard';

    public function label(): string
    {
        return match ($this) {
            self::CASUAL => 'Kasual (Gue/Lo)',
            self::STANDARD => 'Standar (Saya/Kamu)',
        };
    }
}
