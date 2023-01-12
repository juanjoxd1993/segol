<?php

namespace App\Http\Controllers\Backend;

use App\Category;
use App\BusinessUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Product;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use stdClass;

class ProductController extends Controller
{
	public function index() {
		$categories = Category::select('id','name')->get();
		

		return view('backend.products')->with(compact('categories'));
	}

	public function validateForm() {
		$messages = [
			'category_id.required'	=> 'Debe seleccionar una Categoria.',
		];

		$rules = [
			'category_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$category_id = request('category_id');
		$p = request('pagination');
		$q = request('query');
		$page = (int)$p['page'];
		$perpage = (int)( $p['perpage'] ? $p['perpage'] : 10 );
		$search = $q['generalSearch'];
		request()->replace(['page' => $page]);

		$elements = Product::select('id', 'category_id','codigo_hys')
		
			->when($search, function($query, $search) {
			return $query->where('id', 'like', /*'%'.*/$search/*.'%'*/) ->orWhere('codigo_hys', 'like', '%'.$search.'%');
			})
			->where('category_id', $category_id)
			->orderBy('id', 'asc')
			->paginate($perpage);

		$elements->map(function($item, $key) {
			$item->name = $item->product->name;
			
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

		$element = Product::select('id', 'category_id', 'codigo_hys' )->findOrFail($id);

		

		return $element;
	}

	public function validateModalForm() {
		$messages = [
			'category_id.required'			=> 'El Tipo de Documento es obligatorio.',
			'codigo_hys.required'			=> 'El Número de Documento es obligatorio.',
		//	'client_code.required'				=> 'El Código de Cliente es obligatorio.',
			'codigo_meco.required'			=> 'El Nombre o Razón Social es obligatorio.',
			'pricipio_activo.required'					=> 'La Dirección es obligatoria.',
			'ds_articulo.required'				=> 'El Ubigeo es obligatorio.',
			'presentacion.required'			=> 'El Nombre de Contacto 1 es obligatorio.',
			// 'email.required_if'					=> 'El Email es obligatorio.',
			// 'phone_number_1.required'			=> 'El Teléfono 1 es obligatorio.',
			'estado_meco.required'			=> 'La Unidad de Negocio es obligatoria.',
			'estado_hys.required'					=> 'La Zona es obligatoria.',
			'condicion.required'				=> 'El Canal es obligatorio.',
			'registro.required'					=> 'La Ruta es obligatoria.',
			'categoria_3.required'				=> 'El Sector es obligatorio.',
			'fecha_presentacion.required'				=> 'La Condición de Pago es obligatoria.',
			'registro_indecopi.required_if'			=> 'Debe digitar un Límite de Crédito.',
			'laboratorio.required_if'		=> 'Debe digitar los Días de Crédito.',
		
			

		];

		$rules = [
			'id'			=> 'required',
			'category_id'			=> 'required',
		//	'client_code'				=> 'required',
			'codigo_hys'				=> 'required',
			'codigo_meco'					=> 'required',
			'pricipio_activo'					=> 'required',
			'ds_articulo'			=> 'required',
			// 'email'						=> 'required_if:document_type_id,1',
			// 'phone_number_1'			=> 'required',
			'presentacion'			=> 'required',
			'estado_meco'					=> 'required',
			'estado_hys'				=> 'required',
			'condicion'					=> 'required',
			'registro'					=> 'required',
			'categoria_3'				=> 'required',
			'fecha_presentacion'				=> 'required',
			'registro_indecopi'			=> 'required',
			'laboratorio'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}


	public function getClients() {
		$category_id = request('category_id');
		$q = request('q');

		$clients = Product::select('id', 'codigo_hys')
		    ->where('category_id', $company_id)
			
			->where('codigo_hys', 'like', '%'.$q.'%')
			->orderBy('codigo_hys', 'asc')
			->get();

		$clients->map(function($item, $index){
			$item->text = $item->codigo_hys;
			unset($item->codigo_hys);
			return $item;
		});

		return $clients;
	}

	
	public function store() {
		$this->validateModalForm();

		$id = request('id');
		$category_id = request('category_id');
		$codigo_hys= request('codigo_hys');
		$codigo_meco = request('codigo_meco');
		$pricipio_activo = request('pricipio_activo');
		$ds_articulo = request('ds_articulo');
		$presentacion = request('presentacion');
		$estado_meco = request('estado_meco');
		$estado_hys = request('estado_hys');
		$condicion = request('condicion');
		$registro = request('registro');
		$categoria_3 = request('categoria_3');
		$cambios = request('cambios');
		
		$fecha_presentacion = request('fecha_presentacion');
		$registro_indecopi = request('registro_indecopi');
		$laboratorio = request('laboratorio');

		if ( isset($id) ) {
			$element = Product::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;

			
		} else {
			$element = new Product();
			$msg = 'Registro creado exitosamente';
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;

		}
		$element->id = $id;
		$element->category_id = $category_id;
		$element->codigo_hys = $codigo_hys;
		$element->codigo_meco = $codigo_meco;
		$element->pricipio_activo = $pricipio_activo;
		$element->ds_articulo = $ds_articulo;
		$element->presentacion = $presentacion;
		$element->estado_meco = $estado_meco;
		$element->estado_hys = $estado_hys;
		$element->condicion = $condicion;
		$element->registro = $registro;
		$element->cambios = $cambios;
		$element->fecha_presentacion = $fecha_presentacion;
		$element->registro_indecopi = $registro_indecopi;
		$element->laboratorio = $laboratorio;
		
		$element->save();

		$childElement->client_id = $element->id;
		$childElement->address_type_id = 1;
		$childElement->item_number = 1;
		$childElement->address = $address;
		$childElement->address_reference = $address_reference;
		$childElement->ubigeo_id = $ubigeo_id;
		$childElement->gps_x = $gps_x;
		$childElement->gps_y = $gps_y;
		$childElement->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	

	public function delete() {
		$id = request('id');
		$element = Product::findOrFail($id);
		$element->delete();
	}
		
}
