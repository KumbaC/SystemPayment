@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nueva Venta" />
    <x-common.flash-messages />

    <form action="{{ route('sales.store') }}" method="POST" x-data="saleForm()" x-init="init()" @submit="prepareSubmit">
        @csrf

        <div class="mb-4 rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="mb-3 text-sm font-semibold">Condición de cobro</p>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="sale_settlement_type" value="immediate" x-model="settlementType" class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                    Pago inmediato
                </label>
                <label class="inline-flex items-center gap-2 text-sm" :class="creditEnabled ? '' : 'opacity-60 cursor-not-allowed'">
                    <input type="radio" name="sale_settlement_type" value="credit" x-model="settlementType" :disabled="!creditEnabled" class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                    Dejar saldo pendiente (crédito)
                </label>
                <div x-show="settlementType === 'credit' && creditEnabled">
                    <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Fecha de vencimiento</label>
                    <input type="date" name="sale_due_date" :required="settlementType === 'credit'" value="{{ old('sale_due_date', date('Y-m-d', strtotime('+15 days'))) }}"
                        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                </div>
            </div>
            <p x-show="!creditEnabled" class="mt-2 text-xs text-amber-600 dark:text-amber-300">El sistema de crédito está desactivado en configuración.</p>

            <div x-show="settlementType === 'credit' && creditEnabled" class="mt-3 rounded-lg border border-brand-200 bg-brand-50 p-3 dark:border-brand-800 dark:bg-brand-900/20">
                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium uppercase tracking-wide text-gray-500">Cuota inicial (USD)</label>
                        <input type="number" step="0.01" min="0" max="999999999" name="credit_initial_payment_usd" x-model="initialPaymentUsd" @input="refreshCreditPlan()"
                            :readonly="creditUsePercentage"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400" x-show="creditUsePercentage" x-text="'Modo porcentaje activo: ' + asMoney(initialPercentage) + '%' "></p>
                        <p class="mt-1 text-[11px] text-gray-500 dark:text-gray-400" x-show="!creditUsePercentage">Monto editable por usuario.</p>
                    </div>
                    <div class="md:col-span-2 flex items-end gap-2">
                        <button type="button" @click="buildInstallments()" class="rounded-lg border border-brand-500 px-4 py-2 text-xs font-medium text-brand-600 dark:text-brand-300">Cuotas (2)</button>
                        <span class="text-xs text-gray-600 dark:text-gray-400">Mora por atraso: ${{ number_format($creditConfig['lateFeeUsd'] ?? 1, 2) }} por cuota vencida.</span>
                    </div>
                </div>

                <div x-show="installments.length" class="mt-3 overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900/40">
                    <table class="w-full text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/60">
                            <tr class="text-left text-gray-500">
                                <th class="px-3 py-2">Cuota</th>
                                <th class="px-3 py-2">Vencimiento</th>
                                <th class="px-3 py-2 text-right">Monto USD</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="inst in installments" :key="inst.number">
                                <tr class="border-t border-gray-100 dark:border-gray-800">
                                    <td class="px-3 py-2" x-text="'Cuota ' + inst.number"></td>
                                    <td class="px-3 py-2" x-text="inst.due"></td>
                                    <td class="px-3 py-2 text-right" x-text="'$' + asMoney(inst.amount)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="mb-1 block text-sm font-medium">Cliente existente</label>
                <input type="hidden" name="customer_id" :value="customerId">
                <div class="relative">
                    <input type="text" x-model="customerSearch" @focus="customerOpen = true" @input="customerOpen = true"
                        :disabled="showQuickCustomer" placeholder="Buscar por nombre o cédula..."
                        class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                    <div x-show="customerOpen && !showQuickCustomer" @click.outside="customerOpen = false"
                        class="absolute z-20 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <button type="button" @click="selectCustomer(null)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                            Consumidor Final
                        </button>
                        <template x-for="c in filterCustomers()" :key="c.id">
                            <button type="button" @click="selectCustomer(c)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                                <span x-text="c.name"></span>
                                <span class="text-xs text-gray-500" x-text="' · ' + (c.document || 'Sin documento')"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <p x-show="selectedCustomerLabel && !showQuickCustomer" class="mt-1 text-xs text-gray-500" x-text="'Seleccionado: ' + selectedCustomerLabel"></p>
                <button type="button" @click="showQuickCustomer = !showQuickCustomer" class="mt-2 text-xs text-brand-500 hover:underline">
                    <span x-text="showQuickCustomer ? '− Cancelar cliente nuevo' : '+ Registrar cliente nuevo aquí'"></span>
                </button>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Fecha *</label>
                <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div class="flex items-end">
                <div class="rounded-lg border border-brand-200 bg-brand-50 px-4 py-2 text-sm dark:border-brand-800 dark:bg-brand-900/20">
                    Tasa: <strong>{{ number_format($exchangeRate, 2, ',', '.') }} Bs/USD</strong> · IVA: {{ $taxRate }}%
                </div>
            </div>
        </div>

        <div x-show="showQuickCustomer" x-collapse class="mb-6 rounded-xl border border-dashed border-brand-300 bg-brand-50/50 p-4 dark:border-brand-700 dark:bg-brand-900/10">
            <p class="mb-3 text-sm font-medium text-brand-700 dark:text-brand-300">Cliente nuevo (se guardará al registrar la venta)</p>
            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <input type="text" name="quick_customer[name]" value="{{ old('quick_customer.name') }}" placeholder="Nombre *" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                <select name="quick_customer[document_type]" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    <option value="V" @selected(old('quick_customer.document_type', 'V') === 'V')>V</option><option value="E" @selected(old('quick_customer.document_type') === 'E')>E</option><option value="J" @selected(old('quick_customer.document_type') === 'J')>J</option><option value="G" @selected(old('quick_customer.document_type') === 'G')>G</option><option value="P" @selected(old('quick_customer.document_type') === 'P')>P</option>
                </select>
                <input type="text" name="quick_customer[document_number]" value="{{ old('quick_customer.document_number') }}" placeholder="Cédula/RIF" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                <input type="text" name="quick_customer[phone]" value="{{ old('quick_customer.phone') }}" placeholder="Teléfono (WhatsApp)" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            </div>
        </div>

        {{-- Productos --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold">Productos</h3>
                <button type="button" @click="addItem()" class="text-sm text-brand-500 hover:underline">+ Agregar producto</button>
            </div>

            <div x-show="scannerEnabled" class="mb-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-xs text-blue-700 dark:border-blue-700 dark:bg-blue-900/15 dark:text-blue-300">
                Escaner USB activo: enfoca "Buscar producto", escanea el SKU y presiona Enter.
            </div>

            @if (count($products) === 0)
                <div class="mb-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-300">
                    Alerta: no hay productos con stock disponible para vender en este momento.
                </div>
            @endif

            <template x-for="(item, index) in items" :key="index">
                <div class="mb-3 grid grid-cols-12 gap-2 items-end">
                    <div class="col-span-12 md:col-span-5 relative">
                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                        <input type="text" x-model="item.search" @focus="item.open = true" @input="item.open = true" @keydown.enter.prevent="handleScanEnter(index)" placeholder="Buscar producto..." required
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                        <div x-show="item.open" @click.outside="item.open = false" class="absolute z-20 mt-1 max-h-40 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                            <template x-for="p in filterProducts(item.search)" :key="p.id">
                                <button type="button" @click="selectProduct(index, p)" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                                    <span x-text="p.name"></span>
                                    <span class="text-xs text-gray-500" x-text="' · Stock: '+p.stock+' · $'+p.price_usd.toFixed(2)"></span>
                                </button>
                            </template>
                            <div x-show="filterProducts(item.search).length === 0" class="px-3 py-2 text-xs text-amber-600 dark:text-amber-400">
                                No hay este producto en el stock.
                            </div>
                        </div>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <input type="number" step="0.0001" :name="'items['+index+'][quantity]'" x-model="item.quantity" @input="calcTotals()" placeholder="Cant." required min="0.0001"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-4 md:col-span-2 flex h-10 items-center rounded-lg border border-gray-200 bg-gray-50 px-3 text-sm dark:border-gray-700 dark:bg-gray-800/50">
                        <span class="text-gray-500">Precio:</span>
                        <strong class="ml-1" x-text="item.product_id ? '$' + (parseFloat(item.unit_price_usd)||0).toFixed(2) : '—'"></strong>
                    </div>
                    <div class="col-span-3 md:col-span-2 text-sm text-right">
                        <span x-text="'Bs. ' + formatBs(item.subtotal_ves)"></span>
                    </div>
                    <div class="col-span-1">
                        <button type="button" @click="removeItem(index)" class="text-red-500 text-sm">✕</button>
                    </div>
                </div>
            </template>
            <div class="mt-4 text-right space-y-1 text-sm">
                <p>Subtotal USD: <strong x-text="'$' + subtotalUsd.toFixed(2)"></strong></p>
                <p>IVA ({{ $taxRate }}%): <strong x-text="'$' + taxUsd.toFixed(2)"></strong></p>
                <p class="text-lg">Total USD: <strong x-text="'$' + totalUsd.toFixed(2)"></strong></p>
                <p class="text-lg text-brand-600">Total Factura Bs: <strong x-text="'Bs. ' + formatBs(totalVes)"></strong></p>
            </div>
        </div>

        {{-- Pagos --}}
        <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]" x-show="settlementType !== 'credit'">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="font-semibold">Formas de Pago</h3>
                <button type="button" @click="addPayment()" class="text-sm text-brand-500 hover:underline">+ Agregar pago</button>
            </div>
            <template x-for="(payment, index) in payments" :key="'p'+index">
                <div class="mb-3 grid grid-cols-12 gap-2 items-end">
                    <div class="col-span-12 md:col-span-3">
                        <select :name="'payments['+index+'][payment_method]'" x-model="payment.payment_method" :required="settlementType !== 'credit'" :disabled="settlementType === 'credit'"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <select :name="'payments['+index+'][currency]'" x-model="payment.currency" @change="calcPayments()" :required="settlementType !== 'credit'" :disabled="settlementType === 'credit'"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                            @foreach ($currencies as $value => $label)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <input type="number" step="0.01" :name="'payments['+index+'][amount]'" x-model="payment.amount" @input="calcPayments()" placeholder="Monto" :required="settlementType !== 'credit'" :disabled="settlementType === 'credit'" min="0.01"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-4 md:col-span-2">
                        <input type="number" step="0.0001" :name="'payments['+index+'][exchange_rate]'" x-model="payment.exchange_rate" @input="calcPayments()" placeholder="Tasa" :disabled="settlementType === 'credit'"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-10 md:col-span-2">
                        <input type="text" :name="'payments['+index+'][reference]'" x-model="payment.reference" placeholder="Referencia" :disabled="settlementType === 'credit'"
                            class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    <div class="col-span-2 md:col-span-1 text-sm text-right">
                        <span x-text="'Bs. ' + formatBs(payment.amount_ves)"></span>
                    </div>
                </div>
            </template>
            <div class="mt-4 text-right text-sm">
                <p>Total Pagado Bs: <strong x-text="'Bs. ' + formatBs(paymentsTotalVes)" :class="paymentsTotalVes >= totalVes ? 'text-green-600' : 'text-red-600'"></strong></p>
                <p x-show="paymentsTotalVes < totalVes" class="text-red-500">Falta: Bs. <span x-text="formatBs(totalVes - paymentsTotalVes)"></span></p>
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Notas</label>
            <textarea name="notes" rows="2" class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
            Registrar Venta y Generar Factura
        </button>
    </form>
@endsection

@push('scripts')
@php
    $oldSaleForm = [
        'customer_id' => old('customer_id'),
        'quick_customer' => old('quick_customer', []),
        'sale_settlement_type' => old('sale_settlement_type', 'immediate'),
        'credit_initial_payment_usd' => old('credit_initial_payment_usd'),
        'items' => old('items', []),
        'payments' => old('payments', []),
    ];
@endphp
<script>
function saleForm() {
    const rate = {{ $exchangeRate }};
    const taxRate = {{ $taxRate }};
    const eurUsd = 1.08;
    const products = @json($products);
    const customers = @json($customers);
    const creditConfig = @json($creditConfig ?? ['enabled' => false, 'lateFeeUsd' => 1]);
    const scanner = @json($scanner ?? ['enabled' => false, 'minLength' => 4]);
    const oldForm = {{ Illuminate\Support\Js::from($oldSaleForm) }};

    return {
        rate, taxRate, products, customers,
        creditConfig,
        creditEnabled: !!creditConfig.enabled,
        creditUsePercentage: !!creditConfig.usePercentage,
        initialPercentage: Number(creditConfig.initialPercentage || 10),
        scannerEnabled: !!scanner.enabled,
        scannerMinLength: Number(scanner.minLength || 4),
        showQuickCustomer: Array.isArray(oldForm.quick_customer) ? false : !!(oldForm.quick_customer && oldForm.quick_customer.name), customerId: oldForm.customer_id || '', customerSearch: '', customerOpen: false, selectedCustomerLabel: '',
        settlementType: oldForm.sale_settlement_type || 'immediate',
        initialPaymentUsd: Number(oldForm.credit_initial_payment_usd || 0),
        installments: [],
        items: Array.isArray(oldForm.items) && oldForm.items.length
            ? oldForm.items.map(item => ({ product_id: item.product_id || '', search: '', open: false, quantity: item.quantity || 1, unit_price_usd: item.unit_price_usd || 0, subtotal_ves: 0 }))
            : [{ product_id: '', search: '', open: false, quantity: 1, unit_price_usd: 0, subtotal_ves: 0 }],
        payments: Array.isArray(oldForm.payments) && oldForm.payments.length
            ? oldForm.payments.map(payment => ({ payment_method: payment.payment_method || 'efectivo_bs', currency: payment.currency || 'VES', amount: payment.amount || 0, exchange_rate: payment.exchange_rate || rate, reference: payment.reference || '', amount_ves: 0 }))
            : [{ payment_method: 'efectivo_bs', currency: 'VES', amount: 0, exchange_rate: rate, reference: '', amount_ves: 0 }],
        subtotalUsd: 0, taxUsd: 0, totalUsd: 0, totalVes: 0, paymentsTotalVes: 0,

        init() {
            this.showQuickCustomer = !!(oldForm.quick_customer && oldForm.quick_customer.name);

            if (this.customerId) {
                const selectedCustomer = this.customers.find(customer => Number(customer.id) === Number(this.customerId));
                if (selectedCustomer) {
                    this.customerSearch = selectedCustomer.name;
                    this.selectedCustomerLabel = selectedCustomer.name + (selectedCustomer.document ? ' (' + selectedCustomer.document + ')' : '');
                }
            }

            this.items = this.items.map(item => {
                const product = this.products.find(product => Number(product.id) === Number(item.product_id));
                if (!product) {
                    return item;
                }

                const unitPriceUsd = parseFloat(item.unit_price_usd) || parseFloat(product.price_usd) || 0;

                return {
                    ...item,
                    search: product.name,
                    unit_price_usd: unitPriceUsd,
                };
            });

            this.calcTotals();

            if (this.settlementType === 'credit' && this.creditUsePercentage) {
                this.initialPaymentUsd = this.calculateInitialByPercentage();
            }
        },

        filterProducts(q) {
            const s = (q || '').toLowerCase();
            if (!s) return this.products.slice(0, 15);
            return this.products.filter(p => p.name.toLowerCase().includes(s) || String(p.sku).toLowerCase().includes(s)).slice(0, 15);
        },
        filterCustomers() {
            const s = (this.customerSearch || '').toLowerCase();
            if (!s) return this.customers.slice(0, 15);
            return this.customers.filter(c =>
                c.name.toLowerCase().includes(s) ||
                String(c.document_number).toLowerCase().includes(s) ||
                String(c.document).toLowerCase().includes(s)
            ).slice(0, 15);
        },
        selectCustomer(c) {
            if (!c) {
                this.customerId = '';
                this.customerSearch = '';
                this.selectedCustomerLabel = 'Consumidor Final';
            } else {
                this.customerId = c.id;
                this.customerSearch = c.name;
                this.selectedCustomerLabel = c.name + (c.document ? ' (' + c.document + ')' : '');
            }
            this.customerOpen = false;
        },
        selectProduct(i, p) {
            this.items[i].product_id = p.id;
            this.items[i].search = p.name;
            this.items[i].unit_price_usd = p.price_usd;
            this.items[i].open = false;
            this.calcTotals();
        },
        findProductBySku(code) {
            const sku = String(code || '').trim().toLowerCase();
            return this.products.find(p => String(p.sku || '').trim().toLowerCase() === sku);
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
                this.handleScanNotFound(code);
                return;
            }

            const existingIndex = this.items.findIndex((it, idx) => idx !== index && Number(it.product_id) === Number(product.id));
            if (existingIndex !== -1) {
                this.items[existingIndex].quantity = (parseFloat(this.items[existingIndex].quantity) || 0) + 1;
                this.items[index].product_id = '';
                this.items[index].search = '';
                this.items[index].unit_price_usd = 0;
                this.calcTotals();
                return;
            }

            this.selectProduct(index, product);
        },
        addItem() { this.items.push({ product_id: '', search: '', open: false, quantity: 1, unit_price_usd: 0, subtotal_ves: 0 }); },
        removeItem(i) { if (this.items.length > 1) { this.items.splice(i, 1); this.calcTotals(); } },
        addPayment() { this.payments.push({ payment_method: 'efectivo_bs', currency: 'VES', amount: 0, exchange_rate: rate, reference: '', amount_ves: 0 }); },
        asMoney(value) { return Number(value || 0).toFixed(2); },
        formatDate(date) {
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            return `${day}/${month}/${year}`;
        },
        calculateInitialByPercentage() {
            const total = parseFloat(this.totalUsd) || 0;
            return Math.min(total, Math.max(0, Math.round(total * (this.initialPercentage / 100) * 100) / 100));
        },
        buildInstallments() {
            const total = parseFloat(this.totalUsd) || 0;
            const initial = this.creditUsePercentage
                ? this.calculateInitialByPercentage()
                : Math.min(total, Math.max(0, parseFloat(this.initialPaymentUsd) || 0));
            const financed = Math.max(0, total - initial);
            const baseInstallment = Math.round((total / 2) * 100) / 100;
            const first = Math.max(0, Math.round((baseInstallment - initial) * 100) / 100);
            const second = Math.max(0, Math.round((financed - first) * 100) / 100);

            this.initialPaymentUsd = initial;

            const base = new Date();
            const dueOne = new Date(base);
            dueOne.setDate(base.getDate() + 7);
            const dueTwo = new Date(base);
            dueTwo.setDate(base.getDate() + 14);

            this.installments = [
                { number: 1, due: this.formatDate(dueOne), amount: first },
                { number: 2, due: this.formatDate(dueTwo), amount: second },
            ];
        },
        refreshCreditPlan() {
            if (this.settlementType === 'credit') {
                if (this.creditUsePercentage) {
                    this.initialPaymentUsd = this.calculateInitialByPercentage();
                }
                this.buildInstallments();
            }
        },
        calcTotals() {
            this.subtotalUsd = this.items.reduce((s, it) => {
                const sub = (parseFloat(it.quantity)||0) * (parseFloat(it.unit_price_usd)||0);
                it.subtotal_ves = sub * this.rate;
                return s + sub;
            }, 0);
            this.taxUsd = this.subtotalUsd * (this.taxRate / 100);
            this.totalUsd = this.subtotalUsd + this.taxUsd;
            this.totalVes = this.totalUsd * this.rate;
            this.refreshCreditPlan();
            this.calcPayments();
        },
        calcPayments() {
            this.paymentsTotalVes = this.payments.reduce((s, p) => {
                const amt = parseFloat(p.amount) || 0;
                const r = parseFloat(p.exchange_rate) || this.rate;
                if (p.currency === 'VES') p.amount_ves = amt;
                else if (p.currency === 'USD') p.amount_ves = amt * r;
                else if (p.currency === 'EUR') p.amount_ves = amt * eurUsd * r;
                else p.amount_ves = amt;
                return s + p.amount_ves;
            }, 0);
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
        formatBs(v) { return (v||0).toLocaleString('es-VE', {minimumFractionDigits:2, maximumFractionDigits:2}); },
        prepareSubmit(e) {
            if (this.settlementType === 'immediate' && this.paymentsTotalVes < this.totalVes - 0.01) {
                e.preventDefault();
                this.showValidationAlert('El monto pagado es menor al total de la factura en bolivares.');
                return;
            }

            if (this.settlementType === 'credit') {
                if (!this.creditEnabled) {
                    e.preventDefault();
                    this.showValidationAlert('El sistema de credito esta desactivado en configuracion.');
                    return;
                }

                if ((parseFloat(this.initialPaymentUsd) || 0) > (parseFloat(this.totalUsd) || 0)) {
                    e.preventDefault();
                    this.showValidationAlert('El pago inicial no puede ser mayor al total de la venta.');
                    return;
                }
            }
        },
        handleScanNotFound(code) {
            this.showValidationAlert('No se encontro ningun producto con el codigo escaneado: ' + code, 'Producto no encontrado');
        }
    };
}
</script>
@endpush
