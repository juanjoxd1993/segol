<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesVolumeReportExport implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithMapping
{   
    protected $collection;

    public function __construct(array $collection)
    {
        $this->collection = $collection;
    }

    public function array(): array
    {
        return $this->collection;
    }

    public function title(): string
    {
        return 'Reporte de Ventas';
    }

    public function headings(): array
    {
        return [
            'ID Doc.',
            'Tipo de Doc.',
            'Serie',
            'Nº Inicial',
            'Nº Final',
            'Fec. Emisión',
            'Cód. Artículo',
            'Descripción',
            'Cantidad',
            'Valor Venta',
            'IGV',
            'Total'
        ];
    }

    public function map($collection): array
    {
        return [
            $collection->voucher_type_type,
            $collection->voucher_type_name,
            $collection->serie_number,
            $collection->initial_voucher_number,
            $collection->final_voucher_number,
            $collection->issue_date,
            $collection->article_code,
            $collection->article_name,
            $collection->sum_quantity,
            $collection->sum_sale_value,
            $collection->sum_igv,
            $collection->sum_total,
        ];
    }
}
