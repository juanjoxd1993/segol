<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class StockSeekRegisterReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $collection;
    

    public function __construct($collection)
    {
        $this->collection = $collection;
        
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
       
            $array = [
                'Compañía',
                'Fecha de Emisión',
                'Fecha de traslado',
                'Serie',
                'Guía Remisión',
                'Ruta',
                'Tipo',
                'Artículo',
                'Salida',               
                'Retorno Llenos',
                'SCOP',
                'Placa',
            ];
        
        

        return $array;
    }

    public function map($collection): array
    {
        
            $array = [
                $collection->company_short_name,

                $collection->date,
                $collection->traslate_date,
                $collection->referral_guide_serie,
                $collection->referral_guide,
                $collection->route_id,
                $collection->article_code,
                $collection->article_name,
                $collection->quantity,
                $collection->new_stock_return,
                $collection->scop_number,
                $collection->license_plate,
            ];
        
        return $array;
    }

    public function columnFormats(): array
    {
        
            return [
                'B' => NumberFormat::FORMAT_DATE_YYYYMMDDSLASH,
                'C' => NumberFormat::FORMAT_DATE_YYYYMMDDSLASH,
                'I' => NumberFormat::FORMAT_NUMBER,
            ];
        
    }
}
