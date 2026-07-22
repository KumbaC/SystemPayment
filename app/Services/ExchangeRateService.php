<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Setting;

class ExchangeRateService
{
    public function currentRate(): float
    {
        return (float) Setting::get('exchange_rate_usd_ves', 1);
    }

    public function updateRate(float $rate, ?int $userId = null, ?string $notes = null): ExchangeRate
    {
        Setting::set('exchange_rate_usd_ves', $rate);

        return ExchangeRate::query()->create([
            'rate' => $rate,
            'source' => 'manual',
            'user_id' => $userId,
            'notes' => $notes,
        ]);
    }

    public function usdToVes(float $amountUsd, ?float $rate = null): float
    {
        $rate = $rate ?? $this->currentRate();

        return round($amountUsd * $rate, 2);
    }

    public function vesToUsd(float $amountVes, ?float $rate = null): float
    {
        $rate = $rate ?? $this->currentRate();

        if ($rate <= 0) {
            return 0;
        }

        return round($amountVes / $rate, 4);
    }

    public function convertToVes(float $amount, string $currency, ?float $customRate = null): float
    {
        return match ($currency) {
            'VES' => round($amount, 2),
            'USD' => $this->usdToVes($amount, $customRate),
            'EUR' => $this->usdToVes($amount * (float) Setting::get('exchange_rate_eur_usd', 1.08), $customRate),
            default => round($amount, 2),
        };
    }
}
