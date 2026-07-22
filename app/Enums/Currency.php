<?php

namespace App\Enums;

enum Currency: string
{
    case VES = 'VES';
    case USD = 'USD';
    case EUR = 'EUR';

    public function label(): string
    {
        return match ($this) {
            self::VES => 'Bolívares (Bs)',
            self::USD => 'Dólares (USD)',
            self::EUR => 'Euros (EUR)',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $currency) => [$currency->value => $currency->label()]
        )->all();
    }
}
