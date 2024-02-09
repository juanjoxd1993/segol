<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\Article;
use App\Classification;
use App\Exports\WarehouseGlpReportExport;
use App\OperationType;
use App\Unit;
use Auth;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseGlpController extends Controller
{
	public function index() {
		$warehouse_types = WarehouseType::select('id', 'name')->whereIn('type',[2,3,4])->get();
		$families = Classification::select('id', 'name')->where('classification_type_id', 1)->get();
		$groups = Classification::select('id', 'name')->where('classification_type_id', 2)->get();
		$subgroups = Classification::select('id', 'name')->where('classification_type_id', 3)->get();
		$operations = OperationType::select('id', 'name')->get();
		$units = Unit::select('id', 'name')->get();

		return view('backend.warehouse_glp')->with(compact('warehouse_types', 'families', 'groups', 'subgroups', 'operations', 'units'));
	}

	public function validateForm() {
		$messages = [
			'warehouse_type_id.required'    => 'Debe seleccionar un Almacén.',
		];

		$rules = [
			'warehouse_type_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$warehouse_type_id = request('warehouse_type_id');

		$elements = Article::where('warehouse_type_id', $warehouse_type_id)->orderBy('id', 'desc')->get();
		$elements->map(function($item, $key) {
			$item->sale_unit_name = $item->sale_unit->name;
			$item->warehouse_unit_name = $item->warehouse_unit->name;
			$item->family_name = $item->family ? $item->family->name : '';
			$item->group_name = $item->group ? $item->group->name : '';
			$item->subgroup_name = $item->subgroup ? $item->subgroup->name : '';
		});

		return response()->json([
			'data' => $elements,
		]);
	}

	public function detail() {
		$id = request('id');

		$article = Article::select('id', 'warehouse_type_id', 'code', 'name', 'package_sale', 'sale_unit_id','last_price','package_warehouse', 'warehouse_unit_id', 'operation_type_id', 'factor', 'family_id', 'group_id', 'subgroup_id', 'igv', 'perception', 'stock_minimum', 'ubication')->findOrFail($id);
		return $article;
	}

	public function delete() {
		$id = request('id');
		$element = Article::findOrFail($id);
		$element->delete();
	}

	public function validateModalForm() {
		$messages = [
			'code.required'					=> 'El Código de Artículo es obligatorio.',
			'name.required'			        => 'La Descripción es obligatoria.',
			'family_id.required'			=> 'Debe seleccionar una Familia/Marca.',
			'group_id.required'				=> 'Debe seleccionar un Grupo.',
			'subgroup_id.required'			=> 'Debe seleccionar un Sub-Grupo.',
			'sale_unit_id.required'			=> 'Debe seleccionar una Unidad de Medida.',
			'package_sale.required'			=> 'El Empaque de Compra es obligatorio.',
			'operation_type_id.required'	=> 'Debe seleccionar una Operación de Conversión.',
			'factor.required'				=> 'El Factor es obligatorio.',
			'warehouse_unit_id.required'	=> 'Debe seleccionar una Unidad de Medida.',
			'package_warehouse.required'	=> 'El Empaque de Almacén es obligatorio.',
			'igv.required'					=> 'Debe seleccionar si está Afecto a IGV.',
			'perception.required'			=> 'Debe seleccionar si está Afecto a Percepción.',
		];

		$rules = [
			'code'              => 'required',
			'name'				=> 'required',
			'family_id'         => 'required',
			'group_id'          => 'required',
			'subgroup_id'       => 'required',
			'sale_unit_id'      => 'required',
			'package_sale'      => 'required',
			'operation_type_id' => 'required',
			'factor'            => 'required',
			'warehouse_unit_id' => 'required',
			'package_warehouse' => 'required',
			'igv'               => 'required',
			'perception'        => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function store() {
		$this->validateModalForm();

		$id = request('id');
		$warehouse_type_id = request('warehouse_type_id');
		$code = request('code');
		$name = request('name');
		$family_id = request('family_id');
		$group_id = request('group_id');
		$subgroup_id = request('subgroup_id');
		$sale_unit_id = request('sale_unit_id');
		$package_sale = request('package_sale');
		$operation_type_id = request('operation_type_id');
		$factor = request('factor');
		$warehouse_unit_id = request('warehouse_unit_id');
		$package_warehouse = request('package_warehouse');
		$igv = request('igv');
		$perception = request('perception');
		$stock_minimum = request('stock_minimum');
		$ubication = request('ubication');

		if ( isset($id) ) {
			$element = Article::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;
		} else {
			$element = new Article();
			$msg = 'Registro creado exitosamente';
			$element->edit = 0;
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;
		}
		$element->warehouse_type_id = $warehouse_type_id;
		$element->code = $code;
		$element->name = $name;
		$element->package_sale = $package_sale;
		$element->sale_unit_id = $sale_unit_id;
		$element->package_warehouse = $package_warehouse;
		$element->warehouse_unit_id = $warehouse_unit_id;
		$element->operation_type_id = $operation_type_id;
		$element->factor = $factor;
		$element->family_id = $family_id;
		$element->group_id = $group_id;
		$element->subgroup_id = $subgroup_id;
		$element->igv = $igv;
		$element->perception = $perception;
		$element->stock_good = 0;
		$element->stock_repair = 0;
		$element->stock_return = 0;
		$element->stock_damaged = 0;
		$element->stock_minimum = ( $stock_minimum ? $stock_minimum : 0 );
		$element->ubication = $ubication;
		$element->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	public function exportRecord() {
		$warehouse_type_id = request('warehouse_type_id');

		$elements = Article::join('units as sale_units', 'sale_units.id', '=', 'sale_unit_id')
			->join('units as warehouse_units', 'warehouse_units.id', '=', 'warehouse_unit_id')
			->join('classifications as families', 'families.id', '=', 'family_id')
			->join('classifications as groups', 'groups.id', '=', 'group_id')
			->join('classifications as subgroups', 'subgroups.id', '=', 'subgroup_id')
			->select('articles.id', 'code as article_code', 'articles.name as article_name','articles.last_price as article_price', 'sale_units.name as sale_unit_name', 'warehouse_units.name as warehouse_unit_name', 'package_sale', 'package_warehouse', 'families.name as family_name', 'groups.name as group_name', 'subgroups.name as subgroup_name', 'stock_good', 'stock_repair', 'stock_return', 'stock_damaged', 'ubication')
			->where('warehouse_type_id', $warehouse_type_id)
			->orderBy('id', 'desc')
			->get();

		$warehouse_type_name = WarehouseType::find($warehouse_type_id)->first()->name;
		$datetime = CarbonImmutable::now()->format('d/m/Y h:m:s a');

		$excel = new WarehouseGlpReportExport($warehouse_type_name, $datetime, $elements);
		return Excel::download($excel, 'reporte-articulos.xlsx');
	}
}
