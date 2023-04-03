<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class TerminalsReportExport implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize, WithMapping, WithColumnFormatting
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
        return 'Reporte Mov. de Despachos';
    }

    public function headings(): array
    {   
        
            $array = [
                'Compañía',
                'Fecha de Emisión',
                'N° de Factura',
                'Proveedor',
                'Producto',
                'Nro de Guia',
                'Cisterna',
                'Tracto',
                'Cantidad',
                'Precio Kg soles',
                'Soles',
                'Carga Terminal',
                'SCOP',
                'Conductor',
                'TM',
                'Por Recojer',
                'N° de Pedido',
                'Precio Dolares',
                'N° Orden de Pedido',
                'Costo de Aprov.',               
                'Valor Venta',
                'I.G.V',
                'Precio Ponderado',
                'Destino',
                'Mezcla',
                'Fecha Ingreso Planta',               
                'Peso Inicial',
                'Peso Bruto',
                'Peso Neto',
                'CONCATENAR'
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
                $collection->article_name,
                $collection->referral_guide,
                $collection->license_plate,
                $collection->tracto,
                $collection->quantity,
                $collection->price_kg,
                $collection->soles,
                $collection->warehouse_short_name,
                $collection->scop_number,
                $collection->account_name,
                $collection->tm,
                $collection->rest,
                $collection->pedido_fact,
                $collection->price_dol,
                $collection->pedido_m,
                $collection->aprov,
                $collection->sub_total,
                $collection->igv,
                $collection->pond,
                $collection->destiny,
                $collection->mezcla,
                $collection->traslate_date,
                $collection->old_stock,
                $collection->old_stock_r,
                $collection->peso_neto,
                $collection->concat,
            ];
        
        
        return $array;
    }

    public function columnFormats(): array
    {
        
            return [
                'C' => NumberFormat::FORMAT_DATE_DDMMYYYY,
                'I' => NumberFormat::FORMAT_NUMBER,
            ];
        
    }
}
