<?php

namespace App\Enums;

enum PriceIdsEnum: string
{
    case PRO_MONTHLY = 'pro_monthly';
    case PRO_SEMESTER = 'pro_semester';
    case PRO_ANNUAL = 'pro_annual';

    public function priceId(): string
    {
        return match($this) {
            self::PRO_MONTHLY => 'price_1TS1KWCagX8WWsbY6ammIfD7',
            self::PRO_SEMESTER => 'price_1TS1LYCagX8WWsbYueoncZAP',
            self::PRO_ANNUAL => 'price_1TS1LYCagX8WWsbYMkKqzyZp',
        };
    }
}