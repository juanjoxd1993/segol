<?php

namespace App\Http\Controllers\Backend;

use App\Classification;
use App\ClassificationType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ClassificationController extends Controller
{
	public function index() {
		$classification_types = ClassificationType::select('id', 'name')->get();
		return view('backend.classifications')->with(compact('classification_types'));
	}

	public function validateForm() {
		$messages = [
			'classification_type_id.required'    => 'Debe seleccionar un Tipo de Clasificación.',
		];

		$rules = [
			'classification_type_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$classification_type_id = request('classification_type_id');

		$elements = Classification::where('classification_type_id', $classification_type_id)->orderBy('name', 'asc')->get();
		$elements->map(function($item, $key) {
			$item->classification_type_name = $item->classification_type->name;
		});

		return response()->json([
			'data' => $elements,
		]);
	}

	public function detail() {
		$id = request('id');

		$element = Classification::select('id', 'classification_type_id', 'name')->findOrFail($id);
		return $element;
	}

	public function delete() {
		$id = request('id');
		$element = Classification::findOrFail($id);
		$element->delete();
	}

	public function validateModalForm() {
		$messages = [
			'classification_type_id.required'	=> 'El Tipo de Clasificación es obligatorio.',
			'name.required'						=> 'La Descripción es obligatoria.',
		];

		$rules = [
			'classification_type_id'	=> 'required',
			'name'						=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function store() {
		$this->validateModalForm();

		$id = request('id');
		$classification_type_id = request('classification_type_id');
		$name = request('name');

		if ( isset($id) ) {
			$element = Classification::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;
		} else {
			$element = new Classification();
			$msg = 'Registro creado exitosamente';
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;
		}
		$element->classification_type_id = $classification_type_id;
		$element->name = $name;
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
