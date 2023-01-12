<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class StockSalesRegisterReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting
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
                'Fecha de Despacho',
                'Factura',
                'Proveedor',
                'Carga Terminal',
                'Precio_TM Dolares',
                'Tipo',
                'Cantidad KG',
                'TC',
                'Pago Dolares',               
                'Conversión_soles',
                'Precio Kg Soles',
                'Valor Venta',
                'I.G.V',
                'Dolares Kgs',
                'Despacho',
                'GLP por recoger',
                'TM',
                'N° Orden de Pedido',
                'SCOP',
                'CONCATENAR',
            ];
        

        return $array;
    }

    public function map($collection): array
    {
        
            $array = [
                $collection->company_short_name,
                $collection->date,
                $collection->factura,
                $collection->warehouse_name,
                $collection->warehouse_short_name,
                $collection->precio_tm,
                $collection->article_name,
                $collection->quantity,
                $collection->tc,
                $collection->total,
                $collection->conv_soles,
                $collection->kg_soles,
                $collection->sub_soles,
                $collection->igv_soles,
                $collection->kg_dol,
                $collection->despacho,
                $collection->stock,
                $collection->tm,
                $collection->order_sale,
                $collection->scop_number,
                $collection->concat,
            ];
        
        
        return $array;
    }

    public function columnFormats(): array
    {
        
            return [
                'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'K' => NumberFormat::FORMAT_NUMBER,
            ];
        
    }
}
