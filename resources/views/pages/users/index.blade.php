@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Usuarios y Roles" />
    <x-common.flash-messages />

    <div class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
        <h3 class="mb-4 font-semibold">Nuevo Usuario</h3>
        <form action="{{ route('users.store') }}" method="POST" class="grid grid-cols-1 gap-4 md:grid-cols-5">
            @csrf
            <input type="text" name="name" placeholder="Nombre" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <input type="email" name="email" placeholder="Email" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <input type="password" name="password" placeholder="Contraseña" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <select name="role" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
                @foreach ($roles as $role)
                    <option value="{{ $role->name }}">{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-lg bg-brand-500 px-4 text-sm text-white">Crear</button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50">
                <tr class="text-left text-gray-500">
                    <th class="px-4 py-3">Nombre</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Rol</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @can('users.edit')
                                <a href="{{ route('users.edit', $user) }}" class="text-brand-500 text-sm">Editar</a>
                            @endcan
                            @if ($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar usuario?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 text-sm">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>

    <div class="mt-6 rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm dark:border-gray-700 dark:bg-gray-900/30">
        <p class="font-medium mb-2">Roles del sistema:</p>
        <ul class="list-disc pl-5 space-y-1 text-gray-600 dark:text-gray-400">
            <li><strong>administrador</strong> — Acceso total</li>
            <li><strong>gerente</strong> — Ventas, compras, inventario y reportes</li>
            <li><strong>vendedor</strong> — Solo ventas y clientes</li>
            <li><strong>contador</strong> — Reportes y consultas</li>
        </ul>
    </div>
@endsection
