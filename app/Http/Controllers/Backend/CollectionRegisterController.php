<?php

namespace App\Http\Controllers\Backend;

use App\BankAccount;
use App\Client;
use App\Company;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\PaymentMethod;
use App\Sale;
use App\SaleDetail;
use App\WarehouseDocumentType;
use Carbon\CarbonImmutable;
use App\Http\Controllers\Backend\gmp_neg;
use stdClass;
use App\WarehouseTypeInUser;
use Auth;

class CollectionRegisterController extends Controller
{
  public function index() {
		$companies = Company::select('id', 'name')->get();
		$max_sale_date = CarbonImmutable::now()->toAtomString();
		$payment_methods = PaymentMethod::select('id', 'name')->get();
		$currencies = Currency::select('id', 'name')->get();
		$bank_accounts = BankAccount::join('banks', 'bank_accounts.bank_id', '=', 'banks.id')
			->select('bank_accounts.id', 'company_id', 'currency_id', 'account_number', 'short_name', 'banks.id AS bank_id')
			->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();

		return view('backend.collection_register')->with(compact('companies', 'max_sale_date', 'payment_methods', 'currencies', 'bank_accounts', 'warehouse_document_types'));
	}

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text','code')
			->where('company_id', $company_id)
			->where('business_name', 'like', '%'.$q.'%')
			->orWhere('code', 'like', '%'.$q.'%')
			->orWhere('id', 'like', '%'.$q.'%')
			->get();

		return $clients;
	}

	public function validateFirstStep() {
		$messages = [
			'company_id.required'	=> 'Debe seleccionar una Compañía.',
			'client_id.required'	=> 'Debe seleccionar un Cliente.',
		];

		$rules = [
			'company_id'	=> 'required',
			'client_id'		=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function validateSecondStep() {
		$messages = [
			'sale_date.required'								=> 'Debe seleccionar una Fecha de Cancelación.',
			'payment_method_id.required'						=> 'Debe seleccionar un Tipo de Cancelación.',
			'currency_id.required'								=> 'Debe seleccionar una Moneda.',
			'exchange_rate.required_if'							=> 'El Tipo de Cambio es obligatorio.',
			'bank_account_id.required_if'						=> 'Debe seleccionar un Banco.',
			'operation_number.required_if'						=> 'El Nº de Operación es obligatorio.',
			'detraction_number.required_if'						=> 'El Nº de Detracción es obligatorio.',
			'referral_warehouse_document_type_id.required_if'	=> 'Debe seleccionar un Tipo de Documento Aplicación/Canje.',
			'amount.required'									=> 'El Total es obligatorio.',
		];

		$rules = [
			'sale_date'								=> 'required',
			'payment_method_id'						=> 'required',
			'currency_id'							=> 'required',
			'exchange_rate'							=> 'required_if:currency_id,2,3',
			'bank_account_id'						=> 'required_if:payment_method_id,2,3',
			'operation_number'						=> 'required_if:payment_method_id,2,3',
			'detraction_number'						=> 'required_if:bank_id,4',
			'referral_warehouse_document_type_id'	=> 'required_if:payment_method_id,4,5,6',
			'amount'								=> 'required',
		];

		request()->validate($rules, $messages);

		$payment_method_id = request('payment_method_id');
		if ( $payment_method_id > 3 ) {
			$company_id = request('company_id');
			$referral_warehouse_document_type_id = request('referral_warehouse_document_type_id');
			$referral_serie_number = request('referral_serie_number');
			$referral_voucher_number = request('referral_voucher_number');

			$sale = Sale::where('company_id', $company_id)
				->where('warehouse_document_type_id', $referral_warehouse_document_type_id)
				->where('referral_serie_number', $referral_serie_number)
				->where('referral_voucher_number', $referral_voucher_number)
				->first();

			// if ( !$sale ) {
			// 	$error = new stdClass();
			// 	$error->title = 'Error';
			// 	$error->msg = 'El Documento de Referencia no existe.';

			// 	return response()->json(['error' => $error]);
			// }

			// if ( $sale->balance > 0 ) {
			// 	$error = new stdClass();
			// 	$error->title = 'Error';
			// 	$error->msg = 'El Documento de Referencia no cuenta con saldo.';

			// 	return response()->json(['error' => $error]);
			// }

			request()->merge([
				'sale' => $sale
			]);
		}

		return request()->all();
	}

	public function getSales() {
		$company_id = request('company_id');
		$client_id = request('client_id');

		$sales = Sale::join('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->where('sales.balance', '>', 0)
			->where('sales.company_id', $company_id)
			->where('sales.client_id', $client_id)
			->select('sales.id', 'warehouse_document_types.name as warehouse_document_type_name', 'referral_serie_number', 'referral_voucher_number', 'total_perception', 'balance', 'paid', 'sale_date', 'expiry_date')
			->get();

		return $sales;
	}

	public function store() {
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
																							->where('user_id', $user_id)
																							->first();

		// Este valor debe ser dependiendo del almacen que tenga asignado el usuario
		$warehouse_type_id = $warehouse_type_user->warehouse_type_id;
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');
		$sale_date = date('Y-m-d', strtotime(request('model.sale_date')));
		$payment_method_id = request('model.payment_method_id');
		$currency_id = request('model.currency_id');
		$exchange_rate = request('model.exchange_rate');
		$bank_account_id = request('model.bank_account_id');
		$operation_number = request('model.operation_number');
		$detraction_number = request('model.detraction_number');
		$amount = request('model.amount');
		$saldo_favor_id = request('model.saldo_favor_id');
		$document_id = request('model.document_id');
		$items = request('items');
		$total_paid = request('total_paid');
		$to_be_assigned = request('to_be_assigned');

		foreach ($items as $item) {
			$last_collection_number = Liquidation::max('collection_number');
			$collection_number = $last_collection_number ? ++$last_collection_number : 1;
			
			$liquidation = new Liquidation();
			$liquidation->sale_id = $item['id'];
			$liquidation->collection_number = $collection_number;
			$liquidation->company_id = $company_id;
			$liquidation->payment_method_id = $payment_method_id;
			$liquidation->currency_id = $currency_id;
			$liquidation->exchange_rate = $exchange_rate;
			$liquidation->bank_account_id = $bank_account_id;
			$liquidation->operation_number = $operation_number;
			$liquidation->detraction_number = $detraction_number;
			$liquidation->amount = $item['paid'];
			if ($liquidation['payment_date']) {
				$liquidation_model->rem_date = $liquidation['payment_date'];
			}
			if ($liquidation['payment_sede']) {
				$liquidation_model->payment_sede = $liquidation['payment_sede'];
			}
			$liquidation->collection = 1;
			$liquidation->cede = $warehouse_type_id;
			$liquidation->created_at_user = Auth::user()->user;
			$liquidation->updated_at_user = Auth::user()->user;
			$liquidation->save();

			$sale = Sale::find($item['id']);
			$sale->balance -= $item['paid'];
			$sale->paid += $item['paid'];

			$pendSaleLiquidations = $sale->liquidations()->where('payment_method_id', 7)->get();

			foreach ($pendSaleLiquidations as $pendLiquidation) {
				$pendLiquidation->amount -= $item['paid'];
			}

			$sale->save();
		}

		$client = Client::find($client_id);
		if ( $payment_method_id < 5 ) {
			$client->credit_balance = $total_paid;
			$client->save();
		}

		if ($saldo_favor_id) {
			$saldo = Sale::find($saldo_favor_id);

			$rest = $saldo->total_perception - $total_paid;

			if ($rest < 0) {
				$saldo->total_perception = 0;
			} else {
				$saldo->total_perception = $rest;
			}

			$saldo->save();
		};

		if ($document_id) {
			$saldo = Sale::find($document_id);

			$rest = $saldo->total_perception - $total_paid;

			if ($rest < 0) {
				$saldo->total_perception = 0;
			} else {
				$saldo->total_perception = $rest;
			}

			$saldo->save();
		};

		if ( $total_paid > 0 && $to_be_assigned > 0 ) {
			$referral_serie_number = date('Ym', strtotime($sale_date));
			$last_referral_voucher_number = Sale::where('company_id', $company_id)
																					->where('referral_serie_number', $referral_serie_number)
																					->max('referral_voucher_number');

			$newSale = new Sale();
			$newSale->company_id = $company_id;
			$newSale->sale_date = $sale_date;
			$newSale->expiry_date = $sale_date;
			$newSale->client_id = $client_id;
			$newSale->client_code = $client->code;
			$newSale->payment_id = 1;
			$newSale->currency_id = $currency_id;
			$newSale->warehouse_document_type_id = 30;
			$newSale->referral_serie_number = $referral_serie_number;
			$newSale->referral_voucher_number = ++$last_referral_voucher_number;
			$newSale->sale_value = 0;
			$newSale->exonerated_value = 0;
			$newSale->inaccurate_value = $to_be_assigned;
			$newSale->igv = 0;
			$newSale->total = $to_be_assigned;
			$newSale->total_perception = $to_be_assigned;
			$newSale->paid = 0;
			$newSale->save();

			$newSaleDetail = new SaleDetail();
			$newSaleDetail->sale_id = $newSale->id;
			$newSaleDetail->concept = 'Exceso cobrado';
			$newSaleDetail->price_igv = 0;
			$newSaleDetail->sale_value = 0;
			$newSaleDetail->inaccurate_value = $to_be_assigned;
			$newSaleDetail->exonerated_value = 0;
			$newSaleDetail->igv = 0;
			$newSaleDetail->total = $to_be_assigned;
			$newSaleDetail->total_perception = $to_be_assigned;
			$newSaleDetail->igv_percentage = 0;
			$newSaleDetail->igv_perception_percentage = 0;
			$newSaleDetail->igv_percentage = 0;
			$newSaleDetail->save();
			
			$last_collection_number = Liquidation::max('collection_number');
			$collection_number = $last_collection_number ? ++$last_collection_number : 1;

			$newLiquidation = new Liquidation();
			$newLiquidation->sale_id = $newSale->id;
			$newLiquidation->collection_number = $collection_number;
			$newLiquidation->company_id = $company_id;
			$newLiquidation->payment_method_id = $payment_method_id;
			$newLiquidation->currency_id = $currency_id;
			$newLiquidation->exchange_rate = $exchange_rate;
			$newLiquidation->bank_account_id = $bank_account_id;
			$newLiquidation->operation_number = $operation_number;
			$newLiquidation->detraction_number = $detraction_number;
			$newLiquidation->amount = $to_be_assigned;
			$newLiquidation->collection = 1;
			$newLiquidation->save();
		}

		$response = new stdClass();
		$response->type = 1;
		$response->title = 'Ok!';
		$response->msg = 'El Registro de Cobranzas se realizó exitosamente.';

		return response()->json($response);
	}

	// public function validateThirdStep() {
	// 	$messages = [
	// 		'concept.required'					=> 'El Concepto es obligatorio.',
	// 		'unit_id.required'					=> 'Debe seleccionar una Unidad de Medida.',
	// 		'value_type_id.required'			=> 'El Tipo de Venta es obligatorio.',
	// 		'referral_guide_series.required_if'	=> 'La Serie Guía de Remisión es obligatoria.',
	// 		'referral_guide_number.required_if'	=> 'El Nº Guía de Remisión es obligatorio.',
	// 		'carrier_series.required_if'		=> 'La Serie Guía de Transportista es obligatoria.',
	// 		'carrier_number.required_if'		=> 'El Nº Guía de Transportista es obligatorio.',
	// 		'license_plate.required_if'			=> 'La Placa es obligatoria.',
	// 	];

	// 	$rules = [
	// 		'concept'				=> 'required',
	// 		'unit_id'				=> 'required',
	// 		'value_type_id'			=> 'required',
	// 		'referral_guide_series'	=> 'required_if:business_unit_id,5',
	// 		'referral_guide_number'	=> 'required_if:business_unit_id,5',
	// 		'carrier_series'		=> 'required_if:business_unit_id,5',
	// 		'carrier_number'		=> 'required_if:business_unit_id,5',
	// 		'license_plate'			=> 'required_if:business_unit_id,5',
	// 	];

	// 	request()->validate($rules, $messages);
	// 	return request()->all();
	// }

	public function getSaldosFavor() {
		$client_id = request('client_id');

		$saldos_favor = Sale::where('warehouse_document_type_id', 30)
												->where('client_id', $client_id)
												->where('total_perception', '>', 0)
												->select('id',
																'sale_date',
																'referral_serie_number',
																'referral_voucher_number',
																'currency_id',
																'total_perception')
												->get();

		$saldos_favor->map(function($item, $index) {
			$item->name = $item->sale_date . ' | ' . $item->referral_serie_number . '-' . $item->referral_voucher_number . ' | ' . $item->total_perception;

			return $item;
		});

		return response()->json($saldos_favor, 200);
	}

	public function getDocuments() {
		$client_id = request('client_id');
		$warehouse_document_type_id = request('warehouse_document_type_id');

		$documents = Sale::where('warehouse_document_type_id', $warehouse_document_type_id)
												->where('client_id', $client_id)
												->where('total_perception', '>', 0)
												->select('id',
																'sale_date',
																'referral_serie_number',
																'referral_voucher_number',
																'currency_id',
																'total_perception')
												->get();

		$documents->map(function($item, $index) {
			$item->name = $item->sale_date . ' | ' . $item->referral_serie_number . '-' . $item->referral_voucher_number;

			return $item;
		});

		return response()->json($documents, 200);
	}
}
