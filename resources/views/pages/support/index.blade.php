@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Soporte" />
    <x-common.flash-messages />

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold">WhatsApp</h3>
            <p class="mb-4 text-sm text-gray-500">Contáctanos directamente por WhatsApp para soporte rápido.</p>
            @if ($whatsappLink)
                <a href="{{ $whatsappLink }}" target="_blank" rel="noopener"
                    class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-5 py-3 text-sm font-medium text-white hover:bg-green-700">
                    Enviar WhatsApp
                </a>
                <p class="mt-3 text-xs text-gray-500">{{ $whatsappNumber }}</p>
            @else
                <p class="text-sm text-amber-600">Configure el número de WhatsApp en Configuración.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
            <h3 class="mb-4 text-lg font-semibold">Correo de Soporte</h3>
            <form action="{{ route('support.email') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium">Asunto</label>
                    <input type="text" name="subject" required class="h-10 w-full rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Mensaje</label>
                    <textarea name="message" rows="5" required class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900"></textarea>
                </div>
                <button type="submit" class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm text-white">Enviar correo</button>
            </form>
            @if ($supportEmail)
                <p class="mt-3 text-xs text-gray-500">Se enviará a: {{ $supportEmail }}</p>
            @endif
        </div>
    </div>
@endsection
