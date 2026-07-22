@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Compras" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar compra o proveedor..."
                class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
        </form>
        @can('purchases.create')
            <a href="{{ route('purchases.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">+ Nueva Compra</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">N° Compra</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Proveedor</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Total USD</th>
                    <th class="px-4 py-3 text-right">Tasa</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($purchases as $purchase)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $purchase->purchase_number }}</td>
                        <td class="px-4 py-3">{{ $purchase->purchase_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $purchase->supplier->name }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs {{ $purchase->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $purchase->status === 'completed' ? 'Activa' : 'Anulada' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">${{ number_format($purchase->total_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($purchase->exchange_rate, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($purchase->status === 'completed')
                                    <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('¿Deseas anular esta compra? Esto ajustará inventario y saldos.')">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-red-500 px-2.5 py-1.5 text-xs text-white hover:bg-red-600">Anular</button>
                                    </form>
                                @else
                                    <form action="{{ route('purchases.activate', $purchase) }}" method="POST" onsubmit="return confirm('¿Deseas activar nuevamente esta compra?')">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-green-500 px-2.5 py-1.5 text-xs text-white hover:bg-green-600">Activar</button>
                                    </form>
                                @endif

                                <a href="{{ route('purchases.show', $purchase) }}" class="text-brand-500">Ver</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Sin compras</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $purchases->links() }}</div>
@endsection
