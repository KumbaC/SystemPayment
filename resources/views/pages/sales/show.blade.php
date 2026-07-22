@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Venta ' . $sale->sale_number" />

    <div class="mb-4 flex gap-3">
        <a href="{{ route('sales.invoice', $sale) }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">Ver Factura</a>
        <a href="{{ route('sales.invoice.fiscal', $sale) }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">Imprimir Fiscal</a>
        <a href="{{ route('sales.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Volver</a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
            <div><span class="text-gray-500">Factura:</span> <strong>{{ $sale->invoice_number }}</strong></div>
            <div><span class="text-gray-500">Fecha:</span> {{ $sale->sale_date->format('d/m/Y') }}</div>
            <div><span class="text-gray-500">Cliente:</span> {{ $sale->customer?->name ?? 'Consumidor Final' }}</div>
            <div><span class="text-gray-500">Vendedor:</span> {{ $sale->user->name }}</div>
        </div>

        <table class="mb-6 w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">
                    <th class="py-2">Producto</th>
                    <th class="py-2 text-right">Cant.</th>
                    <th class="py-2 text-right">Precio USD</th>
                    <th class="py-2 text-right">IVA Bs</th>
                    <th class="py-2 text-right">Total Bs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-b">
                        <td class="py-2">{{ $item->product->name }}</td>
                        <td class="py-2 text-right">{{ $item->quantity }}</td>
                        <td class="py-2 text-right">${{ number_format($item->unit_price_usd, 2) }}</td>
                        <td class="py-2 text-right">Bs. {{ number_format($item->tax_ves, 2, ',', '.') }}</td>
                        <td class="py-2 text-right">Bs. {{ number_format($item->total_ves, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="text-sm space-y-1">
                <p class="font-medium mb-2">Pagos recibidos:</p>
                @foreach ($sale->payments as $payment)
                    <p>{{ $payment->paymentMethodLabel() }} — {{ $payment->currency }} {{ number_format($payment->amount, 2) }} = Bs. {{ number_format($payment->amount_ves, 2, ',', '.') }}</p>
                @endforeach
            </div>
            <div class="text-right text-sm space-y-1">
                <p>Subtotal USD: <strong>${{ number_format($sale->subtotal_usd, 2) }}</strong></p>
                <p>IVA Bs: <strong>Bs. {{ number_format($sale->tax_ves, 2, ',', '.') }}</strong></p>
                <p class="text-lg">Total Bs: <strong>Bs. {{ number_format($sale->total_ves, 2, ',', '.') }}</strong></p>
                <p class="text-green-600">Ganancia USD: <strong>${{ number_format($sale->profit_usd, 2) }}</strong></p>
            </div>
        </div>

        @if ($sale->credit)
            <div class="mt-8 rounded-xl border border-brand-200 bg-brand-50/40 p-5 dark:border-brand-800 dark:bg-brand-900/20">
                <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-base font-semibold text-gray-800 dark:text-white">Plan de Crédito</h3>
                    <span class="rounded-full px-2 py-1 text-xs font-medium {{ $sale->credit->status === 'paid' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : ($sale->credit->status === 'partial' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300') }}">
                        {{ strtoupper($sale->credit->status) }}
                    </span>
                </div>

                <div class="mb-4 grid grid-cols-1 gap-3 text-sm md:grid-cols-3">
                    <p>Total venta: <strong>${{ number_format($sale->credit->total_usd, 2) }}</strong></p>
                    <p>Pago inicial: <strong>${{ number_format($sale->credit->initial_payment_usd, 2) }}</strong></p>
                    <p>Financiado: <strong>${{ number_format($sale->credit->financed_usd, 2) }}</strong></p>
                </div>

                <div class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/40">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60">
                            <tr class="text-left text-gray-500">
                                <th class="px-3 py-2">Cuota</th>
                                <th class="px-3 py-2">Vence</th>
                                <th class="px-3 py-2 text-right">Monto USD</th>
                                <th class="px-3 py-2 text-right">Mora USD</th>
                                <th class="px-3 py-2 text-right">Pagado USD</th>
                                <th class="px-3 py-2 text-right">Pendiente USD</th>
                                <th class="px-3 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->credit->installments as $installment)
                                @php
                                    $pending = $installment->pendingUsd();
                                @endphp
                                <tr class="border-t border-gray-100 align-top dark:border-gray-800">
                                    <td class="px-3 py-2">Cuota {{ $installment->installment_number }}</td>
                                    <td class="px-3 py-2">{{ $installment->due_date->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2 text-right">${{ number_format($installment->amount_usd, 2) }}</td>
                                    <td class="px-3 py-2 text-right">${{ number_format($installment->late_fee_usd, 2) }}</td>
                                    <td class="px-3 py-2 text-right">${{ number_format($installment->paid_usd, 2) }}</td>
                                    <td class="px-3 py-2 text-right">${{ number_format($pending, 2) }}</td>
                                    <td class="px-3 py-2">
                                        @if ($installment->status !== 'paid')
                                            <form action="{{ route('sales.credits.installments.pay', [$sale, $installment]) }}" method="POST" class="mb-2 flex items-center gap-2">
                                                @csrf
                                                <input type="number" step="0.01" min="0.01" max="{{ number_format($pending, 2, '.', '') }}" name="amount_usd" placeholder="Pago USD" required class="h-9 w-24 rounded border border-gray-300 px-2 text-xs dark:border-gray-700 dark:bg-gray-900">
                                                <button class="h-9 rounded bg-brand-500 px-3 text-xs text-white">Pagar</button>
                                            </form>

                                            @if ($installment->due_date->isPast())
                                                <form action="{{ route('sales.credits.installments.whatsapp', [$sale, $installment]) }}" method="POST" target="_blank">
                                                    @csrf
                                                    <button class="rounded border border-green-500 px-3 py-1 text-xs text-green-700 dark:text-green-300">Enviar WhatsApp Mora</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-500">Botón WhatsApp disponible cuando la cuota esté vencida</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-green-600 dark:text-green-300">Cuota pagada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
