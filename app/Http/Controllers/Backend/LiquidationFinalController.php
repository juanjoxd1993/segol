<?php

namespace App\Http\Controllers\Backend;

use App\SaleSeries;

use App\Article;
use App\Bank;
use App\BankAccount;
use App\Client;
use App\ClientAddress;
use App\Company;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\PaymentMethod;
use App\PriceList;
use App\Rate;
use App\Sale;
use App\SaleDetail;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use App\WarehouseDocumentType;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Carbon\Carbon;
use App\Payment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use DB;

use App\ClientLiquidations;
use App\GuidesState;
use App\WarehouseTypeInUser;

use stdClass;

class LiquidationFinalController extends Controller
{
	public function index() {
		$companies = Company::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')
																										->where('name', 'Factura Electrónica')
																										->orWhere('name', 'Boleta de Venta Electrónica')
																										->orWhere('name', 'Nota Interna')
																										->get();
		$payment_methods = PaymentMethod::select('id', 'name', 'payment_id')->get();
		$currencies = Currency::select('id', 'name')->get();
		$payments = Payment::all();
		$payment_cash = Payment::CASH;
		$payment_credit = Payment::CREDIT;

		return view('backend.liquidations_final')->with(
			compact(
				'companies',
				'warehouse_document_types',
				'payment_methods',
				'currencies',
				'payments',
				'payment_cash',
				'payment_credit'
			)
		);
	}

	public function validateForm() {
		$messages = [
			'company_id.required'               => 'Debe seleccionar una Compañía.',
			'warehouse_movement_id.required'    => 'El Nº de Parte es obligatorio.',
		];

		$rules = [
			'company_id'            => 'required',
			'warehouse_movement_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getWarehouseMovements() {
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
                                              ->where('user_id', $user_id)
                                              ->first();

		$warehouse_type_id = $warehouse_type_user->warehouse_type_id;
		$company_id = request('company_id');

			$guide_state = GuidesState::select('id')
																->where('name', 'Por Liquidar')
																->first();

			$elements = WarehouseMovement::select('id', 'movement_number', 'referral_guide_series', 'referral_guide_number', 'license_plate', 'traslate_date', 'movement_type_id')
					->where('company_id', $company_id)
					->where('warehouse_type_id', $warehouse_type_id)
					->where('sale_id', null)
					->where(function ($query) {
							$query->where('action_type_id', 3)
									->orWhere('action_type_id', 4)
									->orWhere('action_type_id', 6)
									->orWhere('action_type_id', 7)
									->orWhere('action_type_id', 8);
					})
		->where('state', $guide_state->id)
					->orderBy('movement_number', 'asc')
					->get();

			$elements->map(function ($item, $index) {
					$item->creation_date = date('d-m-Y', strtotime($item->traslate_date));
			});

			return $elements;
	}

	public function list() {
		$company_id = request('model.company_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		$warehouse_movement_id = request('model.warehouse_movement_id');

		$saleWarehouseMovement = WarehouseMovement::select('id',
																											'referral_serie_number',
																											'referral_voucher_number',
																											'movement_type_id',
																											'company_id',
																											'account_id')
																							->where('id', $warehouse_movement_id)
																							->where('company_id', $company_id)
																							->first();

		$movement_type_id = $saleWarehouseMovement->movement_type_id;

		$movementDetails = WarehouseMovementDetail::select('id',
																											'warehouse_movement_id',
																											'item_number',
																											'article_code',
																											'digit_amount',
																											'converted_amount',
																											'new_stock_return',
																											'new_stock_cesion')
																							->where('warehouse_movement_id', $warehouse_movement_id)
																							->orderBy('item_number', 'asc')
																							->get();

		$clientLiquidations = ClientLiquidations::select('client_id',
																										'article_id',
																										'quantity')
																						->where('warehouse_movement_id', $warehouse_movement_id)
																						->get();

		$movementDetails->map(function ($item, $index)  {
			$item->sale_warehouse_movement_id = $item->warehouse_movement_id;
			$item->article_id = $item->article->id;
			$item->article_code = $item->article->code;
			$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
			$item->presale_converted_amount = $item->digit_amount;
			$item->sale_converted_amount = number_format(0, 2, '.', '');
			$item->return_converted_amount = $item->new_stock_return;
			$item->balance_converted_amount = number_format($item->digit_amount - $item->return_converted_amount, 2, '.', '');
			$item->new_balance_converted_amount = number_format($item->digit_amount - $item->return_converted_amount, 2, '.', '');
			$item->cesion = $item->new_stock_cesion;
			$item->group_id = $item->article->group_id;

			unset($item->warehouse_movement_id);
			unset($item->converted_amount);
		});

		$elements = array();

		foreach ($movementDetails as $val) {
				array_push($elements, $val);
		};

		$elements = array_filter($elements, function($val) {
				return $val->group_id != 7;
		});

		$clients = array();

		foreach ($clientLiquidations as $liquidation) {
				$client = Client::select('id',
																'code',
																'business_name',
																'payment_id',
																'perception_percentage_id',
																'credit_limit',
																'document_number',
																'document_type_id')
												->where('id', $liquidation->client_id)
												->first();

			array_push($clients, $client);
		};

		$clients_parse = array_values(array_filter($clients, function ($client, $indice) use ($clients) {
			return array_search($client->id, array_column($clients, 'id')) === $indice;
		}, ARRAY_FILTER_USE_BOTH));

		if ($movement_type_id == 12) {
			$client = Client::select('id',
															'code',
															'business_name',
															'payment_id',
															'perception_percentage_id',
															'credit_limit',
															'document_number',
															'document_type_id')
											->where('id', $saleWarehouseMovement->account_id)
											->first();

			$clients_parse = array();

			array_push($clients_parse, $client);
		}

		return response()
					->json([
						'movement_details' => $elements,
						'clients' => $clients_parse,
					]);

	}

	public function getClients() {
			$company_id = request('company_id');
			$client_id = request('client_id');
			$q = request('q');

			if ( isset($client_id) ) {
					$elements = Client::select('id', 'code', 'business_name', 'payment_id', 'perception_percentage_id', 'credit_limit')
							->where('id', $client_id)
			->with(['perception_percentage' => function ($query) {
									$query->select('id', 'value');
							}])
							->first();

					$elements->text = $elements->business_name;
					unset($elements->business_name);
			} else {
					$elements = Client::select('id', 'code', 'business_name', 'document_type_id', 'payment_id', 'perception_percentage_id', 'credit_limit')
							->where('company_id', $company_id)
							->where('business_name', 'like', '%'.$q.'%') ->orWhere('id', 'like', '%'.$q.'%')
							->orderBy('business_name', 'asc')
							->with(['perception_percentage' => function ($query) {
									$query->select('id', 'value');
							}])
							->get();

					$elements->map(function($item, $index) {
							$item->text = $item->id . ' - ' .$item->business_name;
							unset($item->business_name);
							unset($item->code);

							return $item;
					});
			}

			return $elements;
	}

	public function getArticles() {
		$client_id = request('client_id');
		$warehouse_movement_id = request('warehouse_movement_id');

		$saleWarehouseMovement = WarehouseMovement::select('id', 'movement_type_id', 'company_id')
            ->where('id', $warehouse_movement_id)
            ->first();

		$movement_type_id = $saleWarehouseMovement->movement_type_id;

		$clientLiquidations = ClientLiquidations::select(
				'client_id',
				'article_id',
				'quantity'
			)
			->where('warehouse_movement_id', $warehouse_movement_id)
			->where('client_id', $client_id)
			->get();

		$articles = array();

		foreach ($clientLiquidations as $liquidation) {
			$article = Article::select(
					'id',
					'code',
					'name'
				)
				->where('id', $liquidation->article_id)
				->first();

			$article->quantity = $liquidation->quantity;

			array_push($articles, $article);
		};

		if ($movement_type_id == 12) {

			$articles = array();

			$movementsDetails = WarehouseMovementDetail::select(
				'id',
				'article_code',
				'digit_amount',
				'converted_amount',
				'new_stock_return',
				'new_stock_cesion'
			)
            ->where('warehouse_movement_id', $warehouse_movement_id)
            ->orderBy('item_number', 'asc')
            ->get();

			foreach ($movementsDetails as $movementDetail) {
				$article = Article::select(
						'id',
						'code',
						'name',
						'group_id',
						'convertion'
					)
					->where('id', $movementDetail->article_code)
					->first();
	
				$article->quantity = $movementDetail->digit_amount - $movementDetail->new_stock_return;

				if ($article->group_id != 7) {
					array_push($articles, $article);
				}

				if ($movementDetail->new_stock_cesion) {
					$articleBalon = Article::where('warehouse_type_id', 5)
						->where('convertion', $article->convertion)
						->where('name', 'like', '%BALON%')
						->first();

					$articleBalon->quantity = $movementDetail->new_stock_cesion;
					array_push($articles, $articleBalon);
				}
			}
		}

		return $articles;
	}

	public function getSaleSeries() {

		$sale_series = SaleSeries::select(
					'id',
					'num_serie',
					'correlative',
					'warehouse_document_type_id'
					)
					->get();

		$series = array();

		foreach ($sale_series as $sale_serie) {
			$obj = new stdClass();
			$obj->id = $sale_serie->id;
			$obj->num_serie = $sale_serie->num_serie;
			$obj->correlative = $sale_serie->correlative + 1;
			$obj->warehouse_document_type_id = $sale_serie->warehouse_document_type_id;
			array_push($series, $obj);
		}

		return $series;
	}

	public function getArticlePrice() {
			$article_id = request('article_id');
			$client_id = request('client_id');
			$warehouse_movement_id = request('warehouse_movement_id');
			$warehouse_movement = WarehouseMovement::find($warehouse_movement_id, ['id', 'traslate_date']);
			$today = Carbon::now()->startOfDay();
			$current_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));

			$element = PriceList::select('id', 'article_id', 'price_igv')
					->where('client_id', $client_id)
					->where('warehouse_type_id', 5)
					->where('article_id', $article_id)
					->where('initial_effective_date', '<=', $current_date)
					->where('final_effective_date', '>=', $current_date)
					->where('state', 1)
					->with(['article' => function ($query) {
							$query->select('id', 'igv', 'perception');
					}])
					->first();

			return $element;
	}

	public function getBankAccounts() {
			$company_id = request('company_id');
			$currency_id = request('currency_id');

			$elements = BankAccount::select('id', 'company_id', 'bank_id', 'bank_account_type_id', 'currency_id', 'account_number')
					->where('company_id', $company_id)
					->where('currency_id', $currency_id)
					->orderBy('bank_id', 'asc')
					->get();

			$elements->map(function ($item, $index) {
					$item->name = $item->bank->name . ' ' . $item->bank_account_type->name . ' - ' . $item->account_number;

					unset($item->bank);
					unset($item->bank_account_type);
			});

			return $elements;
	}

	public function verifyDocumentType() {
		$company_id = request('model.company_id');
		$warehouse_movement_id = request('model.warehouse_movement_id');
		$warehouse_document_type_id = request('warehouse_document_type_id');
		$referral_serie_number = request('referral_serie_number');
		$referral_voucher_number = request('referral_voucher_number');

		$warehouse_document_type = WarehouseDocumentType::find($warehouse_document_type_id, ['id', 'voucher_type_id', 'previous_date_flag', 'same_voucher_number_flag']);

		$voucher_type = VoucherType::find($warehouse_document_type->voucher_type_id, ['id', 'serie_type']);
		if ( $voucher_type ) {
			if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
				$serie_number = $voucher_type->serie_type . sprintf('%02d', $referral_serie_number);
			} else {
				$serie_number = $voucher_type->serie_type . sprintf('%03d', $referral_serie_number);
			}
		}
		$warehouse_movement = WarehouseMovement::find($warehouse_movement_id, ['id', 'traslate_date']);
		$warehouse_movement_traslate_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));

		if ( $warehouse_document_type->previous_date_flag ) {
			$voucher = Voucher::where('company_id', $company_id)
				->where('voucher_type_id', $voucher_type->id)
				->where('serie_number', $serie_number)
				->orderBy('voucher_number', 'desc')
				->select('id', 'issue_date')
				->first();

			if ( $voucher ) {
				if ( $voucher->issue_date <= $warehouse_movement_traslate_date ) {
					return response()->json([
						'verify' => true
					]);
				} else {
					return response()->json([
						'verify' => false,
						'msg' => 'Ya existe comprobante con fecha posterior al despacho de este parte. Debe cambiar la Serie de Referencia.',
					]);
				}
			}
		}

		if ( $warehouse_document_type->same_voucher_number_flag ) {
			$voucher = Voucher::where('company_id', $company_id)
				->where('voucher_type_id', $voucher_type->id)
				->where('serie_number', $serie_number)
				->where('voucher_number', $referral_voucher_number)
				->first();

			if ( $voucher ) {
				return response()->json([
					'verify' => false,
					'msg' => 'Este comprobante ya fue registrado anteriormente.',
				]);
			} else {
				return response()->json([
					'verify' => true
				]);
			}
		}
	}

	public function store() {
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
																							->where('user_id', $user_id)
																							->first();

		// Este valor debe ser dependiendo del almacen que tenga asignado el usuario
		$warehouse_type_id = $warehouse_type_user->warehouse_type_id;

		$model = request('model');
		$sales = request('sales');

		$warehouse_movement = WarehouseMovement::find($model['warehouse_movement_id'], ['id', 'referral_guide_series', 'referral_guide_number', 'scop_number', 'license_plate', 'state', 'traslate_date']);
		$rate = Rate::where('description', 'IGV')
			->where('state', 1)
			->select('id', 'value')
			->first();

		$igv_percentage = ( $rate->value / 100 ) + 1;

		foreach ($sales as $sale) {
			$total_sale_amount = $sale['total'];
			$first_sale = null;
			$warehouse_document_type_id = $sale['warehouse_document_type_id'];
			$client = Client::find($sale['client_id'], ['id', 'code', 'business_name','bol_name','bol_number', 'link_client_id', 'payment_id', 'credit_limit_days','credit_limit', 'credit_balance','route_id', 'perception_percentage_id','document_type_id']);
			$rates = Rate::find($client->perception_percentage_id,['id','value']);
			$client_address = ClientAddress::where('client_id', $client->id)
																		->where('address_type_id', 1)
																		->select('id', 'address')
																		->first();

			$client->credit_limit_days = $client->credit_limit_days ? $client->credit_limit_days : 0;
			$sale_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));
			$expiry_date = $sale_date;

			if ( $client->payment_id == 2 ) {
				$expiry_date = CarbonImmutable::createFromFormat('Y-m-d', $sale_date)->addDays($client->credit_limit_days);
			}

			if ($warehouse_document_type_id == 7) {
				foreach ($sale['details'] as $detail) {
					$quantity = $detail['quantity'];

					$rest = $quantity % 2;

					$unit_price = $detail['total_perception'] / $quantity;

					echo 'quantity: ' . $quantity;
					echo 'rest: ' . $rest;
					echo 'unit_price: ' . $unit_price;
				}
				// foreach ($sale['details'] as $detail) {
				// 	$quantity = $detail['quantity'];
				// 	$quantity_div = floor($quantity / 2);
				// 	$rest = $quantity % 2;

				// 	// $total = $sale['total'];

				// 	$precio= round($detail['price_igv'], 4);
					
				// 	$total_base = ($rest*$precio);
				// 	$rest_total = $total_base/1.18;
				// 	$total_div= ($precio*2);
				// 	$rest_div=($total_div/1.18);

				// 	// $sale_value = $total_div;
				// 	// $rest_sale_value = $sale_value % $quantity;
				// 	// $sale_value_div = $total_div/1.18;
				// };

				for ($i=1; $i <= ($quantity / 2); $i++) { 
					$sale_model = new Sale();
					$sale_model->company_id = $model['company_id'];
					$sale_model->sale_date = $sale_date;
					$sale_model->expiry_date = $expiry_date;
					$sale_model->warehouse_movement_id = $warehouse_movement->id;
					$sale_model->client_id = $client->id;
					$sale_model->client_code = $client->code;
					$sale_model->route_id = $client->route_id;
					$sale_model->payment_id =  $sale['payment_id'];
					// $sale_model->payment_id =  $client->payment_id;
					$sale_model->currency_id = $sale['currency_id'];
					$sale_model->guide_series = $warehouse_movement->referral_guide_series;
					$sale_model->guide_number = $warehouse_movement->referral_guide_number;
					$sale_model->warehouse_document_type_id = $sale['warehouse_document_type_id'];
					$sale_model->cede = $warehouse_type_id;
					$sale_model->if_bol= 1;

					if ( $sale['warehouse_document_type_id'] >= 4 && $sale['warehouse_document_type_id'] <= 9 ) {
						$voucher_type_id = 2;

						$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type']);
						$serie_number = $voucher_type->serie_type . sprintf('%03d', $sale['referral_serie_number']);
						$last_voucher_number = Voucher::where('company_id', $model['company_id'])
																					->where('voucher_type_id', $voucher_type->id)
																					->where('serie_number', $serie_number)
																					->max('voucher_number');

						$voucher = new Voucher();
						$voucher->company_id = $model['company_id'];
						$voucher->client_id = $client->id;
						$voucher->original_client_id = $client->id;
						$voucher->client_name = $client->bol_name;
						$voucher->client_address = 'S/N';
						$voucher->voucher_type_id = $voucher_type->id;
						$voucher->serie_number = $serie_number;
						$voucher->voucher_number = ++$last_voucher_number;
						$voucher->referral_guide_series = ( $sale['referral_guide_series'] ? $sale['referral_guide_series'] : $warehouse_movement->referral_guide_series );
						$voucher->referral_guide_number = ( $sale['referral_guide_number'] ? $sale['referral_guide_number'] : $warehouse_movement->referral_guide_number );
						$voucher->issue_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));
						$voucher->issue_hour = date('H:i:s', strtotime($warehouse_movement->traslate_date));
						$voucher->expiry_date = $expiry_date;
						$voucher->currency_id = $sale['currency_id'];
						$voucher->payment_id = $sale['payment_id'];
						// $voucher->payment_id = $client->payment_id;
						$voucher->total = $unit_price * 2;
						$voucher->igv_perception = $sale['perception'];
						$voucher->total_perception = $unit_price * 2;
						$voucher->igv_percentage = $rate->value;
						// $voucher->igv_perception_percentage = $sale['perception_percentage'] / 100;
						$voucher->igv_perception_percentage = $rate->value / 100;
						$voucher->ose = 0;
						$voucher->user = Auth::user()->user;
						$voucher->save();

						$taxed_operation = 0;
						$igv = 0;
						foreach ($sale['details'] as $detail) {
							$article = Article::find($detail['article_id'], ['id','name', 'sale_unit_id']);

							$voucher_detail = new VoucherDetail();
							$voucher_detail->voucher_id = $voucher->id;
							$voucher_detail->unit_id = $article->sale_unit_id;
							$voucher_detail->name = $article->name;
							$voucher_detail->quantity = 2;
							$voucher_detail->original_price = round($detail['price_igv'], 4);
							$voucher_detail->unit_price = round($detail['price_igv'] / $igv_percentage, 4);
							$voucher_detail->sale_value = round($detail['price_igv'], 4);
							$voucher_detail->exonerated_value = 0;
							$voucher_detail->inaccurate_value = 0;
							$voucher_detail->igv = round($unit_price * 2, 4) - round($unit_price * 2, 4);
							$voucher_detail->total = round($unit_price * 2, 4);
							$voucher_detail->user = Auth::user()->user;
							$voucher_detail->article_id = $article->id;
							$voucher_detail->save();

							if ( $detail['igv'] == 1 ) {
								$taxed_operation += round($unit_price * 2, 4);
								$igv += round($unit_price * 2, 4) - round($unit_price * 2, 4);
							} else {
								$taxed_operation += round($unit_price * 2, 4);
							}
						}

						$voucher->taxed_operation = round($taxed_operation, 4);
						$voucher->unaffected_operation = 0;
						$voucher->exonerated_operation = 0;
						$voucher->igv = round($igv, 4);
						$voucher->save();

						$sale_model->referral_serie_number = $sale['referral_serie_number'];
						$sale_model->referral_voucher_number = $voucher->voucher_number;
					}

					$sale_model->scop_number = $warehouse_movement->scop_number;
					$sale_model->license_plate = $warehouse_movement->license_plate;

					$sale_value = 0;
					$igv = 0;
					foreach ($sale['details'] as $detail) {
						if ( $detail['igv'] == 1 ) {
							$sale_value += round($unit_price * 2, 4);
							$igv += round($unit_price * 2, 4) - round($unit_price * 2, 4);
						} else {
							$sale_value += round($unit_price * 2, 4);
						}
					}

					$total = $unit_price * 2;
					$total_perception = $unit_price * 2;

					$balance = 0;
					$pre_balance = 0;
					$paid = $total_perception;

					if ( $client->payment_id == 2 ) {
						$balance = $total_perception;
						$pre_balance = $total_perception;
						$paid = 0;
					}

					$sale_model->sale_value = $sale_value;
					$sale_model->exonerated_value = 0;
					$sale_model->inaccurate_value = 0;
					$sale_model->igv = $igv;
					$sale_model->total = $total;
					$sale_model->total_perception = $total_perception;
					$sale_model->balance = $balance;
					$sale_model->pre_balance = $pre_balance;
					$sale_model->paid = $paid;
					$sale_model->created_at_user = Auth::user()->user;
					$sale_model->updated_at_user = Auth::user()->user;
					$sale_model->pend = 0;
					$sale_model->save();

					if ($i == 1) {
						$first_sale = $sale_model;
					};

					$client->credit_balance += $total_perception;
					$client->save();

					foreach ($sale['details'] as $index => $detail) {
						$article = Article::find($detail['article_id'], ['convertion']);

						$sale_detail = new SaleDetail();
						$sale_detail->sale_id = $sale_model->id;
						$sale_detail->item_number = ++$index;
						$sale_detail->article_id = $detail['article_id'];
						$sale_detail->quantity = 2;
						$sale_detail->price_igv = round($detail['price_igv'], 4);
						$sale_detail->sale_value = round($unit_price * 2, 4);
						$sale_detail->inaccurate_value = 0;
						$sale_detail->exonerated_value = 0;
						$sale_detail->igv = round($unit_price * 2, 4) - round($unit_price * 2, 4);
						$sale_detail->total = round($unit_price * 2, 4);
						$sale_detail->total_perception = round($detail['total_perception'], 4);
						$sale_detail->igv_percentage = $rate->value;
						// $sale_detail->igv_perception_percentage = $sale['perception_percentage'];
						$sale_detail->igv_perception_percentage = $rate->value;
						$sale_detail->referential_convertion = $article->convertion;
						$sale_detail->kg = $detail['quantity'] * $sale_detail['referential_convertion'];
						$sale_detail->created_at_user = Auth::user()->user;
						$sale_detail->updated_at_user = Auth::user()->user;
						$sale_detail->save();
					}
	
					SaleSeries::where('id', $sale['sale_serie_id'])
										->update(
											['correlative' => $sale['referral_serie_number']]
										);
				}

				if ($rest) {
					$sale_model = new Sale();
					$sale_model->company_id = $model['company_id'];
					$sale_model->sale_date = $sale_date;
					$sale_model->expiry_date = $expiry_date;
					$sale_model->warehouse_movement_id = $warehouse_movement->id;
					$sale_model->client_id = $client->id;
					$sale_model->client_code = $client->code;
					$sale_model->route_id = $client->route_id;
					$sale_model->payment_id =  $sale['payment_id'];
					// $sale_model->payment_id =  $client->payment_id;
					$sale_model->currency_id = $sale['currency_id'];
					$sale_model->guide_series = $warehouse_movement->referral_guide_series;
					$sale_model->guide_number = $warehouse_movement->referral_guide_number;
					$sale_model->warehouse_document_type_id = $sale['warehouse_document_type_id'];
					$sale_model->cede = $warehouse_type_id;
					$sale_model->if_bol =1;

					$voucher_type_id = 2;

					$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type']);
					$serie_number = $voucher_type->serie_type . sprintf('%03d', $sale['referral_serie_number']);
					$last_voucher_number = Voucher::where('company_id', $model['company_id'])
																				->where('voucher_type_id', $voucher_type->id)
																				->where('serie_number', $serie_number)
																				->max('voucher_number');

					$voucher = new Voucher();
					$voucher->company_id = $model['company_id'];
					$voucher->client_id = $client->id;
					$voucher->original_client_id = $client->id;
					$voucher->client_name = $client->bol_name;
					$voucher->client_address = 'S/N';
					$voucher->voucher_type_id = $voucher_type->id;
					$voucher->serie_number = $serie_number;
					$voucher->voucher_number = ++$last_voucher_number;
					$voucher->referral_guide_series = ( $sale['referral_guide_series'] ? $sale['referral_guide_series'] : $warehouse_movement->referral_guide_series );
					$voucher->referral_guide_number = ( $sale['referral_guide_number'] ? $sale['referral_guide_number'] : $warehouse_movement->referral_guide_number );
					$voucher->issue_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));
					$voucher->issue_hour = date('H:i:s', strtotime($warehouse_movement->traslate_date));
					$voucher->expiry_date = $expiry_date;
					$voucher->currency_id = $sale['currency_id'];
					$voucher->payment_id = $sale['payment_id'];
					// $voucher->payment_id = $client->payment_id;
					$voucher->total = $unit_price;
					$voucher->igv_perception = $sale['perception'];
					$voucher->total_perception = $unit_price;
					$voucher->igv_percentage = $rate->value;
					// $voucher->igv_perception_percentage = $sale['perception_percentage'] / 100;
					$voucher->igv_perception_percentage = $rate->value / 100;
					$voucher->ose = 0;
					$voucher->user = Auth::user()->user;
					$voucher->save();

					$taxed_operation = 0;
					$igv = 0;
					foreach ($sale['details'] as $detail) {
						$article = Article::find($detail['article_id'], ['id','name', 'sale_unit_id']);

						$voucher_detail = new VoucherDetail();
						$voucher_detail->voucher_id = $voucher->id;
						$voucher_detail->unit_id = $article->sale_unit_id;
						$voucher_detail->name = $article->name;
						$voucher_detail->quantity = 1;
						$voucher_detail->original_price = round($detail['price_igv'], 4);
						$voucher_detail->unit_price = round($detail['price_igv'] / $igv_percentage, 4);
						$voucher_detail->sale_value = round($detail['price_igv'], 4);
						$voucher_detail->exonerated_value = 0;
						$voucher_detail->inaccurate_value = 0;
						// $voucher_detail->igv = round($unit_price, 4) - round($rest_total, 4);
						$voucher_detail->igv = round($unit_price, 4) - round($unit_price, 4);
						$voucher_detail->total = round($unit_price, 4);
						$voucher_detail->user = Auth::user()->user;
						$voucher_detail->article_id = $article->id;
						$voucher_detail->save();

						if ( $detail['igv'] == 1 ) {
							$taxed_operation += round($unit_price, 4);
							$igv += round($unit_price, 4) - round($unit_price, 4);
						} else {
							$taxed_operation += round($unit_price, 4);
						}
					}

					$voucher->taxed_operation = round($taxed_operation, 4);
					$voucher->unaffected_operation = 0;
					$voucher->exonerated_operation = 0;
					$voucher->igv = round($igv, 4);
					$voucher->save();

					$sale_model->referral_serie_number = $sale['referral_serie_number'];
					$sale_model->referral_voucher_number = $voucher->voucher_number;

					$sale_model->scop_number = $warehouse_movement->scop_number;
					$sale_model->license_plate = $warehouse_movement->license_plate;

					$sale_value = 0;
					$igv = 0;
					foreach ($sale['details'] as $detail) {
						if ( $detail['igv'] == 1 ) {
							$sale_value += round($unit_price, 4);
							$igv += round($unit_price, 4) - round($unit_price, 4);
						} else {
							$sale_value += round($unit_price, 4);
						}
					}

					$total = $unit_price;
					$total_perception = $unit_price;

					$balance = 0;
					$pre_balance = 0;
					$paid = $total_perception;

					if ( $client->payment_id == 2 ) {
						$balance = $total_perception;
						$pre_balance = $total_perception;
						$paid = 0;
					}

					$sale_model->sale_value = $sale_value;
					$sale_model->exonerated_value = 0;
					$sale_model->inaccurate_value = 0;
					$sale_model->igv = $igv;
					$sale_model->total = $total;
					$sale_model->total_perception = $total_perception;
					$sale_model->balance = $balance;
					$sale_model->pre_balance = $pre_balance;
					$sale_model->paid = $paid;
					$sale_model->created_at_user = Auth::user()->user;
					$sale_model->updated_at_user = Auth::user()->user;
					$sale_model->pend = 0;
					$sale_model->save();

					$client->credit_balance += $total_perception;
					$client->save();

					foreach ($sale['details'] as $index => $detail) {
						$article = Article::find($detail['article_id'], ['convertion']);

						$sale_detail = new SaleDetail();
						$sale_detail->sale_id = $sale_model->id;
						$sale_detail->item_number = ++$index;
						$sale_detail->article_id = $detail['article_id'];
						$sale_detail->quantity = 1;
						$sale_detail->price_igv = round($detail['price_igv'], 4);
						$sale_detail->sale_value = round($unit_price, 4);
						$sale_detail->inaccurate_value = 0;
						$sale_detail->exonerated_value = 0;
						$sale_detail->igv = round($unit_price, 4) - round($unit_price, 4);
						$sale_detail->total = round($unit_price, 4);
						$sale_detail->total_perception =  round($unit_price, 4);
						$sale_detail->igv_percentage = $rate->value;
						// $sale_detail->igv_perception_percentage = $sale['perception_percentage'];
						$sale_detail->igv_perception_percentage = $rate->value;
						$sale_detail->referential_convertion = $article->convertion;
						$sale_detail->kg = $rest * $sale_detail['referential_convertion'];
						$sale_detail->created_at_user = Auth::user()->user;
						$sale_detail->updated_at_user = Auth::user()->user;
						$sale_detail->save();
					}

					SaleSeries::where('id', $sale['sale_serie_id'])
										->update(
											['correlative' => $sale['referral_serie_number']]
										);
				};
			};
     //termina el boleteo

			$sale_model = new Sale();
			$sale_model->company_id = $model['company_id'];
			$sale_model->sale_date = $sale_date;
			$sale_model->expiry_date = $expiry_date;
			$sale_model->warehouse_movement_id = $warehouse_movement->id;
			$sale_model->client_id = $client->id;
			$sale_model->client_code = $client->code;
			$sale_model->route_id = $client->route_id;
			$sale_model->payment_id =  $sale['payment_id'];
			// $sale_model->payment_id =  $client->payment_id;
			$sale_model->currency_id = $sale['currency_id'];
			$sale_model->guide_series = $warehouse_movement->referral_guide_series;
			$sale_model->guide_number = $warehouse_movement->referral_guide_number;
			$sale_model->warehouse_document_type_id = $sale['warehouse_document_type_id'];
			$sale_model->cede = $warehouse_type_id;
			$sale_model->if_bol = 0;

			if ( $sale['warehouse_document_type_id'] = 5 ) {
				switch ($sale['warehouse_document_type_id']) {
					
					case 5:
						$voucher_type_id = 1;
						break;
					case 6:
						$voucher_type_id = 6;
						break;
					case 7:
						$voucher_type_id = 2;
						break;
					
					case 8:
						$voucher_type_id = 3;
						break;
				}

				$scop = '';

				if (array_key_exists('scop_number', $sale)) {
					$scop = $sale['scop_number'];
				};

				$sale_model->scop_number = $scop;

				$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type']);
				$serie_number = $voucher_type->serie_type . sprintf('%03d', $sale['referral_serie_number']);
				$last_voucher_number = Voucher::where('company_id', $model['company_id'])
																			->where('voucher_type_id', $voucher_type->id)
																			->where('serie_number', $serie_number)
																			->max('voucher_number');

				$voucher = new Voucher();
				$voucher->company_id = $model['company_id'];
				$voucher->client_id = $client->id;
				$voucher->original_client_id = $client->id;
				$voucher->client_name = $client->business_name;
				$voucher->client_address = $client_address->address;
				$voucher->voucher_type_id = $voucher_type->id;
				$voucher->serie_number = $serie_number;
				$voucher->voucher_number = ++$last_voucher_number;
				$voucher->referral_guide_series = ( $sale['referral_guide_series'] ? $sale['referral_guide_series'] : $warehouse_movement->referral_guide_series );
				$voucher->referral_guide_number = ( $sale['referral_guide_number'] ? $sale['referral_guide_number'] : $warehouse_movement->referral_guide_number );
				$voucher->issue_date = date('Y-m-d', strtotime($warehouse_movement->traslate_date));
				$voucher->issue_hour = date('H:i:s', strtotime($warehouse_movement->traslate_date));
				$voucher->expiry_date = $expiry_date;
				$voucher->currency_id = $sale['currency_id'];
				$voucher->payment_id = $sale['payment_id'];
				// $voucher->payment_id = $client->payment_id;
				$voucher->total = $sale['total'];
				$voucher->igv_perception = $sale['perception'];
				$voucher->total_perception = $sale['total_perception'];
				$voucher->igv_percentage = $rate->value;
				// $voucher->igv_perception_percentage = $sale['perception_percentage'] / 100;
				$voucher->igv_perception_percentage = $rate->value / 100;
				if ( $voucher_type->id >= 1 && $voucher_type->id <= 4 ) {
					$voucher->ose = 0;
				} else {
					$voucher->ose = 1;
				}
				$voucher->user = Auth::user()->user;
				$voucher->save();

				$taxed_operation = 0;
				$igv = 0;
				foreach ($sale['details'] as $detail) {
					$article = Article::find($detail['article_id'], ['id','name', 'sale_unit_id']);

					$voucher_detail = new VoucherDetail();
					$voucher_detail->voucher_id = $voucher->id;
					$voucher_detail->unit_id = $article->sale_unit_id;
					$voucher_detail->name = $article->name;
					$voucher_detail->quantity = $detail['quantity'];
					$voucher_detail->original_price = round($detail['price_igv'], 4);
					$voucher_detail->unit_price = round($detail['price_igv'] / $igv_percentage, 4);
					$voucher_detail->sale_value = round($detail['price_igv'], 4);
					$voucher_detail->exonerated_value = 0;
					$voucher_detail->inaccurate_value = 0;
					$voucher_detail->igv = round($detail['sale_value'], 4) - round($detail['sale_value'] / $igv_percentage, 4);
					$voucher_detail->total = round($detail['sale_value'], 4);
					$voucher_detail->user = Auth::user()->user;
					$voucher_detail->article_id = $article->id;
					$voucher_detail->save();

					if ( $detail['igv'] == 1 ) {
						$taxed_operation += round($detail['sale_value'] / $igv_percentage, 4);
						$igv += round($detail['sale_value'], 4) - round($detail['sale_value'] / $igv_percentage, 4);
					} else {
						$taxed_operation += round($detail['sale_value'], 4);
					}
				}

				$voucher->taxed_operation = round($taxed_operation, 4);
				$voucher->unaffected_operation = 0;
				$voucher->exonerated_operation = 0;
				$voucher->igv = round($igv, 4);
				$voucher->save();

				$sale_model->referral_serie_number = $sale['referral_serie_number'];
				$sale_model->referral_voucher_number = $voucher->voucher_number;
			} else {
				$referral_serie_number = CarbonImmutable::now()->format('Ym');
				$last_voucher_number = Sale::where('company_id', $model['company_id'])
																	->where('warehouse_document_type_id', $sale['warehouse_document_type_id'])
																	->where('referral_serie_number', $referral_serie_number)
																	->max('referral_voucher_number');

				if ( $sale['warehouse_document_type_id'] == 4 || $sale['warehouse_document_type_id'] == 6 || $sale['warehouse_document_type_id'] == 8 ) {
					$sale_model->referral_serie_number = $sale['referral_serie_number'];
					$sale_model->referral_voucher_number = $sale['referral_voucher_number'];
				} else {
					$int_last_voucher_number = (int)$last_voucher_number;
					$sale_model->referral_serie_number = $referral_serie_number;
					$sale_model->referral_voucher_number = $last_voucher_number != '' ? ++$int_last_voucher_number : 1 ;
				}
			}

			$sale_model->scop_number = $warehouse_movement->scop_number;
			$sale_model->license_plate = $warehouse_movement->license_plate;

			$sale_value = 0;
			$igv = 0;
			foreach ($sale['details'] as $detail) {
				if ( $detail['igv'] == 1 ) {
					$sale_value += round($detail['sale_value'] / $igv_percentage, 4);
					$igv += round($detail['sale_value'], 4) - round($detail['sale_value'] / $igv_percentage, 4);
				} else {
					$sale_value += round($detail['sale_value'], 4);
				}
			}

			$total = $sale['total'];
			$total_perception = $sale['total_perception'];

			if ( $sale['warehouse_document_type_id'] == 8 || $sale['warehouse_document_type_id'] == 9 || $sale['warehouse_document_type_id'] == 20 || $sale['warehouse_document_type_id'] == 22 ) {
				$sale_value = abs($sale_value);
				$igv = abs($igv);
				$total = abs($sale['total']);
				$total_perception = abs($sale['total_perception']);
			}

			$balance = 0;
			$pre_balance = 0;
			$paid = $total_perception;

			if ( $client->payment_id == 2 ) {
				$balance = $total_perception;
				$pre_balance = $total_perception;
				$paid = 0;
			}

			$sale_model->sale_value = $sale_value;
			$sale_model->exonerated_value = 0;
			$sale_model->inaccurate_value = 0;
			$sale_model->igv = $igv;
			$sale_model->total = $total;
			$sale_model->total_perception = $total_perception;
			$sale_model->balance = $balance;
			$sale_model->pre_balance = $pre_balance;
			$sale_model->paid = $paid;
			$sale_model->created_at_user = Auth::user()->user;
			$sale_model->updated_at_user = Auth::user()->user;
			$sale_model->pend = 0;
			$sale_model->save();

			$client->credit_balance += $total_perception;
			$client->save();

			foreach ($sale['details'] as $index => $detail) {
				$article = Article::find($detail['article_id'], ['convertion']);

				$sale_detail = new SaleDetail();
				$sale_detail->sale_id = $sale_model->id;
				$sale_detail->item_number = ++$index;
				$sale_detail->article_id = $detail['article_id'];
				$sale_detail->quantity = $detail['quantity'];
				$sale_detail->price_igv = round($detail['price_igv'], 4);
				$sale_detail->sale_value = round($detail['sale_value'], 4);
				$sale_detail->inaccurate_value = 0;
				$sale_detail->exonerated_value = 0;
				$sale_detail->igv = round($detail['sale_value'], 4) - round($detail['sale_value'] / $igv_percentage, 4);
				$sale_detail->total = round($detail['sale_value'], 4);
				$sale_detail->total_perception = round($detail['total_perception'], 4);
				$sale_detail->igv_percentage = $rate->value;
				// $sale_detail->igv_perception_percentage = $sale['perception_percentage'];
				$sale_detail->igv_perception_percentage = $rate->value;
				$sale_detail->referential_convertion = $article->convertion;
				$sale_detail->kg = $detail['quantity'] * $sale_detail['referential_convertion'];
				$sale_detail->created_at_user = Auth::user()->user;
				$sale_detail->updated_at_user = Auth::user()->user;
				$sale_detail->save();
			}

			SaleSeries::where('id', $sale['sale_serie_id'])
								->update(
									['correlative' => $sale['referral_serie_number']]
								);

			if ( array_key_exists('liquidations', $sale) ) {
				if ( count($sale['liquidations']) > 0 ) {
					foreach ($sale['liquidations'] as $liquidation) {
						$payment_method_id = $liquidation['payment_method']['id'];
						$total_sale_amount -= $liquidation['amount'];

						$liquidation_model = new Liquidation();
						if ($first_sale) {
							$liquidation_model->sale_id = $first_sale->id;
						} else {
							$liquidation_model->sale_id = $sale_model->id;
						}
						$liquidation_model->company_id = $model['company_id'];
						$liquidation_model->payment_method_id = $payment_method_id;
						$liquidation_model->currency_id = $liquidation['currency']['id'];
						$liquidation_model->exchange_rate = $liquidation['exchange_rate'];
						$liquidation_model->bank_account_id = $liquidation['bank_account']['id'];
						$liquidation_model->operation_number = $liquidation['operation_number'];
						$liquidation_model->amount = round($liquidation['amount'], 4);
						if ($liquidation['payment_date']) {
							$liquidation_model->rem_date = $liquidation['payment_date'];
						}
						if ($liquidation['payment_sede']) {
							$liquidation_model->payment_sede = $liquidation['payment_sede'];
						}
						$liquidation_model->created_at_user = Auth::user()->user;
						$liquidation_model->updated_at_user = Auth::user()->user;
						$liquidation_model->cede = $warehouse_type_id;
						$liquidation_model->save();

						if ($payment_method_id == 7) {
							$sale_model->pend = $sale_model->pend + round($liquidation['amount'], 4);
						}

						if ($payment_method_id == 7 || $payment_method_id == 5) {
							$sale_model->balance = $sale_model->balance + round($liquidation['amount'], 4);
						}

						if ( $liquidation['payment_id'] == 1 ) {
								if ($payment_method_id == 1) {
										$sale_model->payment_method_efective = 1;
										$sale_model->efective += round($liquidation['amount'], 4);
								} elseif ($payment_method_id == 2) {
										$sale_model->payment_method_deposit = 4;
										$sale_model->deposit += round($liquidation['amount'], 4);
								}

								$sale_model->save();
						}

						if ( $client->payment_id ==2 ) {
							$sale_model->balance -= $liquidation['amount'];
							$sale_model->pre_balance -= $liquidation['amount'];
							$sale_model->paid += $liquidation['amount'];
							$sale_model->payment_method_credit = 3;
							$sale_model->save();

							DB::table('credits')
								->insert([
										'sale_id' => $sale_model->id,
										'company_id' => $model['company_id'],
										'client_id' => $sale['client_id'],
										'currency_id' =>  $liquidation['currency']['id'],
										'amount' => round($sale_model['pre_balance'], 4),
										'created_at_user' => auth()->user()->name,
										'created_at' => Carbon::now(),
								]);
						}

						if ( $payment_method_id == 10 ) {
							$saldo_favor_search = Sale::find($liquidation['saldo_favor_id']);

							if ($total_sale_amount > 0) {
								$saldo_favor_search->total_perception = 0;
							} else {
								$saldo_favor_search->total_perception = $total_sale_amount * -1;
							};

							$saldo_favor_search->save();
						}

						$client->credit_balance -= $liquidation['amount'];
						$client->save();
					}
				}
			}

			if ($total_sale_amount < 0) {
				$sale_saldo_favor = new Sale();
				$sale_saldo_favor->company_id = $model['company_id'];
				$sale_saldo_favor->sale_date = $sale_date;
				$sale_saldo_favor->expiry_date = $sale_date;
				$sale_saldo_favor->client_id = $client->id;
				$sale_saldo_favor->client_code = $client->code;
				$sale_saldo_favor->payment_id =  1;
				$sale_saldo_favor->currency_id = $sale['currency_id'];
				$sale_saldo_favor->warehouse_document_type_id = 30;
				$sale_saldo_favor->referral_serie_number = $sale['referral_serie_number'];
				$sale_saldo_favor->referral_voucher_number = $sale['referral_voucher_number'];
				$sale_saldo_favor->sale_value = 0;
				$sale_saldo_favor->exonerated_value = 0;
				$sale_saldo_favor->inaccurate_value = $total_sale_amount * -1;
				$sale_saldo_favor->igv = 0;
				$sale_saldo_favor->total = $total_sale_amount * -1;
				$sale_saldo_favor->total_perception = $total_sale_amount * -1;
				$sale_saldo_favor->paid = 0;
				$sale_saldo_favor->save();

				$newSaleDetail = new SaleDetail();
				$newSaleDetail->sale_id = $sale_saldo_favor->id;
				$newSaleDetail->concept = 'Exceso cobrado';
				$newSaleDetail->price_igv = 0;
				$newSaleDetail->sale_value = 0;
				$newSaleDetail->inaccurate_value = $total_sale_amount * -1;
				$newSaleDetail->exonerated_value = 0;
				$newSaleDetail->igv = 0;
				$newSaleDetail->total = $total_sale_amount * -1;
				$newSaleDetail->total_perception = $total_sale_amount * -1;
				$newSaleDetail->igv_percentage = 0;
				$newSaleDetail->igv_perception_percentage = 0;
				$newSaleDetail->igv_percentage = 0;
				$newSaleDetail->save();
			}
		}

		$guide_state = GuidesState::select('id')
            ->where('name', 'Liquidada')
            ->first();

		$warehouse_movement->state = $guide_state->id;
		$warehouse_movement->save();

		return request()->all();
	}

	public function getOperationNumber() {
		if (request('payment_method') == '2') {
			$count = Liquidation::where('bank_account_id', request('bank_account'))
								->where('operation_number', request('operation_number'))
								->count();

			if ($count > 0) {
				return response()->json([], 422);
			}
		}

		return response()->json([], 200);
	}

	public function getSaldoFavor() {
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
}
