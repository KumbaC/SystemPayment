@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Reportes de Ventas" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex rounded-lg border border-gray-200 dark:border-gray-700">
            @foreach (['day' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes'] as $key => $label)
                <a href="{{ route('reports.index', ['period' => $key]) }}"
                    class="px-4 py-2 text-sm {{ $period === $key ? 'bg-brand-500 text-white' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        @can('reports.export')
            <a href="{{ route('reports.export', ['period' => $period]) }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                Exportar a Excel
            </a>
        @endcan
    </div>

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-5">
        @foreach ([
            ['label' => 'Ventas', 'value' => $metrics['count']],
            ['label' => 'Total USD', 'value' => '$'.number_format($metrics['total_usd'], 2)],
            ['label' => 'Total Bs', 'value' => 'Bs. '.number_format($metrics['total_ves'], 2, ',', '.')],
            ['label' => 'Ganancia USD', 'value' => '$'.number_format($metrics['profit_usd'], 2)],
            ['label' => 'Costo USD', 'value' => '$'.number_format($metrics['cost_usd'], 2)],
        ] as $m)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">{{ $m['label'] }}</p>
                <p class="mt-1 text-lg font-bold">{{ $m['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">Factura</th>
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Cliente</th>
                    <th class="px-4 py-3 text-right">Total USD</th>
                    <th class="px-4 py-3 text-right">Total Bs</th>
                    <th class="px-4 py-3 text-right">Ganancia</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sales as $sale)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3"><a href="{{ route('sales.invoice', $sale) }}" class="text-brand-500">{{ $sale->invoice_number }}</a></td>
                        <td class="px-4 py-3">{{ $sale->sale_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $sale->customer?->name ?? 'Consumidor Final' }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($sale->total_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">Bs. {{ number_format($sale->total_ves, 2, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-green-600">${{ number_format($sale->profit_usd, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Sin datos para el período</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
