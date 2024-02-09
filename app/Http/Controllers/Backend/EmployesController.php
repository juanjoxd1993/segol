<?php

namespace App\Http\Controllers\Backend;

use App\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Provider;
use App\Rate;
use App\Employee;
use App\Ubigeo;
use Auth;

class EmployesController extends Controller
{
	public function index() {
		$document_types = DocumentType::select('id', 'name')->get();
		
		
		return view('backend.employes')->with(compact('document_types'));
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

		$elements = Employee::select('id', 'document_type_id', 'document_number', 'first_name', 'last_name', 'license')
			->where('first_name', 'like', '%'.$provider.'%')
			->orderBy('first_name', 'asc')
			->paginate($perpage);

		$elements->map(function($item, $key) {
			$item->document_type_name = $item->document_type->name;
		});

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

		$element = Employee::select('id', 'document_type_id', 'document_number', 'first_name', 'last_name', 'license')->findOrFail($id);
		return $element;
	}

	public function delete() {
		$id = request('id');
		$element = Employee::findOrFail($id);
		$element->delete();
	}

	public function validateModalForm() {
		$messages = [
			'document_type_id.required'	=> 'El Tipo de Documento es obligatorio.',
			'document_number.required'	=> 'El Número de Documento es obligatorio.',
			'first_name.required'	    => 'El Nombre o Razón Social es obligatorio.',
		];

		$rules = [
			'document_type_id'		=> 'required',
			'document_number'		=> 'required',
			'first_name'			=> 'required',
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
		$document_type_id = request('document_type_id');
		$document_number = request('document_number');
		$first_name = request('first_name');
		$last_name = request('last_name');
		$license = request('license');
		
		if ( isset($id) ) {
			$element = Employee::find($id);
			$msg = 'Registro actualizado exitosamente';
		//	$element->updated_at = Auth::user()->user;
		} else {
			$element = new Employee();
			$msg = 'Registro creado exitosamente';
		//	$element->created_at = Auth::user()->user;
		//	$element->updated_at = Auth::user()->user;
		}
		$element->document_type_id = $document_type_id;
		$element->document_number = $document_number;
		$element->first_name = $first_name;
        $element->last_name = $last_name;
		$element->license = $license;
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
