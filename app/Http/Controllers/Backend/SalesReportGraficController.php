<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Company;
use App\Exports\ChartExport;
use App\VoucherType;
use App\Exports\SalesVolumeReportExport;
use App\Sale;
use App\Voucher;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SalesReportGraficController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name')->whereIn('id', [1])->get();
        $current_date = date(DATE_ATOM, mktime(0, 0, 0));

        return view('backend.sales_grafic_report')->with(compact('companies', 'current_date'));
    }

    public function validateForm()
    {
        $messages = [
            'company_id.required'       => 'Debe seleccionar una Compañía.',
            'since_date.required'       => 'Debe seleccionar una Fecha de Inicio.',
            'to_date.required'          => 'Debe seleccionar una Fecha Final.',
        ];

        $rules = [
            'company_id'        => 'required',
            'since_date'        => 'required',
            'to_date'            => 'required',

        ];

        request()->validate($rules, $messages);
        return request()->all();
    }

    public function list()
    {
        $company_id = request('model.company_id');
        $since_date = date('Y-m-d', strtotime(request('model.since_date')));
        $to_date = (request('model.to_date') ? date('Y-m-d', strtotime(request('model.to_date'))) : date('Y-m-d'));

        $elements = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
            ->leftjoin('articles', function ($join) {
                $join->on('voucher_details.name', '=', 'articles.name')
                    ->where('articles.warehouse_type_id', 5);
            })
            ->leftjoin('voucher_types', 'vouchers.voucher_type_id', '=', 'voucher_types.id')
            ->where('vouchers.company_id', $company_id)
            // ->where('vouchers.ose', 1)
            ->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
            ->where('vouchers.voucher_type_id', 2)
            ->where('vouchers.issue_date', '>=', $since_date)
            ->where('vouchers.issue_date', '<=', $to_date)
            ->whereNotIn('vouchers.ose', [3])
            ->select(
                'voucher_types.type as voucher_type_type',
                'voucher_types.name as voucher_type_name',
                'serie_number',
                'clients.document_number as document_number',
                'clients.business_name as client_name',
                DB::Raw('MIN(voucher_number) as initial_voucher_number'),
                DB::Raw('MAX(voucher_number) as final_voucher_number'),
                'issue_date',
                'articles.code as article_code',
                'voucher_details.name as article_name',
                DB::Raw('SUM(voucher_details.quantity) as sum_quantity'),
                DB::Raw('SUM(vouchers.taxed_operation) as sum_sale_value'),
                DB::Raw('SUM(vouchers.igv) as sum_igv'),
                DB::Raw('SUM(vouchers.total) as sum_total'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 24) as gallons'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 23) as sum_1k'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 55) AS sum_5k'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 56) AS sum_10k'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) from vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 57) AS sum_15k'),
                DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 58) AS sum_45k'),
                DB::Raw('(SELECT SUM(voucher_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = voucher_details.article_id AND voucher_details.article_id <> 24)) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id) AS sum_tm_total')
            )
            //	->groupBy('voucher_type_type', 'serie_number', 'issue_date', 'article_code', 'article_name')
            ->groupBy('serie_number', 'issue_date', 'article_name')
            ->orderBy('issue_date')
            ->orderBy('voucher_type_type')
            ->orderBy('serie_number')
            ->orderBy('initial_voucher_number')
            ->orderBy('final_voucher_number')
            ->orderBy('article_code')
            ->get();

        // Crear datos para la paginación de KT Datatable
        $meta = new \stdClass();
        $meta->page = 1;
        $meta->pages = 1;
        $meta->perpage = -1;
        $meta->total = $elements->count();
        $meta->field = 'voucher_id';
        for ($i = 1; $i <= $elements->count(); $i++) {
            $meta->rowIds[] = $i;
        }

        return response()->json([
            'meta' => $meta,
            'data' => $elements,
        ]);
    }

    public function export()
    {
        $company_id = request('model.company_id');
        $since_date = date('Y-m-d', strtotime(request('model.since_date')));
        $to_date = (request('model.to_date') ? date('Y-m-d', strtotime(request('model.to_date'))) : date('Y-m-d'));

        $sales = Sale::leftjoin('sale_details', 'sale_details.sale_id', '=', 'sales.id')
            ->select(
                'sales.sale_date as fecha',
                DB::Raw('SUM(sale_details.kg) as cantidad')
            )
            ->groupBy('sales.sale_date')
            ->where('sales.sale_date', '>=', $since_date)
            ->where('sales.sale_date', '<=', $to_date)
            ->where('sales.company_id', $company_id)
            ->get();

        return Excel::download(new ChartExport($sales), '.xlsx');
        
    }
}
