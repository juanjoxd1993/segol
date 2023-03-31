<?php

namespace App\Http\Controllers\Backend;

use App\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Provider;
use App\Rate;
use App\Employee;
use App\WarehouseType;
use App\Ubigeo;
use Auth;

class PlatesController extends Controller
{
	public function index() {
		$warehouse_types = WarehouseType::select('id', 'name')->get();
		
		return view('backend.plates')->with(compact('warehouse_types'));
	}

	public function validateForm() {
		$messages = [
			'provider.required' => 'El Proveedor es obligatorio.',
		];

		$rules = [
			'provider' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$provider = request('provider');
		$p = request('pagination');
		$page = (int)$p['page'];
		$perpage = (int)( $p['perpage'] ? $p['perpage'] : 10 );
		request()->replace(['page' => $page]);

		$elements = WarehouseType::select('id', 'name', 'short_name', 'type')
			->where('name', 'like', '%'.$provider.'%')
			->orderBy('short_name', 'asc')
			->paginate($perpage);

		

		$meta = new \stdClass();
        $meta->page = $page;
        $meta->pages = $elements->lastPage();
        $meta->perpage = $perpage;
        $meta->total = $elements->total();
        $meta->field = 'id';
        for ($i = 1; $i <= $elements->total() ; $i++) {
            $meta->rowIds[] = $i;
        }

		return response()->json([
			'meta'	=> $meta,
			'data'	=> $elements->items(),
		]);
	}

	public function detail() {
		$id = request('id');

		$element = WarehouseType::select('id', 'name', 'short_name', 'type')->findOrFail($id);
		return $element;
	}

	public function delete() {
		$id = request('id');
		$element = WarehouseType::findOrFail($id);
		$element->delete();
	}

	public function validateModalForm() {
		$messages = [
			'name.required'	            => 'La placa es obligatoria.',
		//	'type.required'	            => 'El Tipo es obligatorio.',
		
		];

		$rules = [
			'name'		=> 'required',
		//	'type'			=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getUbigeos() {
		$q = request('q');

		$ubigeos = Ubigeo::select('id', 'district', 'province', 'department', 'country')
			->where('district', 'like', '%'.$q.'%')
			->orderBy('district', 'asc')
			->get();

		$ubigeos->map(function($item, $index){
			$item->text = $item->district.' - '.$item->province.' - '.$item->department.' - '.$item->country;
			unset($item->district);
			unset($item->province);
			unset($item->department);
			unset($item->country);
			return $item;
		});

		return $ubigeos;
	}

	public function getUbigeo() {
		$ubigeo_id = request('ubigeo_id');

		$ubigeo = Ubigeo::select('id', 'district', 'province', 'department', 'country')
			->where('id', $ubigeo_id)
			->first();
		
		$ubigeo->text = $ubigeo->district.' - '.$ubigeo->province.' - '.$ubigeo->department.' - '.$ubigeo->country;
		unset($ubigeo->district);
		unset($ubigeo->province);
		unset($ubigeo->department);
		unset($ubigeo->country);

		return $ubigeo;
	}

	public function store() {
		$this->validateModalForm();

		$id = request('id');
		$name = request('name');
		$short_name = request('short_name');
		$warehouse_type = request('warehouse_type');
		
		if ( isset($id) ) {
			$element = WarehouseType::find($id);
			$msg = 'Registro actualizado exitosamente';
		//	$element->updated_at = Auth::user()->user;
		} else {
			$element = new WarehouseType();
			$msg = 'Registro creado exitosamente';
		//	$element->created_at = Auth::user()->user;
		//	$element->updated_at = Auth::user()->user;
		}
		$element->name = $name;
		$element->short_name = $short_name;
        $element->type = $warehouse_type;
		$element->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}
}
