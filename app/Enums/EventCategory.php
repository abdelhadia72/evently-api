<?php

namespace App\Enums;

enum EventCategory: string
{
    case MUSIC = 'music';
    case TECH = 'tech';
    case SPORTS = 'sports';
    case FOOD = 'food';
    case ART = 'art';
    case BUSINESS = 'business';
    case EDUCATION = 'education';
    case ENTERTAINMENT = 'entertainment';
    case GAMING = 'gaming';
    case HEALTH = 'health';
    case LIFESTYLE = 'lifestyle';
    case OTHER = 'other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
