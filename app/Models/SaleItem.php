<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price_usd',
        'unit_price_ves',
        'unit_cost_usd',
        'subtotal_usd',
        'subtotal_ves',
        'tax_usd',
        'tax_ves',
        'total_ves',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'unit_price_usd' => 'decimal:4',
            'unit_price_ves' => 'decimal:2',
            'unit_cost_usd' => 'decimal:4',
            'subtotal_usd' => 'decimal:4',
            'subtotal_ves' => 'decimal:2',
            'tax_usd' => 'decimal:4',
            'tax_ves' => 'decimal:2',
            'total_ves' => 'decimal:2',
        ];
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
