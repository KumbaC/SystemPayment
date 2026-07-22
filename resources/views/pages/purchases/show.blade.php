@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="'Compra ' . $purchase->purchase_number" />

    <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div class="mb-6 grid grid-cols-2 gap-4 text-sm">
            <div><span class="text-gray-500">Proveedor:</span> <strong>{{ $purchase->supplier->name }}</strong></div>
            <div><span class="text-gray-500">Fecha:</span> {{ $purchase->purchase_date->format('d/m/Y') }}</div>
            <div><span class="text-gray-500">Registrado por:</span> {{ $purchase->user->name }}</div>
            <div><span class="text-gray-500">Tasa:</span> {{ number_format($purchase->exchange_rate, 4, ',', '.') }} Bs/USD</div>
        </div>

        <table class="mb-6 w-full text-sm">
            <thead><tr class="border-b text-left text-gray-500"><th class="py-2">Producto</th><th class="py-2 text-right">Cant.</th><th class="py-2 text-right">Costo USD</th><th class="py-2 text-right">Subtotal USD</th></tr></thead>
            <tbody>
                @foreach ($purchase->items as $item)
                    <tr class="border-b"><td class="py-2">{{ $item->product->name }}</td><td class="py-2 text-right">{{ $item->quantity }}</td><td class="py-2 text-right">${{ number_format($item->unit_cost_usd, 2) }}</td><td class="py-2 text-right">${{ number_format($item->subtotal_usd, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right text-sm space-y-1">
            <p>Subtotal: <strong>${{ number_format($purchase->subtotal_usd, 2) }}</strong></p>
            <p>IVA: <strong>${{ number_format($purchase->tax_usd, 2) }}</strong></p>
            <p class="text-lg">Total USD: <strong>${{ number_format($purchase->total_usd, 2) }}</strong></p>
            <p class="text-gray-500">Equivalente Bs: Bs. {{ number_format($purchase->total_usd * $purchase->exchange_rate, 2, ',', '.') }}</p>
        </div>
    </div>
@endsection
