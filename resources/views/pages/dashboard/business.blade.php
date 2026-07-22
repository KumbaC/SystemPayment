@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Dashboard del Negocio" />

    <div class="mb-4 rounded-xl border border-brand-200 bg-brand-50 px-4 py-3 text-sm dark:border-brand-800 dark:bg-brand-900/20">
        <span class="font-medium text-brand-700 dark:text-brand-300">Tasa Bs/USD:</span>
        <span class="text-brand-900 dark:text-brand-100">{{ number_format($exchangeRate, 2, ',', '.') }} Bs</span>
        <span class="text-gray-500 dark:text-gray-400"> — Contabilidad en USD · Facturación en Bs</span>
    </div>

    <div class="grid grid-cols-12 gap-4 md:gap-6 mb-6">
        @foreach ([
            ['title' => 'Ventas Hoy', 'data' => $metrics['sales_today'], 'color' => 'brand'],
            ['title' => 'Ventas Semana', 'data' => $metrics['sales_week'], 'color' => 'blue'],
            ['title' => 'Ventas Mes', 'data' => $metrics['sales_month'], 'color' => 'purple'],
        ] as $card)
            <div class="col-span-12 sm:col-span-6 xl:col-span-4">
                <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card['title'] }}</p>
                    <h3 class="mt-2 text-2xl font-bold text-gray-800 dark:text-white/90">{{ $card['data']['count'] }} ventas</h3>
                    <div class="mt-3 space-y-1 text-sm">
                        <p><span class="text-gray-500">Total USD:</span> <strong>${{ number_format($card['data']['total_usd'], 2) }}</strong></p>
                        <p><span class="text-gray-500">Total Bs:</span> <strong>Bs. {{ number_format($card['data']['total_ves'], 2, ',', '.') }}</strong></p>
                        <p><span class="text-gray-500">Ganancia USD:</span> <strong class="text-green-600">${{ number_format($card['data']['profit_usd'], 2) }}</strong></p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-12 gap-4 md:gap-6 mb-6">
        <div class="col-span-12 md:col-span-4">
            <div class="rounded-2xl border border-green-200 bg-green-50 p-5 dark:border-green-800 dark:bg-green-900/10">
                <p class="text-sm text-green-700 dark:text-green-400">Ganancias del Mes (USD)</p>
                <h3 class="mt-2 text-3xl font-bold text-green-700 dark:text-green-300">${{ number_format($metrics['profit_month_usd'], 2) }}</h3>
            </div>
        </div>
        <div class="col-span-12 md:col-span-4">
            <div class="rounded-2xl border border-red-200 bg-red-50 p-5 dark:border-red-800 dark:bg-red-900/10">
                <p class="text-sm text-red-700 dark:text-red-400">Gastos del Mes (USD)</p>
                <p class="text-xs text-red-600/80">Compras + pagos empleados</p>
                <h3 class="mt-2 text-3xl font-bold text-red-700 dark:text-red-300">${{ number_format($metrics['expenses_month_usd'], 2) }}</h3>
            </div>
        </div>
        <div class="col-span-12 md:col-span-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-sm text-gray-500">Resultado Neto del Mes (USD)</p>
                <h3 class="mt-2 text-3xl font-bold {{ $metrics['net_profit_month_usd'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    ${{ number_format($metrics['net_profit_month_usd'], 2) }}
                </h3>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 md:gap-6 mb-6">
        <div class="col-span-12 md:col-span-6">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 dark:border-emerald-800 dark:bg-emerald-900/10">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-emerald-700 dark:text-emerald-400">Cuentas por Cobrar</p>
                        <h3 class="mt-2 text-3xl font-bold text-emerald-700 dark:text-emerald-300">Bs. {{ number_format($metrics['receivables']['pending_ves'], 2, ',', '.') }}</h3>
                        <p class="mt-1 text-xs text-emerald-700/80 dark:text-emerald-300/80">Saldo pendiente actual</p>
                    </div>
                    <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">
                        {{ $metrics['receivables']['count'] }} facturas
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-3">
                    <p><span class="text-emerald-700/80 dark:text-emerald-300/80">Total:</span> <strong>Bs. {{ number_format($metrics['receivables']['total_ves'], 2, ',', '.') }}</strong></p>
                    <p><span class="text-emerald-700/80 dark:text-emerald-300/80">Cobrado:</span> <strong>Bs. {{ number_format($metrics['receivables']['paid_ves'], 2, ',', '.') }}</strong></p>
                    <p><span class="text-emerald-700/80 dark:text-emerald-300/80">Pendiente:</span> <strong>Bs. {{ number_format($metrics['receivables']['pending_ves'], 2, ',', '.') }}</strong></p>
                </div>
            </div>
        </div>
        <div class="col-span-12 md:col-span-6">
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-800 dark:bg-amber-900/10">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm text-amber-700 dark:text-amber-400">Facturas por Pagar</p>
                        <h3 class="mt-2 text-3xl font-bold text-amber-700 dark:text-amber-300">Bs. {{ number_format($metrics['payables']['pending_ves'], 2, ',', '.') }}</h3>
                        <p class="mt-1 text-xs text-amber-700/80 dark:text-amber-300/80">Saldo pendiente actual</p>
                    </div>
                    <span class="rounded-full bg-white/70 px-3 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                        {{ $metrics['payables']['count'] }} facturas
                    </span>
                </div>
                <div class="mt-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-3">
                    <p><span class="text-amber-700/80 dark:text-amber-300/80">Total:</span> <strong>Bs. {{ number_format($metrics['payables']['total_ves'], 2, ',', '.') }}</strong></p>
                    <p><span class="text-amber-700/80 dark:text-amber-300/80">Pagado:</span> <strong>Bs. {{ number_format($metrics['payables']['paid_ves'], 2, ',', '.') }}</strong></p>
                    <p><span class="text-amber-700/80 dark:text-amber-300/80">Pendiente:</span> <strong>Bs. {{ number_format($metrics['payables']['pending_ves'], 2, ',', '.') }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 md:gap-6">
        <div class="col-span-12 xl:col-span-7">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <h4 class="mb-4 font-semibold text-gray-800 dark:text-white/90">Ventas últimos 30 días</h4>
                <div id="salesChart" class="min-h-[300px]"></div>
            </div>
        </div>
        <div class="col-span-12 xl:col-span-5">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
                <div class="mb-4 flex items-center justify-between">
                    <h4 class="font-semibold text-gray-800 dark:text-white/90">Ventas Recientes</h4>
                    @can('reports.export')
                        <a href="{{ route('reports.export', ['period' => 'month']) }}" class="text-sm text-brand-500 hover:underline">Exportar Excel</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-left text-gray-500">
                                <th class="pb-2">Factura</th>
                                <th class="pb-2">Cliente</th>
                                <th class="pb-2 text-right">Total Bs</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentSales as $sale)
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <td class="py-2">
                                        <a href="{{ route('sales.show', $sale) }}" class="text-brand-500 hover:underline">{{ $sale->invoice_number }}</a>
                                    </td>
                                    <td class="py-2">{{ $sale->customer?->name ?? 'Consumidor Final' }}</td>
                                    <td class="py-2 text-right">Bs. {{ number_format($sale->total_ves, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-4 text-center text-gray-500">Sin ventas registradas</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($chartData);
    const labels = chartData.map(d => d.sale_date);
    const totals = chartData.map(d => parseFloat(d.total_usd));
    const profits = chartData.map(d => parseFloat(d.profit_usd));
    const el = document.querySelector('#salesChart');

    if (el && window.ApexCharts) {
        new window.ApexCharts(el, {
            chart: { type: 'area', height: 300, toolbar: { show: false }, fontFamily: 'inherit' },
            series: [
                { name: 'Ventas USD', data: totals },
                { name: 'Ganancia USD', data: profits },
            ],
            xaxis: { categories: labels, labels: { rotate: -45 } },
            colors: ['#465fff', '#12b76a'],
            stroke: { curve: 'smooth', width: 2 },
            dataLabels: { enabled: false },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.4, opacityTo: 0.05 } },
            legend: { position: 'top' },
        }).render();
    }
});
</script>
@endpush
