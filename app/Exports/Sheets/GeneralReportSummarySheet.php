<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralReportSummarySheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected array $report) {}

    public function title(): string
    {
        return 'Resumen';
    }

    public function array(): array
    {
        $s = $this->report['summary'];
        $from = $this->report['from']->format('d/m/Y');
        $to = $this->report['to']->format('d/m/Y');

        return [
            ['REPORTE GENERAL DEL NEGOCIO'],
            [$this->report['company']],
            ['Período', $this->report['period_label'].' ('.$from.' - '.$to.')'],
            ['Tasa Bs/USD', number_format($this->report['exchange_rate'], 4, ',', '.')],
            [],
            ['INDICADOR', 'VALOR'],
            ['Cantidad de ventas', $s['sales_count']],
            ['Total ventas USD', '$'.number_format($s['sales_total_usd'], 2, '.', ',')],
            ['Total ventas Bs', 'Bs. '.number_format($s['sales_total_ves'], 2, ',', '.')],
            ['Ganancia bruta ventas USD', '$'.number_format($s['sales_profit_usd'], 2, '.', ',')],
            ['Costo de mercancía vendida USD', '$'.number_format($s['sales_cost_usd'], 2, '.', ',')],
            [],
            ['Cantidad de compras', $s['purchases_count']],
            ['Total compras USD', '$'.number_format($s['purchases_total_usd'], 2, '.', ',')],
            [],
            ['Pagos a empleados (cantidad)', $s['employee_payments_count']],
            ['Pagos a empleados USD', '$'.number_format($s['employee_payments_usd'], 2, '.', ',')],
            [],
            ['Total gastos USD (compras + nómina)', '$'.number_format($s['total_expenses_usd'], 2, '.', ',')],
            ['RESULTADO NETO USD', '$'.number_format($s['net_profit_usd'], 2, '.', ',')],
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:B1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A6:B6')->getFont()->setBold(true);
        $sheet->getStyle('A6:B6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF465FFF');
        $sheet->getStyle('A6:B6')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A18:B18')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A18:B18')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF12B76A');
        $sheet->getStyle('A18:B18')->getFont()->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:B18')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        return [];
    }
}
