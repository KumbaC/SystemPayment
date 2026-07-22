<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\PayableInvoice;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    public function __construct(
        protected ExchangeRateService $exchangeRate,
        protected InventoryService $inventory
    ) {}

    public function create(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            if (($data['purchase_settlement_type'] ?? 'immediate') === 'credit' && ! empty($data['purchase_due_date'])) {
                if (strtotime($data['purchase_due_date']) < strtotime($data['purchase_date'])) {
                    throw new \RuntimeException('La fecha de vencimiento no puede ser menor a la fecha de compra.');
                }
            }

            $rate = $this->exchangeRate->currentRate();
            $subtotal = 0;

            $purchase = Purchase::query()->create([
                'purchase_number' => $this->nextNumber(),
                'supplier_id' => $data['supplier_id'],
                'user_id' => Auth::id(),
                'purchase_date' => $data['purchase_date'],
                'exchange_rate' => $rate,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);
                $lineSubtotal = $item['quantity'] * $item['unit_cost_usd'];
                $subtotal += $lineSubtotal;

                PurchaseItem::query()->create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_cost_usd' => $item['unit_cost_usd'],
                    'subtotal_usd' => $lineSubtotal,
                ]);

                $productPayload = ['cost_usd' => $item['unit_cost_usd']];

                if (array_key_exists('sale_price_usd', $item) && $item['sale_price_usd'] !== null && $item['sale_price_usd'] !== '') {
                    $productPayload['price_usd'] = $item['sale_price_usd'];
                }

                $product->update($productPayload);
                $this->inventory->adjustStock(
                    $product,
                    $item['quantity'],
                    'purchase',
                    Purchase::class,
                    $purchase->id
                );
            }

            $taxRate = (float) Setting::get('tax_rate', 16);
            $tax = round($subtotal * ($taxRate / 100), 4);

            $purchase->update([
                'subtotal_usd' => $subtotal,
                'tax_usd' => $tax,
                'total_usd' => $subtotal + $tax,
            ]);

            if (($data['purchase_settlement_type'] ?? 'immediate') === 'credit') {
                $totalVes = $this->exchangeRate->usdToVes((float) $purchase->total_usd, $rate);

                PayableInvoice::query()->create([
                    'supplier_id' => $purchase->supplier_id,
                    'reference' => $purchase->purchase_number,
                    'issue_date' => $purchase->purchase_date,
                    'due_date' => $data['purchase_due_date'] ?? null,
                    'amount_ves' => round($totalVes, 2),
                    'paid_ves' => 0,
                    'status' => 'pending',
                    'notes' => 'Generado automaticamente desde la compra '.$purchase->purchase_number,
                    'created_by' => Auth::id(),
                ]);
            }

            return $purchase->load(['supplier', 'items.product', 'user']);
        });
    }

    public function cancel(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->loadMissing(['items.product']);

            if ($purchase->status === 'cancelled') {
                throw new \RuntimeException('La compra ya se encuentra anulada.');
            }

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                if ((float) $product->stock < (float) $item->quantity) {
                    throw new \RuntimeException("No se puede anular la compra porque el producto {$product->name} no tiene stock suficiente para revertir.");
                }
            }

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                $this->inventory->adjustStock(
                    $product,
                    -((float) $item->quantity),
                    'purchase_cancel',
                    Purchase::class,
                    $purchase->id,
                    'Reversion por anulacion de compra '.$purchase->purchase_number
                );
            }

            $purchase->status = 'cancelled';
            $purchase->save();
        });
    }

    public function activate(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            $purchase->loadMissing(['items.product']);

            if ($purchase->status === 'completed') {
                throw new \RuntimeException('La compra ya está activa.');
            }

            foreach ($purchase->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                $this->inventory->adjustStock(
                    $product,
                    (float) $item->quantity,
                    'purchase_reactivate',
                    Purchase::class,
                    $purchase->id,
                    'Ajuste por reactivacion de compra '.$purchase->purchase_number
                );
            }

            $purchase->status = 'completed';
            $purchase->save();
        });
    }

    protected function nextNumber(): string
    {
        $last = Purchase::query()->orderByDesc('id')->value('purchase_number');
        $next = $last ? ((int) str_replace('C-', '', $last)) + 1 : 1;

        return 'C-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
