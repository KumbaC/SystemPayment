<?php

namespace App\Http\Controllers;

use App\Models\DatabaseBackup;
use App\Models\EmployeePayment;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class EmployeePaymentController extends Controller
{
    public function __construct(
        protected ExchangeRateService $exchangeRate,
        protected ActivityLogService $activityLog
    ) {}

    public function index()
    {
        return view('pages.employee-payments.index', [
            'title' => 'Pagos a Empleados',
            'payments' => EmployeePayment::query()->with(['employee', 'creator'])->latest()->paginate(15),
            'employees' => User::query()->orderBy('name')->get(),
            'totalPaid' => EmployeePayment::query()->sum('amount_usd'),
            'exchangeRate' => $this->exchangeRate->currentRate(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'payment_date' => ['required', 'date'],
            'amount_usd' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $payment = EmployeePayment::query()->create([
            ...$data,
            'created_by' => auth()->id(),
            'exchange_rate' => $this->exchangeRate->currentRate(),
        ]);

        $employee = User::query()->find($data['user_id']);
        $this->activityLog->log(
            'employee_payment',
            "Pago a empleado {$employee->name} por \${$data['amount_usd']}",
            $payment
        );

        return back()->with('success', 'Pago a empleado registrado.');
    }

    public function destroy(EmployeePayment $employeePayment)
    {
        $this->activityLog->log('delete', 'Eliminó pago a empleado #'.$employeePayment->id, $employeePayment);
        $employeePayment->delete();

        return back()->with('success', 'Pago eliminado.');
    }
}
