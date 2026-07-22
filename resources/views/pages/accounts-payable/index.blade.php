@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Facturas por Pagar" />
    <x-common.flash-messages />

    <div class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs uppercase tracking-wide text-gray-500">Total por pagar</p>
            <p class="mt-1 text-xl font-semibold text-gray-800 dark:text-white">Bs. {{ number_format($totalAmount, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20">
            <p class="text-xs uppercase tracking-wide text-green-700 dark:text-green-300">Total pagado</p>
            <p class="mt-1 text-xl font-semibold text-green-700 dark:text-green-300">Bs. {{ number_format($totalPaid, 2, ',', '.') }}</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
            <p class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Saldo pendiente</p>
            <p class="mt-1 text-xl font-semibold text-amber-700 dark:text-amber-300">Bs. {{ number_format($totalPending, 2, ',', '.') }}</p>
        </div>
    </div>

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por referencia o proveedor..."
                class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
        </form>
    </div>

    <form action="{{ route('accounts-payable.store') }}" method="POST" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-gray-200 bg-white p-5 md:grid-cols-7 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        <input type="text" name="reference" placeholder="Referencia factura *" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <select name="supplier_id" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="">Proveedor (opcional)</option>
            @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
            @endforeach
        </select>
        <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <input type="date" name="due_date" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <input type="number" step="0.01" min="0.01" name="amount_ves" placeholder="Monto Bs *" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <input type="text" name="notes" placeholder="Notas" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <button type="submit" class="rounded-lg bg-brand-500 px-4 text-sm text-white">Registrar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">Referencia</th>
                    <th class="px-4 py-3">Proveedor</th>
                    <th class="px-4 py-3">Emisión</th>
                    <th class="px-4 py-3">Vencimiento</th>
                    <th class="px-4 py-3 text-right">Monto</th>
                    <th class="px-4 py-3 text-right">Pagado</th>
                    <th class="px-4 py-3 text-right">Pendiente</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Registrar pago</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    @php
                        $pending = $invoice->pendingAmount();
                        $statusColor = $invoice->status === 'paid' ? 'text-green-600 bg-green-50 dark:text-green-300 dark:bg-green-900/20' : ($invoice->status === 'partial' ? 'text-amber-600 bg-amber-50 dark:text-amber-300 dark:bg-amber-900/20' : 'text-red-600 bg-red-50 dark:text-red-300 dark:bg-red-900/20');
                    @endphp
                    <tr class="border-t border-gray-100 align-top dark:border-gray-800">
                        <td class="px-4 py-3 font-medium">{{ $invoice->reference }}</td>
                        <td class="px-4 py-3">{{ $invoice->supplier?->name ?? 'Sin proveedor' }}</td>
                        <td class="px-4 py-3">{{ $invoice->issue_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">Bs. {{ number_format($invoice->amount_ves, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Bs. {{ number_format($invoice->paid_ves, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Bs. {{ number_format($pending, 2, ',', '.') }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs {{ $statusColor }}">{{ strtoupper($invoice->status) }}</span></td>
                        <td class="px-4 py-3">
                            @if ($pending > 0)
                                <form action="{{ route('accounts-payable.payments.store', $invoice) }}" method="POST" class="grid grid-cols-1 gap-2 md:grid-cols-4">
                                    @csrf
                                    <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="h-9 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                    <input type="number" step="0.01" min="0.01" max="{{ number_format($pending, 2, '.', '') }}" name="amount_ves" placeholder="Monto" required class="h-9 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                    <select name="payment_method" class="h-9 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                        <option value="transferencia">Transferencia</option>
                                        <option value="pago_movil">Pago Móvil</option>
                                        <option value="efectivo_bs">Efectivo Bs</option>
                                    </select>
                                    <button class="h-9 rounded bg-brand-500 px-3 text-xs text-white">Pagar</button>
                                </form>
                            @else
                                <span class="text-xs text-green-600 dark:text-green-300">Completamente pagada</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Sin facturas por pagar registradas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invoices->links() }}</div>
@endsection
