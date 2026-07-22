<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GeneralReportEmployeePaymentsSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected array $report) {}

    public function title(): string
    {
        return 'Pagos Empleados';
    }

    public function array(): array
    {
        $rows = [['Fecha', 'Empleado', 'Monto USD', 'Método', 'Registrado por', 'Notas']];

        foreach ($this->report['employee_payments'] as $payment) {
            $rows[] = [
                $payment->payment_date->format('d/m/Y'),
                $payment->employee->name,
                number_format((float) $payment->amount_usd, 2, '.', ''),
                $payment->payment_method,
                $payment->creator->name,
                $payment->notes ?? '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF79009');
        $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB('FFFFFFFF');

        return [];
    }
}
