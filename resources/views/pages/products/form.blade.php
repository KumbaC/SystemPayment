@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="$product->exists ? 'Editar Producto' : 'Nuevo Producto'" />
    <x-common.flash-messages />

    <form action="{{ $product->exists ? route('products.update', $product) : route('products.store') }}" method="POST"
        class="max-w-3xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium">SKU *</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Nombre *</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Categoría</label>
                <select name="category_id" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                    <option value="">Sin categoría</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $product->category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Unidad</label>
                <input type="text" name="unit" value="{{ old('unit', $product->unit ?? 'und') }}" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Costo USD *</label>
                <input type="number" step="0.0001" name="cost_usd" value="{{ old('cost_usd', $product->cost_usd ?? 0) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Precio Venta USD *</label>
                <input type="number" step="0.0001" name="price_usd" value="{{ old('price_usd', $product->price_usd ?? 0) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Stock</label>
                <input type="number" step="0.0001" name="stock" value="{{ old('stock', $product->stock ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Stock Mínimo</label>
                <input type="number" step="0.0001" name="min_stock" value="{{ old('min_stock', $product->min_stock ?? 0) }}" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-medium">Descripción</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2 dark:border-gray-700 dark:bg-gray-900">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="active" value="1" @checked(old('active', $product->active ?? true)) class="rounded">
                <label class="text-sm">Activo</label>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="has_vat" value="1" @checked(old('has_vat', $product->has_vat ?? true)) class="rounded">
                <label class="text-sm">Aplica IVA</label>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm text-white">Guardar</button>
            <a href="{{ route('products.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm">Cancelar</a>
        </div>
    </form>
@endsection
