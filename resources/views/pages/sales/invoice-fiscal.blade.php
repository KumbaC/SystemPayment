@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Factura Fiscal ' . $sale->invoice_number" />

    <div class="mb-4 flex flex-wrap gap-3 print:hidden">
        <button onclick="window.print()" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">Imprimir Formato Fiscal</button>
        <a href="{{ route('sales.invoice', $sale) }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm">Volver a factura visual</a>
    </div>

    <div class="fiscal-ticket mx-auto rounded-lg border border-gray-300 bg-white p-4 text-black print:border-0">
        <div class="text-center">
            <p class="text-[20px] font-bold leading-tight">SENIAT</p>
            <p class="text-[20px] font-bold leading-tight">{{ strtoupper($company['name']) }}</p>
            <p class="text-[16px] font-bold">RIF: {{ $company['rif'] ?: 'N/A' }}</p>
            <p class="text-[14px]">{{ $company['address'] ?: '-' }}</p>
        </div>

        <div class="my-3 text-center">
            <span class="inline-block rounded-full border-2 border-black px-7 py-1 text-[26px] font-bold leading-none">FACTURA</span>
        </div>

        <div class="mb-2 flex items-center justify-between text-[13px]">
            <span>{{ $sale->sale_date->format('d/m/Y') }}</span>
            <span>{{ $sale->created_at?->format('h:i a') }}</span>
        </div>
        <div class="mb-1 flex items-center justify-between text-[13px]">
            <span>Factura N°</span>
            <span class="font-semibold">{{ $sale->invoice_number }}</span>
        </div>
        <div class="mb-2 text-[13px]">
            Cliente: {{ $sale->customer?->name ?? 'Consumidor Final' }}
        </div>

        <table class="w-full text-[13px]">
            <thead>
                <tr class="border-b border-black">
                    <th class="py-1 text-left font-semibold">Descripcion</th>
                    <th class="py-1 text-center font-semibold">Cant</th>
                    <th class="py-1 text-right font-semibold">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="py-0.5 pr-1">{{ $item->product->name }}</td>
                        <td class="py-0.5 text-center">{{ number_format($item->quantity, 0) }}</td>
                        <td class="py-0.5 text-right">{{ number_format($item->subtotal_ves + $item->tax_ves, 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-3 space-y-1 border-t border-black pt-2 text-[13px]">
            <div class="flex items-center justify-between">
                <span>Base Imp. Alic. General</span>
                <span>{{ number_format($sale->subtotal_ves, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span>IVA {{ number_format($sale->tax_rate, 0) }}%</span>
                <span>{{ number_format($sale->tax_ves, 2, ',', '.') }}</span>
            </div>
            <div class="flex items-center justify-between text-[18px] font-bold">
                <span>TOTAL</span>
                <span>{{ number_format($sale->total_ves, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="fiscal-seal-space mt-6 border-t border-dashed border-gray-500 pt-2 text-center text-[12px] text-gray-700">
            Espacio reservado para sello de maquina fiscal
        </div>
    </div>
@endsection

@push('scripts')
<style>
.fiscal-ticket {
    width: 80mm;
    max-width: 80mm;
    font-family: 'Courier New', Courier, monospace;
}

.fiscal-seal-space {
    min-height: 80px;
}

@media print {
    .print\:hidden,
    aside,
    header {
        display: none !important;
    }

    main {
        margin: 0 !important;
        padding: 0 !important;
    }

    .fiscal-ticket {
        width: 80mm;
        max-width: 80mm;
        margin: 0 auto;
        padding-bottom: 12mm;
    }

    .fiscal-seal-space {
        min-height: 18mm;
    }
}
</style>
@endpush
