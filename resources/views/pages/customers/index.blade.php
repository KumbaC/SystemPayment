@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Clientes" />
    <x-common.flash-messages />

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar cliente por nombre o cédula..."
                class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
        </form>
    </div>

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="mb-4 font-semibold">Registrar Cliente</h3>
        <form action="{{ route('customers.store') }}" method="POST" class="grid grid-cols-1 gap-3 md:grid-cols-6">
            @csrf
            <input type="text" name="name" placeholder="Nombre / Razón Social *" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm md:col-span-2 dark:border-gray-700 dark:bg-gray-900">
            <select name="document_type" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                <option value="V">V</option><option value="E">E</option><option value="J">J</option><option value="G">G</option><option value="P">P</option>
            </select>
            <input type="text" name="document_number" placeholder="Cédula/RIF" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <input type="text" name="phone" placeholder="Teléfono" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <button type="submit" class="rounded-lg bg-brand-500 px-4 text-sm text-white">Guardar</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Documento</th><th class="px-4 py-3">Teléfono</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @forelse ($customers as $customer)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $customer->name }}</td>
                        <td class="px-4 py-3">{{ $customer->fullDocument() ?: '-' }}</td>
                        <td class="px-4 py-3">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="text-red-500 text-sm">Eliminar</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Sin clientes</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $customers->links() }}</div>
@endsection
