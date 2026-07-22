<?php

namespace App\Http\Controllers;

use App\Exports\GeneralReportExport;
use App\Exports\SalesReportExport;
use App\Models\Sale;
use App\Services\DashboardService;
use App\Services\GeneralReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard,
        protected GeneralReportService $generalReport
    ) {}

    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->periodRange($period);

        $sales = Sale::query()
            ->with(['customer', 'user'])
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$from, $to])
            ->latest('sale_date')
            ->get();

        return view('pages.reports.index', [
            'title' => 'Reportes',
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'sales' => $sales,
            'metrics' => [
                'count' => $sales->count(),
                'total_usd' => $sales->sum('total_usd'),
                'total_ves' => $sales->sum('total_ves'),
                'profit_usd' => $sales->sum('profit_usd'),
                'cost_usd' => $sales->sum('cost_usd'),
            ],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $period = $request->get('period', 'month');
        [$from, $to] = $this->periodRange($period);

        $filename = 'reporte_ventas_'.$from->format('Y-m-d').'_'.$to->format('Y-m-d').'.xlsx';

        return Excel::download(new SalesReportExport($from, $to), $filename);
    }

    public function general(Request $request)
    {
        $period = $request->get('period', 'month');
        $report = $this->generalReport->build($period);

        return view('pages.reports.general', [
            'title' => 'Reporte General',
            'period' => $period,
            'report' => $report,
        ]);
    }

    public function generalExport(Request $request): BinaryFileResponse
    {
        $period = $request->get('period', 'month');
        $report = $this->generalReport->build($period);
        $filename = 'reporte_general_'.$report['from']->format('Y-m-d').'_'.$report['to']->format('Y-m-d').'.xlsx';

        return Excel::download(new GeneralReportExport($report), $filename);
    }

    protected function periodRange(string $period): array
    {
        $today = Carbon::today();

        return match ($period) {
            'day' => [$today, $today],
            'week' => [Carbon::now()->startOfWeek(), $today],
            default => [Carbon::now()->startOfMonth(), $today],
        };
    }
}
