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
use App\ClientRoute;
use App\GuidesSerie;
use App\Vehicle;
use Auth;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
use PDF;

class GuidesRegisterController extends Controller
{
	public function index()
	{
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

		$guide_series = GuidesSerie::select('id', 'num_serie', 'correlative')->get();


		$igv = Rate::select('description', 'value')
			->where('description', 'IGV')
			->where('state', 1)
			->first();

		return view('backend.guides_register')->with(compact('movement_classes', 'movement_types', 'movement_stock_types', 'warehouse_types', 'companies', 'currencies', 'current_date', 'min_datetime', 'max_datetime', 'warehouse_account_types', 'warehouse_document_types', 'igv', 'guide_series'));
	}


	public function getNextcorrelative()
	{
		$last_guide_serie = GuidesSerie::select('correlative')
			->latest('id')
			->where('company_id', request('company'))
			->where('num_serie', request('guide_serie'))
			->first();

		$next_correlative = $last_guide_serie ? $last_guide_serie->correlative + 1 : 0;

		return response()->json($next_correlative, 200);

		return response()->json($next_correlative, 200);
	}
	public function getAccounts()
	{
		$company_id = request('company_id');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$q = request('q');

		if ($warehouse_account_type_id == 1) {
			$clients = Client::select('id', 'business_name')
				->where('company_id', $company_id)
				->where('business_name', 'like', '%' . $q . '%')
				->get();

			$clients->map(function ($item, $index) {
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ($warehouse_account_type_id == 2) {
			$clients = Provider::select('id', 'business_name')
				->where('business_name', 'like', '%' . $q . '%')
				->get();

			$clients->map(function ($item, $index) {
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ($warehouse_account_type_id == 3) {
			$clients = Employee::select('id', 'first_name', 'last_name')
				->where(function ($query) use ($q) {
					$query->where('first_name', 'like', '%' . $q . '%')
						->orWhere('last_name', 'like', '%' . $q . '%');
				})
				->get();

			$clients->map(function ($item, $index) {
				$item->text = $item->first_name . ' ' . $item->last_name;
				unset($item->first_name);
				unset($item->last_name);

				return $item;
			});
		}

		return $clients;
	}

	public function validateForm()
	{
		$messages = [




			'company_id.required_if'							=> 'Debe seleccionar una Compañía.',
			'since_date.required'								=> 'Debe seleccionar una Fecha.',
			'referral_guide_series.required_if'			        => 'Debe digitar la Serie de Guía de Remisión.',
			'referral_guide_number.required_if'					=> 'Debe digitar el Número de Guía de Remisión.',
			//	'vehicle_id.required_if'							=> 'Debe digitar el Número de Placa.',
			'traslate_date.required_if'                         => 'Debe seleccionar la fecha de traslado.',

		];

		$rules = [

			'company_id'							=> 'required_if:movement_type_id,1,2,11,12,13,14,15,16,17,19,20,21,22',
			'since_date'							=> 'required',
			'referral_guide_series'					=> 'required_if:movement_type_id,1,7,8,9,11,13,15,19,20',
			'referral_guide_number'					=> 'required_if:movement_type_id,1,7,8,9,11,13,15,19,20',
			//	'scop_number'							=> 'required_if:movement_type_id,1,12,13,14,15,16',
			//	'vehicle_id'							=> 'required_if:movement_type_id,11,12,13,14,19,20',
			'traslate_date'							=> 'required',
			//	'route_id'							    => 'required',

		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list()
	{
		$this->validateForm();

		$movement_class_id = 2;
		$movement_type_id = request('movement_type_id');
		$warehouse_type_id = 5;
		$company_id = request('company_id');
		$traslate_date = request('traslate_date');
		$since_date = request('since_date');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$warehouse_account_id = request('warehouse_account_id');
		$referral_guide_series = request('referral_guide_series');
		$referral_guide_number = request('referral_guide_number');
		$scop_number = request('scop_number');
		$license_plate = request('license_plate');
		//	$route_id = request('route_id');

		$model = request()->all();


		// Porcentage de Percepción del Cliente/Proveedor
		if ($warehouse_account_type_id == 1) {
			$perception_percentage = Client::select('perception_percentage_id')
				->where('id', $warehouse_account_id)
				->first();

			$perception_percentage = $perception_percentage->perception_percentage->value;
		} elseif ($warehouse_account_type_id == 2) {
			$perception_percentage = Provider::select('perception_agent_id')
				->where('id', $warehouse_account_id)
				->first();

			$perception_percentage = $perception_percentage->perception_agent->value;
		} else {
			$perception_percentage = 0;
		}

		// Obtener artículos
		$articles = Article::select('id', 'code', 'name', 'package_sale', 'sale_unit_id', 'package_warehouse', 'warehouse_unit_id', 'igv', 'perception', 'stock_good', 'stock_repair', 'stock_return', 'stock_damaged')
			->where('warehouse_type_id', $warehouse_type_id)
			->orderBy('code', 'asc')
			->get();

		$articles->map(function ($item, $index) {
			$item->sale_unit_id = $item->sale_unit['name'];
			$item->warehouse_unit_id = $item->warehouse_unit['name'];
		});



		return response()->json([
			'model'					=> $model,
			'perception_percentage' => $perception_percentage,

			'articles'				=> $articles
		]);
	}

	public function validateModalForm()
	{
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

	public function getArticle()
	{
		// $this->validateModalForm();

		$article_id = request('model.article_id');
		$quantity = request('model.quantity');
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
		$warehouse_account_type_id = request('model.warehouse_account_type_id');

		$articles = array();

		$article = Article::leftjoin(
				'operation_types',
				'operation_types.id',
				'=',
				'articles.operation_type_id'
			)
			->where(
				'articles.id',
				$article_id
			)
			->select(
				'articles.id',
				'code',
				'articles.name',
				'package_sale',
				'sale_unit_id',
				'operation_type_id',
				'factor',
				'operation_types.name as operation_type_name',
				'business_type',
				'convertion',
				'group_id'
			)
			->first();

		$article->item_number = ++$item_number;
		$article->sale_unit_id = $article->sale_unit->name;
		$article->digit_amount = number_format($quantity, 4, '.', ',');
		if ($movement_type_id == 1 || $movement_type_id == 2) {
			if ($article->operation_type_name == 'Suma') {
				$article->converted_amount = number_format($quantity + $article->factor, 4, '.', ',');
			} elseif ($article->operation_type_name == 'Resta') {
				$article->converted_amount = number_format($quantity - $article->factor, 4, '.', ',');
			} elseif ($article->operation_type_name == 'Multiplica') {
				$article->converted_amount = number_format($quantity * $article->factor, 4, '.', ',');
			} elseif ($article->operation_type_name == 'Divide') {
				$article->converted_amount = number_format($quantity / $article->factor, 4, '.', ',');
			}
		} else {
			$article->converted_amount = number_format($quantity, 4, '.', ',');
		}

		$article->price = number_format($price, 4, '.', ',');
		$article->sale_value = number_format($sale_value, 4, '.', ',');
		$article->inaccurate_value = number_format($inaccurate_value, 4, '.', ',');
		$article->igv = number_format($igv, 4, '.', ',');
		$article->total = number_format($total, 4, '.', ',');
		$article->perception = number_format($perception, 4, '.', ',');
		$article->igv_percentage = ($igv == 0 ? 0 : $igv_percentage);
		$article->perception_percentage = $perception_percentage;

		array_push($articles, $article);

		if ($article->group_id == 7) {
			return response()->json(['isSuccess' => true, 'articles' => $articles]);
		};

		//Encontrar conversión
		$article2 = Article::where(function ($query) {
				$query->where('group_id', 7) //Envases
					->orWhere('group_id', 26); //Artículos
			})
			->where('convertion', $article->convertion)
			->first();

		if (!$article2) {
			return response()->json(['isSuccess' => false]);
		}

		$article2->item_number = ++$item_number;
		$article2->sale_unit_id = $article2->sale_unit->name;
		$article2->digit_amount = number_format($quantity, 4, '.', ',');
		if ($movement_type_id == 1 || $movement_type_id == 2) {
			if ($article2->operation_type_name == 'Suma') {
				$article2->converted_amount = number_format($quantity + $article2->factor, 4, '.', ',');
			} elseif ($article2->operation_type_name == 'Resta') {
				$article2->converted_amount = number_format($quantity - $article2->factor, 4, '.', ',');
			} elseif ($article2->operation_type_name == 'Multiplica') {
				$article2->converted_amount = number_format($quantity * $article2->factor, 4, '.', ',');
			} elseif ($article->operation_type_name == 'Divide') {
				$article2->converted_amount = number_format($quantity / $article2->factor, 4, '.', ',');
			}
		} else {
			$article2->converted_amount = number_format($quantity, 4, '.', ',');
		}

		$article2->price = number_format($price, 4, '.', ',');
		$article2->sale_value = number_format($sale_value, 4, '.', ',');
		$article2->inaccurate_value = number_format($inaccurate_value, 4, '.', ',');
		$article2->igv = number_format($igv, 4, '.', ',');
		$article2->total = number_format($total, 4, '.', ',');
		$article2->perception = number_format($perception, 4, '.', ',');
		$article2->igv_percentage = ($igv == 0 ? 0 : $igv_percentage);
		$article2->perception_percentage = $perception_percentage;

		array_push($articles, $article2);

		if ($movement_type_id == 11) {
			/************ Pre-Venta ********/

			//Actualizar stock good y repair
			$m_article = Article::find($article_id);
			$m_article->stock_good = $article->stock_good - $quantity;
			$m_article->stock_repair = $article->stock_repair - $quantity;
			$m_article->save();

			//Generar Movimiento de salida - Mercaderías
			$id = WarehouseMovement::insertGetId([
				'company_id' => 1,
				'warehouse_type_id' => 5, //Mercaderías
				'movement_class_id' => 2, //Salida
				'movement_type_id' => 5, //Compras
				'warehouse_account_type_id' => $warehouse_account_type_id,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			WarehouseMovementDetail::insert([
				'warehouse_movement_id' => $id,
				'item_number' => 1,
				'article_code' => $article->id,
				'new_stock_good' => $quantity,
				'converted_amount' => $quantity,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			//Restar stock artículo por la salida
			$m_article->stock_good = $article->stock_good - $quantity;
			$m_article->save();

			//Generar Movimiento de ingreso - Producción
			$id = WarehouseMovement::insertGetId([
				'company_id' => 1,
				'warehouse_type_id' => 4, //Producción ATE
				'movement_class_id' => 1, //Salida
				'movement_type_id' => 5,
				'warehouse_account_type_id' => $warehouse_account_type_id,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			WarehouseMovementDetail::insert([
				'warehouse_movement_id' => $id,
				'item_number' => 1,
				'article_code' => $article->id,
				'new_stock_good' => $quantity,
				'converted_amount' => $quantity,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			//Sumar stock artículo por la salida
			$m_article->stock_good = $article->stock_good + $quantity;
			$m_article->save();

			//Salida Por Pre Venta
			$id = WarehouseMovement::insertGetId([
				'company_id' => 1,
				'warehouse_type_id' => 4, //Producción
				'movement_class_id' => 2, //Salida
				'movement_type_id' => 11, //Pre-venta
				'warehouse_account_type_id' => $warehouse_account_type_id,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			WarehouseMovementDetail::insert([
				'warehouse_movement_id' => $id,
				'item_number' => 1,
				'article_code' => $article->id,
				'new_stock_good' => $quantity,
				'converted_amount' => $quantity,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			//Restar stock artículo por la salida
			$m_article->stock_good = $article->stock_good - $quantity;
			$m_article->save();
		} else {
			//Generar Movimiento de ingreso - Producción
			$id = WarehouseMovement::insertGetId([
				'company_id' => 1,
				'warehouse_type_id' => 4, //Producción ATE
				'movement_class_id' => 1, //Ingreso
				'movement_type_id' => $movement_type_id,
				'warehouse_account_type_id' => $warehouse_account_type_id,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			WarehouseMovementDetail::insert([
				'warehouse_movement_id' => $id,
				'item_number' => 1,
				'article_code' => $article->id,
				'new_stock_good' => $quantity,
				'converted_amount' => $quantity,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			//Salida por Venta - Planta
			$id = WarehouseMovement::insertGetId([
				'company_id' => 1,
				'warehouse_type_id' => 4, //Mercaderías
				'movement_class_id' => 2, //Salida
				'movement_type_id' => $movement_type_id,
				'warehouse_account_type_id' => $warehouse_account_type_id,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);

			WarehouseMovementDetail::insert([
				'warehouse_movement_id' => $id,
				'item_number' => 1,
				'article_code' => $article->id,
				'new_stock_good' => $quantity,
				'converted_amount' => $quantity,
				'total' => $quantity,
				'created_at' => date('Y-m-d'),
				'updated_at' => date('Y-m-d'),
			]);
		}

		return response()->json(['isSuccess' => true, 'articles' => $articles]);
	}

	public function store()
	{
		$movement_class_id = 2;
		$movement_type_id = request('model.movement_type_id');
		$warehouse_type_id = 5;
		$company_id = request('model.company_id');
		$since_date = request('model.since_date');
		$traslate_date = request('model.traslate_date');
		$fac_date = request('model.traslate_date');
		$warehouse_account_type_id = request('model.warehouse_account_type_id');
		$warehouse_account_id = request('model.warehouse_account_id');
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');
		$scop_number = request('model.scop_number');
		$license_plate = request('model.license_plate');
		//	$route_id = request('model.route_id');
		$articles = request('article_list');

		$tmpGuideSerie = GuidesSerie::where('company_id', $company_id)
			->where('num_serie', $referral_guide_series)
			->orderBy('correlative', 'desc')
			->first();

		if ($tmpGuideSerie) {
			$tmpGuideSerie->correlative = $tmpGuideSerie->correlative ? ($tmpGuideSerie->correlative + 1) : 1;
			$tmpGuideSerie->save();
		}

		$movement_number = WarehouseMovement::select('movement_number')
			->where('movement_class_id', $movement_class_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('company_id', $company_id)
			->max('movement_number');

		$movement_number = ($movement_number ? $movement_number + 1 : 1);

		$account = null;

		if ($warehouse_account_type_id == 1) {
			$account = Client::select('business_name', 'document_number')
				->where('id', $warehouse_account_id)
				->first();
		} elseif ($warehouse_account_type_id == 2) {
			$account = Provider::select('business_name', 'document_number')
				->where('id', $warehouse_account_id)
				->first();
		} elseif ($warehouse_account_type_id == 3) {
			$account = Employee::select('first_name', 'last_name')
				->where('id', $warehouse_account_id)
				->first();

			$account->business_name = $account ? ($account->first_name . ' ' . $account->last_name) : '';
			$account->document_number = '';
		}

		$movement_type = MoventType::find($movement_type_id);



		$movement = new WarehouseMovement();
		$movement->company_id = $company_id;
		$movement->warehouse_type_id = $warehouse_type_id;
		$movement->movement_class_id = $movement_class_id;
		$movement->movement_type_id = $movement_type_id;
		$movement->movement_number = $movement_number;
		$movement->warehouse_account_type_id = $warehouse_account_type_id;
		$movement->account_id = $warehouse_account_id;
		$movement->account_document_number = $account ? $account->document_number : '';
		$movement->account_name = $account ? $account->business_name : '';
		$movement->referral_guide_series = $referral_guide_series;
		$movement->referral_guide_number = $referral_guide_number;
		$movement->scop_number = $scop_number;
		$movement->license_plate = $license_plate;
		$movement->taxed_operation = array_sum(array_column($articles, 'sale_value'));
		$movement->unaffected_operation = array_sum(array_column($articles, 'inaccurate_value'));
		$movement->exonerated_operation = 0;
		$movement->igv = array_sum(array_column($articles, 'igv'));
		$movement->total = array_sum(array_column($articles, 'total'));
		$movement->total_perception = array_sum(array_column($articles, 'perception'));
		$movement->action_type_id = ($movement_type ? $movement_type->action_type_id : null);
		$movement->created_at = date('Y-m-d', strtotime($since_date));
		$movement->created_at_user = Auth::user()->user;
		$movement->updated_at_user = Auth::user()->user;
		$movement->traslate_date = date('Y-m-d', strtotime($traslate_date));
		$movement->fac_date = date('Y-m-d', strtotime($traslate_date));
		//	$movement->route_id = $route_id;

		$movement->save();

		$movement2 = new WarehouseMovement();
		$movement2->company_id = $company_id;
		$movement2->warehouse_type_id = 4;
		$movement2->movement_class_id = 2;
		$movement2->movement_type_id = 21;
		$movement2->movement_number = $movement_number;
		$movement2->warehouse_account_type_id = $warehouse_account_type_id;
		$movement2->account_id = $warehouse_account_id;
		$movement2->account_document_number = $account ? $account->document_number : '';
		$movement2->account_name = $account ? $account->business_name : '';
		$movement2->referral_guide_series = $referral_guide_series;
		$movement2->referral_guide_number = $referral_guide_number;
		$movement2->scop_number = $scop_number;
		$movement2->license_plate = $license_plate;
		$movement2->taxed_operation = array_sum(array_column($articles, 'sale_value'));
		$movement2->unaffected_operation = array_sum(array_column($articles, 'inaccurate_value'));
		$movement2->exonerated_operation = 0;
		$movement2->igv = array_sum(array_column($articles, 'igv'));
		$movement2->total = array_sum(array_column($articles, 'total'));
		$movement2->total_perception = array_sum(array_column($articles, 'perception'));
		$movement2->action_type_id = ($movement_type ? $movement_type->action_type_id : null);
		$movement2->created_at = date('Y-m-d', strtotime($since_date));
		$movement2->created_at_user = Auth::user()->user;
		$movement2->updated_at_user = Auth::user()->user;
		$movement2->traslate_date = date('Y-m-d', strtotime($traslate_date));
		$movement2->fac_date = date('Y-m-d', strtotime($traslate_date));
		//	$movement2->route_id = $route_id;

		$movement2->save();

		foreach ($articles as $item) {
			$article = Article::find($item['id']);

			if ($article) {

				$digit_amount = str_replace(',', '', $item['digit_amount']);
				$converted_amount = str_replace(',', '', $item['converted_amount']);
				$price = str_replace(',', '', $item['price']);
				$sale_value = str_replace(',', '', $item['sale_value']);
				$inaccurate_value = str_replace(',', '', $item['inaccurate_value']);
				$igv = str_replace(',', '', $item['igv']);
				$total = str_replace(',', '', $item['total']);
				$igv_perception = str_replace(',', '', $item['perception']);

				$movementDetail = new WarehouseMovementDetail();
				$movementDetail->warehouse_movement_id = $movement->id;
				$movementDetail->item_number = $item['item_number'];
				$movementDetail->article_code = $item['id'];
				$movementDetail->digit_amount = $digit_amount;
				$movementDetail->converted_amount = $converted_amount;
				$movementDetail->old_stock_good = $article->stock_good;
				// $movementDetail->old_stock_repair = $article->stock_repair;
				// $movementDetail->old_stock_return = $article->stock_return;
				// $movementDetail->old_stock_damaged = $article->stock_damaged;
				$movementDetail->new_stock_good = $article->stock_good;
				// $movementDetail->new_stock_repair = $article->stock_repair;
				// $movementDetail->new_stock_return = '';
				// $movementDetail->new_stock_damaged = $article->stock_damaged;
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

				if ($movement->movement_class_id == 1) {
					$article->stock_good += $movementDetail->converted_amount;
					$movementDetail->new_stock_good += $movementDetail->converted_amount;

					if ($movement->movement_type_id == 1 || $movement->movement_type_id == 2) {
						$article->last_price = $movementDetail->price;
					}
				} elseif ($movement->movement_class_id == 2) {
					if ($movement->movement_type_id == 15) {
						$article->stock_return -= $movementDetail->converted_amount;
						// $movementDetail->new_stock_return -= $movementDetail->converted_amount;
					} elseif ($movement->movement_type_id == 4) {
						$article->stock_repair -= $movementDetail->converted_amount;
						// $movementDetail->new_stock_repair -= $movementDetail->converted_amount;
					} else {
						$article->stock_good -= $movementDetail->converted_amount;
						$movementDetail->new_stock_good -= $movementDetail->converted_amount;

						if ($movement->movement_type_id == 18 && $movement->movement_stock_type_id == 1) {
							$article->stock_return += $movementDetail->converted_amount;
							// $movementDetail->new_stock_return += $movementDetail->converted_amount;
						} elseif ($movement->movement_type_id == 18 && $movement->movement_stock_type_id == 2) {
							$article->stock_repair += $movementDetail->converted_amount;
							// $movementDetail->new_stock_repair += $movementDetail->converted_amount;
						} elseif ($movement->movement_type_id == 18 && $movement->movement_stock_type_id == 3) {
							$article->stock_damaged += $movementDetail->converted_amount;
							// $movementDetail->new_stock_damaged += $movementDetail->converted_amount;
						}
					}
				}

				$movementDetail->save();

				if ($movement_type_id == 11) {
					$relatedArticlesForIcreaseUnits = Article::where('warehouse_type_id', 4)
						->where('business_type', $item['business_type'])
						->where('group_id', 7) // Sólo envases
						->where('convertion', $item['convertion'])
						->get();

					foreach ($relatedArticlesForIcreaseUnits as $relatedArticle) {
						$relatedArticle->stock_repair -= $item['digit_amount'];
						$relatedArticle->stock_damaged += $item['digit_amount'];
						$relatedArticle->save();

						$movementDetail2 = new WarehouseMovementDetail();
						$movementDetail2->warehouse_movement_id = $movement2->id;
						$movementDetail2->item_number = $item['item_number'];
						$movementDetail2->article_code = $relatedArticle->id;
						$movementDetail2->digit_amount = $digit_amount;
						$movementDetail2->converted_amount = $converted_amount;
						$movementDetail2->old_stock_good = $relatedArticle->stock_good;
						// $movementDetail2->old_stock_repair = $relatedArticle->stock_repair;
						// $movementDetail2->old_stock_return = $relatedArticle->stock_return;
						// $movementDetail2->old_stock_damaged = $relatedArticle->stock_damaged;
						$movementDetail2->new_stock_good = $relatedArticle->stock_good;
						// $movementDetail2->new_stock_repair = $relatedArticle->stock_repair;
						// $movementDetail2->new_stock_return = null;
						// $movementDetail2->new_stock_damaged = $relatedArticle->stock_damaged;
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
				} else if ($movement_type_id == 12) {
					$relatedArticles2 = Article::where('warehouse_type_id', 4)
						->where('business_type', $item['business_type'])
						->where('group_id', 7) // Sólo envases
						->where('convertion', $item['convertion'])
						->get();

					foreach ($relatedArticles2 as $relatedArticle) {
						$relatedArticle->stock_repair -= $item['digit_amount'];
						$relatedArticle->stock_good += $item['digit_amount'];
						$relatedArticle->save();

						$movementDetail2 = new WarehouseMovementDetail();
						$movementDetail2->warehouse_movement_id = $movement2->id;
						$movementDetail2->item_number = $item['item_number'];
						$movementDetail2->article_code = $relatedAticle->id;
						$movementDetail2->digit_amount = $digit_amount;
						$movementDetail2->converted_amount = $converted_amount;
						$movementDetail2->old_stock_good = $relatedAticle->stock_good;
						// $movementDetail2->old_stock_repair = $relatedAticle->stock_repair;
						// $movementDetail2->old_stock_return = $relatedAticle->stock_return;
						// $movementDetail2->old_stock_damaged = $relatedAticle->stock_damaged;
						$movementDetail2->new_stock_good = $relatedAticle->stock_good;
						// $movementDetail2->new_stock_repair = $relatedAticle->stock_repair;
						// $movementDetail2->new_stock_return = $relatedAticle->stock_return;
						// $movementDetail2->new_stock_damaged = $relatedAticle->stock_damaged;
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
				}

				// validar
				//  Article::where('warehouse_type_id', 4)
				//  	->where('code', $article->code)
				//  	->update(
				//  		['stock_good' => $article->stock_good -  $converted_amount]
				//  	);
				$article->stock_good = $article->stock_good -  $converted_amount;

				$article->edit = 1;
				$article->save();


				//Encontrar artículo de conversión
				$articleConversion = Article::where('warehouse_type_id', 4)
									->where('business_type', $item['business_type'])
									->where('group_id', 7) // Sólo envases
									->where('convertion', $item['convertion'])
									->first();

				if($articleConversion){

					$converted_amount = $converted_amount * $articleConversion->convertion;
					
					$articleEnvasado = Article::find(4791);
					$articleEnvasado->stock_good = $articleEnvasado->stock_good - $converted_amount;
					$articleEnvasado->stock_repair = $articleEnvasado->stock_repair + $converted_amount;
					$articleEnvasado->save();

					//Movimiento por producción
					$id = WarehouseMovement::insertGetId([
						'company_id' => $company_id,
						'warehouse_type_id' => 4, //Producción ATE
						'movement_class_id' => 2,//Salida
						'movement_type_id' => 5, //Producción
						'warehouse_account_type_id' => 3, //Trabajador
						'total' => $converted_amount,
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					]);

					WarehouseMovementDetail::insert([
						'warehouse_movement_id' => $id,
						'item_number' => 1,
						'article_code' => $articleEnvasado->id,
						'converted_amount' => $converted_amount,
						'total' => $converted_amount,
						'created_at' => date('Y-m-d H:i:s'),
						'updated_at' => date('Y-m-d H:i:s'),
					]);
				}
			}
		}

		return $this->generatePdf($movement);
	}

	public function generatePdf($warehouseMovement)
	{
		$warehouse_movement = WarehouseMovement::leftjoin('company_addresses', function ($join) {
			$join->on('warehouse_movements.company_id', '=', 'company_addresses.company_id')
				->where('company_addresses.type', '=', 2);
		})
			->leftjoin('employees', 'warehouse_movements.account_id', '=', 'employees.id')
			->select('warehouse_movements.id', 'warehouse_movements.company_id', 'company_addresses.address as company_address', 'company_addresses.district as company_district', 'company_addresses.province as company_province', 'company_addresses.department as company_department', 'warehouse_type_id', 'movement_class_id', 'movement_type_id', 'movement_number', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'scop_number', 'license_plate', 'total', 'warehouse_movements.created_at', 'warehouse_movements.traslate_date', 'employees.license as employee_license')
			->where('warehouse_movements.id', $warehouseMovement->id)
			->first();

		$elements = WarehouseMovementDetail::select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'warehouse_movement_details.total', 'warehouse_movements.id')
			->join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->where('warehouse_movement_id', $warehouse_movement->id)
			->where('company_id', $warehouse_movement->company_id)
			->where('warehouse_type_id', $warehouse_movement->warehouse_type_id)
			->get();

		$packaging = WarehouseMovementDetail::join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->join('articles', 'articles.id', '=', 'warehouse_movement_details.article_code')
			->join('classifications', 'classifications.id', '=', 'articles.subgroup_id')
			->where('warehouse_movement_id', $warehouse_movement->id)
			->where('company_id', $warehouse_movement->company_id)
			->where('warehouse_movements.warehouse_type_id', $warehouse_movement->warehouse_type_id)
			->where('articles.subgroup_id', '>=', 55)
			->where('articles.subgroup_id', '<=', 58)
			->select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'warehouse_movement_details.total', 'articles.subgroup_id', 'classifications.name as classification_name', DB::Raw('SUM(converted_amount) as total_converted_amount'))
			->groupBy('articles.subgroup_id')
			->get();

		$packaging->map(function ($item, $index) {
			$item->total_converted_amount = number_format($item->total_converted_amount, 0, '.', ',');
		});


		$packaging = WarehouseMovementDetail::join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->join('articles', 'articles.id', '=', 'warehouse_movement_details.article_code')
			->join('classifications', 'classifications.id', '=', 'articles.subgroup_id')
			->where('warehouse_movement_id', $warehouse_movement->id)
			->where('company_id', $warehouse_movement->company_id)
			->where('warehouse_movements.warehouse_type_id', $warehouse_movement->warehouse_type_id)
			->where('articles.subgroup_id', '>=', 55)
			->where('articles.subgroup_id', '<=', 58)
			->select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'warehouse_movement_details.total', 'articles.subgroup_id', 'classifications.name as classification_name', DB::Raw('SUM(converted_amount) as total_converted_amount'))
			->groupBy('articles.subgroup_id')
			->get();

		$packaging->map(function ($item, $index) {
			$item->total_converted_amount = number_format($item->total_converted_amount, 0, '.', ',');
		});

		try{
			$pdf = PDF::loadView('backend.pdf.referral_guide2', compact('warehouse_movement', 'elements', 'packaging'));
			
			return $pdf->download('guia-remision-' . $warehouse_movement->movement_class_name . '-' . $warehouse_movement->movement_type_name . '-N' . $warehouse_movement->movement_number . '.pdf');
		}catch(Exception $e){
			return '';
		}
	}
}
