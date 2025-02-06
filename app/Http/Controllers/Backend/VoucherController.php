<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\Client;
use App\Http\Controllers\Controller;
use App\Sale;
use Illuminate\Support\Facades\App;
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
            ->whereIn('cede', [1, 4, 13, 75])
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
            ->whereIn('cede', [1, 4, 13, 75])
            ->whereIn('warehouse_document_type_id', [5, 13, 31]) // Factura electrónica,NOTA DE PEDIDO, Resumen de Boletas
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

        //GRAFICO DE PORCENTAJE

        $articles = Article::leftjoin('warehouse_types', 'warehouse_types.id', '=', 'articles.warehouse_type_id')
            ->select(
                'warehouse_types.name as warehouse_type_name',
                'articles.name as article_name',
                'articles.stock_good as article_stock',
                'articles.stock_minimum as article_minimum'
            )
            ->whereIn('articles.id', [4952, 4953, 4959, 4791, 4792])
            ->get();

        //Numero de Clientes

        $clients = Client::where('company_id', 2)
            ->count();

        //GRAFICO DE Stock de Articulos

        $articles_stock = Article::leftjoin('warehouse_types', 'warehouse_types.id', '=', 'articles.warehouse_type_id')
            ->select(
                DB::Raw('CONCAT(warehouse_types.name," ::: ",articles.name) AS producto'),
                'articles.stock_good as article_stock',
            )
            ->whereIn('articles.id', [4841, 4846, 4844, 4848, 4954, 4956, 4957, 4958])
            ->get();


        $tipo_ventas = Sale::leftjoin('liquidations', 'liquidations.sale_id', '=', 'sales.id')
            ->select(
                'sales.sale_date as fecha',
                DB::Raw('SUM(sales.total_perception - sales.pre_balance) as total_contado'),
                DB::Raw('SUM(sales.pre_balance) as total_credito'),
            )
            ->where('sales.company_id', 2)
            ->whereIn('sales.cede', [1, 4, 13, 75])
            ->whereIn('sales.warehouse_document_type_id', [5, 13, 31]) // Factura electrónica,NOTA DE PEDIDO, Resumen de Boletas
            ->groupBy('sales.sale_date')
            ->orderBy('sales.sale_date', 'DESC')
            ->take(4)
            ->get();

        $tipo_ventas = $tipo_ventas->reverse();

        //AQUI

        DB::statement("SET lc_time_names = 'es_ES';");
        $tipo_ventas_porcentaje = Sale::leftJoin('liquidations', 'liquidations.sale_id', '=', 'sales.id')
            ->select(
                DB::raw("DATE_FORMAT(sales.sale_date, '%Y-%M') as mes"),
                DB::raw("SUM(sales.total_perception - sales.pre_balance) as total_contado"),
                DB::raw("SUM(sales.pre_balance) as total_credito")
            )
            ->where('sales.company_id', 2)
            ->whereIn('sales.cede', [1, 4, 13, 75])
            ->whereIn('sales.warehouse_document_type_id', [5, 13, 31]) // Factura electrónica, NOTA DE PEDIDO, Resumen de Boletas
            ->groupBy(DB::raw("DATE_FORMAT(sales.sale_date, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(sales.sale_date, '%Y-%m')"), 'DESC')
            ->take(4)
            ->get();

        $tipo_ventas_porcentaje = $tipo_ventas_porcentaje->map(function ($item) {
            $totalGeneral = $item->total_contado + $item->total_credito;
            $item->porcentaje_contado = ($totalGeneral > 0) ? ($item->total_contado / $totalGeneral) * 100 : 0;
            $item->porcentaje_credito = ($totalGeneral > 0) ? ($item->total_credito / $totalGeneral) * 100 : 0;
            return $item;
        });

        return view('backend.voucher_send_ose', compact('resultadosPorFecha', 'barrasData', 'articles', 'clients', 'articles_stock', 'tipo_ventas', 'tipo_ventas_porcentaje'));
    }
}
