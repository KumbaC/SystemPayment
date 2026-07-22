<?php

namespace App\Models;

use App\Models\SaleCredit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'invoice_number',
        'customer_id',
        'user_id',
        'sale_date',
        'subtotal_usd',
        'subtotal_ves',
        'tax_rate',
        'tax_usd',
        'tax_ves',
        'total_usd',
        'total_ves',
        'cost_usd',
        'profit_usd',
        'exchange_rate',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sale_date' => 'date',
            'subtotal_usd' => 'decimal:4',
            'subtotal_ves' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_usd' => 'decimal:4',
            'tax_ves' => 'decimal:2',
            'total_usd' => 'decimal:4',
            'total_ves' => 'decimal:2',
            'cost_usd' => 'decimal:4',
            'profit_usd' => 'decimal:4',
            'exchange_rate' => 'decimal:4',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalePayment::class);
    }

    public function credit(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(SaleCredit::class);
    }
}
