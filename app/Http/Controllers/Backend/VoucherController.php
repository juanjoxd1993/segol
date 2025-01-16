<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Sale;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{


    public function index()
    {

        //GRAFICO DE LINEAS
        $lista = Sale::leftJoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
            ->leftJoin('articles', 'articles.id', '=', 'sale_details.article_id')
            ->selectRaw("
                DATE(sales.sale_date) as fecha_emision,
                SUBSTRING_INDEX(articles.name, ' ', -2) as weight,  -- Extraemos el peso (10 KG, 45 KG)
                SUM(sale_details.quantity) as total_quantity
            ")
            ->where('sales.company_id', 2)
            ->whereIn('cede',[1, 4, 13, 75])
            ->whereIn('sales.warehouse_document_type_id', [5, 31]) // Factura electrónica, Resumen de Boletas
            ->groupBy('fecha_emision', 'weight')
            ->orderBy('fecha_emision', 'DESC')
            ->take(20)
            ->get();

        $lista = $lista->reverse();

        $resultadosPorFecha = [];
        foreach ($lista as $item) {
            $resultadosPorFecha[$item->fecha_emision][] = [
                'weight' => $item->weight,
                'total_quantity' => $item->total_quantity,
            ];
        }

        //GRAFICO DE BARRAS

        $barras = Sale::select(
            'sale_date',
            DB::Raw('SUM(total_perception) as venta_total')
        )
            ->where('company_id', 2)
            ->whereIn('cede',[1, 4, 13, 75])
            ->whereIn('warehouse_document_type_id', [5,13, 31]) // Factura electrónica,NOTA DE PEDIDO, Resumen de Boletas
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'DESC')
            ->take(4)
            ->get();

        $barras = $barras->reverse();

        $barrasData = $barras->map(function ($item) {
            return [
                'x' => $item->sale_date,
                'y' => (float) $item->venta_total,
            ];
        })->values();


        return view('backend.voucher_send_ose', compact('resultadosPorFecha', 'barrasData'));
    }
}
