<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Employee;
use App\Exports\StockFinalReportExport;
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

class StockFinalReportController extends Controller
{
	public function index() {
		$movement_classes = MoventClass::select('id','name')->get();
		$movement_types = MoventType::select('id','movent_class','name')->where('id', '!=', 22)->get();
		$warehouse_types = WarehouseType::select('id','name')->get();
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$warehouse_account_types = WarehouseAccountType::select('id','name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id','name')->get();

		return view('backend.stock_final_report')->with(compact('movement_classes', 'movement_types', 'warehouse_types', 'companies', 'current_date', 'warehouse_account_types', 'warehouse_document_types'));
	}

	public function validateForm() {
		$messages = [
			'movement_class_id.required'			=> 'Debe seleccionar Ingreso o Salida.',
			'warehouse_type_id.required'			=> 'Debe seleccionar un Almacén.',
			'initial_date.required'					=> 'Debe seleccionar una Fecha Inicial.',
			'final_date.required'					=> 'Debe seleccionar una Fecha Final.',
		];

		$rules = [
			'movement_class_id'	=> 'required',
			'warehouse_type_id'	=> 'required',
			'initial_date'		=> 'required',
			'final_date'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getAccounts() {
		$company_id = request('company_id');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$q = request('q');
		
		if ( $warehouse_account_type_id == 1 ) {
			$query = Client::query()->withTrashed();
		} elseif ( $warehouse_account_type_id == 2 ) {
			$query = Provider::query()->withTrashed();
		} elseif ( $warehouse_account_type_id == 3 ) {
			$query = Employee::query();
		}

		$query->select('id','business_name')
			->where('company_id', $company_id)
			->where('business_name', 'like', '%'.$q.'%');
		$clients = $query->get();

		$clients->map(function($item, $index){
			$item->text = $item->business_name; // veamos si lo guarda primero prueba  si ya no vas a usar business borralo
			unset($item->business_name);
			return $item;
		});

		return $clients;
	}

	public function list() {
		$export = request('export');
		$valued = request('valued');

		$initial_date = date_create(request('model.initial_date') . ' 00:00:00');
		$final_date = date_create(request('model.final_date') . ' 23:59:59');
		
		$movement_class_id = request('model.movement_class_id');
		$movement_type_id = request('model.movement_type_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		$company_id = request('model.company_id');
		$initial_date = date_format($initial_date, 'Y-m-d H:i:s');
		$final_date = date_format($final_date, 'Y-m-d H:i:s');
		$warehouse_account_type_id = request('model.warehouse_account_type_id');
		$warehouse_account_id = request('model.warehouse_account_id');
		// $referral_guide_series = request('model.referral_guide_series');
		// $referral_guide_number = request('model.referral_guide_number');
		// $warehouse_document_type_id = request('model.warehouse_document_type_id');
		// $referral_serie_number = request('model.referral_serie_number');
		// $referral_voucher_number = request('model.referral_voucher_number');
		// $scop_number = request('model.scop_number');
		// $license_plate = request('model.license_plate');

		$movements = WarehouseMovement::select('id', 'company_id', 'movement_class_id', 'movement_type_id', 'movement_stock_type_id', 'movement_number', 'created_at', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'referral_warehouse_document_type_id', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'state')
			->where('movement_class_id', $movement_class_id)
			->when($movement_type_id, function($query, $movement_type_id) {
				return $query->where('movement_type_id', $movement_type_id);
			})
			->where('warehouse_type_id', $warehouse_type_id)
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('created_at', '>=', $initial_date)
			->where('created_at', '<=', $final_date)
			->when($warehouse_account_type_id, function($query, $warehouse_account_type_id) {
				return $query->where('warehouse_account_type_id', $warehouse_account_type_id);
			})
			->when($warehouse_account_id, function($query, $warehouse_account_id) {
				return $query->where('account_id', $warehouse_account_id);
			})
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
			->orderBy('movement_class_id', 'asc')
			->orderBy('movement_type_id', 'asc')
			->orderBy('created_at', 'asc')
			->get();

		$movement_details = collect([]);
		$movements->map(function($item, $index) use($movement_details, $valued, $export) {
			$item->warehouse_movement_details;
			
			if ( $item->warehouse_account_type_id == 1 ) {
				$query = Client::select('document_type_id')
					->where('id', $item->account_id)
					->withTrashed()
					->first();
			} elseif ( $item->warehouse_account_type_id == 2 ) {
				$query = Provider::select('document_type_id')
					->where('id', $item->account_id)
					->withTrashed()
					->first();
			} elseif ( $item->warehouse_account_type_id == 3 ) {
				$query = Employee::select('document_type_id')
					->where('id', $item->account_id)
					->first();
			}

			$account_document_type = $query->document_type->name;
			
			$item->warehouse_movement_details->map(function($detail, $detailIndex) use($item, $account_document_type, $movement_details, $valued, $export) {
				$detail->article;
				$detail->warehouse_movement_id = $item->id;
				$detail->company_short_name = $item->company->short_name;
				$detail->movement_class = $item->movement_class->name;
				$detail->movement_type = $item->movement_type->name;
				$detail->movement_number = $item->movement_number;
				$detail->date = date('d-m-Y', strtotime($item->created_at));
				$detail->article_code = $detail->article->code;
				$detail->article_name = $detail->article->name.' '.$detail->article->warehouse_unit->name.' x '.$detail->article->package_warehouse;
				$detail->quantity = number_format($detail->converted_amount, 4, '.', ',');
                $detail->return = number_format($detail->new_stock_return, 4, '.', ',');
				if ( !empty($valued) && empty($export) ) {
					$detail->price = number_format($detail->price, 4, '.', ',');
					$detail->sale_value = number_format($detail->sale_value, 4, '.', ',');
				}
				$detail->movement_stock_type = ( $item->movement_stock_type_id ? $item->movement_stock_type->name : '' );
				$detail->account_document_type = $account_document_type;
				$detail->account_document_number = $item->account_document_number;
				$detail->account_name = $item->account_name;
				$detail->referral_guide = $item->referral_guide_series.'-'.$item->referral_guide_number;
				$detail->reference_document_type = $item->warehouse_document_type->short_name;
				$detail->reference_document = $item->referral_serie_number.'-'.$item->referral_voucher_number;
				$detail->scop_number = $item->scop_number;
				$detail->license_plate = $item->license_plate;
				if ( Auth::user()->user == 'operaciones1' || Auth::user()->user == 'global1' || Auth::user()->user == 'admin' || Auth::user()->user == 'sistemas1') {
					$detail->state = $item->state;
				} else {
					$detail->state = 1;
				}

				unset($detail->digit_amount);
				unset($detail->currency_id);
				if ( empty($valued) ) {
					unset($detail->price);
					unset($detail->sale_value);
				}
				unset($detail->exonerated_value);
				unset($detail->inaccurate_value);
				unset($detail->igv);
				unset($detail->total);
				unset($detail->igv_percentage);
				unset($detail->igv_perception);
				unset($detail->igv_perception_percentage);

				$movement_details->push($detail);
			});
		});

		if ( $export ) {
			$data = new StockFinalReportExport($movement_details, $valued);
			$file = Excel::download($data, 'reporte-salidas-de-mercaderia-'.time().'.xls');

			return $file;
		} else {
			return $movement_details;
		}
	}

	public function detail() {
		$id = request('id');

		$element = WarehouseMovement::select('id', 'movement_type_id', 'account_id', 'referral_guide_series', 'referral_guide_number', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'created_at')
			->findOrFail($id);

		$date = CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->created_at)));
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
		$element->created_at = date('Y-m-d', strtotime($date));
		$element->save();

		$data = new stdClass();
		$data->type = 1;
		$data->title = '¡Ok!';
		$data->msg = 'Registro actualizado exitosamente.';

		return response()->json($data);
	}
}
