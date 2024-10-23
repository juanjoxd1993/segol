<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Company;
use App\MoventType;
use App\Article;
use App\WarehouseAccountType;
use App\Client;
use App\Employee;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\GuidesSerie;
use Auth;
use Carbon\CarbonImmutable;

class GuidesRegisterController extends Controller
{
	public function index()
	{
		$warehouse_account_types = WarehouseAccountType::whereIn('id', [1, 3])->get();
		$companies = Company::select('id', 'name')->whereIn('id', [2])->get();
		$current_date = date('d-m-Y');
		$date = CarbonImmutable::now()->startOfDay();
		$current_date = $date->startOfDay()->toAtomString();
		$min_datetime = $date->startOfDay()->toAtomString();
		$max_datetime = $date->startOfDay()->addDays(2)->toAtomString();
		$guide_series = GuidesSerie::select('id', 'num_serie', 'correlative')->get();
		$articles = Article::select('id', 'code', 'name', 'package_sale', 'stock_good', 'convertion')
			->orderBy('code', 'asc')
			->whereIn('id', [4841, 4844, 4846, 4848])
			->get();

		return view('backend.guides_register')->with(compact(
			'companies',
			'current_date',
			'min_datetime',
			'max_datetime',
			'warehouse_account_types',
			'guide_series',
			'articles',
		));
	}

	public function getNextcorrelative()
	{
		$last_guide_serie = GuidesSerie::select('correlative')
			->latest('id')
			->where('company_id', request('company'))
			->where('num_serie', request('guide_serie'))
			->first();

		$next_correlative = $last_guide_serie ? $last_guide_serie->correlative + 1 : 1;

		return response()->json($next_correlative, 200);
	}

	public function getClients()
	{
		$q = request('q');

		$clients = Client::select('id', 'business_name')
			->where('business_name', 'like', '%' . $q . '%')
			->get();

		$clients->map(function ($item, $index) {
			$item->text = $item->business_name;
			unset($item->business_name);

			return $item;
		});

		return $clients;
	}

	public function getEmployees()
	{
		$q = request('q');

		$employees = Employee::select('id', 'first_name', 'last_name')
			->where(function ($query) use ($q) {
				$query->where('first_name', 'like', '%' . $q . '%')
					->orWhere('last_name', 'like', '%' . $q . '%');
			})
			->get();

		$employees->map(function ($item, $index) {
			$item->text = $item->first_name . ' ' . $item->last_name;
			unset($item->first_name);
			unset($item->last_name);

			return $item;
		});

		return $employees;
	}

	public function validateForm()
	{
		$messages = [
			'movement_type_id.required'							=> 'Debe seleccionar Tipo de Movimiento.',
			'company_id.required'								=> 'Debe seleccionar una Compañía.',
			'since_date.required'								=> 'Debe seleccionar Fecha de Emisión.',
			'traslate_date.required'							=> 'Debe seleccionar Fecha de Emisión.',
			'license_plate.required'							=> 'Debe digita la Placa.',
			'referral_guide_series.required'					=> 'Debe seleccionar la Serie de Guía de Remisión.',
			'client_id.required_if'								=> 'Debe seleccionar al Cliente.',
			'chofer_id.required'								=> 'Debe seleccionar al Chofer.',
			'referral_guide_number.required'					=> 'Este campo se completa al seleccionar la Serie de Guía de Remisión.',
		];

		$rules = [
			'movement_type_id'									=> 'required',
			'company_id'										=> 'required',
			'since_date'										=> 'required',
			'traslate_date'										=> 'required',
			'license_plate'										=> 'required',
			'referral_guide_series'								=> 'required',
			'client_id'											=> 'required_if:movement_type_id,12',
			'chofer_id'											=> 'required',
			'referral_guide_number'								=> 'required',
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
		$warehouse_type_id = 75; //PLANTA IQUITOS
		$movement_class_id = 2; //SALIDA
		$movement_type_id = request('model.movement_type_id');
		$warehouse_account_type_id = $movement_type_id == 12 ? 1 : 3;
		$client_id = request('model.client_id') ?  request('model.client_id') : null;
		$chofer_id = request('model.chofer_id') ?  request('model.chofer_id') : null;
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');
		$license_plate = request('model.license_plate');
		$since_date = request('model.since_date');
		$traslate_date = request('model.traslate_date');
		$articles = request('article_list');;

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

		if ($warehouse_account_type_id == 1) {
			$account = Client::select('business_name', 'document_number')
				->where('id', $client_id)
				->first();
		} elseif ($warehouse_account_type_id == 3) {
			$account = Employee::select('first_name', 'last_name')
				->where('id', $chofer_id)
				->first();

			$account->business_name = $account ? ($account->first_name . ' ' . $account->last_name) : '';
		}

		$movement_type = MoventType::find($movement_type_id);

		$movement = new WarehouseMovement();
		$movement->company_id = $company_id;
		$movement->warehouse_type_id = $warehouse_type_id;
		$movement->movement_class_id = $movement_class_id;
		$movement->movement_type_id = $movement_type_id;
		$movement->movement_number = $movement_number;
		$movement->warehouse_account_type_id = $warehouse_account_type_id;
		$movement->employee_id = $chofer_id;
		$movement->account_id = $client_id;
		$movement->account_document_number = $movement_type_id == 12 ? $account->document_number : '';
		$movement->account_name = $movement_type_id == 12 ? $account->business_name : '';
		$movement->referral_guide_series = $referral_guide_series;
		$movement->referral_guide_number = $referral_guide_number;
		$movement->license_plate = $license_plate;
		$movement->total = array_sum(array_column($articles, 'quantity'));
		$movement->action_type_id = ($movement_type ? $movement_type->action_type_id : null);
		$movement->created_at = date('Y-m-d', strtotime($since_date));
		$movement->created_at_user = Auth::user()->user;
		$movement->updated_at_user = Auth::user()->user;
		$movement->traslate_date = date('Y-m-d', strtotime($traslate_date));
		$movement->fac_date = date('Y-m-d', strtotime($traslate_date));
		$movement->state = 1;
		$movement->save();

		//Primer movimiento
		$movement_one = new WarehouseMovement();
		$movement_one->company_id = $company_id;
		$movement_one->warehouse_type_id = $warehouse_type_id;
		$movement_one->movement_class_id = $movement_class_id;
		$movement_one->movement_type_id = 5; //PRODUCCION
		$movement_one->movement_number = $movement_number;
		$movement_one->warehouse_account_type_id = $warehouse_account_type_id;
		$movement_one->account_id = $client_id;
		$movement_one->employee_id = $chofer_id;
		$movement_one->account_document_number = $movement_type_id == 12 ? $account->document_number : '';
		$movement_one->account_name = $movement_type_id == 12 ? $account->business_name : '';
		$movement_one->referral_guide_series = $referral_guide_series;
		$movement_one->referral_guide_number = $referral_guide_number;
		$movement_one->license_plate = $license_plate;
		$movement_one->total = array_sum(array_column($articles, 'quantity'));
		$movement_one->action_type_id = ($movement_type ? $movement_type->action_type_id : null);
		$movement_one->created_at = date('Y-m-d', strtotime($since_date));
		$movement_one->created_at_user = Auth::user()->user;
		$movement_one->updated_at_user = Auth::user()->user;
		$movement_one->traslate_date = date('Y-m-d', strtotime($traslate_date));
		$movement_one->fac_date = date('Y-m-d', strtotime($traslate_date));
		$movement_one->state = 1;

		$rpt = false;
		foreach ($articles as $item) {
			$rpt = false;
			$article = Article::findOrFail($item['article_id']); //PRODUCTO

			$digit_amount = str_replace(',', '', $item['quantity']);
			$converted_amount = str_replace(',', '', $item['quantity'] * $item['convertion']);

			$movementDetail = new WarehouseMovementDetail();
			$movementDetail->warehouse_movement_id = $movement->id;
			$movementDetail->item_number = $item['item_number'];
			$movementDetail->article_code = $item['article_id'];
			$movementDetail->article_num = $article->id;
			$movementDetail->digit_amount = $digit_amount;
			$movementDetail->converted_amount = $converted_amount;
			$movementDetail->old_stock_good = $article->stock_good;
			$movementDetail->new_stock_good = $article->stock_good;
			$movementDetail->total = $converted_amount;
			$movementDetail->created_at_user = Auth::user()->user;
			$movementDetail->updated_at_user = Auth::user()->user;
			$movementDetail->save();

			if ($article->stock_good <= $digit_amount) {
				$movement_one->save();
				$movementDetailOne = new WarehouseMovementDetail();
				$movementDetailOne->warehouse_movement_id = $movement_one->id;
				$movementDetailOne->item_number = $item['item_number'];
				$movementDetailOne->article_code = $item['article_id'];
				$movementDetailOne->article_num = $article->id;
				$movementDetailOne->digit_amount = $digit_amount - $article->stock_good;
				$movementDetailOne->converted_amount = $movementDetailOne->digit_amount * $article->convertion;
				$movementDetailOne->total = $movementDetailOne->converted_amount;
				$movementDetailOne->created_at_user = Auth::user()->user;
				$movementDetailOne->updated_at_user = Auth::user()->user;
				$movementDetailOne->save();
				$rpt = true;

				$fierro = null;
				switch ($article->id) {
					case 4841:
					case 4846:
						$fierro = Article::findOrFail(4838);
						break; //BALON VACIO DE 10 KG
					case 4844:
					case 4848:
						$fierro = Article::findOrFail(4840);
						break; //BALON VACIO DE 45 KG
				}

				if ($fierro != null) {
					$fierro->stock_good = $fierro->stock_good + ($digit_amount - $article->stock_good);
					$fierro->stock_return = 0;
					$fierro->save();
				}

				$granel = Article::findOrFail(4791); //GRANEL KG ENVASADO
				$granel->stock_good = $granel->stock_good - (($digit_amount - $article->stock_good) * $article->convertion);
				$granel->save();
			}

			if ($article->stock_good <= $digit_amount) {
				$article->stock_good = 0;
			} else {
				$article->stock_good = $article->stock_good - $digit_amount;
			}

			$article->save();
		}

		$detail = WarehouseMovementDetail::where('warehouse_movement_id', $movement_one->id)->get();

		$suma = 0;
		foreach ($detail as $obj) {
			$suma = $suma + $obj->converted_amount;
		}

		if ($rpt == true) {
			$movementDetailOne = new WarehouseMovementDetail();
			$movementDetailOne->warehouse_movement_id = $movement_one->id;
			$movementDetailOne->item_number = $item['item_number'] + 1;
			$movementDetailOne->article_code = 4791; // GLP KGS ENVASADO
			$movementDetailOne->article_num = 4791; // GLP KGS ENVASADO
			$movementDetailOne->converted_amount = $suma;
			$movementDetailOne->total = $suma;
			$movementDetailOne->created_at_user = Auth::user()->user;
			$movementDetailOne->updated_at_user = Auth::user()->user;
			$movementDetailOne->save();
		}

		$type = 3;
		$title = 'Ok!';
		$msg = 'Guía registrado exitosamente.';
		$url = route('dashboard.operations.guides_register');

		return response()->json([
			'type'    => $type,
			'title'    => $title,
			'msg'    => $msg,
			'url'    => $url,
		]);
	}
}
