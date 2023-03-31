<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\Company;
use App\Exports\InventoryReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Inventory;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;
use PDF;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class AdjustInventoryController extends Controller
{
	public function index()
	{
		$companies = Company::select('id', 'name')->get();
		$warehouse_types = WarehouseType::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));

		return view('backend.adjustinventories')->with(compact('companies', 'warehouse_types', 'current_date'));
	}

	public function validateForm()
	{
		$messages = [
			'company_id.required'           => 'Debe seleccionar una Compañía.',
			'warehouse_type_id.required'    => 'Debe seleccionar un Almacén.',
			'creation_date.required'    	=> 'Debe seleccionar una Fecha.',
		];

		$rules = [
			'company_id'        => 'required',
			'warehouse_type_id' => 'required',
			'creation_date'     => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list()
	{
		$company_id = request('model.company_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		$creation_date = request('model.creation_date');
		$creation_date = date('Y-m-d', strtotime($creation_date));

		$p = request('pagination');
		$q = request('query');
		$page = (int)$p['page'];
		$perpage = (int)($p['perpage'] ? $p['perpage'] : 10);
		$search = $q['generalSearch'];
		request()->replace(['page' => $page]);

		$elements = Inventory::select('id', 'company_id', 'warehouse_type_id', 'article_id', 'found_stock_good', 'found_stock_damaged', 'observations', 'state')
			->where('company_id', $company_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('creation_date', $creation_date)
			->orderBy('id', 'desc')
			->paginate($perpage);

		$elements->map(function ($item, $index) {
			$item->company_short_name = $item->company->short_name;
			$item->article_code = $item->article->code;
			$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
		});

		$meta = new \stdClass();
		$meta->page = $page;
		$meta->pages = $elements->lastPage();
		$meta->perpage = $perpage;
		$meta->total = $elements->total();
		$meta->field = 'id';
		for ($i = 1; $i <= $elements->total(); $i++) {
			$meta->rowIds[] = $i;
		}

		return response()->json([
			'meta'		=> $meta,
			'data'		=> $elements->items(),
			'state'		=> ($elements->total() > 0 ? $elements[0]->state : ''),
		]);
	}

	public function createRecord()
	{
		$company_id = request('model.company_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		$creation_date = date('Y-m-d', strtotime(request('model.creation_date')));
		$creation_date_initial = date_create($creation_date . ' 00:00:00');
		$creation_date_final = date_create($creation_date . ' 23:59:59');

		$elements = Article::select('id', 'warehouse_type_id', 'code', 'name', 'stock_good', 'stock_repair', 'stock_return', 'stock_damaged', 'created_at')
			->where('warehouse_type_id', $warehouse_type_id)
			->where(function ($query) {
				$query->where('stock_good', '!=', 0)
					->orWhere('stock_repair', '!=', 0)
					->orWhere('stock_return', '!=', 0)
					->orWhere('stock_damaged', '!=', 0);
			})
			->get();

		$elements->each(function ($item, $index) use ($company_id, $warehouse_type_id, $creation_date) {
			$element = new Inventory();
			$element->company_id = $company_id;
			$element->warehouse_type_id = $warehouse_type_id;
			$element->article_id = $item->id;
			$element->creation_date = $creation_date;
			$element->stock_good = $item->stock_good;
			$element->stock_damaged = number_format($item->stock_repair + $item->stock_return + $item->stock_damaged, 4, '.', '');
			$element->found_stock_good = 0;
			$element->found_stock_damaged = 0;
			$element->state = 0;
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;
			$element->save();

			$article = Article::find($item->id);
			$article->edit = 1;
			$article->save();
		});

		return response()->json([
			'total'		=> $elements->count(),
		]);
	}

	public function getArticles()
	{
		$q = request('q');
		$warehouse_type_id = request('warehouse_type_id');

		$articles = Article::select('id', 'code', 'name', 'package_warehouse', 'warehouse_unit_id')
			->where('warehouse_type_id', $warehouse_type_id)
			->when($q, function ($query, $q) {
				return $query->where('code', 'like', '%' . $q . '%')
					->orWhere('name', 'like', '%' . $q . '%');
			})
			->orderBy('code', 'asc')
			->get();

		$articles->map(function ($item, $index) {
			$item->text = $item->code . ' - ' . $item->name . ' ' . $item->warehouse_unit->name . ' x ' . $item->package_warehouse;
		});

		return $articles;
	}

	public function detail()
	{
		$id = request('id');

		$element = Inventory::select('id', 'article_id', 'found_stock_good', 'found_stock_damaged', 'observations')
			->where('id', $id)
			->first();

		return $element;
	}

	public function getSelect2()
	{
		$article_id = request('article_id');

		$element = Article::select('id', 'code', 'name', 'package_warehouse', 'warehouse_unit_id')
			->where('id', $article_id)
			->first();

		$element->text = $element->code . ' - ' . $element->name . ' ' . $element->warehouse_unit->name . ' x ' . $element->package_warehouse;

		return $element;
	}

	public function validateModalForm()
	{
		$messages = [
			'article_id.required'			=> 'Debe seleccionar un Artículo.',
			'found_stock_good.required'		=> 'La Cantidad Buen estado es obligatoria.',
			'found_stock_damaged.required'	=> 'La Cantidad Mal estado es obligatoria.',
		];

		$rules = [
			'article_id'			=> 'required',
			'found_stock_good'		=> 'required',
			'found_stock_damaged'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function store()
	{
		$this->validateModalForm();

		$id = request('id');
		$company_id = request('company_id');
		$warehouse_type_id = request('warehouse_type_id');
		$creation_date = date('Y-m-d', strtotime(request('creation_date')));
		$article_id = request('article_id');
		$found_stock_good = request('found_stock_good');
		$found_stock_damaged = request('found_stock_damaged');
		$observations = request('observations');

		$article = Article::find($article_id);
		// $article->stock_good = $found_stock_good;
		// $article->stock_damaged = $found_stock_damaged;
		$article->edit = 1;
		$article->save();

		if (isset($id)) {
			$element = Inventory::findOrFail($id);
			$element->found_stock_good = $found_stock_good;
			$element->found_stock_damaged = $found_stock_damaged;
			$element->observations = $observations;
			$element->updated_at_user = Auth::user()->user;

			$msg = 'Registro actualizado exitosamente';
		} else {
			$element = Inventory::where('company_id', $company_id)
				->where('warehouse_type_id', $warehouse_type_id)
				->where('article_id', $article_id)
				->where('creation_date', $creation_date)
				->first();

			if (empty($element)) {
				$element = new Inventory();
				$element->company_id = $company_id;
				$element->warehouse_type_id = $warehouse_type_id;
				$element->article_id = $article_id;
				$element->creation_date = $creation_date;
				$element->found_stock_good = $found_stock_good;
				$element->found_stock_damaged = $found_stock_damaged;
				$element->observations = $observations;
				$element->state = 0;
				$element->created_at_user = Auth::user()->user;
				$element->updated_at_user = Auth::user()->user;

				$msg = 'Registro creado exitosamente';
			} else {
				$element->found_stock_good = $found_stock_good;
				$element->found_stock_damaged = $found_stock_damaged;
				$element->observations = $observations;
				$element->updated_at_user = Auth::user()->user;

				$msg = 'Registro actualizado exitosamente';
			}
		}

		$element->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	public function delete()
	{
		$id = request('id');
		$element = Inventory::findOrFail($id);
		$element->delete();
	}

	public function closeRecord()
	{
		$company_id = request('company_id');
		$warehouse_type_id = request('warehouse_type_id');
		$creation_date = date('Y-m-d', strtotime(request('creation_date')));
		$data_flag = 0;

		$elements = Inventory::select()
			->where('company_id', $company_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('creation_date', $creation_date)
			->get();

		foreach ($elements as $element) {
		
			$article = Article::find($element->article_id);
			$article->stock_good = $element->found_stock_good;
			$article->stock_damaged = $element->found_stock_damaged;
			$article->save();

			//Verificar si tiene stock a favor o en contra
			$diff = $article->stock_good - $article->stock_damaged;
			if($diff != 0){

				if($diff > 0){
					$movement_class_id = 1;
				}else{
					$movement_class_id = 2;
				}

				$id = WarehouseMovement::insertGetId([
					'company_id' => $company_id,
					'warehouse_type_id' => $warehouse_type_id,
					'movement_class_id' => $movement_class_id,
					'movement_type_id' => 22, //Diferencia de Inventario
					'warehouse_account_type_id' => 3, //Trabajador
					'total' => $diff,
					'created_at' => $creation_date ,
					'updated_at' => $creation_date ,
				]);

				WarehouseMovementDetail::insert([
					'warehouse_movement_id' => $id,
					'item_number' => 1,
					'article_code' => $article->id,
					'new_stock_good' => $article->stock_good,
					'new_stock_damaged' => $article->stock_damaged,
					'converted_amount' => $diff,
					'total' => $diff,
					'created_at' => $creation_date ,
					'updated_at' => $creation_date ,
				]);

			}


			$element->state = 1;
			$element->save();
		}

		return $data_flag;
	}

	public function formRecord()
	{
		$company_id = request('company_id');
		$warehouse_type_id = request('warehouse_type_id');
		$creation_date = date('Y-m-d', strtotime(request('creation_date')));

		$elements = Inventory::select('id', 'company_id', 'warehouse_type_id', 'article_id', 'creation_date','found_stock_good','found_stock_damaged')
			->where('company_id', $company_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('creation_date', $creation_date)
			->get();

		$elements->map(function ($item, $index) {
			$item->article_code = $item->article->code;
			$item->article_name = $item->article->name;
			$item->warehouse_unit_short_name = $item->article->warehouse_unit->short_name;
			$item->package_warehouse = $item->article->package_warehouse;
		});
		

		$company = Company::select('id', 'name')->where('id', $company_id)->first();
		$warehouse_type = WarehouseType::select('id', 'name')->where('id', $warehouse_type_id)->first();

		$pdf = PDF::loadView('backend.inventories_pdf', compact('company', 'warehouse_type', 'elements', 'creation_date'));

		return $pdf->download('formulario-inventario-' . $creation_date . '.pdf');
	}

	public function exportRecord()
	{
		$company_id = request('company_id');
		$warehouse_type_id = request('warehouse_type_id');
		$creation_date = date('Y/m/d', strtotime(request('creation_date')));

		$elements = Inventory::select('id', 'company_id', 'warehouse_type_id', 'article_id', 'creation_date', 'stock_good', 'stock_damaged', 'found_stock_good', 'found_stock_damaged', 'observations', 'state')
			->where('company_id', $company_id)
			->where('warehouse_type_id', $warehouse_type_id)
			->where('creation_date', $creation_date)
			->get();

		$elements->map(function ($item, $index) {
			$item->article_code = $item->article->code;
			$item->article_name = $item->article->name;
			$item->warehouse_unit_short_name = $item->article->warehouse_unit->short_name;
			$item->package_warehouse = $item->article->package_warehouse;
			$item->difference_stock_good = $item->found_stock_good - $item->stock_good;
			$item->difference_stock_damaged = $item->found_stock_damaged - $item->stock_damaged;
		});

		$company = Company::select('id', 'name')->where('id', $company_id)->first();
		$warehouse_type = WarehouseType::select('id', 'name')->where('id', $company_id)->first();
		$state = ($elements->count() > 0 ? $elements[0]->state : '');

		$excel = new InventoryReportExport($elements, $company, $warehouse_type, $creation_date, $state);
		return Excel::download($excel, 'inventario.xlsx');
	}

	public function test()
	{
		$warehouse_type_id = 3;
		$inventories = Inventory::where('warehouse_type_id', $warehouse_type_id)
			->get();

		foreach ($inventories as $inventory) {
			$article = Article::where('id', $inventory->article_id)->first();
			$article->stock_good = $inventory->found_stock_good;
			$article->stock_damaged = $inventory->found_stock_damaged;
			$article->save();
		}
	}

}
