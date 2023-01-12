<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class StockRegisterReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $collection;
    protected $valued;

    public function __construct($collection, $valued)
    {
        $this->collection = $collection;
        $this->valued = $valued;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function title(): string
    {
        return 'Reporte Mov. de Existencias';
    }

    public function headings(): array
    {   
        if ( empty($this->valued) ) {
            $array = [
                'Compañía',
                'Clase de Mov.',
                'Tipo de Mov.',
                'Parte',
                'Fecha',
                'Artículo',
                'Descripción',
                'Cantidad',
                'Estado Stock',
                'Tipo de Documento',
                'Documento',
                'Nombre o Razón Social',
                'Guía Remisión',
                'Tipo de Referencia',
                'Referencia',
                'SCOP',
                'Placa',
            ];
        } else {
            $array = [
                'Compañía',
                'Clase de Mov.',
                'Tipo de Mov.',
                'Parte',
                'Fecha',
                'Artículo',
                'Descripción',
                'Cantidad',
                'Estado Stock',
                'Precio Unit.',
                'Monto',
                'Tipo de Documento',
                'Documento',
                'Nombre o Razón Social',
                'Guía Remisión',
                'Tipo de Referencia',
                'Referencia',
                'SCOP',
                'Placa',
            ];
        }

        return $array;
    }

    public function map($collection): array
    {
        if ( empty($this->valued) ) {
            $array = [
                $collection->company_short_name,
                $collection->movement_class,
                $collection->movement_type,
                $collection->movement_number,
                $collection->date,
                $collection->article_code,
                $collection->article_name,
                $collection->quantity,
                $collection->movement_stock_type,
                $collection->account_document_type,
                $collection->account_document_number,
                $collection->account_name,
                $collection->referral_guide,
                $collection->reference_document_type,
                $collection->reference_document,
                $collection->scop_number,
                $collection->license_plate,
            ];
        } else {
            $array = [
                $collection->company_short_name,
                $collection->movement_class,
                $collection->movement_type,
                $collection->movement_number,
                $collection->date,
                $collection->article_code,
                $collection->article_name,
                $collection->quantity,
                $collection->movement_stock_type,
                $collection->price,
                $collection->sale_value,
                $collection->account_document_type,
                $collection->account_document_number,
                $collection->account_name,
                $collection->referral_guide,
                $collection->reference_document_type,
                $collection->reference_document,
                $collection->scop_number,
                $collection->license_plate,
            ];
        }
        return $array;
    }

    public function columnFormats(): array
    {
        if ( $this->valued ) {
            return [
                'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'H' => NumberFormat::FORMAT_NUMBER,
                'J' => NumberFormat::FORMAT_NUMBER,
                'K' => NumberFormat::FORMAT_NUMBER,
            ];
        } else {
            return [
                'E' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'H' => NumberFormat::FORMAT_NUMBER,
            ];
        }
    }
}
