@props(['products' => [], 'name' => 'product_id', 'model' => 'product_id', 'required' => true])

<div x-data="productSearch(@js($products), '{{ $name }}')" class="relative">
    <input type="hidden" :name="fieldName" :value="selectedId" @if($required) required @endif>
    <input type="text" x-model="search" @focus="open = true" @click="open = true" @input="open = true"
        placeholder="Buscar producto..."
        class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
    <div x-show="open" @click.outside="open = false"
        class="absolute z-50 mt-1 max-h-48 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
        <template x-for="product in filtered" :key="product.id">
            <button type="button" @click="select(product)"
                class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-800">
                <span x-text="product.name"></span>
                <span class="text-xs text-gray-500" x-text="' · Stock: ' + product.stock + ' · $' + product.price_usd.toFixed(2)"></span>
            </button>
        </template>
        <div x-show="filtered.length === 0" class="px-3 py-2 text-sm text-gray-500">Sin resultados</div>
    </div>
</div>

@once
@push('scripts')
<script>
function productSearch(products, fieldName) {
    return {
        products, fieldName, open: false, search: '', selectedId: '',
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.products.slice(0, 20);
            return this.products.filter(p =>
                p.name.toLowerCase().includes(q) || String(p.sku).toLowerCase().includes(q)
            ).slice(0, 20);
        },
        select(product) {
            this.selectedId = product.id;
            this.search = product.name;
            this.open = false;
            this.$dispatch('product-selected', { id: product.id, price: product.price_usd, stock: product.stock });
        }
    };
}
</script>
@endpush
@endonce
