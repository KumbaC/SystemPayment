<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class InventoryService
{
    public function adjustStock(
        Product $product,
        float $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): InventoryMovement {
        $stockBefore = (float) $product->stock;
        $stockAfter = $stockBefore + $quantity;

        $product->update(['stock' => $stockAfter]);

        return InventoryMovement::query()->create([
            'product_id' => $product->id,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => Auth::id(),
            'notes' => $notes,
        ]);
    }
}
