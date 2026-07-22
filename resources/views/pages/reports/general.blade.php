@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Reporte General del Negocio" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex rounded-lg border border-gray-200 dark:border-gray-700">
            @foreach (['day' => 'Hoy', 'week' => 'Semana', 'month' => 'Mes', 'year' => 'Año'] as $key => $label)
                <a href="{{ route('reports.general', ['period' => $key]) }}"
                    class="px-4 py-2 text-sm {{ $period === $key ? 'bg-brand-500 text-white' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
        @can('reports.export')
            <a href="{{ route('reports.general.export', ['period' => $period]) }}" class="rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700">
                Exportar Excel completo
            </a>
        @endcan
        <span class="text-sm text-gray-500">
            {{ $report['from']->format('d/m/Y') }} — {{ $report['to']->format('d/m/Y') }}
        </span>
    </div>

    @php $s = $report['summary']; @endphp

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-5">
        @foreach ([
            ['label' => 'Ventas', 'value' => $s['sales_count'], 'color' => 'text-brand-600'],
            ['label' => 'Ingresos USD', 'value' => '$'.number_format($s['sales_total_usd'], 2), 'color' => ''],
            ['label' => 'Ingresos Bs', 'value' => 'Bs. '.number_format($s['sales_total_ves'], 2, ',', '.'), 'color' => ''],
            ['label' => 'Ganancia bruta USD', 'value' => '$'.number_format($s['sales_profit_usd'], 2), 'color' => 'text-green-600'],
            ['label' => 'Compras USD', 'value' => '$'.number_format($s['purchases_total_usd'], 2), 'color' => 'text-red-500'],
            ['label' => 'Pagos empleados USD', 'value' => '$'.number_format($s['employee_payments_usd'], 2), 'color' => 'text-orange-500'],
            ['label' => 'Total gastos USD', 'value' => '$'.number_format($s['total_expenses_usd'], 2), 'color' => 'text-red-600'],
            ['label' => 'Resultado neto USD', 'value' => '$'.number_format($s['net_profit_usd'], 2), 'color' => $s['net_profit_usd'] >= 0 ? 'text-green-600' : 'text-red-600'],
        ] as $m)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
                <p class="text-xs text-gray-500">{{ $m['label'] }}</p>
                <p class="mt-1 text-lg font-bold {{ $m['color'] }}">{{ $m['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="border-b border-gray-100 px-4 py-3 font-semibold dark:border-gray-800">Ventas ({{ $report['sales']->count() }})</h3>
            <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-2">Factura</th><th class="px-4 py-2 text-right">Total USD</th><th class="px-4 py-2 text-right">Ganancia</th></tr></thead>
                    <tbody>
                        @forelse ($report['sales'] as $sale)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-2"><a href="{{ route('sales.invoice', $sale) }}" class="text-brand-500">{{ $sale->invoice_number }}</a></td>
                                <td class="px-4 py-2 text-right">${{ number_format($sale->total_usd, 2) }}</td>
                                <td class="px-4 py-2 text-right text-green-600">${{ number_format($sale->profit_usd, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin ventas</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="border-b border-gray-100 px-4 py-3 font-semibold dark:border-gray-800">Compras ({{ $report['purchases']->count() }})</h3>
            <div class="max-h-64 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-2">N°</th><th class="px-4 py-2">Proveedor</th><th class="px-4 py-2 text-right">Total USD</th></tr></thead>
                    <tbody>
                        @forelse ($report['purchases'] as $purchase)
                            <tr class="border-t border-gray-100 dark:border-gray-800">
                                <td class="px-4 py-2"><a href="{{ route('purchases.show', $purchase) }}" class="text-brand-500">{{ $purchase->purchase_number }}</a></td>
                                <td class="px-4 py-2">{{ $purchase->supplier->name }}</td>
                                <td class="px-4 py-2 text-right">${{ number_format($purchase->total_usd, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Sin compras</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="border-b border-gray-100 px-4 py-3 font-semibold dark:border-gray-800">Pagos a empleados ({{ $report['employee_payments']->count() }})</h3>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Empleado</th><th class="px-4 py-3">Método</th><th class="px-4 py-3 text-right">Monto USD</th></tr></thead>
            <tbody>
                @forelse ($report['employee_payments'] as $payment)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $payment->employee->name }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_method }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($payment->amount_usd, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Sin pagos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
