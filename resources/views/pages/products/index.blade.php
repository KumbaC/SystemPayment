@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Productos / Inventario" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar producto..."
                class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
        </form>
        @can('products.manage')
            <a href="{{ route('products.create') }}" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white hover:bg-brand-600">+ Nuevo Producto</a>
        @endcan
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Producto</th>
                    <th class="px-4 py-3">Categoría</th>
                    <th class="px-4 py-3 text-right">Costo USD</th>
                    <th class="px-4 py-3 text-right">Precio USD</th>
                    <th class="px-4 py-3">IVA</th>
                    <th class="px-4 py-3 text-right">Ajuste precio</th>
                    <th class="px-4 py-3 text-right">Stock</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($products as $product)
                    <tr class="border-t border-gray-100 dark:border-gray-800 {{ $product->isLowStock() ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">
                        <td class="px-4 py-3">{{ $product->sku }}</td>
                        <td class="px-4 py-3 font-medium">{{ $product->name }}</td>
                        <td class="px-4 py-3">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($product->cost_usd, 2) }}</td>
                        <td class="px-4 py-3 text-right">${{ number_format($product->price_usd, 2) }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-4 text-sm">
                                <label class="inline-flex items-center gap-2">
                                    <input form="quick-update-{{ $product->id }}" type="radio" name="has_vat" value="1" @checked($product->has_vat) data-autosave-trigger class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                                    <span>Con IVA</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input form="quick-update-{{ $product->id }}" type="radio" name="has_vat" value="0" @checked(! $product->has_vat) data-autosave-trigger class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500">
                                    <span>Sin IVA</span>
                                </label>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <input form="quick-update-{{ $product->id }}" type="number" step="0.0001" min="0" name="price_usd" value="{{ number_format((float) $product->price_usd, 4, '.', '') }}" data-autosave-trigger
                                class="h-10 w-32 rounded-lg border border-gray-300 px-3 text-sm text-right dark:border-gray-700 dark:bg-gray-900">
                        </td>
                        <td class="px-4 py-3 text-right">{{ number_format($product->stock, 2) }} {{ $product->unit }}</td>
                        <td class="px-4 py-3 text-right">
                            @can('products.manage')
                                <form id="quick-update-{{ $product->id }}" action="{{ route('products.quick-update', $product) }}" method="POST" data-autosave-form class="inline-flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <a href="{{ route('products.edit', $product) }}" class="text-brand-500 hover:underline">Editar</a>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-8 text-center text-gray-500">No hay productos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const pendingByForm = new Map();
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const submitForm = async (form) => {
        if (!form || form.dataset.isSubmitting === '1') {
            return;
        }

        form.dataset.isSubmitting = '1';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrf,
                },
                body: new FormData(form),
            });

            if (!response.ok) {
                throw new Error('No se pudo guardar el ajuste automático.');
            }

            form.classList.add('ring-1', 'ring-green-300');
            setTimeout(() => form.classList.remove('ring-1', 'ring-green-300'), 700);
        } catch (error) {
            console.error(error);
            form.classList.add('ring-1', 'ring-red-300');
            setTimeout(() => form.classList.remove('ring-1', 'ring-red-300'), 1200);
        } finally {
            form.dataset.isSubmitting = '0';
        }
    };

    document.querySelectorAll('[data-autosave-trigger]').forEach((field) => {
        field.addEventListener('change', () => submitForm(field.form));

        if (field.type === 'number') {
            field.addEventListener('input', () => {
                const form = field.form;
                if (!form) {
                    return;
                }

                const key = form.id || form.action;
                if (pendingByForm.has(key)) {
                    clearTimeout(pendingByForm.get(key));
                }

                const timeoutId = setTimeout(() => {
                    submitForm(form);
                    pendingByForm.delete(key);
                }, 700);

                pendingByForm.set(key, timeoutId);
            });
        }
    });
});
</script>
@endpush
