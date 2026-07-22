@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Ventas" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar venta, factura o cliente..."
                class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
        </form>
        @can('sales.create')
            <a href="{{ route('sales.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">+ Nueva Venta</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">N° Venta</th>
                    <th class="px-4 py-3">Factura</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3 text-right">Total USD</th>
                    <th class="px-4 py-3 text-right">Total Bs</th>
                    <th class="px-4 py-3 text-right">Ganancia USD</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    @php
                        $overdueInstallment = $sale->credit?->installments
                            ?->first(fn ($installment) => $installment->status !== 'paid' && $installment->due_date && $installment->due_date->isPast())
                    @endphp
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $sale->sale_number }}</td>
                        <td class="px-4 py-3">{{ $sale->invoice_number }}</td>
                        <td class="px-4 py-3">{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Consumidor Final' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-1 text-xs {{ $sale->status === 'completed' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' }}">
                                {{ $sale->status === 'completed' ? 'Activa' : 'Anulada' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">${{ number_format($sale->total_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">Bs. {{ number_format($sale->total_ves, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $sale->status === 'completed' ? 'text-green-600' : 'text-gray-400' }}">${{ number_format($sale->profit_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if ($overdueInstallment && $sale->customer?->phone)
                                    <form action="{{ route('sales.credits.installments.whatsapp', [$sale, $overdueInstallment]) }}" method="POST" target="_blank">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 rounded-lg bg-green-500 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-600" title="Enviar mensaje de mora por WhatsApp">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4">
                                                <path d="M12 2a10 10 0 0 0-8.66 15l-1.2 4.37a.75.75 0 0 0 .92.92L7.43 21A10 10 0 1 0 12 2Zm0 1.5a8.5 8.5 0 0 1 7.3 12.84.75.75 0 0 0-.08.59l.91 3.28-3.28-.9a.75.75 0 0 0-.59.07A8.5 8.5 0 1 1 12 3.5Zm-3.11 4.52c-.24 0-.5.09-.71.31-.21.21-.56.56-.56 1.37 0 .8.58 1.58.66 1.69.08.1 1.14 1.83 2.8 2.57 1.38.6 1.66.48 1.96.45.3-.03.96-.39 1.1-.77.13-.38.13-.71.1-.78-.03-.07-.11-.1-.24-.17-.13-.06-.78-.39-.9-.44-.12-.04-.21-.06-.3.07-.09.13-.35.44-.43.54-.08.1-.16.11-.29.04-.13-.07-.55-.2-1.05-.65-.39-.34-.65-.76-.73-.89-.07-.13-.01-.2.06-.27.06-.06.13-.16.2-.24.06-.08.08-.13.12-.22.04-.08.02-.16-.01-.23-.04-.06-.3-.73-.41-1-.11-.27-.22-.24-.3-.24h-.26Z" />
                                            </svg>
                                            <span>Mora</span>
                                        </button>
                                    </form>
                                @endif

                                @if ($sale->status === 'completed')
                                    <form action="{{ route('sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('¿Deseas anular esta venta? Esto ajustará inventario y saldos.')">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-red-500 px-2.5 py-1.5 text-xs text-white hover:bg-red-600">Anular</button>
                                    </form>
                                @else
                                    <form action="{{ route('sales.activate', $sale) }}" method="POST" onsubmit="return confirm('¿Deseas activar nuevamente esta venta?')">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-green-500 px-2.5 py-1.5 text-xs text-white hover:bg-green-600">Activar</button>
                                    </form>
                                @endif

                                <a href="{{ route('sales.invoice', $sale) }}" class="text-brand-500 hover:underline">Factura</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">Sin ventas</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sales->links() }}</div>
@endsection
