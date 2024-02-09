<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\Client;
use App\Company;
use App\Employee;
use App\Exports\TerminalsReportExport;
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

class TerminalsReportController extends Controller
{
	public function index() {
	
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));

		return view('backend.terminals_report')->with(compact('companies','current_date'));
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

		$warehouse_type_ids = WarehouseType::select('id')
		->where('type', 2)
		->get();

		$array_warehouse_type_ids = array();

		foreach ($warehouse_type_ids as $warehouse_type_id) {
			array_push($array_warehouse_type_ids, $warehouse_type_id->id);
		}

		$movements = WarehouseMovement::select('id', 
									'company_id', 
									'movement_class_id', 
									'movement_type_id', 
									'warehouse_type_id', 
									'movement_stock_type_id', 
									'movement_number', 
									'movement_type_id', 
									'created_at', 
									'warehouse_account_type_id', 
									'account_id', 
									'account_document_number', 
									'account_name', 
									'license_plate_2', 
									'mezcla', 
									'origin',
									'referral_guide_series',
									DB::Raw('CONCAT(referral_serie_number, "-", referral_voucher_number) as factura'), 	
									'referral_guide_number', 
									'referral_voucher_number',
									'traslate_date',
									'scop_number',
									'license_plate',
									'state')
									->whereIn('warehouse_type_id',$array_warehouse_type_ids)
									->where('movement_class_id', 2)
									->whereIn('movement_type_id', [30,31])
									->where('created_at', '>=', $initial_date)
									->where('created_at', '<=', $final_date)
									->orderBy('company_id', 'asc')
									->orderBy('created_at', 'asc')
									->get();

		$movement_details = collect([]);
		$movements->map(function($item, $index) use($movement_details, $export) {
			$item->warehouse_movement_details;

			$item->warehouse_movement_details->map(function($detail, $detailIndex) use($item, $movement_details, $export) {
				
				$total = WarehouseMovement::where('referral_voucher_number', $item['referral_voucher_number'])
				->where('movement_class_id', 1)
				->select('total')
				->sum('total');

				$cantidad = WarehouseMovement::where('referral_voucher_number', $item['referral_voucher_number'])
				->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')
				->where('movement_class_id', 1)
				->select('converted_amount')
				->sum('converted_amount');

				$pedido = WarehouseMovement::where('referral_voucher_number', $item['referral_voucher_number'])
				->where('movement_class_id', 1)
				->select('referral_guide_number')
				->sum('referral_guide_number');

				$recojo = WarehouseMovement::where('referral_voucher_number', $item['referral_voucher_number'])
				->where('movement_class_id', 1)
				->select('stock_pend')
				->sum('stock_pend');

				$tc = WarehouseMovement::where('referral_voucher_number', $item['referral_voucher_number'])
				->where('movement_class_id', 1)
				->select('tc')
				->sum('tc');

				$detail->article;
				$detail->cantidad += $cantidad;
				$detail->total += $total;
				$detail->recojo += $recojo;
				$detail->pedido_m += $pedido;
				$detail->factura = $item->factura;
				$detail->quantity = $detail->converted_amount;
				$detail->warehouse_movement_id = $item->id;
				$detail->company_short_name = $item->company->short_name;
				$detail->movement_class = $item->movement_class->name;	
				$detail->movement_type = $item->movement_type->name;
                $detail->warehouse_name = $item->warehouse_type->name;
				$detail->convertion= $item->warehouse_type->convertion;
				$detail->aprov=$detail->quantity*$detail->convertion;
				$detail->warehouse_short_name = $item->warehouse_type->short_name;
				$detail->movement_number = $item->movement_number;
				$detail->movement_type_id = $item->movement_type_id;

				if ($detail->cantidad != 0)  {
					$detail->price_kg = $total*$tc/$detail->cantidad;
				}
				else {
					$detail->price_kg = 0;
				}
				if ($detail->cantidad != 0 )  {
					$detail->soles = $detail->quantity * $detail->price_kg;
				}
				else {
					$detail->soles = 0;
				}
				if ($detail->cantidad != 0 )  {
					$detail->price_dol = $total/$cantidad*1000;
				}
				else {
					$detail->price_dol = 0;
				}
				if ($detail->cantidad != 0 )  {
					$detail->sub_total = $detail->quantity * ($total*$tc/$cantidad)/1.18;
				}
				else {
					$detail->sub_total = 0;
				}

				if ($detail->cantidad != 0 )  {
					$detail->rest = $detail->recojo;
				}
				else {
					$detail->rest = 0;
				}

				$detail->igv = $detail->soles-$detail->sub_total;			
				$detail->date = date('d-m-Y', strtotime($item->created_at));
				$detail->article_code = $detail->article->code;

                if ($item->origin != 27){
				$detail->article_name = 'E';
				}
				else{
					$detail->article_name = 'G';	
				}

				$detail->movement_stock_type = ( $item->movement_stock_type_id ? $item->movement_stock_type->name : '' );
				$detail->account_document_number = $item->account_document_number;
				$detail->account_name = $item->account_name;
				$detail->referral_guide = $item->referral_guide_number;
				$detail->scop_number = $item->scop_number;
				$detail->tracto = $item->license_plate_2;
				$detail->mezcla =$item->mezcla;
				$detail->pedido_fact = $item->referral_guide_series;
				$detail->destiny = 'PLANTA';
				$detail->license_plate = $item->license_plate;
				$detail->tm = $detail->quantity/1000;

				if ($detail->cantidad != 0 )  {
					$detail->pond =($detail->converted_amount * ($total*$tc/$cantidad))/$detail->tm;
					}
					else {
						$detail->pond = 0;
					}

				$detail->traslate_date = $item->traslate_date;
				$detail->old_stock = $detail->old_stock_return;
				$detail->old_stock_r = $detail->old_stock_damaged;
				$detail->peso_neto=$detail->old_stock_r-$detail->old_stock;
				$detail->dif=$detail->peso_neto-$detail->quantity;

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
			$data = new TerminalsReportExport($movement_details);
			$file = Excel::download($data, 'reporte-movimientos-de-almacen-'.time().'.xls');

			return $file;
		} else {
			return $movement_details;
		}
	}

	public function detail() {
		$id = request('id');

		$element = WarehouseMovement::select('id',
											'created_at',
											'movement_type_id',
											'account_id',
											'referral_guide_series',
											'referral_guide_number',
											'referral_serie_number',
											'referral_voucher_number',
											'scop_number',
											'license_plate_2',
											'license_plate',
											'traslate_date',
											'price_mes',
											'stock_ini')
											->findOrFail($id);

		$date_emi = CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->created_at)));
		$date = CarbonImmutable::createFromDate(date('Y-m-d', strtotime($element->traslate_date)));
		$element->date = $date->startOfDay()->toAtomString();
		$element->date_emi = $date_emi->startOfDay()->toAtomString();
		$element->min_datetime = $date->startOfDay()->subDays(2)->toAtomString();
		$element->max_datetime = $date->startOfDay()->addDays(2)->toAtomString();
		$element->typing_error = $element->movement_type_id == 29 ? 1 : 0;
		$element->quantity = $element->stock_ini;

		return $element;
	}

	public function update() {
		$id = request('id');
		$account_id = request('account_id');
		// $article_code = request('article_code');
		$referral_guide_series = request('referral_guide_series');
		$referral_guide_number = request('referral_guide_number');
		$referral_serie_number = request('referral_serie_number');
		$referral_voucher_number = request('referral_voucher_number');
		$scop_number = request('scop_number');
		$license_plate = request('license_plate');
		$license_plate_2 = request('license_plate_2');
		$price_mes = request('price_mes');
		$date = request('date');
		$date_emi = request('date_emi');
		$typing_error = request('typing_error');
		$quantity = request('quantity');

		$element = WarehouseMovement::findOrFail($id);

		$stock_ini = $element->stock_ini;
		$stock_pend = $element->stock_pend;

		$difference = $stock_ini - $stock_pend;
		$new_difference = $stock_ini - $quantity;

		$element->stock_ini = $quantity;
		$element->stock_pend = $quantity - $difference;

		$element_movement_detail = WarehouseMovementDetail::where('warehouse_movement_id', $id)
														->first();

		$element_movement_detail->digit_amount = $quantity;
		$element_movement_detail->converted_amount = $quantity - $difference;
		$element_movement_detail->save();

		$element_article = Article::find($element_movement_detail->article_code);

		$element_article->stock_good += $new_difference;
		$element_article->save();

		$element_receiver = WarehouseMovement::whereNotIn('id', [$id])
											->where('movement_type_id', $element->movement_type_id)
											->where('movement_number', $element->movement_number)
											->first();

		$element_receiver->stock_ini = $quantity;
		$element_receiver->stock_pend = $quantity - $difference;
		$element_receiver->save();

		$element_receiver_movement_detail = WarehouseMovementDetail::where('warehouse_movement_id', $element_receiver->id)
																	->first();

		$element_receiver_movement_detail->digit_amount = $quantity;
		$element_receiver_movement_detail->converted_amount = $quantity - $difference;
		$element_receiver_movement_detail->save();

		$element_receiver_article = Article::find($element_receiver_movement_detail->article_code);

		$element_receiver_article->stock_good -= $new_difference;
		$element_receiver_article->save();

		$element->referral_guide_series = $referral_guide_series;
		$element->referral_guide_number = $referral_guide_number;
		$element->referral_serie_number = $referral_serie_number;
		$element->referral_voucher_number = $referral_voucher_number;
		$element->scop_number = $scop_number;
		$element->license_plate = $license_plate;
		$element->license_plate_2 = $license_plate_2;
		$element->price_mes = $price_mes;

		if ( $typing_error ) {
			$element->movement_type_id = 29;
			$element->action_type_id = null;
		}

		$element->traslate_date = date('Y-m-d', strtotime($date));
		$element->created_at = date('Y-m-d', strtotime($date_emi));
		$element->save();

		$data = new stdClass();
		$data->type = 1;
		$data->title = '¡Ok!';
		$data->msg = 'Registro actualizado exitosamente.';

		return response()->json($data);
	}

	public function delete() {
		$id = request('id');
		$date = CarbonImmutable::now()->startOfDay();

		$element = WarehouseMovement::findOrFail($id);

		$element->deleted_at = $date;
		$element->save();

		$stock_ini = $element->stock_ini;
		$stock_pend = $element->stock_pend;

		$element_movement_detail = WarehouseMovementDetail::where('warehouse_movement_id', $id)
														->first();

		$element_movement_detail->deleted_at = $date;
		$element_movement_detail->save();

		$element_article = Article::find($element_movement_detail->article_code);

		$element_article->stock_good += $stock_ini;
		$element_article->save();

		$element_receiver = WarehouseMovement::whereNotIn('id', [$id])
											->where('movement_type_id', $element->movement_type_id)
											->where('movement_number', $element->movement_number)
											->first();

		$element_receiver->deleted_at = $date;
		$element_receiver->save();

		$element_receiver_movement_detail = WarehouseMovementDetail::where('warehouse_movement_id', $element_receiver->id)
																	->first();

		$element_receiver_movement_detail->deleted_at = $date;
		$element_receiver_movement_detail->save();

		$element_receiver_article = Article::find($element_receiver_movement_detail->article_code);

		$element_receiver_article->stock_good -= $stock_ini;
		$element_receiver_article->save();
	}
}
