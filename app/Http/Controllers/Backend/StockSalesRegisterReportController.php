<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Employee;
use App\Article;
use App\Exports\StockSalesRegisterReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MoventClass;
use App\MoventType;
use App\Provider;
use App\WarehouseAccountType;
use App\WarehouseDocumentType;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use stdClass;

class StockSalesRegisterReportController extends Controller
{
	public function index() {
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));

		return view('backend.stock_sales_register_report')->with(compact('companies', 'current_date'));
	}

	public function getWarehouseMovements() {
		$company_id = request('company_id');
		$q = request('q');

		$warehouse_movements = WarehouseMovement::select('id', 'referral_voucher_number as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('referral_voucher_number', 'like', '%'.$q.'%')
		//	->whereIn('warehouse_type_id',[8,9,10,11])
			->withTrashed()
			->get();

		return $warehouse_movements;
	}

	public function validateForm() {
		$messages = [
	
		//	'initial_date.required'					=> 'Debe seleccionar una Fecha Inicial.',
		//	'final_date.required'					=> 'Debe seleccionar una Fecha Final.',
		];

		$rules = [
		
		//	'initial_date'		=> 'required',
		//	'final_date'		=> 'required',
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
		$warehouse_movement_id = request('model.warehouse_movement_id');

		$warehouse_types = WarehouseType::select('id')
			->where('type', 2)
			->get();

		$warehouse_types_ids = array();

		foreach ($warehouse_types as $warehouse_type) {
			array_push($warehouse_types_ids, $warehouse_type->id);
		};

		$movements = WarehouseMovement::select('id', 
		'company_id', 
		'movement_class_id', 
		'movement_type_id', 
		'warehouse_type_id', 
		'movement_stock_type_id', 
		'movement_number', 
		'created_at', 
		'warehouse_account_type_id', 
		'account_id', 
		'account_document_number', 
		'account_name', 
		'license_plate_2', 
		'mezcla', 
        'taxed_operation',
        'igv',
        'tc',
		'origin',
        'total',
		'referral_guide_series',
		DB::Raw('CONCAT(referral_serie_number, "-", referral_voucher_number) as factura'), 
		'referral_guide_number', 
		'referral_serie_number',
		'referral_voucher_number',
		'traslate_date',
		'scop_number', 'license_plate', 'state')
        ->whereIn('warehouse_type_id', $warehouse_types_ids)
        ->where('movement_class_id', 1)
        ->whereIn('movement_type_id', [1])
		->where('created_at', '>=', $initial_date)
		->where('created_at', '<=', $final_date)
		->when($warehouse_movement_id, function($query, $warehouse_movement_id) {
			return $query->where('id', $warehouse_movement_id);
		})
		->orderBy('company_id', 'asc')
		->orderBy('created_at', 'asc')
		->get();

		$movement_details = collect([]);
		$movements->map(function($item, $index) use($movement_details, $export) {
			$item->warehouse_movement_details;

			$item->warehouse_movement_details->map(function($detail, $detailIndex) use($item, $movement_details, $export) {
				
                $despacho = WarehouseMovement::join('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')
				->where('referral_voucher_number', $item['referral_voucher_number'])
				->where('movement_class_id', 2)
				->select('converted_amount')
				->sum('converted_amount');

                $detail->article;
                $detail->company_short_name = $item->company->short_name;
                $detail->date = date('d-m-Y', strtotime($item->created_at));
                $detail->factura = $item->factura;
                $detail->warehouse_name = $item->warehouse_type->name;
                $detail->warehouse_short_name = $item->warehouse_type->short_name;
				if ($item->origin != 27){
					$detail->article_name = 'E';
					}
					else{
					$detail->article_name = 'G';	
					}
                $detail->total = $item->total;
                $detail->tc = $item->tc;
                $detail->quantity = $detail->converted_amount;
                $detail->precio_tm = $detail->total/$detail->quantity*1000;
                $detail->taxed_operation = $item->taxed_operation;
                $detail->igv = $item->igv;
                $detail->order_sale = $item->referral_guide_number;
                $detail->scop_number = $item->scop_number;
                $detail->conv_soles= $detail->total*$detail->tc;
                $detail->kg_soles= $detail->conv_soles/$detail->quantity;
                $detail->tm=$detail->quantity/1000;
                $detail->sub_soles=$detail->conv_soles/1.18;
                $detail->igv_soles=$detail->conv_soles-$detail->sub_soles;
                $detail->kg_dol=$detail->total/$detail->quantity;
                $detail->despacho += $despacho;
                $detail->stock=$detail->quantity-$detail->despacho;
				$detail->concat=$detail->warehouse_name."_". $detail->warehouse_short_name. $detail->article_name;

				if ( Auth::user()->user == 'comercial4' || Auth::user()->user == 'admin' || Auth::user()->user == 'sistemas1') {
					$detail->state = $item->state;
				} else {
					$detail->state = 1;
				}

				$movement_details->push($detail);
			});
		});

		if ( $export ) {
			$data = new StockSalesRegisterReportExport($movement_details);
			$file = Excel::download($data, 'reporte-movimientos-de-almacen-'.time().'.xls');

			return $file;
		} else {
			return $movement_details;
		}
	}

	public function detail() {
		$id = request('id');

		$element = WarehouseMovement::select('id',
										'movement_type_id',
										'warehouse_type_id',
										'account_id',
										'account_name',
										'referral_guide_number',
										'referral_serie_number',
										'referral_voucher_number',
										'scop_number',
										'created_at',
										'traslate_date',
										'total',
										'price_mes',
										'tc')
									->findOrFail($id);

		// $cantidad=WarehouseMovementDetail::where('warehouse_movement_id', $id)
		// 	->select('converted_amount')
		// 	->sum('converted_amount');

        // $element->cantidad += $cantidad;
		$date = CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->traslate_date)));
		$fecha= CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->created_at)));
		$element->date = $date->startOfDay()->toAtomString();
		$element->fecha =$element->created_at;
		$element->min_datetime = $date->startOfDay()->subDays(2)->toAtomString();
		$element->max_datetime = $date->startOfDay()->addDays(2)->toAtomString();
		// $element->typing_error = $element->movement_type_id == 29 ? 1 : 0;
		$details = [];

		foreach($element->warehouse_movement_details as $detail){
			array_push($details, [
				'id' => $detail->id,
				'article_id' => $detail->article_code,
				'old_article_code' => $detail->article->code,
				'article_code' => $detail->article->code,
				'code' => $detail->article->code,
				'name' => $detail->article->name,
				'old_converted_amount' => intval(floatval($detail->digit_amount)),
				'converted_amount' => intval(floatval($detail->digit_amount)),
			]);
		}

		$element->details = $details;
		$element->old_warehouse_type_id = $element->warehouse_type_id;

		return $element;
	}

	public function update() {
		$id = request('id');
		$account_id = request('account_id');
		$account_name = request('account_name');
		$old_warehouse_type_id = request('old_warehouse_type_id');
		$warehouse_type_id = request('warehouse_type_id');
		$referral_guide_number = request('referral_guide_number');
		$referral_serie_number = request('referral_serie_number');
		$referral_voucher_number = request('referral_voucher_number');
		$scop_number = request('scop_number');
		$fecha = request('fecha');
		$total = request('total');
		$date = request('date');
		// $cantidad=request('cantidad');
		$tc = request('tc');
		$price_mes = request('price_mes');
		$details = request('details');

		$element = WarehouseMovement::findOrFail($id);
		$element->account_name = $account_name;
		$element->warehouse_type_id = $warehouse_type_id;
		$element->referral_guide_number = $referral_guide_number;
		$element->referral_serie_number = $referral_serie_number;
		$element->referral_voucher_number = $referral_voucher_number;
		$element->scop_number = $scop_number;
		$element->created_at = date('Y-m-d', strtotime($fecha));
		$element->traslate_date = date('Y-m-d', strtotime($date));
		$element->total = $total;
		$element->soles = $total * $tc;
		$element->tc = $tc;
		$element->price_mes = $price_mes;

		foreach($details as $detail) {
			$old_converted_amount = $detail['old_converted_amount'];
			$converted_amount = $detail['converted_amount'];

			$old_article_code = $detail['old_article_code'];
			$article_code = $detail['article_code'];

			$difference = $element->stock_ini - $element->stock_pend;

			$element->stock_ini = $converted_amount;
			$element->stock_pend = $converted_amount - $difference;
			$element->cost_glp = $total / $converted_amount;

			$old_article = Article::where('code', $old_article_code)
								->where('warehouse_type_id', $old_warehouse_type_id)
								->first();

			$old_article->stock_good -= $old_converted_amount;
			$old_article->save();

			$article = Article::where('code', $article_code)
								->where('warehouse_type_id', $warehouse_type_id)
								->first();

			$article->stock_good += $converted_amount;
			$article->save();

			WarehouseMovementDetail::where('id', $detail['id'])
								->update([
									'article_code' => $article->id,
									'digit_amount' => $converted_amount,
									'converted_amount' => $converted_amount - $difference
								]);
		}

		$element->save();

		$data = new stdClass();
		$data->type = 1;
		$data->title = '¡Ok!';
		$data->msg = 'Registro actualizado exitosamente.';

		return response()->json($data);
	}

	public function getWarehouseTypeTwo() {
		$warehouse_types = WarehouseType::select('name', 'id')
										->where('type', 2)
										->get();

		return $warehouse_types;
	}

	public function getArticles() {
		$articles = Article::select('name', 'code', 'warehouse_type_id')
							->where('warehouse_type_id', 5)
							->whereIn('code', [1, 2])
							->get();

		return $articles;
	}

}
