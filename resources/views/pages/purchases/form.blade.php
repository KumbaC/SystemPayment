@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nueva Compra" />
    <x-common.flash-messages />

    <form action="{{ route('purchases.store') }}" method="POST" x-data="purchaseForm()" x-init="init()">
        @csrf

        <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mb-3 text-sm font-semibold">Condición de pago</p>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="purchase_settlement_type" value="immediate" x-model="settlementType" class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                    Pago inmediato
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="purchase_settlement_type" value="credit" x-model="settlementType" class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                    Dejar saldo pendiente (crear CxP)
                </label>
                <div x-show="settlementType === 'credit'">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Fecha de vencimiento</label>
                    <input type="date" name="purchase_due_date" :required="settlementType === 'credit'" value="{{ old('purchase_due_date', date('Y-m-d', strtotime('+15 days'))) }}"
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">Proveedor *</label>
                <input type="hidden" name="supplier_id" :value="supplierId" required>
                <div class="relative">
                    <input type="text" x-model="supplierSearch" @focus="supplierOpen = true" @input="supplierOpen = true"
                        placeholder="Buscar por nombre o RIF..." required
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                    <div x-show="supplierOpen" @click.outside="supplierOpen = false"
                        class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <template x-for="s in filterSuppliers()" :key="s.id">
                            <button type="button" @click="selectSupplier(s)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                                <span x-text="s.name"></span>
                                <span class="text-xs text-gray-500" x-show="s.rif" x-text="' · RIF: ' + s.rif"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Fecha *</label>
                <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
        </div>

        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex justify-between">
                <h3 class="font-semibold">Productos (contabilidad en USD)</h3>
                <button type="button" @click="addItem()" class="text-sm text-brand-500">+ Agregar</button>
            </div>

            <div x-show="scannerEnabled" class="mb-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-xs text-blue-700 dark:border-blue-700 dark:bg-blue-900/15 dark:text-blue-300">
                Escaner USB activo: enfoca "Buscar producto", escanea el SKU y presiona Enter.
            </div>

            <div class="mb-4 grid grid-cols-1 gap-3 rounded-xl border border-gray-200 bg-gray-50 p-3 md:grid-cols-2 dark:border-gray-700 dark:bg-gray-900/40">
                <div>
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Margen sugerido (%)</label>
                    <input type="hidden" name="margin_percent" :value="marginPercent">
                    <input type="number" min="0" step="0.01" x-model.number="marginPercent"
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400 md:pt-6">
                    Prediccion de venta: costo por unidad x (1 + margen/100). Puedes ajustar manualmente el precio sugerido por cada producto.
                </div>
            </div>

            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 grid grid-cols-12 gap-2 rounded-xl border border-gray-200 p-3 dark:border-gray-700">
                    <div class="col-span-12 md:col-span-6 relative">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Producto</label>
                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                        <input type="text" x-model="item.search" @focus="item.open = true" @input="item.open = true" @keydown.enter.prevent="handleScanEnter(index)" placeholder="Buscar producto..." required
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <div x-show="item.open" @click.outside="item.open = false" class="absolute z-20 mt-1 max-h-40 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            <template x-for="p in filterProducts(item.search)" :key="p.id">
                                <button type="button" @click="selectProduct(index, p)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800" x-text="p.name"></button>
                            </template>
                        </div>
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Cantidad comprada</label>
                        <input type="number" step="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" required min="0.0001" placeholder="Ej: 10"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Monto por unidad (costo USD)</label>
                        <input type="number" step="0.0001" :name="'items['+index+'][unit_cost_usd]'" x-model.number="item.unit_cost_usd" @input="refreshSalePrice(item)" required min="0" placeholder="Ej: 2.50"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-8 md:col-span-2">
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Precio sugerido (venta USD)</label>
                        <input type="number" step="0.0001" :name="'items['+index+'][sale_price_usd]'" x-model.number="item.sale_price_usd" @input="item.manual_sale_price = true" min="0" placeholder="Precio de venta"
                            class="h-10 w-full rounded-lg border border-brand-300 bg-brand-50/40 px-3 text-sm dark:border-brand-700/60 dark:bg-brand-900/15">
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400" x-text="'Prediccion: $' + asMoney(recommendedPrice(item)) + ' | Utilidad/u: $' + asMoney((item.sale_price_usd || 0) - (item.unit_cost_usd || 0))"></p>
                    </div>
                    <div class="col-span-4 md:col-span-0 flex items-end md:items-center">
                        <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-red-500 text-sm">Eliminar</button>
                    </div>
                </div>
            </template>
        </div>

        <textarea name="notes" rows="2" placeholder="Notas..." class="mb-4 w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('notes') }}</textarea>
        <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm text-white">Registrar Compra</button>
    </form>
@endsection

@push('scripts')
@php
    $oldPurchaseForm = [
        'supplier_id' => old('supplier_id'),
        'purchase_settlement_type' => old('purchase_settlement_type', 'immediate'),
        'margin_percent' => old('margin_percent', 30),
        'items' => old('items', []),
    ];
@endphp
<script>
function purchaseForm() {
    const products = @json($products);
    const suppliers = @json($suppliers);
    const scanner = @json($scanner ?? ['enabled' => false, 'minLength' => 4]);
    const oldForm = {{ Illuminate\Support\Js::from($oldPurchaseForm) }};
    return {
        products, suppliers,
        scannerEnabled: !!scanner.enabled,
        scannerMinLength: Number(scanner.minLength || 4),
        settlementType: oldForm.purchase_settlement_type || 'immediate',
        supplierId: oldForm.supplier_id || '', supplierSearch: '', supplierOpen: false,
        marginPercent: Number(oldForm.margin_percent || 30),
        items: Array.isArray(oldForm.items) && oldForm.items.length
            ? oldForm.items.map(item => ({ product_id: item.product_id || '', search: '', open: false, quantity: item.quantity || 1, unit_cost_usd: item.unit_cost_usd || 0, sale_price_usd: item.sale_price_usd || 0, manual_sale_price: true }))
            : [{ product_id: '', search: '', open: false, quantity: 1, unit_cost_usd: 0, sale_price_usd: 0, manual_sale_price: false }],
        init() {
            if (this.supplierId) {
                const selectedSupplier = this.suppliers.find(supplier => Number(supplier.id) === Number(this.supplierId));
                if (selectedSupplier) {
                    this.supplierSearch = selectedSupplier.name + (selectedSupplier.rif ? ' (' + selectedSupplier.rif + ')' : '');
                }
            }

            this.items = this.items.map(item => {
                const product = this.products.find(product => Number(product.id) === Number(item.product_id));
                if (!product) {
                    return item;
                }

                return {
                    ...item,
                    search: product.name,
                    unit_cost_usd: parseFloat(item.unit_cost_usd) || parseFloat(product.cost_usd) || 0,
                    sale_price_usd: parseFloat(item.sale_price_usd) || parseFloat(product.price_usd) || 0,
                };
            });
        },
        addItem() {
            this.items.push({
                product_id: '',
                search: '',
                open: false,
                quantity: 1,
                unit_cost_usd: 0,
                sale_price_usd: 0,
                manual_sale_price: false,
            });
        },
        filterProducts(q) {
            const s = (q || '').toLowerCase();
            if (!s) return this.products.slice(0, 15);
            return this.products.filter(p => p.name.toLowerCase().includes(s) || String(p.sku).toLowerCase().includes(s)).slice(0, 15);
        },
        filterSuppliers() {
            const s = (this.supplierSearch || '').toLowerCase();
            if (!s) return this.suppliers.slice(0, 15);
            return this.suppliers.filter(sup =>
                sup.name.toLowerCase().includes(s) ||
                String(sup.rif).toLowerCase().includes(s)
            ).slice(0, 15);
        },
        selectSupplier(s) {
            this.supplierId = s.id;
            this.supplierSearch = s.name + (s.rif ? ' (' + s.rif + ')' : '');
            this.supplierOpen = false;
        },
        recommendedPrice(item) {
            const cost = Number(item.unit_cost_usd || 0);
            const margin = Number(this.marginPercent || 0);
            return cost * (1 + (margin / 100));
        },
        refreshSalePrice(item) {
            if (!item.manual_sale_price) {
                item.sale_price_usd = this.recommendedPrice(item);
            }
        },
        asMoney(value) {
            return Number(value || 0).toFixed(2);
        },
        selectProduct(i, p) {
            const item = this.items[i];
            item.product_id = p.id;
            item.search = p.name;
            item.open = false;
            item.unit_cost_usd = Number(p.cost_usd || 0);

            const currentPrice = Number(p.price_usd || 0);
            item.sale_price_usd = currentPrice > 0 ? currentPrice : this.recommendedPrice(item);
            item.manual_sale_price = false;
        },
        findProductBySku(code) {
            const sku = String(code || '').trim().toLowerCase();
            return this.products.find(p => String(p.sku || '').trim().toLowerCase() === sku);
        },
        showValidationAlert(message, title = 'Validacion') {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'warning',
                    title,
                    text: message,
                    confirmButtonText: 'Entendido',
                });
            }
        },
        handleScanEnter(index) {
            if (!this.scannerEnabled) {
                return;
            }

            const code = String(this.items[index]?.search || '').trim();
            if (code.length < this.scannerMinLength) {
                return;
            }

            const product = this.findProductBySku(code);
            if (!product) {
                this.showValidationAlert('No se encontro ningun producto con el codigo escaneado: ' + code, 'Producto no encontrado');
                return;
            }

            this.selectProduct(index, product);
        },
    };
}
</script>
@endpush
