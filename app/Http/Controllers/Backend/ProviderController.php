<?php

namespace App\Http\Controllers\Backend;

use App\DocumentType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Provider;
use App\Rate;
use App\Ubigeo;
use Auth;

class ProviderController extends Controller
{
	public function index() {
		$document_types = DocumentType::select('id', 'name')->get();
		$perceptions = Rate::select('id', 'description', 'value')
			->where('description', 'Percepción')
			->orderBy('id', 'desc')
			->get();
		$perceptions->map(function($item, $index) {
			$item->value = number_format($item->value, 1, '.', '');
		});
		
		return view('backend.providers')->with(compact('document_types', 'perceptions'));
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

		$elements = Provider::select('id', 'document_type_id', 'document_number', 'business_name', 'contact', 'email', 'phone_number_1', 'phone_number_2', 'retention_agent', 'perception_agent_id')
			->where('business_name', 'like', '%'.$provider.'%')
			->orderBy('business_name', 'asc')
			->paginate($perpage);

		$elements->map(function($item, $key) {
			$item->document_type_name = $item->document_type->name;
			$item->perception_agent_value = number_format($item->perception_agent->value, 1, '.', '');
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

		$element = Provider::select('id', 'document_type_id', 'document_number', 'business_name', 'address', 'ubigeo_id', 'contact', 'email', 'phone_number_1', 'phone_number_2', 'retention_agent', 'perception_agent_id')->findOrFail($id);
		return $element;
	}

	public function delete() {
		$id = request('id');
		$element = Provider::findOrFail($id);
		$element->delete();
	}

	public function validateModalForm() {
		$messages = [
			'document_type_id.required'	=> 'El Tipo de Documento es obligatorio.',
			'document_number.required'	=> 'El Número de Documento es obligatorio.',
			'business_name.required'	=> 'El Nombre o Razón Social es obligatorio.',
			'address.required'			=> 'La Dirección es obligatoria.',
			'ubigeo_id.required'		=> 'El Ubigeo es obligatorio.',
			'contact.required'			=> 'El Contacto es obligatorio.',
			'email.required'			=> 'El Email es obligatorio.',
			'phone_number_1.required'	=> 'El Teléfono 1 es obligatorio.',
			'retention_agent.required'	=> 'El Agente de Retención es obligatorio.',
			'perception_agent_id.required'	=> 'El Agente de Percepción es obligatorio.',
		];

		$rules = [
			'document_type_id'		=> 'required',
			'document_number'		=> 'required',
			'business_name'			=> 'required',
			'address'				=> 'required',
			'ubigeo_id'				=> 'required',
			'contact'				=> 'required',
			'email'					=> 'required',
			'phone_number_1'		=> 'required',
			'retention_agent'		=> 'required',
			'perception_agent_id'	=> 'required',
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
		$business_name = request('business_name');
		$address = request('address');
		$ubigeo_id = request('ubigeo_id');
		$contact = request('contact');
		$email = request('email');
		$phone_number_1 = request('phone_number_1');
		$phone_number_2 = request('phone_number_2');
		$retention_agent = request('retention_agent');
		$perception_agent_id = request('perception_agent_id');

		if ( isset($id) ) {
			$element = Provider::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;
		} else {
			$element = new Provider();
			$msg = 'Registro creado exitosamente';
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;
		}
		$element->document_type_id = $document_type_id;
		$element->document_number = $document_number;
		$element->business_name = $business_name;
		$element->address = $address;
		$element->ubigeo_id = $ubigeo_id;
		$element->contact = $contact;
		$element->email = $email;
		$element->phone_number_1 = $phone_number_1;
		$element->phone_number_2 = $phone_number_2;
		$element->retention_agent = $retention_agent;
		$element->perception_agent_id = $perception_agent_id;
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
