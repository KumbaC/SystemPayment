<?php

namespace App\Http\Controllers;

use App\Models\PayableInvoice;
use App\Models\PayablePayment;
use App\Models\Supplier;
use Illuminate\Http\Request;

class PayableInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $invoices = PayableInvoice::query()
            ->with(['supplier'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%")
                            ->orWhere('rif', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $totalAmount = (float) PayableInvoice::query()->sum('amount_ves');
        $totalPaid = (float) PayableInvoice::query()->sum('paid_ves');

        return view('pages.accounts-payable.index', [
            'title' => 'Facturas por Pagar',
            'invoices' => $invoices,
            'suppliers' => Supplier::query()->where('active', true)->orderBy('name', 'asc')->get(),
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalPending' => max(0, $totalAmount - $totalPaid),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'reference' => ['required', 'string', 'max:100', 'unique:payable_invoices,reference'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'amount_ves' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        PayableInvoice::query()->create([
            ...$data,
            'paid_ves' => 0,
            'status' => 'pending',
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Factura por pagar registrada.');
    }

    public function addPayment(Request $request, PayableInvoice $payableInvoice)
    {
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount_ves' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $pending = $payableInvoice->pendingAmount();
        if ((float) $data['amount_ves'] > $pending) {
            return back()->with('error', 'El pago excede el saldo pendiente.');
        }

        PayablePayment::query()->create([
            'payable_invoice_id' => $payableInvoice->id,
            ...$data,
            'created_by' => $request->user()?->id,
        ]);

        $payableInvoice->paid_ves = (float) $payableInvoice->paid_ves + (float) $data['amount_ves'];
        $payableInvoice->status = $payableInvoice->resolveStatus();
        $payableInvoice->save();

        return back()->with('success', 'Pago registrado en factura por pagar.');
    }
}
