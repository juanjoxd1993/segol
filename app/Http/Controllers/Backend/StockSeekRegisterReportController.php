<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Employee;
use App\Exports\StockSeekRegisterReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MoventClass;
use App\MoventType;
use App\Provider;
use App\WarehouseAccountType;
use App\WarehouseDocumentType;
use App\WarehouseMovement;
use App\WarehouseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class StockSeekRegisterReportController extends Controller
{
	public function index() {
		
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		

		return view('backend.stock_seek_register_report')->with(compact('movement_classes', 'movement_types', 'warehouse_types', 'companies', 'current_date', 'warehouse_account_types', 'warehouse_document_types'));
	}

	public function validateForm() {
		$messages = [
			
			'initial_date.required'					=> 'Debe seleccionar una Fecha Inicial.',
			'final_date.required'					=> 'Debe seleccionar una Fecha Final.',
		];

		$rules = [
			
			'initial_date'		=> 'required',
			'final_date'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	

	public function list() {
		$export = request('export');
		

		$initial_date = date_create(request('model.initial_date') . ' 00:00:00');
		$final_date = date_create(request('model.final_date') . ' 23:59:59');
		$company_id = request('model.company_id');
		$initial_date = date_format($initial_date, 'Y-m-d H:i:s');
		$final_date = date_format($final_date, 'Y-m-d H:i:s');
		//$warehouse_account_type_id = request('model.warehouse_account_type_id');
		//$warehouse_account_id = request('model.warehouse_account_id');
		// $referral_guide_series = request('model.referral_guide_series');
		// $referral_guide_number = request('model.referral_guide_number');
		// $warehouse_document_type_id = request('model.warehouse_document_type_id');
		// $referral_serie_number = request('model.referral_serie_number');
		// $referral_voucher_number = request('model.referral_voucher_number');
		// $scop_number = request('model.scop_number');
		// $license_plate = request('model.license_plate');

		$movements = WarehouseMovement::select('id', 'company_id', 'movement_class_id', 'movement_type_id', 'movement_stock_type_id', 'movement_number', 'created_at', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'referral_warehouse_document_type_id', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'state','traslate_date','route_id')
		->where('warehouse_type_id', 5)	
		->where('movement_class_id', 2)
		->whereNotIn('movement_type_id', [1, 2, 3, 4,5,6,7,8,9,10,15,16,17,18,19,20,21,22,23,24,25,26,27,28])
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('created_at', '>=', $initial_date)
			->where('created_at', '<=', $final_date)
		//	->when($warehouse_account_type_id, function($query, $warehouse_account_type_id) {
		//		return $query->where('warehouse_account_type_id', $warehouse_account_type_id);
		//	})
		//	->when($warehouse_account_id, function($query, $warehouse_account_id) {
		//		return $query->where('account_id', $warehouse_account_id);
		//	})
			// ->when($referral_guide_series, function($query, $referral_guide_series) {
			// 	return $query->where('referral_guide_series', $referral_guide_series);
			// })
			// ->when($referral_guide_number, function($query, $referral_guide_number) {
			// 	return $query->where('referral_guide_number', $referral_guide_number);
			// })
			// ->when($warehouse_document_type_id, function($query, $warehouse_document_type_id) {
			// 	return $query->where('referral_warehouse_document_type_id', $warehouse_document_type_id);
			// })
			// ->when($referral_serie_number, function($query, $referral_serie_number) {
			// 	return $query->where('referral_serie_number', $referral_serie_number);
			// })
			// ->when($referral_voucher_number, function($query, $referral_voucher_number) {
			// 	return $query->where('referral_voucher_number', $referral_voucher_number);
			// })
			// ->when($scop_number, function($query, $scop_number) {
			// 	return $query->where('scop_number', $scop_number);
			// })
			// ->when($license_plate, function($query, $license_plate) {
			// 	return $query->where('license_plate', $license_plate);
			// })
			->orderBy('company_id', 'asc')
		//	->orderBy('movement_class_id', 'asc')
		//	->orderBy('movement_type_id', 'asc')
			->orderBy('created_at', 'asc')
			->get();

		$movement_details = collect([]);
		$movements->map(function($item, $index) use($movement_details, $export) {
			$item->warehouse_movement_details;
			
		
			
			$item->warehouse_movement_details->map(function($detail) use($item, $movement_details, $export) {
				$detail->article;
				$detail->warehouse_movement_id = $item->id;
				$detail->company_short_name = $item->company->short_name;
				$detail->movement_type = $item->movement_type->name;
				$detail->date = date('d/m/Y', strtotime($item->created_at));
				$detail->traslate_date = date('d/m/Y', strtotime($item->traslate_date));
				$detail->route_id = $item->route_id;
				$detail->article_code = $detail->article->convertion;
				$detail->article_name = $detail->article->name;
				$detail->quantity = number_format($detail->converted_amount, 4, '.', ',');
				$detail->new_stock_return = number_format($detail->new_stock_return, 4, '.', ',');
				$detail->referral_guide_serie = $item->referral_guide_series;
				$detail->referral_guide = $item->referral_guide_number;		
				$detail->reference_document = $item->referral_serie_number.'-'.$item->referral_voucher_number;
				$detail->scop_number = $item->scop_number;
				$detail->license_plate = $item->license_plate;
				if ( Auth::user()->user == 'operaciones3' || Auth::user()->user == 'admin' || Auth::user()->user == 'sistemas1'   ) {
					$detail->state = $item->state;
				} else {
					$detail->state = 1;
				}


				$movement_details->push($detail);
			});
		});

		if ( $export ) {
			$data = new StockSeekRegisterReportExport($movement_details);
			$file = Excel::download($data, 'reporte-movimientos-de-almacen-'.time().'.xls');

			return $file;
		} else {
			return $movement_details;
		}
	}

	public function detail() {
		$id = request('id');

		$element = WarehouseMovement::select('id', 'movement_type_id', 'account_id', 'referral_guide_series', 'referral_guide_number', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'traslate_date')
			->findOrFail($id);

		$date = CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->traslate_date)));
		$element->date = $date->startOfDay()->toAtomString();
		$element->min_datetime = $date->startOfDay()->subDays(2)->toAtomString();
		$element->max_datetime = $date->startOfDay()->addDays(2)->toAtomString();
		$element->typing_error = $element->movement_type_id == 29 ? 1 : 0;

		return $element;
	}

	public function update() {
		$id = request('id');
		$account_id = request('account_id');
		$referral_guide_series = request('referral_guide_series');
		$referral_guide_number = request('referral_guide_number');
		$referral_serie_number = request('referral_serie_number');
		$referral_voucher_number = request('referral_voucher_number');
		$scop_number = request('scop_number');
		$license_plate = request('license_plate');
		$date = request('date');
		$typing_error = request('typing_error');

		// Revisar Nª de Scop
		// $scop = WarehouseMovement::where('scop_number', $scop_number)
		// 	->where('account_id', '!=', $account_id)
		// 	->first();

		// if ( $scop ) {
		// 	$data = new stdClass();
		// 	$data->type = 5;
		// 	$data->title = '¡Error!';
		// 	$data->msg = 'El Nº de Scop ya existe en otro Registro.';

		// 	return response()->json($data);
		// }

		$element = WarehouseMovement::findOrFail($id);
		$element->referral_guide_series = $referral_guide_series;
		$element->referral_guide_number = $referral_guide_number;
		$element->referral_serie_number = $referral_serie_number;
		$element->referral_voucher_number = $referral_voucher_number;
		$element->scop_number = $scop_number;
		$element->license_plate = $license_plate;
		if ( $typing_error ) {
			$element->movement_type_id = 29;
			$element->action_type_id = null;
		}
		$element->traslate_date = date('Y-m-d', strtotime($date));
		$element->save();

		$data = new stdClass();
		$data->type = 1;
		$data->title = '¡Ok!';
		$data->msg = 'Registro actualizado exitosamente.';

		return response()->json($data);
	}
}
