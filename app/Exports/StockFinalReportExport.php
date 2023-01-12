<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class StockFinalReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting
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
                'Fecha Traslado',
                'Fecha Despacho',
                'Guía Remisión',
                'Ruta',
                'Placa',
                'Artículo',
                'Descripción',
                'Cantidad',
                'Retorno',
                'Estado Stock',
                'Tipo de Documento',
                'Documento',
                'Nombre o Razón Social',
                'Tipo de Referencia',
                'Referencia',
                'SCOP',
                
            ];
        } else {
            $array = [
                'Compañía',
                'Clase de Mov.',
                'Tipo de Mov.',
                'Parte',
                'Fecha Traslado',
                'Fecha Despacho',
                'Guía Remisión',
                'Ruta',
                'Placa',
                'Artículo',
                'Descripción',
                'Cantidad',
                'Retorno',
                'Estado Stock',
                'Precio Unit.',
                'Monto',
                'Tipo de Documento',
                'Documento',
                'Nombre o Razón Social',
                'Tipo de Referencia',
                'Referencia',
                'SCOP',
               
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
                $collection->traslate_date,
                $collection->date,
                $collection->referral_guide,
                $collection->route_id,
                $collection->license_plate,
                $collection->article_code,
                $collection->article_name,
                $collection->quantity,
                $collection->new_stock_return,
                $collection->movement_stock_type,
                $collection->account_document_type,
                $collection->account_document_number,
                $collection->account_name,
                $collection->reference_document_type,
                $collection->reference_document,
                $collection->scop_number,
                
            ];
        } else {
            $array = [
                $collection->company_short_name,
                $collection->movement_class,
                $collection->movement_type,
                $collection->movement_number,
                $collection->traslate_date,
                $collection->date,
                $collection->referral_guide,
                $collection->route_id,
                $collection->license_plate,
                $collection->article_code,
                $collection->article_name,
                $collection->quantity,
                $collection->new_stock_return,
                $collection->movement_stock_type,
                $collection->price,
                $collection->sale_value,
                $collection->account_document_type,
                $collection->account_document_number,
                $collection->account_name,               
                $collection->reference_document_type,
                $collection->reference_document,
                $collection->scop_number,
                
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
                'F' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'I' => NumberFormat::FORMAT_NUMBER,
            ];
        }
    }
}
