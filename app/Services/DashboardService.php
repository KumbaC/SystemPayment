<?php

namespace App\Services;

use App\Models\EmployeePayment;
use App\Models\PayableInvoice;
use App\Models\Purchase;
use App\Models\ReceivableInvoice;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function metrics(): array
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        $salesToday = $this->salesQuery($today, $today)->get();
        $salesWeek = $this->salesQuery($weekStart, $today)->get();
        $salesMonth = $this->salesQuery($monthStart, $today)->get();

        $purchasesMonth = Purchase::query()
            ->where('purchase_date', '>=', $monthStart->toDateString())
            ->where('purchase_date', '<=', $today->toDateString())
            ->where('status', 'completed')
            ->get();

        $employeePaymentsMonth = EmployeePayment::query()
            ->where('payment_date', '>=', $monthStart->toDateString())
            ->where('payment_date', '<=', $today->toDateString())
            ->sum('amount_usd');

        $receivables = ReceivableInvoice::query()->get();
        $payables = PayableInvoice::query()->get();

        $profitMonth = $salesMonth->sum('profit_usd');
        $expensesMonth = $purchasesMonth->sum('total_usd') + $employeePaymentsMonth;
        $revenueMonth = $salesMonth->sum('total_usd');

        return [
            'sales_today' => [
                'count' => $salesToday->count(),
                'total_usd' => $salesToday->sum('total_usd'),
                'total_ves' => $salesToday->sum('total_ves'),
                'profit_usd' => $salesToday->sum('profit_usd'),
            ],
            'sales_week' => [
                'count' => $salesWeek->count(),
                'total_usd' => $salesWeek->sum('total_usd'),
                'total_ves' => $salesWeek->sum('total_ves'),
                'profit_usd' => $salesWeek->sum('profit_usd'),
            ],
            'sales_month' => [
                'count' => $salesMonth->count(),
                'total_usd' => $salesMonth->sum('total_usd'),
                'total_ves' => $salesMonth->sum('total_ves'),
                'profit_usd' => $salesMonth->sum('profit_usd'),
            ],
            'profit_month_usd' => $profitMonth,
            'expenses_month_usd' => $expensesMonth,
            'employee_payments_month_usd' => $employeePaymentsMonth,
            'revenue_month_usd' => $revenueMonth,
            'net_profit_month_usd' => $profitMonth - $expensesMonth,
            'receivables' => [
                'count' => $receivables->count(),
                'total_ves' => (float) $receivables->sum('amount_ves'),
                'paid_ves' => (float) $receivables->sum('paid_ves'),
                'pending_ves' => (float) $receivables->sum(fn ($invoice) => $invoice->pendingAmount()),
            ],
            'payables' => [
                'count' => $payables->count(),
                'total_ves' => (float) $payables->sum('amount_ves'),
                'paid_ves' => (float) $payables->sum('paid_ves'),
                'pending_ves' => (float) $payables->sum(fn ($invoice) => $invoice->pendingAmount()),
            ],
            'exchange_rate' => app(ExchangeRateService::class)->currentRate(),
        ];
    }

    public function dailyChart(int $days = 30): Collection
    {
        $start = Carbon::now()->subDays($days - 1)->startOfDay();

        return Sale::query()
            ->where('status', 'completed')
            ->where('sale_date', '>=', $start)
            ->selectRaw('sale_date, COUNT(*) as count, SUM(total_usd) as total_usd, SUM(total_ves) as total_ves, SUM(profit_usd) as profit_usd')
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'asc')
            ->get();
    }

    public function recentSales(int $limit = 10): Collection
    {
        return Sale::query()
            ->with(['customer', 'user'])
            ->where('status', 'completed')
            ->latest('sale_date')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    protected function salesQuery(Carbon $from, Carbon $to)
    {
        return Sale::query()
            ->where('status', 'completed')
            ->where('sale_date', '>=', $from->toDateString())
            ->where('sale_date', '<=', $to->toDateString());
    }
}
