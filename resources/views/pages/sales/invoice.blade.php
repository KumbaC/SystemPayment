@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Factura ' . $sale->invoice_number" />

    <div class="mb-4 flex flex-wrap gap-3 print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">Imprimir Factura</button>
        <a href="{{ route('sales.invoice.fiscal', $sale) }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white hover:bg-black">Formato Fiscal</a>
        @if (session('whatsapp_url'))
            <a href="{{ session('whatsapp_url') }}" target="_blank" rel="noopener" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                Enviar WhatsApp al cliente
            </a>
        @endif
        <a href="{{ route('sales.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Volver</a>
    </div>

    <div class="mx-auto max-w-3xl rounded-2xl border border-gray-200 bg-white p-8 dark:border-gray-800 dark:bg-white/[0.03] print:border-0 print:shadow-none">
        <div class="mb-8 flex justify-between border-b pb-6">
            <div>
                <h1 class="text-xl font-bold">{{ $company['name'] }}</h1>
                <p class="text-sm text-gray-600">RIF: {{ $company['rif'] }}</p>
                <p class="text-sm text-gray-600">{{ $company['address'] }}</p>
                <p class="text-sm text-gray-600">Tel: {{ $company['phone'] }}</p>
            </div>
            <div class="text-right">
                <h2 class="text-2xl font-bold text-brand-600">FACTURA</h2>
                <p class="text-sm"><strong>N°:</strong> {{ $sale->invoice_number }}</p>
                <p class="text-sm"><strong>Fecha:</strong> {{ $sale->sale_date->format('d/m/Y') }}</p>
                <p class="text-sm"><strong>Tasa BCV:</strong> {{ number_format($sale->exchange_rate, 4, ',', '.') }} Bs/USD</p>
            </div>
        </div>

        <div class="mb-6">
            <p class="text-sm font-medium">Cliente:</p>
            <p>{{ $sale->customer?->name ?? 'Consumidor Final' }}</p>
            @if ($sale->customer?->fullDocument())
                <p class="text-sm text-gray-600">{{ $sale->customer->fullDocument() }}</p>
            @endif
        </div>

        <table class="mb-6 w-full text-sm">
            <thead>
                <tr class="border-b bg-gray-50 text-left">
                    <th class="py-2 px-2">Producto</th>
                    <th class="py-2 px-2 text-right">Cant.</th>
                    <th class="py-2 px-2 text-right">Precio Bs</th>
                    <th class="py-2 px-2 text-right">IVA Bs</th>
                    <th class="py-2 px-2 text-right">Total Bs</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr class="border-b">
                        <td class="py-2 px-2">{{ $item->product->name }}</td>
                        <td class="py-2 px-2 text-right">{{ number_format($item->quantity, 2) }}</td>
                        <td class="py-2 px-2 text-right">Bs. {{ number_format($item->unit_price_ves, 2, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right">Bs. {{ number_format($item->tax_ves, 2, ',', '.') }}</td>
                        <td class="py-2 px-2 text-right">Bs. {{ number_format($item->total_ves, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mb-6 flex justify-end">
            <div class="w-64 space-y-1 text-sm">
                <div class="flex justify-between"><span>Subtotal Bs:</span><span>Bs. {{ number_format($sale->subtotal_ves, 2, ',', '.') }}</span></div>
                <div class="flex justify-between"><span>IVA total:</span><span>Bs. {{ number_format($sale->tax_ves, 2, ',', '.') }}</span></div>
                <div class="flex justify-between border-t pt-2 text-lg font-bold"><span>TOTAL Bs:</span><span>Bs. {{ number_format($sale->total_ves, 2, ',', '.') }}</span></div>
                <div class="flex justify-between text-xs text-gray-500"><span>Equivalente USD:</span><span>${{ number_format($sale->total_usd, 2) }}</span></div>
            </div>
        </div>

        <div class="border-t pt-4">
            <p class="mb-2 text-sm font-medium">Formas de Pago:</p>
            @foreach ($sale->payments as $payment)
                <p class="text-sm">
                    {{ $payment->paymentMethodLabel() }} ({{ $payment->currency }})
                    — {{ number_format($payment->amount, 2) }} → Bs. {{ number_format($payment->amount_ves, 2, ',', '.') }}
                    @if ($payment->reference) · Ref: {{ $payment->reference }} @endif
                </p>
            @endforeach
        </div>

        <p class="mt-8 text-center text-xs text-gray-500">Documento generado por sistema de gestión — Venezuela</p>
    </div>
@endsection

@push('scripts')
<style>@media print { .print\:hidden { display: none !important; } aside, header { display: none !important; } }</style>
@endpush
