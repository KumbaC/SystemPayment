<?php

namespace App\Http\Controllers;

use App\Enums\Currency;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleCreditInstallment;
use App\Models\Setting;
use App\Services\ExchangeRateService;
use App\Services\SaleService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService,
        protected ExchangeRateService $exchangeRate,
        protected WhatsAppService $whatsapp
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');

        $sales = Sale::query()
            ->with(['customer', 'user', 'credit.installments'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('sale_number', 'like', "%{$search}%")
                        ->orWhere('invoice_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                            ->orWhere('document_number', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.sales.index', [
            'title' => 'Ventas',
            'sales' => $sales,
        ]);
    }

    public function create()
    {
        $creditEnabled = filter_var(Setting::get('credit_system_enabled', '1'), FILTER_VALIDATE_BOOL);

        $scannerEnabled = filter_var(Setting::get('scanner_enabled', '1'), FILTER_VALIDATE_BOOL);
        $scannerScope = Setting::get('scanner_scope', 'both');
        $scannerApplies = in_array($scannerScope, ['both', 'sales'], true);

        $products = Product::query()
            ->where('active', true)
            ->where('stock', '>', 0)
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'sku', 'price_usd', 'stock'])
            ->toArray();

        return view('pages.sales.form', [
            'title' => 'Nueva Venta',
            'customers' => Customer::query()
                ->where('active', true)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'document_type', 'document_number'])
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'document' => $c->fullDocument(),
                        'document_number' => $c->document_number ?? '',
                    ];
                })
                ->toArray(),
            'products' => $products,
            'paymentMethods' => PaymentMethod::options(),
            'currencies' => Currency::options(),
            'exchangeRate' => $this->exchangeRate->currentRate(),
            'taxRate' => Setting::get('tax_rate', 16),
            'creditConfig' => [
                'enabled' => $creditEnabled,
                'lateFeeUsd' => (float) Setting::get('credit_late_fee_usd', '1'),
                'usePercentage' => filter_var(Setting::get('credit_initial_by_percentage', '0'), FILTER_VALIDATE_BOOL),
                'initialPercentage' => (float) Setting::get('credit_initial_percentage', '10'),
            ],
            'scanner' => [
                'enabled' => $scannerEnabled && $scannerApplies,
                'minLength' => (int) Setting::get('scanner_min_length', '4'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'quick_customer' => ['nullable', 'array'],
            'quick_customer.name' => ['nullable', 'string', 'max:255'],
            'quick_customer.document_type' => ['nullable', 'in:V,E,J,G,P'],
            'quick_customer.document_number' => ['nullable', 'string', 'max:20'],
            'quick_customer.phone' => ['nullable', 'string', 'max:30'],
            'sale_date' => ['required', 'date'],
            'sale_settlement_type' => ['required', 'in:immediate,credit'],
            'sale_due_date' => ['nullable', 'date', 'required_if:sale_settlement_type,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_price_usd' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.payment_method' => ['required_with:payments', 'string'],
            'payments.*.currency' => ['required_with:payments', 'string'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.exchange_rate' => ['nullable', 'numeric', 'min:0.0001'],
            'payments.*.reference' => ['nullable', 'string', 'max:100'],
            'credit_initial_payment_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['sale_settlement_type'] === 'immediate' && empty($data['payments'])) {
            return back()->withInput()->with('error', 'Para pago inmediato debes registrar al menos un pago.');
        }

        if ($data['sale_settlement_type'] === 'credit') {
            $hasCustomer = ! empty($data['customer_id']) || ! empty($data['quick_customer']['name']);
            if (! $hasCustomer) {
                return back()->withInput()->with('error', 'Para venta a crédito debes seleccionar o registrar un cliente.');
            }
        }

        try {
            $sale = $this->saleService->create($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $message = $this->whatsapp->saleThankYouMessage($sale);
        $whatsappUrl = $this->whatsapp->buildLink($sale->customer?->phone, $message);

        // Try to send via CallMeBot (if configured) — best effort, don't fail the request
        try {
            $status = $this->whatsapp->sendViaCallMeBot($sale->customer?->phone, $message);
            // optionally log or flash the status
            if ($status && is_int($status)) {
                // success
            }
        } catch (\Throwable $e) {
            // ignore send errors
        }

        // Notify admin if license is expiring soon
        try {
            $this->whatsapp->notifyAdminLicenseExpiryIfNeeded();
        } catch (\Throwable $e) {
            // ignore
        }

        return redirect()
            ->route('sales.invoice', $sale)
            ->with('success', 'Venta registrada y factura generada.')
            ->with('whatsapp_url', $whatsappUrl);
    }

    public function show(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments', 'user', 'credit.installments']);

        return view('pages.sales.show', [
            'title' => 'Venta '.$sale->sale_number,
            'sale' => $sale,
            'today' => now()->toDateString(),
        ]);
    }

    public function payInstallment(Request $request, Sale $sale, SaleCreditInstallment $installment)
    {
        if (! $sale->credit || $installment->sale_credit_id !== $sale->credit->id) {
            return back()->with('error', 'La cuota no pertenece a la venta seleccionada.');
        }

        $data = $request->validate([
            'amount_usd' => ['required', 'numeric', 'min:0.01'],
        ]);

        if ($installment->status === 'paid') {
            return back()->with('error', 'La cuota ya esta pagada.');
        }

        $lateFeeUsd = (float) Setting::get('credit_late_fee_usd', '1');
        if (! $installment->late_fee_applied && $installment->due_date && $installment->due_date->isPast()) {
            $installment->late_fee_usd = $lateFeeUsd;
            $installment->late_fee_applied = true;
        }

        $maxPending = $installment->pendingUsd();
        $paying = min((float) $data['amount_usd'], $maxPending);
        $installment->paid_usd = (float) $installment->paid_usd + $paying;
        $installment->status = $installment->resolveStatus();
        if ($installment->status === 'paid') {
            $installment->paid_at = now();
            $installment->whatsapp_sent_at = null;
        }
        $installment->save();

        $credit = $sale->credit->load('installments');
        $credit->status = $credit->resolveStatus();
        $credit->save();

        return back()->with('success', 'Pago de cuota registrado.');
    }

    public function sendInstallmentReminder(Sale $sale, SaleCreditInstallment $installment)
    {
        if (! $sale->credit || $installment->sale_credit_id !== $sale->credit->id) {
            return back()->with('error', 'La cuota no pertenece a la venta seleccionada.');
        }

        if ($installment->status === 'paid') {
            return back()->with('error', 'La cuota ya fue pagada.');
        }

        if (! $sale->customer?->phone) {
            return back()->with('error', 'El cliente no tiene telefono para WhatsApp.');
        }

        if (! $installment->due_date || ! $installment->due_date->isPast()) {
            return back()->with('error', 'El recordatorio por mora solo aplica para cuotas vencidas.');
        }

        $message = 'Buen dia, le notificamos que tiene cuotas en mora con nosotros por favor ponerse al dia, gracias';
        $whatsappUrl = $this->whatsapp->buildLink($sale->customer->phone, $message);

        if (! $whatsappUrl) {
            return back()->with('error', 'No se pudo generar el enlace de WhatsApp para el cliente.');
        }

        $installment->whatsapp_sent_at = now();
        $installment->save();

        return redirect()->away($whatsappUrl);
    }

    public function cancel(Sale $sale)
    {
        try {
            $this->saleService->cancel($sale);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Venta anulada correctamente.');
    }

    public function activate(Sale $sale)
    {
        try {
            $this->saleService->activate($sale);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Venta activada correctamente.');
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments', 'user']);

        return view('pages.sales.invoice', [
            'title' => 'Factura '.$sale->invoice_number,
            'sale' => $sale,
            'company' => [
                'name' => Setting::get('company_name', 'Mi Negocio'),
                'rif' => Setting::get('company_rif', ''),
                'address' => Setting::get('company_address', ''),
                'phone' => Setting::get('company_phone', ''),
            ],
        ]);
    }

    public function fiscalInvoice(Sale $sale)
    {
        $sale->load(['customer', 'items.product', 'payments', 'user']);

        return view('pages.sales.invoice-fiscal', [
            'title' => 'Factura Fiscal '.$sale->invoice_number,
            'sale' => $sale,
            'company' => [
                'name' => Setting::get('company_name', 'Mi Negocio'),
                'rif' => Setting::get('company_rif', ''),
                'address' => Setting::get('company_address', ''),
            ],
        ]);
    }
}
