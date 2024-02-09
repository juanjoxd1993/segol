<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\MoventClass;
use App\MoventType;
use App\WarehouseDocumentType;
use App\WarehouseType;
use App\Article;
use App\Currency;
use App\WarehouseAccountType;
use App\Client;
use App\Employee;
use App\MovementStockType;
use App\Provider;
use App\Rate;
use App\Unit;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Auth;
use Carbon\CarbonImmutable;
use PDF;

class StockRegisterController extends Controller
{
    public function index() {
		$movement_classes = MoventClass::select('id', 'name')->get();
		$movement_types = MoventType::select('id', 'movent_class', 'name')->get();
		$movement_stock_types = MovementStockType::select('id', 'name')->get();
		$warehouse_types = WarehouseType::select('id', 'name')->get();
		$companies = Company::select('id', 'name')->get();
		$currencies = Currency::select('id', 'name', 'symbol')->get();
		$current_date = date('d-m-Y');
		$date = CarbonImmutable::now()->startOfDay();
		$current_date = $date->startOfDay()->toAtomString();
		$min_datetime = $date->startOfDay()->toAtomString();
		$max_datetime = $date->startOfDay()->addDays(2)->toAtomString();
		$warehouse_account_types = WarehouseAccountType::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();
		$igv = Rate::select('description', 'value')
			->where('description', 'IGV')
			->where('state', 1)
			->first();

		return view('backend.stock_register')->with(compact('movement_classes', 'movement_types', 'movement_stock_types', 'warehouse_types', 'companies', 'currencies', 'current_date', 'min_datetime', 'max_datetime', 'warehouse_account_types', 'warehouse_document_types', 'igv'));
	}

	public function getAccounts() {
		$company_id = request('company_id');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$q = request('q');
		
		if ( $warehouse_account_type_id == 1 ) {
			$clients = Client::select('id', 'business_name')
				->where('company_id', $company_id)
				->where('business_name', 'like', '%'.$q.'%')
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 2 ) {			
			$clients = Provider::select('id', 'business_name')
				->where('business_name', 'like', '%'.$q.'%')
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 3 ) {
			$clients = Employee::select('id', 'first_name', 'last_name')
				->where(function ($query) use ($q) {
					$query->where('first_name', 'like', '%'.$q.'%')
						->orWhere('last_name', 'like', '%'.$q.'%');
				})
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->first_name . ' ' . $item->last_name;
				unset($item->first_name);
				unset($item->last_name);

				return $item;
			});
		}

		return $clients;
	}

	public function validateForm() {
		$messages = [
			'movement_class_id.required'						=> 'Debe seleccionar Ingreso o Salida.',
			'movement_type_id.required'							=> 'Debe seleccionar un Tipo de Movimiento.',
			'movement_stock_type_id.required_if'				=> 'Debe seleccionar un Tipo de Stock.',
			'warehouse_type_id.required'						=> 'Debe seleccionar un Almacén.',
			'company_id.required_if'							=> 'Debe seleccionar una Compañía.',
			'since_date.required'								=> 'Debe seleccionar una Fecha.',
			'referral_guide_series.required_if'					=> 'Debe digitar la Serie de Guía de Remisión.',
			'referral_guide_number.required_if'					=> 'Debe digitar el Número de Guía de Remisión.',
			'referral_warehouse_document_type_id.required_if'	=> 'Debe seleccionar un Tipo de Referencia.',
			'referral_serie_number.required_if'					=> 'Debe digitar la Serie de Referencia.',
			'referral_voucher_number.required_if'				=> 'Debe digitar el Número de Referencia.',
			'scop_number.required_if'							=> 'Debe digitar el Número de SCOP.',
			'license_plate.required_if'							=> 'Debe digitar el Número de Placa.',
		];

		$rules = [
			'movement_class_id'						=> 'required',
			'movement_type_id'						=> 'required',
			'movement_stock_type_id'				=> 'required_if:movement_type_id,21',
			'warehouse_type_id'						=> 'required',
			'company_id'							=> 'required_if:movement_type_id,1,2,11,12,13,14,15,16,17,19,20,21,22',
			'since_date'							=> 'required',
			'referral_guide_series'					=> 'required_if:movement_type_id,1,7,8,9,11,13,15,19,20',
			'referral_guide_number'					=> 'required_if:movement_type_id,1,7,8,9,11,13,15,19,20',
			'referral_warehouse_document_type_id'	=> 'required_if:movement_type_id,1,2,3,4,5,6,10,11,13,15,16,17,18,19,20,21,22',
			'referral_serie_number'					=> 'required_if:movement_type_id,1,2,3,4,5,6,10,11,13,15,16,17,18,19,20,21,22',
			'referral_voucher_number'				=> 'required_if:movement_type_id,1,2,3,4,5,6,10,11,13,15,16,17,18,19,20,21,22',
			'scop_number'							=> 'required_if:movement_type_id,1,12,13,14,15,16',
			'license_plate'							=> 'required_if:movement_type_id,11,12,13,14,19,20',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$this->validateForm();

		$movement_class_id = request('movement_class_id');
		$movement_type_id = request('movement_type_id');
		$movement_stock_type_id = request('movement_stock_type_id');
		$warehouse_type_id = request('warehouse_type_id');
		$company_id = request('company_id');
		$currency_id = request('currency');
		$since_date = request('since_date');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$warehouse_account_id = request('warehouse_account_id');
		$referral_guide_series = request('referral_guide_series');
		$referral_guide_number = request('referral_guide_number');
		$referral_warehouse_document_type_id = request('referral_warehouse_document_type_id');
		$referral_serie_number = request('referral_serie_number');
		$referral_voucher_number = request('referral_voucher_number');
		$scop_number = request('scop_number');
		$license_plate = request('license_plate');

		$model = request()->all();

		// Porcentage de Percepción del Cliente/Proveedor
		if ( $warehouse_account_type_id == 1 ) {
			$perception_percentage = Client::select('perception_percentage_id')
				->where('id', $warehouse_account_id)
				->first();
			
			$perception_percentage = $perception_percentage ? $perception_percentage->perception_percentage->value : 0;
		} elseif ( $warehouse_account_type_id == 2 ) {
			$perception_percentage = Provider::select('perception_agent_id')
				->where('id', $warehouse_account_id)
				->first();
			
			$perception_percentage = $perception_percentage ? $perception_percentage->perception_agent->value : 0;
		} else {
			$perception_percentage = 0;
		}

		// Obtener artículos
		$articles = Article::select('id', 'code', 'name', 'package_sale', 'sale_unit_id', 'package_warehouse', 'warehouse_unit_id', 'igv', 'perception', 'stock_good', 'stock_repair', 'stock_return', 'stock_damaged')
			->where('warehouse_type_id', $warehouse_type_id)
			->orderBy('code', 'asc')
			->get();
		
		$articles->map(function($item, $index) {
			$item->sale_unit_id = $item->sale_unit['name'];
			$item->warehouse_unit_id = $item->warehouse_unit['name'];
		});

		// Obtener tipo de moneda
		$currency = Currency::select('id', 'name', 'symbol')
			->where('id', $currency_id)
			->first();

		return response()->json([
			'model'					=> $model,
			'perception_percentage' => $perception_percentage,
			'currency'				=> $currency,
			'articles'				=> $articles
		]);
	}

	public function validateModalForm() {
		$messages = [
			'article_id.required'	=> 'Debe seleccionar un Artículo.',
			'quantity.required'		=> 'La Cantidad es obligatoria.',
		];

		$rules = [
			'article_id'	=> 'required',
			'quantity'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getArticle() {
		// $this->validateModalForm();

		$article_id = request('model.article_id');
		$quantity = request('model.quantity');
		$currency_id = request('currency');
		$price = request('model.price');
		$sale_value = request('model.sale_value');
		$inaccurate_value = request('model.inaccurate_value');
		$igv = request('model.igv');
		$total = request('model.total');
		$perception = request('model.perception');
		$igv_percentage = request('igv_percentage');
		$perception_percentage = request('perception_percentage');
		$item_number = request('item_number');
		$movement_type_id = request('movement_type_id');

		$currency = Currency::select('name')
			->where('id', $currency_id)
			->first();

		$article = Article::leftjoin('operation_types', 'operation_types.id', '=', 'articles.operation_type_id')
			->where('articles.id', $article_id)
			->select('articles.id', 'code', 'articles.name', 'package_sale', 'sale_unit_id', 'operation_type_id', 'factor', 'operation_types.name as operation_type_name', 'articles.business_type', 'seal')
			->first();
		
		$article->item_number = ++$item_number;
		$article->sale_unit_id = $article->sale_unit->name;
		$article->digit_amount = number_format($quantity, 4, '.', ',');
		if ( $movement_type_id == 1 || $movement_type_id == 2 ) {
			if ( $article->operation_type_name == 'Suma' ) {
				$article->converted_amount = number_format($quantity + $article->factor, 4, '.', ',');
			} elseif ( $article->operation_type_name == 'Resta' ) {
				$article->converted_amount = number_format($quantity - $article->factor, 4, '.', ',');
			} elseif ( $article->operation_type_name == 'Multiplica' ) {
				$article->converted_amount = number_format($quantity * $article->factor, 4, '.', ',');
			} elseif ( $article->operation_type_name == 'Divide' ) {
				$article->converted_amount = number_format($quantity / $article->factor, 4, '.', ',');
			}
		} else {
			$article->converted_amount = number_format($quantity, 4, '.', ',');
		}
		$article->currency = $currency->name;
		$article->currency_id = $currency_id;
		$article->price = number_format($price, 4, '.', ',');
		$article->sale_value = number_format($sale_value, 4, '.', ',');
		$article->inaccurate_value = number_format($inaccurate_value, 4, '.', ',');
		$article->igv = number_format($igv, 4, '.', ',');
		$article->total = number_format($total, 4, '.', ',');
		$article->perception = number_format($perception, 4, '.', ',');
		$article->igv_percentage = ( $igv == 0 ? 0 : $igv_percentage );
		$article->perception_percentage = $perception_percentage;

		return $article;
	}

	public function store() {
		$movement_class_id = request('model.movement_class_id');
		$movement_type_id = request('model.movement_type_id');
		$movement_stock_type_id = request('model.movement_stock_type_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		$company_id = request('model.company_id');
		$currency_id = request('model.currency');
		$since_date = request('model.since_date');
		$warehouse_account_type_id = request('model.warehouse_account_type_id');
		$warehouse_account_id = request('model.warehouse_account_id');
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');
		$referral_warehouse_document_type_id = request('model.referral_warehouse_document_type_id');
		$referral_serie_number = request('model.referral_serie_number');
		$referral_voucher_number = request('model.referral_voucher_number');
		$scop_number = request('model.scop_number');
		$license_plate = request('model.license_plate');
		$articles = request('article_list');

		$movement_number = WarehouseMovement::select('movement_number')
			->where('movement_class_id', $movement_class_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('company_id', $company_id)
			->max('movement_number');
		
		$movement_number = ( $movement_number ? $movement_number + 1 : 1 );

		$account = null;

		if ( $warehouse_account_type_id == 1 ) {
			$account = Client::select('business_name', 'document_number')
				->where('id', $warehouse_account_id)
				->first();
		} elseif ( $warehouse_account_type_id == 2 ) {
			$account = Provider::select('business_name', 'document_number')
				->where('id', $warehouse_account_id)
				->first();
		} elseif ( $warehouse_account_type_id == 3 ) {
			$account = Employee::select('first_name', 'last_name')
				->where('id', $warehouse_account_id)
				->first();
			
			if ($account) {
				$account->business_name = $account->first_name . ' ' . $account->last_name;
				$account->document_number = '';
			}
		}

		$movement_type = MoventType::find($movement_type_id);

		$movement = new WarehouseMovement();
		$movement->company_id = $company_id;
		$movement->warehouse_type_id = $warehouse_type_id;
		$movement->movement_class_id = $movement_class_id;
		$movement->movement_type_id = $movement_type_id;
		$movement->movement_stock_type_id = $movement_stock_type_id;
		$movement->movement_number = $movement_number;
		$movement->warehouse_account_type_id = $warehouse_account_type_id;
		$movement->account_id = $warehouse_account_id;
		$movement->account_document_number = $account ? $account->document_number : '';
		$movement->account_name = $account ? $account->business_name : '';
		$movement->referral_guide_series = $referral_guide_series;
		$movement->referral_guide_number = $referral_guide_number;
		$movement->referral_warehouse_document_type_id = $referral_warehouse_document_type_id;
		$movement->referral_serie_number = $referral_serie_number;
		$movement->referral_voucher_number = $referral_voucher_number;
		$movement->scop_number = $scop_number;
		$movement->license_plate = $license_plate;
		$movement->currency_id = $currency_id;
		$movement->taxed_operation = array_sum(array_column($articles, 'sale_value'));
		$movement->unaffected_operation = array_sum(array_column($articles, 'inaccurate_value'));
		$movement->exonerated_operation = 0;
		$movement->igv = array_sum(array_column($articles, 'igv'));
		$movement->total = array_sum(array_column($articles, 'total'));
		$movement->total_perception = array_sum(array_column($articles, 'perception'));
		$movement->action_type_id = ( $movement_type ? $movement_type->action_type_id : '' );
		$movement->created_at = date('Y-m-d', strtotime($since_date));
		$movement->created_at_user = Auth::user()->user;
		$movement->updated_at_user = Auth::user()->user;
		$movement->save();

		$movement2 = new WarehouseMovement();
		$movement2->company_id = $company_id;
		$movement2->warehouse_type_id = 1;
		$movement2->movement_class_id = 2;
		$movement2->movement_type_id = 6;
		$movement2->movement_stock_type_id = $movement_stock_type_id;
		$movement2->movement_number = $movement_number;
		$movement2->warehouse_account_type_id = $warehouse_account_type_id;
		$movement2->account_id = $warehouse_account_id;
		$movement2->account_document_number = $account ? $account->document_number : '';
		$movement2->account_name = $account ? $account->business_name : '';
		$movement2->referral_guide_series = $referral_guide_series;
		$movement2->referral_guide_number = $referral_guide_number;
		$movement2->referral_warehouse_document_type_id = $referral_warehouse_document_type_id;
		$movement2->referral_serie_number = $referral_serie_number;
		$movement2->referral_voucher_number = $referral_voucher_number;
		$movement2->scop_number = $scop_number;
		$movement2->license_plate = $license_plate;
		$movement2->currency_id = $currency_id;
		$movement2->taxed_operation = array_sum(array_column($articles, 'sale_value'));
		$movement2->unaffected_operation = array_sum(array_column($articles, 'inaccurate_value'));
		$movement2->exonerated_operation = 0;
		$movement2->igv = array_sum(array_column($articles, 'igv'));
		$movement2->total = array_sum(array_column($articles, 'total'));
		$movement2->total_perception = array_sum(array_column($articles, 'perception'));
		$movement2->action_type_id = ( $movement_type ? $movement_type->action_type_id : '' );
		$movement2->created_at = date('Y-m-d', strtotime($since_date));
		$movement2->created_at_user = Auth::user()->user;
		$movement2->updated_at_user = Auth::user()->user;
		$movement2->save();

		$movement3 = new WarehouseMovement();
		$movement3->company_id = $company_id;
		$movement3->warehouse_type_id = $warehouse_type_id;
		$movement3->movement_class_id = 2;
		$movement3->movement_type_id = 6;
		$movement3->movement_stock_type_id = $movement_stock_type_id;
		$movement3->movement_number = $movement_number;
		$movement3->warehouse_account_type_id = $warehouse_account_type_id;
		$movement3->account_id = $warehouse_account_id;
		$movement3->account_document_number = $account ? $account->document_number : '';
		$movement3->account_name = $account ? $account->business_name : '';
		$movement3->referral_guide_series = $referral_guide_series;
		$movement3->referral_guide_number = $referral_guide_number;
		$movement3->referral_warehouse_document_type_id = $referral_warehouse_document_type_id;
		$movement3->referral_serie_number = $referral_serie_number;
		$movement3->referral_voucher_number = $referral_voucher_number;
		$movement3->scop_number = $scop_number;
		$movement3->license_plate = $license_plate;
		$movement3->currency_id = $currency_id;
		$movement3->taxed_operation = array_sum(array_column($articles, 'sale_value'));
		$movement3->unaffected_operation = array_sum(array_column($articles, 'inaccurate_value'));
		$movement3->exonerated_operation = 0;
		$movement3->igv = array_sum(array_column($articles, 'igv'));
		$movement3->total = array_sum(array_column($articles, 'total'));
		$movement3->total_perception = array_sum(array_column($articles, 'perception'));
		$movement3->action_type_id = ( $movement_type ? $movement_type->action_type_id : '' );
		$movement3->created_at = date('Y-m-d', strtotime($since_date));
		$movement3->created_at_user = Auth::user()->user;
		$movement3->updated_at_user = Auth::user()->user;
		$movement3->save();

		foreach ($articles as $item) {
			$article = Article::where('warehouse_type_id', $movement->warehouse_type_id)
				->where('id', $item['id'])
				->firstOrFail();

			$digit_amount = str_replace(',', '', $item['digit_amount']);
			$converted_amount = str_replace(',', '', $item['converted_amount']);
			$price = str_replace(',', '', $item['price']);
			$sale_value = str_replace(',', '', $item['sale_value']);
			$inaccurate_value = str_replace(',', '', $item['inaccurate_value']);
			$igv = str_replace(',', '', $item['igv']);
			$total = str_replace(',', '', $item['total']);
			$igv_perception = str_replace(',', '', $item['perception']);

			if ($movement_class_id == 2 && $movement_type_id == 6 && $warehouse_type_id == 4) {
				$relatedArticles = Article::where('warehouse_type_id', 1)->get();

				foreach ($relatedArticles as $relatedArticle) {
					$relatedArticle->stock_good -= $item['digit_amount'];
					$relatedArticle->save();
				}

				$relatedArticles2 = Article::where('warehouse_type_id', 1)->where('code', $article->code)->get();

				foreach ($relatedArticles2 as $relatedArticle) {
					$relatedArticle->stock_good -= $item['digit_amount'];
					$relatedArticle->save();

					$movementDetail2 = new WarehouseMovementDetail();
					$movementDetail2->warehouse_movement_id = $movement2->id;
					$movementDetail2->item_number = $item['item_number'];
					$movementDetail2->article_code = $relatedArticle->id;
					$movementDetail2->digit_amount = $digit_amount;
					$movementDetail2->converted_amount = $converted_amount;
					$movementDetail2->old_stock_good = $relatedArticle->stock_good;
					$movementDetail2->old_stock_repair = $relatedArticle->stock_repair;
					$movementDetail2->old_stock_return = $relatedArticle->stock_return;
					$movementDetail2->old_stock_damaged = $relatedArticle->stock_damaged;
					$movementDetail2->new_stock_good = $relatedArticle->stock_good;
					$movementDetail2->new_stock_repair = $relatedArticle->stock_repair;
					$movementDetail2->new_stock_return = $relatedArticle->stock_return;
					$movementDetail2->new_stock_damaged = $relatedArticle->stock_damaged;
					$movementDetail2->currency_id = $item['currency_id'];
					$movementDetail2->price = $price;
					$movementDetail2->sale_value = $sale_value;
					$movementDetail2->exonerated_value = 0;
					$movementDetail2->inaccurate_value = $inaccurate_value;
					$movementDetail2->igv = $igv;
					$movementDetail2->total = $total;
					$movementDetail2->igv_perception = $igv_perception;
					$movementDetail2->igv_percentage = $item['igv_percentage'];
					$movementDetail2->igv_perception_percentage = $item['perception_percentage'];
					$movementDetail2->created_at_user = Auth::user()->user;
					$movementDetail2->updated_at_user = Auth::user()->user;
					$movementDetail2->save();
				}

				$relatedArticles3 = Article::where('warehouse_type_id', 4)->where('code', $article->code)->get();

				foreach ($relatedArticles3 as $relatedArticle) {
					$relatedArticle->stock_good += $item['digit_amount'];
					$relatedArticle->save();

					$movementDetail3 = new WarehouseMovementDetail();
					$movementDetail3->warehouse_movement_id = $movement3->id;
					$movementDetail3->item_number = $item['item_number'];
					$movementDetail3->article_code = $relatedArticle->id;
					$movementDetail3->digit_amount = $digit_amount;
					$movementDetail3->converted_amount = $converted_amount;
					$movementDetail3->old_stock_good = $relatedArticle->stock_good;
					$movementDetail3->old_stock_repair = $relatedArticle->stock_repair;
					$movementDetail3->old_stock_return = $relatedArticle->stock_return;
					$movementDetail3->old_stock_damaged = $relatedArticle->stock_damaged;
					$movementDetail3->new_stock_good = $relatedArticle->stock_good;
					$movementDetail3->new_stock_repair = $relatedArticle->stock_repair;
					$movementDetail3->new_stock_return = $relatedArticle->stock_return;
					$movementDetail3->new_stock_damaged = $relatedArticle->stock_damaged;
					$movementDetail3->currency_id = $item['currency_id'];
					$movementDetail3->price = $price;
					$movementDetail3->sale_value = $sale_value;
					$movementDetail3->exonerated_value = 0;
					$movementDetail3->inaccurate_value = $inaccurate_value;
					$movementDetail3->igv = $igv;
					$movementDetail3->total = $total;
					$movementDetail3->igv_perception = $igv_perception;
					$movementDetail3->igv_percentage = $item['igv_percentage'];
					$movementDetail3->igv_perception_percentage = $item['perception_percentage'];
					$movementDetail3->created_at_user = Auth::user()->user;
					$movementDetail3->updated_at_user = Auth::user()->user;
					$movementDetail3->save();
				}
			}

			$movementDetail = new WarehouseMovementDetail();
			$movementDetail->warehouse_movement_id = $movement->id;
			$movementDetail->item_number = $item['item_number'];
			$movementDetail->article_code = $item['id'];
			$movementDetail->digit_amount = $digit_amount;
			$movementDetail->converted_amount = $converted_amount;
			$movementDetail->old_stock_good = $article->stock_good;
			$movementDetail->old_stock_repair = $article->stock_repair;
			$movementDetail->old_stock_return = $article->stock_return;
			$movementDetail->old_stock_damaged = $article->stock_damaged;
			$movementDetail->new_stock_good = $article->stock_good;
			$movementDetail->new_stock_repair = $article->stock_repair;
			$movementDetail->new_stock_return = $article->stock_return;
			$movementDetail->new_stock_damaged = $article->stock_damaged;
			$movementDetail->currency_id = $item['currency_id'];
			$movementDetail->price = $price;
			$movementDetail->sale_value = $sale_value;
			$movementDetail->exonerated_value = 0;
			$movementDetail->inaccurate_value = $inaccurate_value;
			$movementDetail->igv = $igv;
			$movementDetail->total = $total;
			$movementDetail->igv_perception = $igv_perception;
			$movementDetail->igv_percentage = $item['igv_percentage'];
			$movementDetail->igv_perception_percentage = $item['perception_percentage'];
			$movementDetail->created_at_user = Auth::user()->user;
			$movementDetail->updated_at_user = Auth::user()->user;
			
			if ( $movement->movement_class_id == 1 ) {
				$article->stock_good += $movementDetail->converted_amount;
				$movementDetail->new_stock_good += $movementDetail->converted_amount;
				
				if ( $movement->movement_type_id == 1 || $movement->movement_type_id == 2 ) {
					$article->last_price = $movementDetail->price;
				}
			} elseif ( $movement->movement_class_id == 2 ) {
				if ( $movement->movement_type_id == 15 ) {
					$article->stock_return -= $movementDetail->converted_amount;
					$movementDetail->new_stock_return -= $movementDetail->converted_amount;
				} elseif ( $movement->movement_type_id == 4 ) {
					$article->stock_repair -= $movementDetail->converted_amount;
					$movementDetail->new_stock_repair -= $movementDetail->converted_amount;
				} else {
					$article->stock_good -= $movementDetail->converted_amount;
					$movementDetail->new_stock_good -= $movementDetail->converted_amount;

					if ( $movement->movement_type_id == 21 && $movement->movement_stock_type_id == 1 ) {
						$article->stock_return += $movementDetail->converted_amount;
						$movementDetail->new_stock_return += $movementDetail->converted_amount;
					} elseif ( $movement->movement_type_id == 21 && $movement->movement_stock_type_id == 2 ) {
						$article->stock_repair += $movementDetail->converted_amount;
						$movementDetail->new_stock_repair += $movementDetail->converted_amount;
					} elseif ( $movement->movement_type_id == 21 && $movement->movement_stock_type_id == 3 ) {
						$article->stock_damaged += $movementDetail->converted_amount;
						$movementDetail->new_stock_damaged += $movementDetail->converted_amount;
					}
				}
			}

			$movementDetail->save();

			$article->edit = 1;
			$article->save();
		}

		return $articles;
	}
}