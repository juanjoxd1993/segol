<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Company;
use App\MoventClass;
use App\MoventType;
use App\WarehouseType;
use App\Article;
use App\Currency;
use App\Client;
use App\Employee;
use App\Provider;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Auth;
use Carbon\CarbonImmutable;

class MovementRegisterController extends Controller
{
	public function index()
	{
		$articles = Article::whereIn('id', [4841, 4844, 4846, 4848])->get();
		$movement_classes = MoventClass::select('id', 'name')->whereIn('id', [2])->get();
		$movement_types = MoventType::select('id', 'movent_class', 'name')->whereIn('id', [5])->get();
		$warehouse_types = WarehouseType::select('id', 'name')->whereIn('id', [75])->get();
		$companies = Company::select('id', 'name')->whereIn('id', [2])->get();

		$date = CarbonImmutable::now()->startOfDay();
		$max_datetime = $date->startOfDay()->addDays(0)->toAtomString();

		return view('backend.movement_register_controller')->with(compact(
			'articles',
			'movement_classes',
			'movement_types',
			'warehouse_types',
			'companies',
			'max_datetime'
		));
	}

	public function validateForm()
	{
		$messages = [
			'movement_class_id.required'						=> 'Debe seleccionar Ingreso o Salida.',
			'movement_type_id.required'							=> 'Debe seleccionar un Tipo de Movimiento.',
			'warehouse_type_id.required'						=> 'Debe seleccionar un Almacén.',
			'company_id.required'							    => 'Debe seleccionar una Compañía.',
			'since_date.required'								=> 'Debe seleccionar una Fecha.',
			'tanque.required'									=> 'Debe seleccionar el tanque.',
		];

		$rules = [
			'movement_class_id'						=> 'required',
			'movement_type_id'						=> 'required',
			'warehouse_type_id'						=> 'required',
			'company_id'							=> 'required',
			'since_date'							=> 'required',
			'tanque'								=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list()
	{
		$this->validateForm();
		$model = request()->all();

		return response()->json([
			'model'					=> $model,
		]);
	}

	public function store()
	{
		$company_id = request('model.company_id');
		$movement_class_id = request('model.movement_class_id');
		$movement_type_id = request('model.movement_type_id');
		$since_date = request('model.since_date');
		$warehouse_type_id = request('model.warehouse_type_id');
		$tanque = request('model.tanque');
		$articles = request('article_list');

		$movement = new WarehouseMovement();
		$movement->company_id = $company_id;
		$movement->warehouse_type_id = $warehouse_type_id;
		$movement->movement_class_id = $movement_class_id;
		$movement->movement_type_id = $movement_type_id;
		$movement->fac_date = $since_date;
		$movement->save();

		foreach ($articles as $article) {

			//presentacion articulo
			$obj = Article::findOrFail($article['id']);
			$obj->stock_good = $obj->stock_good + $article['quantity'];
			$obj->save();

			$detail = new WarehouseMovementDetail();
			$detail->warehouse_movement_id = $movement->id;
			$detail->item_number = $article['item_number'];
			$detail->article_code = $article['code'];
			$detail->digit_amount = $article['quantity'];
			$detail->converted_amount = $article['quantity'] * $article['convertion'];
			$detail->total = $detail->converted_amount;
			$detail->save();

			$id_fierro = 0;
			switch ($article['id']) {
				case 4841:
				case 4846:
					$id_fierro = 4838;
					break; //valon vacio de 10 kg
				case 4844:
				case 4848:
					$id_fierro = 4840;
					break; //valon vacio de 45 kg
			}

			//BALON VACIO
			$fierro = Article::findOrFail($id_fierro);
			$fierro->stock_good = $fierro->stock_good - $article['quantity'];
			$fierro->stock_return = $fierro->stock_return + $article['quantity'];
			$fierro->save();

			//GRANEL KG ENVASADO
			$granel = Article::findOrFail($tanque);
			$granel->stock_good = $granel->stock_good - ($article['quantity'] * $article['convertion']);
			$granel->save();
		}

		$type = 3;
		$title = '¡Ok!';
		$msg = "Registro exitoso.";
		$url = route('dashboard.logistics.movement_register');


		return response()->json([
			'type'    => $type,
			'title'    => $title,
			'msg'    => $msg,
			'url'    => $url,
		]);
	}
}
