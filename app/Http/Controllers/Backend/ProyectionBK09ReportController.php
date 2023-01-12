<?php

namespace App\Http\Controllers\Backend;

use App\Sale;
use App\SaleDetail;
use App\AcRep;
use App\Article;
use App\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class 	ProyectionReportController extends Controller
{
    public function index() {

		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.proyection_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha.',
			'day.required'			=> 'Debe ingresar un día.',
			'day.numeric'				=> 'El día debe ser mayor a 0.',
			'day.min'					=> 'El día debe ser mayor a 0.',
			'day.not_in'				=> 'El día debe ser mayor a 0.',
			
		];

		$rules = [
			'initial_date'	=> 'required',
			'day'			=> 'required|numeric|min:0|not_in:0',
			
			
		];

		request()->validate($rules, $messages);
		return request()->all();
	}
	public function list() {

            $export = request('export');
            $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
            $uno_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-01');
            $dos_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-02');
            $tres_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-03');
            $cuatro_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-04');
            $cinco_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-05');
            $seis_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-06');
            $siete_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-07');
            $ocho_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-08');
            $nueve_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-09');
            $diez_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-10');
            $once_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-11');
            $doce_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-12');
            $trece_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-13');
            $catorce_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-14');
            $quince_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-15');
            $dieciseis_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-16');
            $diecisiete_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-17');
            $dieciocho_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-18');
            $diecinueve_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-19');
            $veinte_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-20');
            $ventiuno_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-21');
            $ventidos_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-22');
            $ventitres_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-23');
            $venticuatro_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-24');
            $venticinco_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-25');
            $ventiseis_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-26');
            $ventisiete_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-27');
            $ventiocho_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-28');
            $ventinueve_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-29');
            $treinta_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-30');
            $treintayuno_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-31');
            $price_mes = CarbonImmutable::createFromDate(request('model.initial_date'))->format('m');
            $day = request('model.day');
    

		DB::enableQueryLog();
		
		
			$result = AcRep::select(
			'ac_reps.udid as udid',
			'ac_reps.año as year',
			'ac_reps.mes as month',
			'ac_reps.channel_name as client_channel_name',
			'ac_reps.name as name',
			 'ac_reps.dias as dias_mes',
			 'ac_reps.sale_option as option',
			 'ac_reps.route_id as route_id',
			 'ac_reps.zone_id as zone_id',
			 'ac_reps.sector_id as sector_id',
			 'ac_reps.orden as orden'
		 )

		 ->where('ac_reps.mes', '=', $price_mes)

		 ->orderBy('ac_reps.sale_option')
		 ->orderBy('ac_reps.orden')		 
		 ->get();

			


		foreach ($result as $report) {

            $report->route_id = $report ['route_id'];


				 $tm_01 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $uno_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=', $report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_02 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $dos_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=', $report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_03 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $tres_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_04 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $cuatro_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_05 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $cinco_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_06 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $seis_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_07 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $siete_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_08 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ocho_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_09 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $nueve_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_10 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $diez_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_11 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $once_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_12 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $doce_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_13 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $trece_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_14 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $catorce_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_15 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $quince_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_16 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $dieciseis_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_17 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $diecisiete_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_18 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $dieciocho_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_19 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $diecinueve_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_20 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $veinte_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_21 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventiuno_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_22 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventidos_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_23 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventitres_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_24 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $venticuatro_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_25 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $venticinco_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_26 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventiseis_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_27 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventisiete_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_28 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventiocho_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_29 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $ventinueve_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_30 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $treinta_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				 $tm_31 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $treintayuno_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

                 $report->tm_01 = $tm_01/1000;
                 $report->tm_02 = $tm_02/1000;
                 $report->tm_03 = $tm_03/1000;
                 $report->tm_04 = $tm_04/1000;
                 $report->tm_05 = $tm_05/1000;
                 $report->tm_06 = $tm_06/1000;
                 $report->tm_07 = $tm_07/1000;
                 $report->tm_08 = $tm_08/1000;
                 $report->tm_09 = $tm_09/1000;
                 $report->tm_10 = $tm_10/1000;
                 $report->tm_11 = $tm_11/1000;
                 $report->tm_12 = $tm_12/1000;
                 $report->tm_13 = $tm_13/1000;
                 $report->tm_14 = $tm_14/1000;
                 $report->tm_15 = $tm_15/1000;
                 $report->tm_16 = $tm_16/1000;
                 $report->tm_17 = $tm_17/1000;
                 $report->tm_18 = $tm_18/1000;
                 $report->tm_19 = $tm_19/1000;
                 $report->tm_20 = $tm_20/1000;
                 $report->tm_21 = $tm_21/1000;
                 $report->tm_22 = $tm_22/1000;
                 $report->tm_23 = $tm_23/1000;
                 $report->tm_24 = $tm_24/1000;
                 $report->tm_25 = $tm_25/1000;
                 $report->tm_26 = $tm_26/1000;
                 $report->tm_27 = $tm_27/1000;
                 $report->tm_28 = $tm_28/1000;
                 $report->tm_29 = $tm_29/1000;
                 $report->tm_30 = $tm_30/1000;
                 $report->tm_31 = $tm_31/1000;



				
	


		   $response[] = $report;

			}


		/*$totals = new stdClass();
		$totals->soles_glp_e = '';
		$totals->kgs_glp_e = '';
		$totals->cost_glp = '';
		
		$response[] = $totals;*/






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AH1');
			
			$sheet->setCellValue('A1', 'REPORTE DE PROYECCIONES DEL '.CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]

			]);


			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'Tipo');
			$sheet->setCellValue('C3', 'Nombre');
			$sheet->setCellValue('D3', '01');
			$sheet->setCellValue('E3', '02');
			$sheet->setCellValue('F3', '03');
			$sheet->setCellValue('G3', '04');
			$sheet->setCellValue('H3', '05');
			$sheet->setCellValue('I3', '06');
			$sheet->setCellValue('J3', '07');
			$sheet->setCellValue('K3', '08');
            $sheet->setCellValue('L3', '09');
            $sheet->setCellValue('M3', '10');
            $sheet->setCellValue('N3', '11');
            $sheet->setCellValue('O3', '12');
            $sheet->setCellValue('P3', '13');
            $sheet->setCellValue('Q3', '14');
            $sheet->setCellValue('R3', '15');
            $sheet->setCellValue('S3', '16');
            $sheet->setCellValue('T3', '17');
            $sheet->setCellValue('U3', '18');
            $sheet->setCellValue('V3', '19');
            $sheet->setCellValue('W3', '20');
            $sheet->setCellValue('X3', '21');
            $sheet->setCellValue('Y3', '22');
            $sheet->setCellValue('Z3', '23');
            $sheet->setCellValue('AA3', '24');
            $sheet->setCellValue('AB3', '25');
            $sheet->setCellValue('AC3', '26');
            $sheet->setCellValue('AD3', '27');
            $sheet->setCellValue('AE3', '28');
            $sheet->setCellValue('AF3', '29');
            $sheet->setCellValue('AG3', '30');
            $sheet->setCellValue('AH3', '31');
           
           
			
			$sheet->getStyle('A3:AH3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
					$sheet->setCellValue('B'.$row_number, $element->client_channel_name);
					$sheet->setCellValue('C'.$row_number, $element->name);
					$sheet->setCellValue('D'.$row_number, $element->tm_01);
					$sheet->setCellValue('E'.$row_number, $element->tm_02);
					$sheet->setCellValue('F'.$row_number, $element->tm_03);
					$sheet->setCellValue('G'.$row_number, $element->tm_04);
					$sheet->setCellValue('H'.$row_number, $element->tm_05);
					$sheet->setCellValue('I'.$row_number, $element->tm_06);
					$sheet->setCellValue('J'.$row_number, $element->tm_07);
					$sheet->setCellValue('K'.$row_number, $element->tm_08);
					$sheet->setCellValue('L'.$row_number, $element->tm_09);
					$sheet->setCellValue('M'.$row_number, $element->tm_10);
					$sheet->setCellValue('N'.$row_number, $element->tm_11);
					$sheet->setCellValue('O'.$row_number, $element->tm_12);
					$sheet->setCellValue('P'.$row_number, $element->tm_13);
					$sheet->setCellValue('Q'.$row_number, $element->tm_14);
					$sheet->setCellValue('R'.$row_number, $element->tm_15);
					$sheet->setCellValue('S'.$row_number, $element->tm_16);
					$sheet->setCellValue('T'.$row_number, $element->tm_17);
					$sheet->setCellValue('U'.$row_number, $element->tm_18);
					$sheet->setCellValue('V'.$row_number, $element->tm_19);
					$sheet->setCellValue('W'.$row_number, $element->tm_20);
					$sheet->setCellValue('X'.$row_number, $element->tm_21);
					$sheet->setCellValue('Y'.$row_number, $element->tm_22);
					$sheet->setCellValue('Z'.$row_number, $element->tm_23);
					$sheet->setCellValue('AA'.$row_number, $element->tm_24);
					$sheet->setCellValue('AB'.$row_number, $element->tm_25);
					$sheet->setCellValue('AC'.$row_number, $element->tm_26);
					$sheet->setCellValue('AD'.$row_number, $element->tm_27);
					$sheet->setCellValue('AE'.$row_number, $element->tm_28);
					$sheet->setCellValue('AF'.$row_number, $element->tm_29);
					$sheet->setCellValue('AG'.$row_number, $element->tm_30);
					$sheet->setCellValue('AH'.$row_number, $element->tm_31);
				
                    $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('T'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('U'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('V'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('W'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('X'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('Y'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('Z'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AA'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AB'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AC'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AD'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AE'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AF'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AG'.$row_number)->getNumberFormat()->setFormatCode('0.0');
                    $sheet->getStyle('AH'.$row_number)->getNumberFormat()->setFormatCode('0.0');

              

		
				$row_number++;
			}

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
			$sheet->getColumnDimension('H')->setAutoSize(true);
			$sheet->getColumnDimension('I')->setAutoSize(true);
			$sheet->getColumnDimension('J')->setAutoSize(true);
			$sheet->getColumnDimension('K')->setAutoSize(true);
			$sheet->getColumnDimension('L')->setAutoSize(true);
			$sheet->getColumnDimension('M')->setAutoSize(true);
			$sheet->getColumnDimension('N')->setAutoSize(true);
			$sheet->getColumnDimension('O')->setAutoSize(true);
			$sheet->getColumnDimension('P')->setAutoSize(true);
			$sheet->getColumnDimension('Q')->setAutoSize(true);
			$sheet->getColumnDimension('R')->setAutoSize(true);
			$sheet->getColumnDimension('S')->setAutoSize(true);
			$sheet->getColumnDimension('T')->setAutoSize(true);
			$sheet->getColumnDimension('U')->setAutoSize(true);
			$sheet->getColumnDimension('V')->setAutoSize(true);
			$sheet->getColumnDimension('W')->setAutoSize(true);
			$sheet->getColumnDimension('X')->setAutoSize(true);
			$sheet->getColumnDimension('Y')->setAutoSize(true);
			$sheet->getColumnDimension('Z')->setAutoSize(true);
			$sheet->getColumnDimension('AA')->setAutoSize(true);
			$sheet->getColumnDimension('AB')->setAutoSize(true);
			$sheet->getColumnDimension('AC')->setAutoSize(true);
			$sheet->getColumnDimension('AD')->setAutoSize(true);
			$sheet->getColumnDimension('AE')->setAutoSize(true);
			$sheet->getColumnDimension('AF')->setAutoSize(true);
			$sheet->getColumnDimension('AG')->setAutoSize(true);
			$sheet->getColumnDimension('AH')->setAutoSize(true);

			


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








