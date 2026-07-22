<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Supplier;
use App\Services\ExchangeRateService;
use App\Services\PurchaseService;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(
        protected PurchaseService $purchaseService,
        protected ExchangeRateService $exchangeRate
    ) {}

    public function index(Request $request)
    {
        $search = $request->get('search');

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('purchase_number', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%")
                            ->orWhere('rif', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.purchases.index', [
            'title' => 'Compras',
            'purchases' => $purchases,
        ]);
    }

    public function create()
    {
        $scannerEnabled = filter_var(Setting::get('scanner_enabled', '1'), FILTER_VALIDATE_BOOL);
        $scannerScope = Setting::get('scanner_scope', 'both');
        $scannerApplies = in_array($scannerScope, ['both', 'purchases'], true);

        return view('pages.purchases.form', [
            'title' => 'Nueva Compra',
            'suppliers' => Supplier::query()
                ->where('active', true)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'rif'])
                ->toArray(),
            'products' => Product::query()
                ->where('active', true)
                ->orderBy('name', 'asc')
                ->get(['id', 'name', 'sku', 'cost_usd', 'price_usd'])
                ->toArray(),
            'exchangeRate' => $this->exchangeRate->currentRate(),
            'scanner' => [
                'enabled' => $scannerEnabled && $scannerApplies,
                'minLength' => (int) Setting::get('scanner_min_length', '4'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'purchase_settlement_type' => ['required', 'in:immediate,credit'],
            'purchase_due_date' => ['nullable', 'date', 'required_if:purchase_settlement_type,credit'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_cost_usd' => ['required', 'numeric', 'min:0'],
            'items.*.sale_price_usd' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($data['purchase_settlement_type'] === 'credit' && ! empty($data['purchase_due_date'])) {
            if (strtotime($data['purchase_due_date']) < strtotime($data['purchase_date'])) {
                return back()->withInput()->with('error', 'La fecha de vencimiento no puede ser menor a la fecha de compra.');
            }
        }

        try {
            $purchase = $this->purchaseService->create($data);
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Compra registrada correctamente.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product', 'user']);

        return view('pages.purchases.show', [
            'title' => 'Compra '.$purchase->purchase_number,
            'purchase' => $purchase,
        ]);
    }

    public function cancel(Purchase $purchase)
    {
        try {
            $this->purchaseService->cancel($purchase);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Compra anulada correctamente.');
    }

    public function activate(Purchase $purchase)
    {
        try {
            $this->purchaseService->activate($purchase);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Compra activada correctamente.');
    }
}
