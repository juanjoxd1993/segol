<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromArray, WithTitle, WithHeadings, ShouldAutoSize, WithMapping
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
            'Serie',
            'Doc. Inicial',
            'Doc. Final',
            'Fec. Emisión',
            'RUC/DNI/CE',
            'Razón Social',
            'Valor Venta',
            'Valor Inafecto',
            'Valor Exonerado',
            'IGV',
            'Total',
            'Serie de Ref.',
            'Nº de Ref.',
            'Fecha de Ref.',
            'Método de Pago',
        ];
    }

    public function map($collection): array
    {
        return [
            $collection->voucher_type,
            $collection->serie_number,
            $collection->voucher_number_since,
            $collection->voucher_number_to,
            $collection->issue_date,
            $collection->client_document_number,
            $collection->client_name,
            $collection->taxed_operation,
            $collection->unaffected_operation,
            $collection->exonerated_operation,
            $collection->igv,
            $collection->total,
            $collection->credit_note_reference_serie,
            $collection->credit_note_reference_number,
            $collection->credit_note_reference_date,
            $collection->payment_name,
        ];
    }
}
