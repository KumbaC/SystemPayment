<?php

namespace App\Models;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalePayment extends Model
{
    protected $fillable = [
        'sale_id',
        'payment_method',
        'currency',
        'amount',
        'amount_ves',
        'exchange_rate',
        'reference',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'amount_ves' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function paymentMethodLabel(): string
    {
        return PaymentMethod::tryFrom($this->payment_method)?->label() ?? $this->payment_method;
    }

    public function currencyLabel(): string
    {
        return Currency::tryFrom($this->currency)?->label() ?? $this->currency;
    }
}
