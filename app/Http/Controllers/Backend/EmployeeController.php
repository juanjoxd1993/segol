<?php

namespace App\Http\Controllers\Backend;

use App\AddressType;
use App\Article;
use App\BusinessUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Client;
use App\EmployeeAddress;
use App\Area;
use App\Tasa;
use App\Saludtasa;
use App\Sctasa;
use App\Company;
use App\DocumentType;
use App\Payment;
use App\PriceList;
use App\Rate;
use App\Employee;
use App\Ubigeo;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use stdClass;
use App\Services\ClientDataService;

class EmployeeController extends Controller
{
	public function index() {
		$companies = Company::select('id','name')->get();
		$document_types = DocumentType::select('id', 'name')->get();
		$address_types = AddressType::select('id', 'name')->get();
		$areas = Area::select('id', 'name')->get();
		$tasas = Tasa::select('id', 'name')->get();
		$saludtasas = Saludtasa::select('id', 'name')->get();
		$sctasas = Sctasa::select('id', 'name')->get();

		return view('backend.employees')->with(compact('companies', 'document_types', 'address_types', 'areas', 'tasas', 'saludtasas', 'sctasas'));
	}

	public function validateForm() {
		$messages = [
			'company_id.required'	=> 'Debe seleccionar una Compañía.',
		];

		$rules = [
			'company_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$company_id = request('company_id');
		$p = request('pagination');
		$q = request('query');
		$page = (int)$p['page'];
		$perpage = (int)( $p['perpage'] ? $p['perpage'] : 10 );
		$search = $q['generalSearch'] ?? '';
		request()->replace(['page' => $page]);

		$elements = Employee::select('id', 'company_id', 'first_name', 'last_name','document_type_id', 'document_number', 'asignacion_id', 'sueldo', 'afp_id', 'area_id', 'sctr_id', 'fecha_inicio','email', 'phone_number_1','contact_name_1')
		
			->when($search, function($query, $search) {
			return $query->where('document_number', 'like', '%'.$search.'%') ->orWhere('first_name', 'like', '%'.$search.'%');
			})
			->where('company_id', $company_id)
			->orderBy('id', 'asc')
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
			'search' => $search,
		]);
	}

	public function detail() {
		$id = request('id');

		$element = Employee::select('id', 'company_id', 'first_name', 'last_name','document_type_id', 'document_number', 'asignacion_id', 'sueldo', 'afp_id', 'area_id', 'sctr_id', 'fecha_inicio','email', 'phone_number_1','contact_name_1')->findOrFail($id);

		$childElement = EmployeeAddress::where('employee_id', $id)
			->where('address_type_id', 1)
			->where('item_number', 1)
			->first();
		
		$element->address = ( $childElement ? $childElement->address : '' );
		$element->address_reference = ( $childElement ? $childElement->address_reference : '' );
		$element->ubigeo_id = ( $childElement ? $childElement->ubigeo_id : '' );
		

		return $element;
	}

	public function validateModalForm() {
		$messages = [
			'document_type_id.required'			=> 'El Tipo de Documento es obligatorio.',
			'document_number.required'			=> 'El Número de Documento es obligatorio.',
		//	'client_code.required'				=> 'El Código de Cliente es obligatorio.',
			'first_name.required'			=> 'El Nombre o Razón Social es obligatorio.',
			'address.required'					=> 'La Dirección es obligatoria.',
			'ubigeo_id.required'				=> 'El Ubigeo es obligatorio.',
			'contact_name_1.required'			=> 'El Nombre de Contacto 1 es obligatorio.',
			// 'email.required_if'					=> 'El Email es obligatorio.',
			// 'phone_number_1.required'			=> 'El Teléfono 1 es obligatorio.',
			'asignacion_id.required'			=> 'La Asignación es obligatoria.',
			'sueldo.required'					=> 'El Salario es obligatorio.',
			'tasa_id.required'				=> 'El Tipo de AFP es obligatorio.',
		//	'salud_id.required'					=> 'El tipo de Seguro es obligatorio.',
		//	'sctr_id.required'				=> 'El tipo de SCTR es obligatorio.',
			'since_date.required'				=> 'La fecha de Alta es obligatoria.',
		];

		$rules = [
			'document_type_id'			=> 'required',
			'document_number'			=> 'required',
		//	'client_code'				=> 'required',
			'first_name'				=> 'required',
			'address'					=> 'required',
			'ubigeo_id'					=> 'required',
			'contact_name_1'			=> 'required',
		//  'email'						=> 'required_if:document_type_id,1',
		//  'phone_number_1'			=> 'required',
			'asignacion_id'				=> 'required',
			'tasa_id'					=> 'required',
		//	'salud_id'					=> 'required',
		//	'sctr_id'					=> 'required',
			'since_date'				=> 'required',			
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

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Employee::select('id', 'first_name')
		    ->where('company_id', $company_id)
			->where('first_name', 'like', '%'.$q.'%')
			->orderBy('code', 'asc')
			->get();

		$clients->map(function($item, $index){
			$item->text = $item->first_name;
			unset($item->first_name);
			return $item;
		});

		return $clients;
	}

	public function getSelect2() {
		$ubigeo_id = request('ubigeo_id');
		
		$ubigeo = '';
		

		if ( isset($ubigeo_id) ) {
			$ubigeo = Ubigeo::select('id', 'district', 'province', 'department', 'country')
				->where('id', $ubigeo_id)
				->first();
			
			$ubigeo->text = $ubigeo->district.' - '.$ubigeo->province.' - '.$ubigeo->department.' - '.$ubigeo->country;
			unset($ubigeo->district);
			unset($ubigeo->province);
			unset($ubigeo->department);
			unset($ubigeo->country);
		}

		

		return response()->json([
			'ubigeo'	=> $ubigeo,
			
		]);
	}

	public function store() {
		$this->validateModalForm();

		$id = request('id');
		$company_id = request('company_id');
		$first_name = request('business_name');
		$document_type_id = request('document_type_id');
		$document_number = request('document_number');
		$address = request('address');
		$address_reference = request('address_reference');
		$ubigeo_id = request('ubigeo_id');
		$sueldo = request('police');
		$contact_name_1 = request('contact_name_1');
		$email = request('email');
		$since_date = request('model.since_date');
		$phone_number_1 = request('phone_number_1');
		$phone_number_2 = request('phone_number_2');
		$asignacion_id = request('asignacion_id');
		$area_id = request('area_id');
		$afp_id = request('afp_id');
		$salud_id = request('salud_id');
		$sctr_id = request('sctr_id');
		$license = request('dgh');
		

		if ( isset($id) ) {
			$element = Employee::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;

			$childElement = EmployeeAddress::where('employee_id', $id)
				->where('address_type_id', 1)
				->where('item_number', 1)
				->first();

			if ($childElement) {
				$childElement->updated_at_user = Auth::user()->user;
			} else {
				$childElement = new EmployeeAddress();
			//	$childElement->created_at_user = Auth::user()->user;
			//	$childElement->updated_at_user = Auth::user()->user;
			}
			
		} else {
			$element = new Employee();
			$msg = 'Registro creado exitosamente';
		//	$element->created_at_user = Auth::user()->user;
		//	$element->updated_at_user = Auth::user()->user;
      

			$childElement = new EmployeeAddress();
		//	$childElement->created_at_user = Auth::user()->user;
		//	$childElement->updated_at_user = Auth::user()->user;
		}
		$element->company_id = $company_id;
		$element->first_name = $first_name;
		$element->document_type_id = $document_type_id;
		$element->document_number = $document_number;
		$element->sueldo = $sueldo;
		$element->license = $license;
		$element->contact_name_1 = $contact_name_1;
		$element->email = $email;
		$element->phone_number_1 = $phone_number_1;
		$element->phone_number_2 = $phone_number_2;
		$element->asignacion_id = $asignacion_id;
		$element->afp_id = $afp_id;
		$element->salud_id = $salud_id;
		$element->sctr_id = $sctr_id;
		$element->area_id = $area_id;
		$element->fecha_inicio =  date('Y-m-d', strtotime($since_date));;
		$element->save();

		$childElement->employee_id = $element->id;
		$childElement->address_type_id = 1;
		$childElement->item_number = 1;
		$childElement->address = $address;
		$childElement->address_reference = $address_reference;
		$childElement->ubigeo_id = $ubigeo_id;

		$childElement->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	public function update($id) {
		$company = Company::where('id', $id)->first();
		$clients = DB::connection($company->database_name)
			->table('MaestroCliente')
			->get();
		
		$clients->each(function($item, $key) use ($company) {
			if ( trim($item->RucCliente) ) {
				$documentType = 1;
				$documentNumber = trim($item->RucCliente);
			} elseif ( trim($item->DNI) ) {
				$documentType = 2;
				$documentNumber = trim($item->DNI);
			} else {
				$documentType = 3;
				$documentNumber = trim($item->CodCliente);
			}

			$client = Employee::where('company_id', $company->id)
				->where('code', $item->CodCliente)
				->first();
			if ( $client ) {
				$client->company_id = $company->id;
				$client->code = trim($item->CodCliente);
				$client->business_name = trim($item->RazonSocial);
				$client->document_type_id = $documentType;
				$client->document_number = $documentNumber;
				$client->email = trim($item->EMail);
				$client->phone_number_1 = trim($item->TelfMovil);
				$client->phone_number_2 = '';
				$client->client_group_id = trim($item->CodGrupo);
				$client->zip_code = trim($item->CodZip);
				$client->seller_id = trim($item->CodVendedor);
				$client->credit_limit = trim($item->LimiteCredito);
				$client->save();

				$clientAddress = ClientAddress::where('client_id', $client->id)->first();
				if ( !$clientAddress ) {
					$clientAddress = new ClientAddress();
				}
				$clientAddress->client_id = $client->id;
				$clientAddress->address_type_id = 1;
				$clientAddress->address = trim($item->Direccion);
				$clientAddress->ubigeo_id = 347;
				$clientAddress->save();
			}
		});
	}

	public function delete() {
		$id = request('id');
		$element = Employee::findOrFail($id);
		$element->delete();
	}

	public function addressList() {
		$employee_id = request('employee_id');
		$p = request('pagination');
		$page = (int)$p['page'];
		$perpage = (int)( $p['perpage'] ? $p['perpage'] : 10 );
		request()->replace(['page' => $page]);		

		$elements = EmployeeAddress::select('id', 'employee_id', 'address_type_id', 'item_number', 'address', 'ubigeo_id')
			->where('employee_id', $employee_id)
			->paginate($perpage);

		$elements->map(function($item, $index) {
			$item->address_type_name = $item->address_type->name;
			$item->ubigeo_name = $item->ubigeo->district.', '.$item->ubigeo->province.', '.$item->ubigeo->department.','.$item->ubigeo->country;
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

	public function validateAddressModalForm() {
		$messages = [
			'address_type_id.required'		=> 'El Tipo de Dirección es obligatorio.',
			'address.required'				=> 'La Dirección es obligatoria.',
			'address_ubigeo_id.required'	=> 'El Ubigeo es obligatorio.',
		];

		$rules = [
			'address_type_id'	=> 'required',
			'address'			=> 'required',
			'address_ubigeo_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function addressDetail() {
		$id = request('id');

		$element = EmployeeAddress::select('id', 'employee_id', 'address_type_id', 'item_number', 'address', 'address_reference', 'ubigeo_id')
			->findOrFail($id);
		
		$option = new stdClass();
		$option->text = $element->ubigeo->district.'-'.$element->ubigeo->province.'-'.$element->ubigeo->department.'-'.$element->ubigeo->country;
		$option->id = $element->ubigeo_id;

		return response()->json([
			'element'	=> $element,
			'option'	=> $option
		]);
	}

	public function addressStore() {
		$this->validateAddressModalForm();

		$id = request('id');
		$employee_id = request('employee_id');
		$address_type_id = request('address_type_id');
		$item_number = request('item_number');
		$address = request('address');
		$address_reference = request('address_reference');
		$ubigeo_id = request('address_ubigeo_id');

		if ( isset($id) ) {
			$element = EmployeeAddress::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;
		} else {
			$element = new EmployeeAddress();
			$msg = 'Registro creado exitosamente';
		//	$element->created_at_user = Auth::user()->user;
		//	$element->updated_at_user = Auth::user()->user;

			$item_number = EmployeeAddress::select('employee_id', 'address_type_id', 'item_number')
				->where('employee_id', $employee_id)
				->where('address_type_id', $address_type_id)
				->max('item_number');
			$element->item_number = ++$item_number;
		}

		$element->employee_id = $employee_id;
		$element->address_type_id = $address_type_id;
		$element->address = $address;
		$element->address_reference = $address_reference;
		$element->ubigeo_id = $ubigeo_id;
		$element->save();

		$type = 1;
		$title = '¡Ok!';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	public function addressDelete() {
		$id = request('id');
		$element = EmployeeAddress::findOrFail($id);
		$element->item_number = '';
		$element->delete();

		$elements = EmployeeAddress::select('id', 'item_number')
			->where('employee_id', $element->employee->id)
			->where('address_type_id', $element->address_type->id)
			->orderBy('item_number', 'asc')
			->get();
		$count = 0;

		foreach ($elements as $element) {
			$element->item_number = ++$count;
			$element->save();
		}
	}

	
	public function priceList() {
		$client_id = request('client_id');
		$today = Carbon::now()->startOfDay();

		$elements = PriceList::select('id', 'client_id', 'article_id', 'price_igv', 'initial_effective_date', 'final_effective_date', 'state')
			->where('client_id', $client_id)
			->where('final_effective_date', '>=', date('Y-m-d', strtotime($today)))
			->where('state', 1)
			->orderBy('initial_effective_date', 'asc')
			->orderBy('final_effective_date', 'asc')
			->get();

		$elements->map(function ($item, $key) {
			$item->code = $item->article->code;
			$item->name = $item->article->code . ' - ' . $item->article->name . ' ' . $item->article->sale_unit->name . ' x ' . $item->article->package_sale;
			$item->initial_effective_date = date('d/m/Y', strtotime($item->initial_effective_date));
			$item->final_effective_date = date('d/m/Y', strtotime($item->final_effective_date));
		});

		return $elements;
	}

	public function priceArticles() {
		$warehouse_type_id = request('warehouse_type_id');
		$q = request('q');

		$elements = Article::select('id', 'code', 'name', 'package_sale', 'sale_unit_id')
			->where('warehouse_type_id', $warehouse_type_id)
			->where(function ($query) use ($q) {
				$query->where('code', 'like', '%'.$q.'%')
					->orWhere('name', 'like', '%'.$q.'%');
			})
			->get();
		
		$elements->map(function($item, $key) {
			$item->text = $item->code . ' - ' . $item->name . ' ' . $item->sale_unit->name . ' x ' . $item->package_sale;
		});

		return $elements;
	}

	public function priceMinEffectiveDate() {
		$client_id = request('client_id');
		$article_id = request('article_id');

		$last_date = PriceList::select('id', 'client_id', 'article_id', 'initial_effective_date', 'final_effective_date')
			->where('client_id', $client_id)
			->where('article_id', $article_id)
			->orderBy('initial_effective_date', 'desc')
			->orderBy('final_effective_date', 'desc')
			->first();

		if ( isset($last_date) ) {
			$initial_effective_date = date('d-m-Y', strtotime($last_date->final_effective_date . '+1 day'));
			$min_effective_date = date(DATE_ATOM, strtotime($last_date->final_effective_date . '+2 day'));
		} else {
			$today = Carbon::now()->startOfDay();
			$initial_effective_date = date('d-m-Y', strtotime($today));
			$min_effective_date = date(DATE_ATOM, strtotime($today . '+1 day'));
		}
		
		return response()->json([
			'initial_effective_date' => $initial_effective_date,
			'min_effective_date' => $min_effective_date,
		]);
	}

	public function validatePriceModalForm() {
		$messages = [
			'article_id.required'				=> 'Debe seleccionar un Artículo.',
			'price_igv.required'				=> 'El Precio es obligatorio.',
			'initial_effective_date.required'	=> 'La Fecha inicial es obligatoria.',
			'final_effective_date.required'		=> 'La Fecha final es obligatoria.',
		];

		$rules = [
			'article_id'				=> 'required',
			'price_igv'					=> 'required',
			'initial_effective_date'	=> 'required',
			'final_effective_date'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function priceStore() {
		$this->validatePriceModalForm();

		$client_id = request('client_id');
		$warehouse_type_id = request('warehouse_type_id');
		$article_id = request('article_id');
		$price_igv = request('price_igv');
		$initial_effective_date = request('initial_effective_date');
		$final_effective_date = request('final_effective_date');

		$article = Article::select('igv')
			->where('id', $article_id)
			->first();

		$igv = Rate::select('value')
			->where('description', 'IGV')
			->where('state', 1)
			->first();

		$igv->operator = ($igv->value / 100) + 1;
		
		if ( $article->igv == 1 ) {
			$price = $price_igv / $igv->operator;
		} else {
			$price = $price_igv;
		}

		$element = new PriceList();
		$element->client_id = $client_id;
		$element->warehouse_type_id = $warehouse_type_id;
		$element->article_id = $article_id;
		$element->price = number_format($price, 4, '.', '');
		$element->price_igv = number_format($price_igv, 4, '.', '');
		$element->initial_effective_date = date('Y-m-d', strtotime($initial_effective_date));
		$element->final_effective_date = date('Y-m-d', strtotime($final_effective_date));
		$element->state = 1;
	//	$element->created_at_user = Auth::user()->user;
     // $element->updated_at_user = Auth::user()->user;
		$element->save();

		$type = 1;
		$title = '¡Ok!';
		$msg = 'Registro creado exitosamente';

		return response()->json([
			'type'	=> $type,
			'title'	=> $title,
			'msg'	=> $msg,
		]);
	}

	public function update_address($id) {
		$company = Company::where('id', $id)->first();
		$employees = DB::connection($company->database_name)
			->table('Maestroemployeee')
			->get();
		
		$employees->each(function($item, $key) use ($company) {
			$employee = employee::where('company_id', $company->id)
				->where('code', $item->Codemployeee)
				->first();

			$employeeAddress = EmployeeAddress::where('employee_id', $employee->id)->first();
			if ( !$employeeAddress ) {
				$employeeAddress = new EmployeeAddress();
			}
			$employeeAddress->employee_id = $employee->id;
			$employeeAddress->address_type_id = 1;
			$employeeAddress->address = trim($item->Direccion);
			$employeeAddress->ubigeo_id = 347;
			$employeeAddress->save();
		});
	}

	public function store_tmp($id) {
		$company = Company::where('id', $id)->first();
		$employees = DB::connection($company->database_name)
			->table('MaestroCliente')
			->get();
		
		$employees->each(function($item, $key) use ($company) {
			$employee = Employee::where('company_id', $company->id)
				->where('code', $item->CodEmployeee)
				->first();
			if ( !$employee ) {
				if ( trim($item->RucEmployee) ) {
					$documentType = 1;
					$documentNumber = trim($item->RucEmployee);
				} elseif ( trim($item->DNI) ) {
					$documentType = 2;
					$documentNumber = trim($item->DNI);
				} else {
					$documentType = 3;
					$documentNumber = trim($item->CodEmployee);
				}

				$newEmployee = new Employee();
				$newEmployee->company_id = $company->id;
				$newEmployee->code = trim($item->CodEmployeee);
				$newEmployee->business_name = trim($item->RazonSocial);
				$newEmployee->document_type_id = $documentType;
				$newEmployee->document_number = $documentNumber;
				$newEmployee->email = trim($item->EMail);
				$newEmployee->channel_id = trim($item->Canal);
				$newEmployee->phone_number_1 = trim($item->TelfMovil);
				$newEmployee->phone_number_2 = '';
			//	$newEmployee->created_at_user = trim($item->UsuarioCreador);
			//	$newEmployee->updated_at_user = trim($item->UsuarioCreador);
				$newEmployee->save();

				$employeeAddress = new EmployeeAddress();
				$employeeAddress->employee_id = $newEmployee->id;
				$employeeAddress->address_type_id = 1;
				$employeeAddress->address = trim($item->Direccion);
				$employeeAddress->ubigeo_id = 347;
			//	$employeeAddress->created_at_user = trim($item->UsuarioCreador);
			//	$employeeAddress->updated_at_user = trim($item->UsuarioCreador);
				$employeeAddress->save();
			}
		});
	}

	public function searchClientByRuc(ClientDataService $clientDataService)
	{
		$response = $clientDataService
		->getClientInfoByRuc(request('ruc'));

		if ($response === null) {
			abort(404);
		}

		$data = [
			'business_name' => $response['nombre_o_razon_social'],
			'address' => $response['direccion_completa'],
		];

		return response()->json($data, 200);
	}
}
