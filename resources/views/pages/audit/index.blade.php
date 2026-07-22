@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Auditoría del Sistema" />
    <x-common.flash-messages />

    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar actividad..." class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <button class="rounded-lg bg-gray-100 px-4 text-sm dark:bg-gray-800">Buscar</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">Fecha</th>
                    <th class="px-4 py-3">Usuario</th>
                    <th class="px-4 py-3">Acción</th>
                    <th class="px-4 py-3">Descripción</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $log->user?->name ?? 'Sistema' }}</td>
                        <td class="px-4 py-3"><span class="rounded bg-gray-100 px-2 py-0.5 text-xs dark:bg-gray-800">{{ $log->action }}</span></td>
                        <td class="px-4 py-3">{{ $log->description }}</td>
                        <td class="px-4 py-3">{{ $log->ip_address }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Sin registros de auditoría</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>
@endsection
