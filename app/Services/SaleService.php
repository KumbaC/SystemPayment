<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleCredit;
use App\Models\SaleCreditInstallment;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Models\ReceivableInvoice;
use App\Models\Setting;
use App\Services\ActivityLogService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        protected ExchangeRateService $exchangeRate,
        protected InventoryService $inventory,
        protected ActivityLogService $activityLog
    ) {}

    public function create(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $rate = $this->exchangeRate->currentRate();
            $taxRate = (float) Setting::get('tax_rate', 16);
            $subtotalUsd = 0;
            $costUsd = 0;
            $taxUsd = 0;

            $customerId = $data['customer_id'] ?? null;

            if (! empty($data['quick_customer']['name'])) {
                $customer = Customer::query()->create([
                    'name' => $data['quick_customer']['name'],
                    'document_type' => $data['quick_customer']['document_type'] ?? 'V',
                    'document_number' => $data['quick_customer']['document_number'] ?? null,
                    'phone' => $data['quick_customer']['phone'] ?? null,
                    'active' => true,
                ]);
                $customerId = $customer->id;
            }

            $sale = Sale::query()->create([
                'sale_number' => $this->nextSaleNumber(),
                'invoice_number' => $this->nextInvoiceNumber(),
                'customer_id' => $customerId,
                'user_id' => Auth::id(),
                'sale_date' => $data['sale_date'],
                'tax_rate' => $taxRate,
                'exchange_rate' => $rate,
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::query()->findOrFail($item['product_id']);

                if ((float) $product->stock <= 0) {
                    throw new \RuntimeException("No hay {$product->name} en el stock.");
                }

                if ($product->stock < $item['quantity']) {
                    throw new \RuntimeException("Stock insuficiente para {$product->name}");
                }

                $unitPriceUsd = $product->price_usd;
                $lineSubtotalUsd = $item['quantity'] * $unitPriceUsd;
                $lineSubtotalVes = $this->exchangeRate->usdToVes($lineSubtotalUsd, $rate);
                $lineCostUsd = $item['quantity'] * $product->cost_usd;
                $lineTaxUsd = $product->has_vat ? round($lineSubtotalUsd * ($taxRate / 100), 4) : 0;
                $lineTaxVes = $this->exchangeRate->usdToVes($lineTaxUsd, $rate);
                $lineTotalVes = $lineSubtotalVes + $lineTaxVes;

                $subtotalUsd += $lineSubtotalUsd;
                $costUsd += $lineCostUsd;
                $taxUsd += $lineTaxUsd;

                SaleItem::query()->create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price_usd' => $unitPriceUsd,
                    'unit_price_ves' => $this->exchangeRate->usdToVes($unitPriceUsd, $rate),
                    'unit_cost_usd' => $product->cost_usd,
                    'subtotal_usd' => $lineSubtotalUsd,
                    'subtotal_ves' => $lineSubtotalVes,
                    'tax_usd' => $lineTaxUsd,
                    'tax_ves' => $lineTaxVes,
                    'total_ves' => $lineTotalVes,
                ]);

                $this->inventory->adjustStock(
                    $product,
                    -$item['quantity'],
                    'sale',
                    Sale::class,
                    $sale->id
                );
            }

            $subtotalVes = $this->exchangeRate->usdToVes($subtotalUsd, $rate);
            $taxVes = $this->exchangeRate->usdToVes($taxUsd, $rate);
            $totalUsd = $subtotalUsd + $taxUsd;
            $totalVes = $subtotalVes + $taxVes;
            $profitUsd = $subtotalUsd - $costUsd;

            $sale->update([
                'subtotal_usd' => $subtotalUsd,
                'subtotal_ves' => $subtotalVes,
                'tax_usd' => $taxUsd,
                'tax_ves' => $taxVes,
                'total_usd' => $totalUsd,
                'total_ves' => $totalVes,
                'cost_usd' => $costUsd,
                'profit_usd' => $profitUsd,
            ]);

            $paymentsTotalVes = 0;
            $settlementType = $data['sale_settlement_type'] ?? 'immediate';
            $creditEnabled = filter_var(Setting::get('credit_system_enabled', '1'), FILTER_VALIDATE_BOOL);

            if ($settlementType === 'credit' && ! $creditEnabled) {
                throw new \RuntimeException('El sistema de creditos esta desactivado en configuracion.');
            }

            if ($settlementType === 'credit') {
                $usePercentage = filter_var(Setting::get('credit_initial_by_percentage', '0'), FILTER_VALIDATE_BOOL);
                $initialPaymentUsd = 0;

                if ($usePercentage) {
                    $initialPercentage = max(0, min(100, (float) Setting::get('credit_initial_percentage', '10')));
                    $initialPaymentUsd = round($totalUsd * ($initialPercentage / 100), 4);
                } else {
                    $initialPaymentUsd = round(max(0, (float) ($data['credit_initial_payment_usd'] ?? 0)), 4);
                }

                if ($initialPaymentUsd > $totalUsd) {
                    throw new \RuntimeException('El pago inicial no puede ser mayor al total de la venta.');
                }

                if ($initialPaymentUsd > 0) {
                    $initialPaymentVes = $this->exchangeRate->usdToVes($initialPaymentUsd, $rate);

                    SalePayment::query()->create([
                        'sale_id' => $sale->id,
                        'payment_method' => 'credito_inicial',
                        'currency' => 'USD',
                        'amount' => round($initialPaymentUsd, 4),
                        'amount_ves' => $initialPaymentVes,
                        'exchange_rate' => $rate,
                        'reference' => 'Pago inicial de credito',
                    ]);

                    $paymentsTotalVes += $initialPaymentVes;
                }

                $financedUsd = max(0, round($totalUsd - $initialPaymentUsd, 4));
                if ($financedUsd > 0) {
                    $lateFeeUsd = (float) Setting::get('credit_late_fee_usd', '1');

                    $credit = SaleCredit::query()->create([
                        'sale_id' => $sale->id,
                        'customer_id' => $customerId,
                        'total_usd' => round($totalUsd, 4),
                        'initial_payment_usd' => round($initialPaymentUsd, 4),
                        'financed_usd' => $financedUsd,
                        'installments_count' => 2,
                        'installment_gap_days' => 7,
                        'late_fee_usd' => $lateFeeUsd,
                        'status' => 'pending',
                    ]);

                    $baseInstallment = round($totalUsd / 2, 4);
                    $first = max(0, round($baseInstallment - $initialPaymentUsd, 4));
                    $second = max(0, round($financedUsd - $first, 4));
                    $firstDue = ! empty($data['sale_due_date']) ? Carbon::parse($data['sale_due_date']) : Carbon::parse($sale->sale_date)->addDays(7);
                    $secondDue = $firstDue->copy()->addDays(7);

                    SaleCreditInstallment::query()->create([
                        'sale_credit_id' => $credit->id,
                        'installment_number' => 1,
                        'due_date' => $firstDue->toDateString(),
                        'amount_usd' => $first,
                        'paid_usd' => 0,
                        'status' => 'pending',
                    ]);

                    ReceivableInvoice::query()->create([
                        'customer_id' => $customerId,
                        'reference' => 'CXC-'.$sale->invoice_number.'-CUOTA-1',
                        'issue_date' => $sale->sale_date,
                        'due_date' => $firstDue->toDateString(),
                        'amount_ves' => $this->exchangeRate->usdToVes($first, $rate),
                        'paid_ves' => 0,
                        'status' => 'pending',
                        'notes' => 'Generada automaticamente desde venta '.$sale->invoice_number.' (cuota 1 de credito).',
                        'created_by' => Auth::id(),
                    ]);

                    SaleCreditInstallment::query()->create([
                        'sale_credit_id' => $credit->id,
                        'installment_number' => 2,
                        'due_date' => $secondDue->toDateString(),
                        'amount_usd' => $second,
                        'paid_usd' => 0,
                        'status' => 'pending',
                    ]);

                    ReceivableInvoice::query()->create([
                        'customer_id' => $customerId,
                        'reference' => 'CXC-'.$sale->invoice_number.'-CUOTA-2',
                        'issue_date' => $sale->sale_date,
                        'due_date' => $secondDue->toDateString(),
                        'amount_ves' => $this->exchangeRate->usdToVes($second, $rate),
                        'paid_ves' => 0,
                        'status' => 'pending',
                        'notes' => 'Generada automaticamente desde venta '.$sale->invoice_number.' (cuota 2 de credito).',
                        'created_by' => Auth::id(),
                    ]);
                }
            } else {
                if (empty($data['payments'])) {
                    throw new \RuntimeException('Para pago inmediato debes registrar al menos un pago.');
                }

                foreach (($data['payments'] ?? []) as $payment) {
                    if ((float) ($payment['amount'] ?? 0) <= 0) {
                        continue;
                    }

                    $paymentRate = $payment['exchange_rate'] ?? $rate;
                    $amountVes = $this->exchangeRate->convertToVes(
                        (float) $payment['amount'],
                        $payment['currency'],
                        $paymentRate
                    );

                    SalePayment::query()->create([
                        'sale_id' => $sale->id,
                        'payment_method' => $payment['payment_method'],
                        'currency' => $payment['currency'],
                        'amount' => $payment['amount'],
                        'amount_ves' => $amountVes,
                        'exchange_rate' => $paymentRate,
                        'reference' => $payment['reference'] ?? null,
                    ]);

                    $paymentsTotalVes += $amountVes;
                }

                if (round($paymentsTotalVes, 2) < round($totalVes, 2)) {
                    throw new \RuntimeException('El monto pagado es menor al total de la factura.');
                }
            }

            $sale = $sale->load(['customer', 'items.product', 'payments', 'user']);

            $this->activityLog->log(
                'sale_created',
                "Registró venta {$sale->invoice_number} por Bs. ".number_format($sale->total_ves, 2, ',', '.'),
                $sale
            );

            return $sale;
        });
    }

    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->loadMissing(['items.product']);

            if ($sale->status === 'cancelled') {
                throw new \RuntimeException('La venta ya se encuentra anulada.');
            }

            foreach ($sale->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                $this->inventory->adjustStock(
                    $product,
                    (float) $item->quantity,
                    'sale_cancel',
                    Sale::class,
                    $sale->id,
                    'Reintegro por anulacion de venta '.$sale->invoice_number
                );
            }

            $sale->status = 'cancelled';
            $sale->save();

            $this->activityLog->log(
                'sale_cancelled',
                "Anuló venta {$sale->invoice_number}",
                $sale
            );
        });
    }

    public function activate(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $sale->loadMissing(['items.product']);

            if ($sale->status === 'completed') {
                throw new \RuntimeException('La venta ya está activa.');
            }

            foreach ($sale->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                if ((float) $product->stock < (float) $item->quantity) {
                    throw new \RuntimeException("No hay stock suficiente para reactivar la venta (producto: {$product->name}).");
                }
            }

            foreach ($sale->items as $item) {
                $product = $item->product;

                if (! $product) {
                    continue;
                }

                $this->inventory->adjustStock(
                    $product,
                    -((float) $item->quantity),
                    'sale_reactivate',
                    Sale::class,
                    $sale->id,
                    'Descuento por reactivacion de venta '.$sale->invoice_number
                );
            }

            $sale->status = 'completed';
            $sale->save();

            $this->activityLog->log(
                'sale_reactivated',
                "Reactivó venta {$sale->invoice_number}",
                $sale
            );
        });
    }

    protected function nextSaleNumber(): string
    {
        $last = Sale::query()->orderByDesc('id')->value('sale_number');
        $next = $last ? ((int) str_replace('V-', '', $last)) + 1 : 1;

        return 'V-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    protected function nextInvoiceNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'F');
        $last = Sale::query()->orderByDesc('id')->value('invoice_number');
        $next = $last ? ((int) preg_replace('/\D/', '', $last)) + 1 : 1;

        return $prefix.'-'.str_pad((string) $next, 8, '0', STR_PAD_LEFT);
    }
}
