<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralReportSalesSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected array $report) {}

    public function title(): string
    {
        return 'Ventas';
    }

    public function array(): array
    {
        $rows = [['Factura', 'N° Venta', 'Fecha', 'Cliente', 'Vendedor', 'Subtotal USD', 'IVA USD', 'Total USD', 'Total Bs', 'Ganancia USD']];

        foreach ($this->report['sales'] as $sale) {
            $rows[] = [
                $sale->invoice_number,
                $sale->sale_number,
                $sale->sale_date->format('d/m/Y'),
                $sale->customer?->name ?? 'Consumidor Final',
                $sale->user->name,
                number_format((float) $sale->subtotal_usd, 2, '.', ''),
                number_format((float) $sale->tax_usd, 2, '.', ''),
                number_format((float) $sale->total_usd, 2, '.', ''),
                number_format((float) $sale->total_ves, 2, '.', ''),
                number_format((float) $sale->profit_usd, 2, '.', ''),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF465FFF');
        $sheet->getStyle('A1:J1')->getFont()->getColor()->setARGB('FFFFFFFF');

        return [];
    }
}
