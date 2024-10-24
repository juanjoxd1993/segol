<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\BusinessUnit;
use App\Client;
use App\ClientAddress;
use App\Company;
use App\CreditNoteReason;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Payment;
use App\Rate;
use App\Sale;
use App\SaleDetail;
use App\Unit;
use App\ValueType;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use App\WarehouseDocumentType;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use App\GuidesSerie;
use Illuminate\Support\Facades\Auth;
use stdClass;

class RegisterDocumentFactController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();
		$min_sale_date = CarbonImmutable::now()->subWeek()->toAtomString();
		$max_sale_date = CarbonImmutable::now()->toAtomString();
		$payments = Payment::select('id', 'name')->get();
		$min_expiry_date = CarbonImmutable::now()->toAtomString();
		$max_expiry_date = CarbonImmutable::now()->addDays(2)->toAtomString();
		$currencies = Currency::select('id', 'name')->get();
		$business_units = BusinessUnit::select('id', 'name')->get();
		$credit_note_reasons = CreditNoteReason::select('id', 'name')->get();
		$units = Unit::select('id', 'name')->get();
		$articles= Article::select('id','name')->get();

		$value_types = ValueType::select('id', 'name')->get();

		return view('backend.register_document_fact')->with(compact('companies', 'warehouse_document_types', 'min_sale_date', 'max_sale_date', 'payments', 'min_expiry_date', 'max_expiry_date', 'currencies', 'business_units', 'credit_note_reasons', 'units', 'value_types','articles'));
	}

	public function validateFirstStep() {
		$messages = [
			'company_id.required'					=> 'Debe seleccionar una Compañía.',
			'warehouse_document_type_id.required'	=> 'Debe seleccionar un Tipo de Documento.',
			'serie_number.required'					=> 'El Nº de Serie es obligatorio.',
		];

		$rules = [
			'company_id'					=> 'required',
			'warehouse_document_type_id'	=> 'required',
			'serie_number'					=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getVoucher() {
		$this->validateFirstStep();

		$company_id = request('company_id');
		$warehouse_document_type_id = request('warehouse_document_type_id');
		$serie_number = request('serie_number');
		$voucher_number = request('voucher_number');

		$warehouse_document_type = WarehouseDocumentType::find($warehouse_document_type_id, ['id', 'name', 'voucher_type_id']);
		$voucher_type = VoucherType::where('id', $warehouse_document_type->voucher_type_id)
			->select('id', 'serie_type', 'type')
			->first();

		if ( $voucher_type && $voucher_type->type ) {
			if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
				$serie_number = $voucher_type->serie_type . sprintf('%02d', $serie_number);
			} else {
				$serie_number = $voucher_type->serie_type . sprintf('%03d', $serie_number);
			}

			$voucher = Voucher::where('company_id', $company_id)
				->where('voucher_type_id', $voucher_type->id)
				->where('serie_number', $serie_number)
				->select('id', 'client_id', 'voucher_number', 'issue_date', 'expiry_date', 'currency_id')
				->orderBy('serie_number', 'DESC')
				->orderBy('voucher_number', 'DESC')
				->first();

			if ( $voucher ) {
				$voucher_issue_date = CarbonImmutable::createFromDate($voucher->issue_date);
				$min_sale_date = CarbonImmutable::now()->subWeek()->toAtomString();

				if ( $voucher_issue_date < $min_sale_date ) {
					$min_sale_date = $voucher_issue_date->toAtomString();
					$min_expiry_date = $voucher_issue_date->toAtomString();

					request()->merge([
						'min_sale_date' => $min_sale_date,
						'min_expiry_date' => $min_expiry_date
					]);
				}
			}

			$voucher_number = $voucher ? $voucher->voucher_number : 1;

			request()->merge([
				'voucher_type_id'	=> $voucher_type->id,
				'voucher_number'	=> $voucher_number
			]);
		}

		return request()->all();
	}

	public function validateSecondStep() {
		$messages = [
			'sale_date.required'								=> 'La Fecha de Emisión es obligatoria.',
			'client_id.required'								=> 'Debe seleccionar un Cliente.',
			'payment_id.required'								=> 'Debe seleccionar una Condición de Venta.',
			'expiry_date.required_if'							=> 'La Fecha de Vencimiento es obligatoria.',
			'currency_id.required'								=> 'Debe seleccionar una Moneda.',
			'exchange_rate.required_if'							=> 'El Tipo de Cambio es obligatorio.',
			'business_unit_id.required'							=> 'Debe seleccionar una Unidad de Negocio.',
			'credit_note_reason_id.required_if'					=> 'Debe sellecionar un Motivo.',
			'referral_warehouse_document_type_id.required_if'	=> 'Debe seleccionar un Tipo de Referencia.',
			'guide_series.required_if'					        => 'La Serie de Referencia es obligatoria.',
			'guide_number.required_if'				            => 'El Nº de Referencia es obligatorio.',
		];

		$rules = [
			'sale_date'								=> 'required',
			'client_id'								=> 'required',
			'payment_id'							=> 'required',
			'expiry_date'							=> 'required_if:payment_id,2',
			'currency_id'							=> 'required',
			'exchange_rate'							=> 'required_if:currency_id,2,3',
			'business_unit_id'						=> 'required',
			'credit_note_reason_id'					=> 'required_if:voucher_type_id,3,7,11',
			'referral_warehouse_document_type_id'	=> 'required_if:voucher_type_id 4,7,8,11,12',
			'guide_series'					        => 'required_if:voucher_type_id 4,7,8,11,12',
			'guide_number'				            => 'required_if:voucher_type_id 4,7,8,11,12',
		];

		request()->validate($rules, $messages);

		$company_id = request('company_id');
		$voucher_type_id = request('voucher_type_id');
		$client_id = request('client_id');
		$referral_warehouse_document_type_id = request('referral_warehouse_document_type_id');
		$guide_series = request('guide_series');
		$guide_number = request('guide_number');

		$igv_percentage = Rate::where('description', 'IGV')
			->select('value')
			->first()
			->value;

		request()->merge([
			'igv_percentage' => $igv_percentage
		]);

		if ( $referral_warehouse_document_type_id != '' || $guide_series != '' || $guide_number != '' ) {
			$sale = Sale::where('company_id', $company_id)
				->where('warehouse_document_type_id', $referral_warehouse_document_type_id)
				->where('guide_series', $guide_series)
				->where('guide_number', $guide_number)
				->first();

			if ( !$sale ) {
				$error = new stdClass();
				$error->title = 'Error';
				$error->msg = 'El Documento de Referencia no existe.';

				return response()->json(['error' => $error]);
			}

			if ( ( $voucher_type_id == 3 || $voucher_type_id == 4 || $voucher_type_id == 7 || $voucher_type_id == 8 || $voucher_type_id == 11 || $voucher_type_id == 12 ) && $sale->client_id != $client_id ) {
				$error = new stdClass();
				$error->title = 'Error';
				$error->msg = 'El Cliente no coincide.';

				return response()->json(['error' => $error]);
			}
		}

		return request()->all();
	}

	public function getClients() {
		$company_id = request('company_id');
		$voucher_type_id = request('voucher_type_id');
		$q = request('q');

		$document_type_id = '';
		$document_type_ids = '';

		if ( $voucher_type_id == 1 || $voucher_type_id == 5 || $voucher_type_id == 9 ) {
			$document_type_id = 1;
		} elseif ( $voucher_type_id == 2 || $voucher_type_id == 6 || $voucher_type_id == 10 ) {
			$document_type_id = 2;
		} elseif ( $voucher_type_id == 3 || $voucher_type_id == 4 || $voucher_type_id == 7 || $voucher_type_id == 8 || $voucher_type_id == 11 || $voucher_type_id == 12 ) {
			$document_type_ids = array(1, 2);
		}

		$clients = Client::select('id', 'business_name as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->when($document_type_id, function($query, $document_type_id) {
				return $query->where('document_type_id', $document_type_id);
			})
			->when($document_type_ids, function($query, $document_type_ids) {
				return $query->whereIn('document_type_id', $document_type_ids);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->get();

		return $clients;
	}
	
	public function validateThirdStep() {
		$messages = [
			'article_id.required'				=> 'El Concepto es obligatorio.',
			'unit_id.required'					=> 'Debe seleccionar una Unidad de Medida.',
			'value_type_id.required'			=> 'El Tipo de Venta es obligatorio.',
			'referral_guide_series.required_if'	=> 'La Serie Guía de Remisión es obligatoria.',
			'referral_guide_number.required_if'	=> 'El Nº Guía de Remisión es obligatorio.',
			'carrier_series.required_if'		=> 'La Serie Guía de Transportista es obligatoria.',
			'carrier_number.required_if'		=> 'El Nº Guía de Transportista es obligatorio.',
			'license_plate.required_if'			=> 'La Placa es obligatoria.',
		];

		$rules = [
			'article_id'			=> 'required',
			'unit_id'				=> 'required',
			'value_type_id'			=> 'required',
			'referral_guide_series'	=> 'required_if:business_unit_id,5',
			'referral_guide_number'	=> 'required_if:business_unit_id,5',
			'carrier_series'		=> 'required_if:business_unit_id,5',
			'carrier_number'		=> 'required_if:business_unit_id,5',
			'license_plate'			=> 'required_if:business_unit_id,5',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function store() {
		$business_unit_id = request('model.business_unit_id');
		$client_id = request('model.client_id');
		$company_id = request('model.company_id');
		$credit_note_reason_id = request('model.credit_note_reason_id');
		$currency_id = request('model.currency_id');
		$detraction_percentage = request('model.detraction_percentage');
		$exchange_rate = request('model.exchange_rate');
		$expiry_date = date('Y-m-d', strtotime(request('model.expiry_date')));
		$igv_percentage = request('model.igv_percentage');
		$payment_id = request('model.payment_id');
		$referral_warehouse_document_type_id = request('model.referral_warehouse_document_type_id');
		$referral_serie_number = request('model.referral_serie_number');
		$referral_voucher_number = request('model.referral_voucher_number');
		$sale_date = date('Y-m-d', strtotime(request('model.sale_date')));
		$serie_number = request('model.serie_number');
		$voucher_number = request('model.voucher_number');
		$voucher_type_id = request('model.voucher_type_id');
		$warehouse_document_type_id = request('model.warehouse_document_type_id');

		$items = request('items');

		$client = Client::find($client_id, ['id', 'code', 'business_name', 'link_client_id', 'perception_percentage_id', 'credit_balance']);
		if ( $expiry_date ) {
			$expiry_date = $sale_date;
		}
		$client_address = ClientAddress::where('client_id', $client_id)
			->where('address_type_id', 1)
			->first();
		$perception = Rate::find($client->perception_percentage_id, ['id', 'value']);
		$perception_percentage = $perception->value > 0 ? ( $perception->value / 100 ) + 1 : '';

		$sale_value = 0;
		$inaccurate_value = 0;
		$exonerated_value = 0;
		$igv = 0;
		foreach ($items as $item) {
			$sale_value += round($item['sale_value'], 2);
			$inaccurate_value += round($item['inaccurate_value'], 2);
			$exonerated_value += round($item['exonerated_value'], 2);
			$igv += round($item['igv'], 2);
		}
		$total = $sale_value + $inaccurate_value + $exonerated_value + $igv;
		$total_perception = $voucher_type_id == 1 && $perception_percentage ? $total * $perception_percentage : $total;
		$detraction = $detraction_percentage > 0 ? $total * ( $detraction_percentage / 100 ) : 0;

		if ( $warehouse_document_type_id == 8 || $warehouse_document_type_id == 9 || $warehouse_document_type_id == 14 || $warehouse_document_type_id == 15 || $warehouse_document_type_id == 20 || $warehouse_document_type_id == 22 || $warehouse_document_type_id == 29 ) {
			$sale_value = -abs($sale_value);
			$exonerated_value = -abs($exonerated_value);
			$inaccurate_value = -abs($inaccurate_value);
			$igv = -abs($igv);
			$total = -abs($total);
			$total_perception = -abs($total_perception);
		}



		$client->credit_balance += $total_perception;
		$client->save();

		if ( $voucher_type_id ) {
			$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type']);
			if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
				$voucher_serie_number = $voucher_type->serie_type . sprintf('%02d', $serie_number);
			} else {
				$voucher_serie_number = $voucher_type->serie_type . sprintf('%03d', $serie_number);
			}

			$last_voucher_number = Voucher::where('company_id', $company_id)
					->where('voucher_type_id', $voucher_type->id)
					->where('serie_number', $voucher_serie_number)
					->max('voucher_number');

			$credit_note_reference_serie = '';
			$credit_note_reference_number = '';
			if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
                $credit_note_reference_serie = $voucher_type->serie_type . sprintf('%02d', $referral_serie_number);
				$credit_note_reference_number = $referral_voucher_number;
			} else if ( $voucher_type->id == 7 ||$voucher_type->id == 8 || $voucher_type->id == 11 ||$voucher_type->id == 12 ) {
                $credit_note_reference_serie = $voucher_type->serie_type . sprintf('%03d', $referral_serie_number);
				$credit_note_reference_number = $referral_voucher_number;
            }

			$ose = 1;
			if ( $voucher_type_id >= 1 && $voucher_type_id <= 4 ) {
				$ose = 0;
			}

            $sale_value = abs($sale_value);
			$exonerated_value = abs($exonerated_value);
			$inaccurate_value = abs($inaccurate_value);
			$igv = abs($igv);
			$total = abs($total);
			$total_perception = abs($total_perception);

			$voucher = new Voucher();
			$voucher->company_id = $company_id;
			$voucher->client_id = $client_id;
			$voucher->original_client_id = $client->link_client_id ? $client->link_client_id : $client_id;
			$voucher->client_name = $client->business_name;
			$voucher->client_address = $client_address->address;
			$voucher->voucher_type_id = $voucher_type_id;
			$voucher->serie_number = $voucher_serie_number;
			$voucher->voucher_number = ++$last_voucher_number;
			$voucher->credit_note_reason_id = $credit_note_reason_id;
			$voucher->credit_note_reference_serie = $credit_note_reference_serie;
			$voucher->credit_note_reference_number = $credit_note_reference_number;
			$voucher->issue_date = $sale_date;
			$voucher->issue_hour = Carbon::now()->format('H:i:s');
			$voucher->expiry_date = $expiry_date;
			$voucher->currency_id = $currency_id;
			$voucher->payment_id = $payment_id;
			$voucher->taxed_operation = $sale_value;
			$voucher->unaffected_operation = $inaccurate_value;
			$voucher->exonerated_operation = $exonerated_value;
			$voucher->igv = $igv;
			$voucher->total = $total;
			$voucher->igv_perception = $total_perception - $total;
			$voucher->total_perception = $total_perception;
			$voucher->igv_percentage = $igv_percentage;
			$voucher->igv_perception_percentage = $perception_percentage ? $perception_percentage - 1 : 0;
			$voucher->ose = $ose;
			$voucher->user = Auth::user()->user;
			$voucher->save();

		
		}

		foreach ($items as $index => $item) {
            if ( $warehouse_document_type_id == 8 || $warehouse_document_type_id == 9 || $warehouse_document_type_id == 14 || $warehouse_document_type_id == 15 || $warehouse_document_type_id == 20 || $warehouse_document_type_id == 22 || $warehouse_document_type_id == 29 ) {
                $item['sale_value'] = -abs($item['sale_value']);
                $item['referential_sale_value'] = -abs($item['referential_sale_value']);
                $item['inaccurate_value'] = -abs($item['inaccurate_value']);
                $item['exonerated_value'] = -abs($item['exonerated_value']);
                $item['igv'] = -abs($item['igv']);
                $item['total'] = -abs($item['total']);
                $item['total_perception'] = -abs($perception_percentage ? $item['total'] * $perception_percentage : $item['total']);
            }

		

			if ( $voucher_type_id ) {
				$unit_price = $item['price_igv'] / ( ( $igv_percentage / 100 ) + 1 );

				$voucherDetail = new VoucherDetail();
				$voucherDetail->voucher_id = $voucher->id;
				$voucherDetail->unit_id = $item['unit_id'];
				$voucherDetail->name = $item['article_name'];
				$voucherDetail->quantity = $item['quantity'];
				$voucherDetail->original_price = $item['price_igv'];
				$voucherDetail->unit_price = $unit_price;
				$voucherDetail->sale_value = abs($item['price_igv']);
				$voucherDetail->exonerated_value = abs($item['exonerated_value']);
				$voucherDetail->inaccurate_value = abs($item['inaccurate_value']);
				$voucherDetail->igv = abs($item['igv']);
				$voucherDetail->total = abs($item['total']);
				$voucherDetail->user = Auth::user()->user;
				$voucherDetail->article_id = $item['article_id'];
				$voucherDetail->save();
			}
		}

		$response = new stdClass();
		$response->title = 'Ok';
		$response->msg = 'Se ha generado el documento exitosamente.';

		return response()->json($response);
	}
}
