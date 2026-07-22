@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Respaldos de Base de Datos" />
    <x-common.flash-messages />

    <div class="mb-4 flex justify-between items-center">
        <p class="text-sm text-gray-500">Los respaldos se guardan en la carpeta <code>backup/</code> del proyecto y en <code>storage/app/backups</code>.</p>
        <form action="{{ route('backups.store') }}" method="POST">
            @csrf
            <button type="submit" class="rounded-lg bg-brand-500 px-4 py-2 text-sm text-white">Crear respaldo ahora</button>
        </form>
    </div>

    <p class="mb-4 text-sm text-gray-500">Respaldo automático programado diariamente a las 2:00 AM. Ejecute <code>php artisan schedule:work</code> en producción.</p>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Archivo</th><th class="px-4 py-3">Tamaño</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Notas</th></tr></thead>
            <tbody>
                @forelse ($backups as $backup)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $backup->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ $backup->filename }}</td>
                        <td class="px-4 py-3">{{ number_format($backup->size / 1024, 1) }} KB</td>
                        <td class="px-4 py-3">{{ $backup->status }}</td>
                        <td class="px-4 py-3">{{ $backup->notes }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Sin respaldos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $backups->links() }}</div>
@endsection
