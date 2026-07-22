@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar Usuario" />
    <x-common.flash-messages />

    <form action="{{ route('users.update', $user) }}" method="POST" class="max-w-xl rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="mb-1 block text-sm font-medium">Nombre</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Nueva contraseña (opcional)</label>
                <input type="password" name="password" class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Rol</label>
                <select name="role" required class="h-11 w-full rounded-lg border border-gray-300 px-4 dark:border-gray-700 dark:bg-gray-900">
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}" @selected($user->roles->first()?->name === $role->name)>{{ ucfirst($role->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm text-white">Guardar cambios</button>
            <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-6 py-2.5 text-sm">Cancelar</a>
        </div>
    </form>
@endsection
