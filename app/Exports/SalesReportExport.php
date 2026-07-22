<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class SalesReportExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        protected Carbon $from,
        protected Carbon $to
    ) {}

    public function collection(): Collection
    {
        return Sale::query()
            ->with(['customer', 'user'])
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$this->from, $this->to])
            ->orderBy('sale_date')
            ->get();
    }

    public function headings(): array
    {
        return [
            'N° Venta',
            'N° Factura',
            'Fecha',
            'Cliente',
            'Documento',
            'Vendedor',
            'Subtotal USD',
            'IVA USD',
            'Total USD',
            'Total Bs',
            'Costo USD',
            'Ganancia USD',
            'Tasa BCV',
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_number,
            $sale->invoice_number,
            $sale->sale_date->format('d/m/Y'),
            $sale->customer?->name ?? 'Consumidor Final',
            $sale->customer?->fullDocument() ?? '',
            $sale->user->name,
            number_format((float) $sale->subtotal_usd, 2, '.', ''),
            number_format((float) $sale->tax_usd, 2, '.', ''),
            number_format((float) $sale->total_usd, 2, '.', ''),
            number_format((float) $sale->total_ves, 2, '.', ''),
            number_format((float) $sale->cost_usd, 2, '.', ''),
            number_format((float) $sale->profit_usd, 2, '.', ''),
            number_format((float) $sale->exchange_rate, 4, '.', ''),
        ];
    }

    public function title(): string
    {
        return 'Ventas';
    }
}
