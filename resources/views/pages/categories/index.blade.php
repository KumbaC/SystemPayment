@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Categorías" />
    <x-common.flash-messages />

    <form action="{{ route('categories.store') }}" method="POST" class="mb-6 flex gap-3">
        @csrf
        <input type="text" name="name" placeholder="Nombre de categoría" required class="h-10 flex-1 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <button type="submit" class="rounded-lg bg-brand-500 px-4 text-sm text-white">Agregar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Productos</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $category->name }}</td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="text-red-500 text-sm">Eliminar</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">Sin categorías</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
@endsection
