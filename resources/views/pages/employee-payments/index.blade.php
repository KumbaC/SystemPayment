@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Pagos a Empleados" />
    <x-common.flash-messages />

    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm dark:border-amber-800 dark:bg-amber-900/20">
        Total pagado a empleados (histórico): <strong>${{ number_format($totalPaid, 2) }}</strong> — Estos montos se restan del saldo del negocio en el dashboard.
    </div>

    <form action="{{ route('employee-payments.store') }}" method="POST" class="mb-6 grid grid-cols-1 gap-3 rounded-2xl border border-gray-200 bg-white p-5 md:grid-cols-6 dark:border-gray-800 dark:bg-white/[0.03]">
        @csrf
        <select name="user_id" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm md:col-span-2 dark:border-gray-700 dark:bg-gray-900">
            <option value="">Empleado (usuario)...</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
            @endforeach
        </select>
        <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <input type="number" step="0.01" name="amount_usd" placeholder="Monto USD" required class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
        <select name="payment_method" class="h-10 rounded-lg border border-gray-300 px-3 text-sm dark:border-gray-700 dark:bg-gray-900">
            <option value="transferencia">Transferencia</option>
            <option value="efectivo_usd">Efectivo USD</option>
            <option value="pago_movil">Pago Móvil</option>
        </select>
        <button type="submit" class="rounded-lg bg-brand-500 px-4 text-sm text-white">Registrar pago</button>
        <textarea name="notes" rows="1" placeholder="Notas..." class="md:col-span-6 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900"></textarea>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50"><tr class="text-left text-gray-500"><th class="px-4 py-3">Fecha</th><th class="px-4 py-3">Empleado</th><th class="px-4 py-3">Monto USD</th><th class="px-4 py-3">Método</th><th class="px-4 py-3">Registrado por</th><th class="px-4 py-3"></th></tr></thead>
            <tbody>
                @forelse ($payments as $payment)
                    <tr class="border-t border-gray-100 dark:border-gray-800">
                        <td class="px-4 py-3">{{ $payment->payment_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ $payment->employee->name }}</td>
                        <td class="px-4 py-3">${{ number_format($payment->amount_usd, 2) }}</td>
                        <td class="px-4 py-3">{{ $payment->payment_method }}</td>
                        <td class="px-4 py-3">{{ $payment->creator->name }}</td>
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('employee-payments.destroy', $payment) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="text-red-500 text-sm">Eliminar</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Sin pagos registrados</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
