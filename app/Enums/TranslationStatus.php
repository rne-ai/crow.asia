<?php

namespace App\Enums;

enum TranslationStatus: string
{
    case DRAFT = 'DRAFT';
    case REVIEW = 'REVIEW';
    case PUBLISHED = 'PUBLISHED';

    public function isLive(): bool
    {
        return $this === self::PUBLISHED;
    }
}
