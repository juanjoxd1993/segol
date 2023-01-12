<?php

namespace App\Http\Controllers\Backend;

use App\Route;
use App\SaleDetail;
use App\Sale;
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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use stdClass;


class 	RutasReportController extends Controller
{
    public function index() {
		
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.rutas_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			
			'final_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			
			'final_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	

	public function list() {

		$export = request('export');

	    $mes_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d');
        $initial_date = CarbonImmutable::createFromDate(request('model.final_date'))->format('Y-m-01');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('m');
		
		
			$elements = Route::select('routes.id','routes.mes as mes', 'routes.canal as canal', 'routes.cobertura as cobertura',
			'routes.mes_name as mes_name','routes.canal_name as canal_name', 'routes.ruta_name as ruta_name', 'routes.supervisor_name as supervisor_name','routes.vendedor_name as vendedor_name', 'routes.cuota as cuota','routes.udid as udid')
			->where('routes.mes', '=', $final_date)
			->get();
			$response=[];

			


			foreach ($elements as $saledetail) {
				

	
				$cobertura_1 = Route::select('cobertura')
				->where('mes', '01')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				

				$cobertura_2 = Route::select('cobertura')
				->where('mes', '02')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				$cobertura_3 = Route::select('cobertura')
				->where('mes', '03')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_4 = Route::select('cobertura')
				->where('mes', '04')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_5 = Route::select('cobertura')
				->where('mes', '05')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_6 = Route::select('cobertura')
				->where('mes', '06')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_7 = Route::select('cobertura')
				->where('mes', '07')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				$cobertura_8 = Route::select('cobertura')
				->where('mes', '08')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

                if ( $saledetail->udid == '1' ) {
					$cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.manager_id', 9)
					->select('kg')
					->sum('kg');  
				}

                elseif  ( $saledetail->udid == '2' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
                    ->where('clients.manager_id', 1)
					->select('kg')
					->sum('kg');  
                }

                elseif  ( $saledetail->udid == '3' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
                    ->where('clients.zone_id', 1)
                    ->where('clients.manager_id', 4)
					->select('kg')
					->sum('kg');  
                }
                elseif  ( $saledetail->udid == '4' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
                    ->where('clients.manager_id', 3)
					->select('kg')
					->sum('kg');  
                }

                elseif  ( $saledetail->udid == '5' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
                    ->where('clients.manager_id', 4)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '6' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 1)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '7' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 2)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '8' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 3)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '9' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 5)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '10' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 7)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '11' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 10)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '12' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 11)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '13' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 29)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '14' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 4)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '15' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 12)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '16' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 13)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '17' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 23)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '18' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 6)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '19' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 9)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '20' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 8)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '21' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 15)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '22' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 16)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '23' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 17)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '24' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 18)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '25' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 24)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '26' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 33)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '27' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
					->whereIn('clients.zone_id',[6,9])
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '28' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
					->whereIn('clients.zone_id',[8,11])
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '29' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 20)
					->where('clients.zone_id',9)
					->select('kg')
					->sum('kg');  
                }

				elseif  ( $saledetail->udid == '30' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 34)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '31' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 35)
					->select('kg')
					->sum('kg');  
                }
				elseif  ( $saledetail->udid == '32' ){
                    $cobertura_9 = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '<=', $mes_date)
					->where('sales.sale_date', '>=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
                    ->where('clients.route_id', 37)
					->select('kg')
					->sum('kg');  
                }





				$saledetail->canal_name = $saledetail['canal_name'];
				$saledetail->ruta_name = $saledetail ['ruta_name'];
				$saledetail->cobertura_1 = $cobertura_1;
				$saledetail->cobertura_2 = $cobertura_2;
				$saledetail->cobertura_3 = $cobertura_3;
				$saledetail->cobertura_4 = $cobertura_4;
				$saledetail->cobertura_5 = $cobertura_5;
				$saledetail->cobertura_6 = $cobertura_6;
				$saledetail->cobertura_7 = $cobertura_7;
				$saledetail->cobertura_8 = $cobertura_8;
				$saledetail->cobertura_9 = ($cobertura_9/1000);
				$saledetail->cuota = $saledetail['cuota'];

			


				$response[] = $saledetail;

			}


		$totals = new stdClass();
		$totals->canal_name = 'TOTAL';
		$totals->ruta_name = '';
		$totals->cobertura_1 = '';
		$totals->cobertura_2 = '';
		$totals->cobertura_3 = '';
		$totals->cobertura_4 = '';
		$totals->cobertura_5 = '';
		$totals->cobertura_6 = '';
		$totals->cobertura_7 = '';
		$totals->cobertura_8 = '';
		$totals->cobertura_9 = '';
		$totals->cuota ='';


		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'REPORTE DE COBERTURAS AL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Ruta');
			$sheet->setCellValue('D3', 'Enero');
            $sheet->setCellValue('E3', 'Febrero'); 
            $sheet->setCellValue('F3', 'Marzo');
            $sheet->setCellValue('G3', 'Abril');
			$sheet->setCellValue('H3', 'Mayo');
			$sheet->setCellValue('I3', 'Junio');
			$sheet->setCellValue('J3', 'Julio');
			$sheet->setCellValue('K3', 'Agosto');
			$sheet->setCellValue('L3', 'Septiembre');
			$sheet->setCellValue('M3', 'Cuota');
			$sheet->setCellValue('N3', 'Dif%');
            $sheet->setCellValue('O3', 'Dif');
           
			
			

			$sheet->getStyle('A3:O3')->applyFromArray([
				'font' => ['bold' => true],
				'name'      =>  'Calibri',
				'size'      =>  10,
			]);


	
		
		$sheet->getStyle('A3:C3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'name'      =>  'Calibri',
				'size'      =>  10,
				
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '0B2861')
			]
		]);
		$sheet->getStyle('D3:L3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'name'      =>  'Calibri',
				'size'      =>  10,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '11B464')
			]
		]);
		$sheet->getStyle('M3:O3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'name'      =>  'Calibri',
				'size'      =>  10,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'E91010')
			]
		]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
		



				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->canal_name);
				$sheet->setCellValue('C'.$row_number, $element->ruta_name);
				$sheet->setCellValue('D'.$row_number, $element->cobertura_1);
                $sheet->setCellValue('E'.$row_number, $element->cobertura_2);
                $sheet->setCellValue('F'.$row_number, $element->cobertura_3);
                $sheet->setCellValue('G'.$row_number, $element->cobertura_4);
				$sheet->setCellValue('H'.$row_number, $element->cobertura_5);
				$sheet->setCellValue('I'.$row_number, $element->cobertura_6);
				$sheet->setCellValue('J'.$row_number, $element->cobertura_7);
				$sheet->setCellValue('K'.$row_number, $element->cobertura_8);
				$sheet->setCellValue('L'.$row_number, $element->cobertura_9);
                $sheet->setCellValue('M'.$row_number, $element->cuota);
            //   $sheet->setCellValue('N'.$row_number, $element->);
			//	$sheet->setCellValue('O'.$row_number, $element->price); 			
            //    $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
						

		
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
		


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








