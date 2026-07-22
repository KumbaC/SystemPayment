<?php

namespace App\Exports;

use App\Services\GeneralReportService;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class GeneralReportExport implements WithMultipleSheets
{
    public function __construct(protected array $report) {}

    public function sheets(): array
    {
        return [
            new Sheets\GeneralReportSummarySheet($this->report),
            new Sheets\GeneralReportSalesSheet($this->report),
            new Sheets\GeneralReportPurchasesSheet($this->report),
            new Sheets\GeneralReportEmployeePaymentsSheet($this->report),
        ];
    }

    public static function make(string $period): self
    {
        return new self(app(GeneralReportService::class)->build($period));
    }
}
