@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Configuración" />
    <x-common.flash-messages />

    <form action="{{ route('configuration.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Datos de la Empresa (Venezuela)</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">Razón Social</label>
                    <input type="text" name="company_name" value="{{ old('company_name', $settings['company_name']) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">RIF</label>
                    <input type="text" name="company_rif" value="{{ old('company_rif', $settings['company_rif']) }}" placeholder="J-12345678-9"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Teléfono</label>
                    <input type="text" name="company_phone" value="{{ old('company_phone', $settings['company_phone']) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Prefijo Factura</label>
                    <input type="text" name="invoice_prefix" value="{{ old('invoice_prefix', $settings['invoice_prefix']) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div class="md:col-span-2">
                    <label class="mb-1 block text-sm font-medium">Dirección Fiscal</label>
                    <textarea name="company_address" rows="2" class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('company_address', $settings['company_address']) }}</textarea>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Moneda y Tributos</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium">Tasa Bs/USD (Facturación)</label>
                    <input type="number" step="0.0001" name="exchange_rate_usd_ves" value="{{ old('exchange_rate_usd_ves', $settings['exchange_rate_usd_ves']) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                    <p class="mt-1 text-xs text-gray-500">Tasa usada para convertir precios a bolívares en facturas</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Tasa EUR/USD</label>
                    <input type="number" step="0.0001" name="exchange_rate_eur_usd" value="{{ old('exchange_rate_eur_usd', $settings['exchange_rate_eur_usd']) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">IVA (%)</label>
                    <input type="number" step="0.01" name="tax_rate" value="{{ old('tax_rate', $settings['tax_rate']) }}" required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div class="md:col-span-3">
                    <label class="mb-1 block text-sm font-medium">Notas del cambio de tasa</label>
                    <input type="text" name="rate_notes" placeholder="Ej: Tasa BCV del día..."
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Soporte</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium">WhatsApp de Soporte</label>
                    <input type="text" name="support_whatsapp" value="{{ old('support_whatsapp', $settings['support_whatsapp']) }}" placeholder="+58412XXXXXXX"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Email de Soporte</label>
                    <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email']) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Escaner USB (Pistola Laser)</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="flex items-center gap-2 md:col-span-1">
                    <input type="checkbox" name="scanner_enabled" value="1" @checked(old('scanner_enabled', $settings['scanner_enabled'])) class="rounded">
                    <label class="text-sm font-medium">Activar escaneo por SKU</label>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Aplicar en</label>
                    <select name="scanner_scope" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                        <option value="both" @selected(old('scanner_scope', $settings['scanner_scope']) === 'both')>Ventas y Compras</option>
                        <option value="sales" @selected(old('scanner_scope', $settings['scanner_scope']) === 'sales')>Solo Ventas</option>
                        <option value="purchases" @selected(old('scanner_scope', $settings['scanner_scope']) === 'purchases')>Solo Compras</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Longitud minima del codigo</label>
                    <input type="number" name="scanner_min_length" min="3" max="30" value="{{ old('scanner_min_length', $settings['scanner_min_length']) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>
            <p class="mt-3 text-xs text-gray-500">
                La pistola USB funciona como teclado. En Productos de Venta o Compra, enfoca el buscador del item, escanea el SKU y presiona Enter (muchas pistolas ya lo envian automaticamente).
            </p>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]" x-data="{ useCreditPercentage: {{ old('credit_initial_by_percentage', $settings['credit_initial_by_percentage']) ? 'true' : 'false' }} }">
            <h3 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">Sistema de Credito de Ventas</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div class="flex items-center gap-2 md:col-span-1">
                    <input type="checkbox" name="credit_system_enabled" value="1" @checked(old('credit_system_enabled', $settings['credit_system_enabled'])) class="rounded">
                    <label class="text-sm font-medium">Activar creditos en ventas</label>
                </div>
                <div class="flex items-center gap-2 md:col-span-1">
                    <input type="checkbox" name="credit_initial_by_percentage" value="1" @checked(old('credit_initial_by_percentage', $settings['credit_initial_by_percentage'])) x-model="useCreditPercentage" class="rounded">
                    <label class="text-sm font-medium">Cuota inicial por porcentaje</label>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Porcentaje de cuota inicial (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="credit_initial_percentage" value="{{ old('credit_initial_percentage', $settings['credit_initial_percentage']) }}" :disabled="!useCreditPercentage" :required="useCreditPercentage"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Mora por cuota vencida (USD)</label>
                    <input type="number" step="0.01" min="0" name="credit_late_fee_usd" value="{{ old('credit_late_fee_usd', $settings['credit_late_fee_usd']) }}"
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div class="text-xs text-gray-500 md:pt-8">
                    Si activas el porcentaje, el sistema calcula la cuota inicial automaticamente. Si lo desactivas, el usuario escribe manualmente el monto de cuota inicial al crear la venta.
                </div>
            </div>
        </div>

        <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            Guardar Configuración
        </button>
    </form>

    <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="mb-4 text-lg font-semibold">Historial de Tasas</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-gray-500">
                    <th class="pb-2">Fecha</th>
                    <th class="pb-2">Tasa Bs/USD</th>
                    <th class="pb-2">Usuario</th>
                    <th class="pb-2">Notas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rateHistory as $rate)
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <td class="py-2">{{ $rate->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-2">{{ number_format($rate->rate, 4, ',', '.') }}</td>
                        <td class="py-2">{{ $rate->user?->name ?? 'Sistema' }}</td>
                        <td class="py-2">{{ $rate->notes }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
