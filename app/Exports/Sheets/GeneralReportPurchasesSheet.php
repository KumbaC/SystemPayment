<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralReportPurchasesSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected array $report) {}

    public function title(): string
    {
        return 'Compras';
    }

    public function array(): array
    {
        $rows = [['N° Compra', 'Fecha', 'Proveedor', 'Registrado por', 'Subtotal USD', 'IVA USD', 'Total USD', 'Tasa BCV']];

        foreach ($this->report['purchases'] as $purchase) {
            $rows[] = [
                $purchase->purchase_number,
                $purchase->purchase_date->format('d/m/Y'),
                $purchase->supplier->name,
                $purchase->user->name,
                number_format((float) $purchase->subtotal_usd, 2, '.', ''),
                number_format((float) $purchase->tax_usd, 2, '.', ''),
                number_format((float) $purchase->total_usd, 2, '.', ''),
                number_format((float) $purchase->exchange_rate, 4, '.', ''),
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF04438');
        $sheet->getStyle('A1:H1')->getFont()->getColor()->setARGB('FFFFFFFF');

        return [];
    }
}
