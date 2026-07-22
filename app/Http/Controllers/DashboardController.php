<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use App\Services\ExchangeRateService;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboard,
        protected ExchangeRateService $exchangeRate
    ) {}

    public function index()
    {
        return view('pages.dashboard.business', [
            'title' => 'Dashboard',
            'metrics' => $this->dashboard->metrics(),
            'chartData' => $this->dashboard->dailyChart(),
            'recentSales' => $this->dashboard->recentSales(),
            'exchangeRate' => $this->exchangeRate->currentRate(),
        ]);
    }
}
