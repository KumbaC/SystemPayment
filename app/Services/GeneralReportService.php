<?php

namespace App\Services;

use App\Models\EmployeePayment;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Setting;
use Carbon\Carbon;

class GeneralReportService
{
    public function periodRange(string $period): array
    {
        $today = Carbon::today();

        return match ($period) {
            'day' => [$today->copy(), $today->copy()],
            'week' => [Carbon::now()->startOfWeek(), $today->copy()],
            'month' => [Carbon::now()->startOfMonth(), $today->copy()],
            'year' => [Carbon::now()->startOfYear(), $today->copy()],
            default => [Carbon::now()->startOfMonth(), $today->copy()],
        };
    }

    public function periodLabel(string $period): string
    {
        return match ($period) {
            'day' => 'Día',
            'week' => 'Semana',
            'month' => 'Mes',
            'year' => 'Año',
            default => 'Mes',
        };
    }

    public function build(string $period): array
    {
        [$from, $to] = $this->periodRange($period);

        $sales = Sale::query()
            ->with(['customer', 'user'])
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->orderBy('sale_date')
            ->get();

        $purchases = Purchase::query()
            ->with(['supplier', 'user'])
            ->where('status', 'completed')
            ->whereBetween('purchase_date', [$from, $to])
            ->orderBy('purchase_date')
            ->get();

        $employeePayments = EmployeePayment::query()
            ->with(['employee', 'creator'])
            ->whereBetween('payment_date', [$from, $to])
            ->orderBy('payment_date')
            ->get();

        $salesProfit = (float) $sales->sum('profit_usd');
        $purchasesTotal = (float) $purchases->sum('total_usd');
        $employeeTotal = (float) $employeePayments->sum('amount_usd');
        $totalExpenses = $purchasesTotal + $employeeTotal;

        return [
            'period' => $period,
            'period_label' => $this->periodLabel($period),
            'from' => $from,
            'to' => $to,
            'company' => Setting::get('company_name', 'Mi Negocio'),
            'exchange_rate' => app(ExchangeRateService::class)->currentRate(),
            'summary' => [
                'sales_count' => $sales->count(),
                'sales_total_usd' => (float) $sales->sum('total_usd'),
                'sales_total_ves' => (float) $sales->sum('total_ves'),
                'sales_profit_usd' => $salesProfit,
                'sales_cost_usd' => (float) $sales->sum('cost_usd'),
                'purchases_count' => $purchases->count(),
                'purchases_total_usd' => $purchasesTotal,
                'employee_payments_count' => $employeePayments->count(),
                'employee_payments_usd' => $employeeTotal,
                'total_expenses_usd' => $totalExpenses,
                'net_profit_usd' => $salesProfit - $totalExpenses,
            ],
            'sales' => $sales,
            'purchases' => $purchases,
            'employee_payments' => $employeePayments,
        ];
    }
}
