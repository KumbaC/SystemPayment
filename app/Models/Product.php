<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'description',
        'cost_usd',
        'price_usd',
        'stock',
        'min_stock',
        'unit',
        'active',
        'has_vat',
    ];

    protected function casts(): array
    {
        return [
            'cost_usd' => 'decimal:4',
            'price_usd' => 'decimal:4',
            'stock' => 'decimal:4',
            'min_stock' => 'decimal:4',
            'active' => 'boolean',
            'has_vat' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->min_stock;
    }
}
