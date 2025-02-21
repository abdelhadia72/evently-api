<?php

namespace App\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ACTIVE = 'active';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case POSTPONED = 'postponed';
    case SOLD_OUT = 'sold_out';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
