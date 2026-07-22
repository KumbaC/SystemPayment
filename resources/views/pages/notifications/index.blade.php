@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Notificaciones" />
    <x-common.flash-messages />

    <form action="{{ route('notifications.read-all') }}" method="POST" class="mb-4">
        @csrf
        <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm dark:bg-gray-800">Marcar todas como leídas</button>
    </form>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-white/[0.03] {{ $notification->read_at ? 'opacity-70' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h4 class="font-medium text-gray-800 dark:text-white/90">{{ $notification->title }}</h4>
                        <p class="mt-1 text-sm text-gray-500">{{ $notification->message }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $notification->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if (! $notification->read_at)
                        <form action="{{ route('notifications.read', $notification) }}" method="POST">@csrf<button class="text-xs text-brand-500">Marcar leída</button></form>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-center text-gray-500 py-8">Sin notificaciones</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $notifications->links() }}</div>
@endsection
