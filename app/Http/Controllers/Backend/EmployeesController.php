<?php

namespace App\Http\Controllers\Backend;

use App\AddressType;
use App\Article;
use App\BusinessUnit;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Client;
use App\ClientAddress;
use App\ClientChannel;
use App\ClientRoute;
use App\ClientSector;
use App\ClientZone;
use App\Company;
use App\DocumentType;
use App\Payment;
use App\PriceList;
use App\Rate;
use App\Ubigeo;
use Auth;
use Carbon\Carbon;
use DateTime;
use DB;
use stdClass;
use App\Services\ClientDataService;

class EmployeesController extends Controller
{
	public function index() {
		$companies = Company::select('id','name')->get();
		$document_types = DocumentType::select('id', 'name')->get();
		$payments = Payment::select('id', 'name')->get();
		$perceptions = Rate::select('id', 'description', 'value')
			->where('description', 'Percepción')
			->orderBy('id', 'desc')
			->get();
		$perceptions->map(function($item, $index) {
			$item->value = number_format($item->value, 1, '.', '');
		});
		$address_types = AddressType::select('id', 'name')->get();
		$business_units = BusinessUnit::select('id', 'name')->get();
		$client_zones = ClientZone::select('id', 'name')->get();
		$client_channels = ClientChannel::select('id', 'name')->get();
		$client_routes = ClientRoute::select('id', 'name')->get();
		$client_sectors = ClientSector::select('id', 'name')->get();

		return view('backend.employees')->with(compact('companies', 'document_types', 'payments', 'perceptions', 'address_types', 'business_units', 'client_zones', 'client_channels', 'client_routes', 'client_sectors'));
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

		$elements = Client::select('id', 'company_id', 'code', 'business_name', 'document_type_id', 'document_number', 'channel_id', 'email', 'phone_number_1', 'phone_number_2', 'seller_id', 'credit_limit','manager_id', 'perception_percentage_id', 'zone_id', 'route_id', 'sector_id','grupo')
		
			->when($search, function($query, $search) {
			return $query->where('document_number', 'like', '%'.$search.'%') ->orWhere('business_name', 'like', '%'.$search.'%');
			})
			->where('company_id', $company_id)
			->orderBy('id', 'asc')
			->paginate($perpage);

		$elements->map(function($item, $key) {
			$item->document_type_name = $item->document_type->name;
			$item->credit_limit = number_format($item->credit_limit, 2, '.', ',');
			$item->perception_percentage_value = number_format($item->perception_percentage->value, 1, '.', '');
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

		$element = Client::select('id', 'company_id', 'code', 'business_name', 'document_type_id', 'document_number', 'contact_name_1', 'contact_name_2', 'email', 'phone_number_1', 'phone_number_2', 'phone_number_3', 'seller_id', 'business_type','dgh','police','manager','manager_id','manager_mail', 'payment_id', 'credit_limit', 'credit_limit_days', 'perception_percentage_id', 'business_unit_id', 'zone_id', 'channel_id', 'route_id', 'sector_id','grupo')->findOrFail($id);

		$childElement = ClientAddress::where('client_id', $id)
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
			'business_name.required'			=> 'El Nombre o Razón Social es obligatorio.',
			'address.required'					=> 'La Dirección es obligatoria.',
			'ubigeo_id.required'				=> 'El Ubigeo es obligatorio.',
			'contact_name_1.required'			=> 'El Nombre de Contacto 1 es obligatorio.',
			// 'email.required_if'					=> 'El Email es obligatorio.',
			// 'phone_number_1.required'			=> 'El Teléfono 1 es obligatorio.',
			'business_unit_id.required'			=> 'La Unidad de Negocio es obligatoria.',
			'zone_id.required'					=> 'La Zona es obligatoria.',
			'channel_id.required'				=> 'El Canal es obligatorio.',
			'route_id.required'					=> 'La Ruta es obligatoria.',
			'sector_id.required'				=> 'El Sector es obligatorio.',
			'payment_id.required'				=> 'La Condición de Pago es obligatoria.',
			'credit_limit.required_if'			=> 'Debe digitar un Límite de Crédito.',
			'credit_limit_days.required_if'		=> 'Debe digitar los Días de Crédito.',
			'perception_percentage_id.required'	=> 'El Agente de Percepción es obligatorio.',
			'int_name.required'	=> 'La referencia interna es obligatoria.',
		];

		$rules = [
			'document_type_id'			=> 'required',
			'document_number'			=> 'required',
		//	'client_code'				=> 'required',
			'business_name'				=> 'required',
			'address'					=> 'required',
			'ubigeo_id'					=> 'required',
			'contact_name_1'			=> 'required',
			// 'email'						=> 'required_if:document_type_id,1',
			// 'phone_number_1'			=> 'required',
			'business_unit_id'			=> 'required',
			'zone_id'					=> 'required',
			'channel_id'				=> 'required',
			'route_id'					=> 'required',
			'sector_id'					=> 'required',
			'payment_id'				=> 'required',
			'credit_limit'				=> 'required_if:payment_id,2',
			'credit_limit_days'			=> 'required_if:payment_id,2',
			'perception_percentage_id'	=> 'required',
			'int_name'                  => 'required',
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

		$clients = Client::select('id', 'code')
		    ->where('company_id', $company_id)
			->where('code', 'like', '%'.$q.'%')
			->where('business_name', 'like', '%'.$q.'%')
			->orderBy('code', 'asc')
			->get();

		$clients->map(function($item, $index){
			$item->text = $item->code;
			unset($item->code);
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
		$business_name = request('business_name');
		$document_type_id = request('document_type_id');
		$document_number = request('document_number');
		$client_code = request('client_code');
		$address = request('address');
		$address_reference = request('address_reference');
		$ubigeo_id = request('ubigeo_id');
		$contact_name_1 = request('contact_name_1');
		$contact_name_2 = request('contact_name_2');
		$email = request('email');
		$phone_number_1 = request('phone_number_1');
		$phone_number_2 = request('phone_number_2');
		$phone_number_3 = request('phone_number_3');
		$business_unit_id = request('business_unit_id');
		$zone_id = request('zone_id');
		$channel_id = request('channel_id');
		$route_id = request('route_id');
		$sector_id = request('sector_id');
		$seller_id = request('seller_id');
		$business_type = request('business_type');
		$dgh = request('dgh');
		$police = request('police');
		$manager = request('manager');
		$manager_id = request('manager_id');
		$manager_mail = request('manager_mail');
		$payment_id = request('payment_id');
		$credit_limit = request('credit_limit');
		$credit_limit_days = request('credit_limit_days');
		$grupo = request('grupo');
		$perception_percentage_id = request('perception_percentage_id');
		$bol_name = 'CLIENTES VARIOS';
		$bol_number = '12345678';
		$int_name = $business_name . ' - ' . request('int_name');

		if ( isset($id) ) {
			$element = Client::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;

			$childElement = ClientAddress::where('client_id', $id)
				->where('address_type_id', 1)
				->where('item_number', 1)
				->first();

			if ($childElement) {
				$childElement->updated_at_user = Auth::user()->user;
			} else {
				$childElement = new ClientAddress();
				$childElement->created_at_user = Auth::user()->user;
				$childElement->updated_at_user = Auth::user()->user;
			}
			
		} else {
			$element = new Client();
			$msg = 'Registro creado exitosamente';
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;
            $element->bol_name = $bol_name;
            $element->bol_number = $bol_number;

			$childElement = new ClientAddress();
			$childElement->created_at_user = Auth::user()->user;
			$childElement->updated_at_user = Auth::user()->user;
		}
		$element->company_id = $company_id;
		$element->business_name = $business_name;
		$element->document_type_id = $document_type_id;
		$element->document_number = $document_number;
		$element->code = $client_code;
		$element->contact_name_1 = $contact_name_1;
		$element->contact_name_2 = $contact_name_2;
		$element->email = $email;
		$element->phone_number_1 = $phone_number_1;
		$element->phone_number_2 = $phone_number_2;
		$element->phone_number_3 = $phone_number_3;
		$element->business_unit_id = $business_unit_id;
		$element->zone_id = $zone_id;
		$element->channel_id = $channel_id;
		$element->route_id = $route_id;
		$element->sector_id = $sector_id;
		$element->seller_id = $seller_id;
		$element->business_type = $business_type;
		$element->dgh = $dgh;
		$element->police = $police;
		$element->manager = $manager;
		$element->manager_id = $manager_id;
		$element->manager_mail= $manager_mail;
		$element->payment_id = $payment_id;
		$element->grupo = $grupo;
		$element->credit_limit = $payment_id == 2 ? $credit_limit : 0;
		$element->credit_limit_days = $payment_id == 2 ? $credit_limit_days : 0;
		$element->perception_percentage_id = $perception_percentage_id;
		$element->int_name = $int_name;
		$element->save();

		$childElement->client_id = $element->id;
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

			$client = Client::where('company_id', $company->id)
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
		$element = Client::findOrFail($id);
		$element->delete();
	}

	public function addressList() {
		$client_id = request('client_id');
		$p = request('pagination');
		$page = (int)$p['page'];
		$perpage = (int)( $p['perpage'] ? $p['perpage'] : 10 );
		request()->replace(['page' => $page]);		

		$elements = ClientAddress::select('id', 'client_id', 'address_type_id', 'item_number', 'address', 'ubigeo_id')
			->where('client_id', $client_id)
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

		$element = ClientAddress::select('id', 'client_id', 'address_type_id', 'item_number', 'address', 'address_reference', 'ubigeo_id')
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
		$client_id = request('client_id');
		$address_type_id = request('address_type_id');
		$item_number = request('item_number');
		$address = request('address');
		$address_reference = request('address_reference');
		$ubigeo_id = request('address_ubigeo_id');

		if ( isset($id) ) {
			$element = ClientAddress::find($id);
			$msg = 'Registro actualizado exitosamente';
			$element->updated_at_user = Auth::user()->user;
		} else {
			$element = new ClientAddress();
			$msg = 'Registro creado exitosamente';
			$element->created_at_user = Auth::user()->user;
			$element->updated_at_user = Auth::user()->user;

			$item_number = ClientAddress::select('client_id', 'address_type_id', 'item_number')
				->where('client_id', $client_id)
				->where('address_type_id', $address_type_id)
				->max('item_number');
			$element->item_number = ++$item_number;
		}

		$element->client_id = $client_id;
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
		$element = ClientAddress::findOrFail($id);
		$element->item_number = '';
		$element->delete();

		$elements = ClientAddress::select('id', 'item_number')
			->where('client_id', $element->client->id)
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
		$element->created_at_user = Auth::user()->user;
        $element->updated_at_user = Auth::user()->user;
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
		$clients = DB::connection($company->database_name)
			->table('MaestroCliente')
			->get();
		
		$clients->each(function($item, $key) use ($company) {
			$client = Client::where('company_id', $company->id)
				->where('code', $item->CodCliente)
				->first();

			$clientAddress = ClientAddress::where('client_id', $client->id)->first();
			if ( !$clientAddress ) {
				$clientAddress = new ClientAddress();
			}
			$clientAddress->client_id = $client->id;
			$clientAddress->address_type_id = 1;
			$clientAddress->address = trim($item->Direccion);
			$clientAddress->ubigeo_id = 347;
			$clientAddress->save();
		});
	}

	public function store_tmp($id) {
		$company = Company::where('id', $id)->first();
		$clients = DB::connection($company->database_name)
			->table('MaestroCliente')
			->get();
		
		$clients->each(function($item, $key) use ($company) {
			$client = Client::where('company_id', $company->id)
				->where('code', $item->CodCliente)
				->first();
			if ( !$client ) {
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

				$newClient = new Client();
				$newClient->company_id = $company->id;
				$newClient->code = trim($item->CodCliente);
				$newClient->business_name = trim($item->RazonSocial);
				$newClient->document_type_id = $documentType;
				$newClient->document_number = $documentNumber;
				$newClient->email = trim($item->EMail);
				$newClient->channel_id = trim($item->Canal);
				$newClient->phone_number_1 = trim($item->TelfMovil);
				$newClient->phone_number_2 = '';
				$newClient->client_group_id = trim($item->CodGrupo);
				$newClient->zip_code = trim($item->CodZip);
				$newClient->seller_id = trim($item->CodVendedor);
				$newClient->credit_limit = trim($item->LimiteCredito);
				$newClient->created_at_user = trim($item->UsuarioCreador);
				$newClient->updated_at_user = trim($item->UsuarioCreador);
				$newClient->save();

				$clientAddress = new ClientAddress();
				$clientAddress->client_id = $newClient->id;
				$clientAddress->address_type_id = 1;
				$clientAddress->address = trim($item->Direccion);
				$clientAddress->ubigeo_id = 347;
				$clientAddress->created_at_user = trim($item->UsuarioCreador);
				$clientAddress->updated_at_user = trim($item->UsuarioCreador);
				$clientAddress->save();
			}
		});
	}

	public function searchClientByRuc(ClientDataService $clientDataService)
	{
		$response = $clientDataService->getClientInfoByRuc(request('ruc'));

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
