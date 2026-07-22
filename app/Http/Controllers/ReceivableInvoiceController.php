<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\ReceivableInvoice;
use App\Models\ReceivablePayment;
use Illuminate\Http\Request;

class ReceivableInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $invoices = ReceivableInvoice::query()
            ->with(['customer'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")
                            ->orWhere('document_number', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $totalAmount = (float) ReceivableInvoice::query()->sum('amount_ves');
        $totalPaid = (float) ReceivableInvoice::query()->sum('paid_ves');

        return view('pages.accounts-receivable.index', [
            'title' => 'Cuentas por Cobrar',
            'invoices' => $invoices,
            'customers' => Customer::query()->where('active', true)->orderBy('name', 'asc')->get(),
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'totalPending' => max(0, $totalAmount - $totalPaid),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'reference' => ['required', 'string', 'max:100', 'unique:receivable_invoices,reference'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'amount_ves' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        ReceivableInvoice::query()->create([
            ...$data,
            'paid_ves' => 0,
            'status' => 'pending',
            'created_by' => $request->user()?->id,
        ]);

        return back()->with('success', 'Cuenta por cobrar registrada.');
    }

    public function addPayment(Request $request, ReceivableInvoice $receivableInvoice)
    {
        $data = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount_ves' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string', 'max:50'],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $pending = $receivableInvoice->pendingAmount();
        if ((float) $data['amount_ves'] > $pending) {
            return back()->with('error', 'El abono excede el saldo pendiente.');
        }

        ReceivablePayment::query()->create([
            'receivable_invoice_id' => $receivableInvoice->id,
            ...$data,
            'created_by' => $request->user()?->id,
        ]);

        $receivableInvoice->paid_ves = (float) $receivableInvoice->paid_ves + (float) $data['amount_ves'];
        $receivableInvoice->status = $receivableInvoice->resolveStatus();
        $receivableInvoice->save();

        return back()->with('success', 'Abono registrado en cuenta por cobrar.');
    }
}
