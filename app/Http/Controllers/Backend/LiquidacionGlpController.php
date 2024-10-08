<?php

namespace App\Http\Controllers\Backend;

use App\GlpSeries;

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
use App\WarehouseType;
use App\Employee;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use DB;

use stdClass;

class LiquidacionGlpController extends Controller
{
	public function index() {
		$companies = Company::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')
			->where('name', 'Factura Electrónica')
			->orWhere('name', 'Boleta de Venta Electrónica')
			->orWhere('name', 'Nota Interna')
			->get();
		$warehouse_types = WarehouseType::select('id', 'name')
										->whereIn('type', [3, 4])
										->get();
		$payment_methods = PaymentMethod::select('id', 'name', 'payment_id')->get();
		$currencies = Currency::select('id', 'name')->get();
		$payments = Payment::all();
		$payment_cash = Payment::CASH;
		$payment_credit = Payment::CREDIT;

		return view('backend.liquidations_glp')->with(
				compact(
					'companies',
					'warehouse_document_types',
					'payment_methods',
					'currencies',
					'payments',
					'payment_cash',
					'payment_credit',
					'warehouse_types'
				)
		);
	}

	public function validateForm() {
		$messages = [
			'company_id.required'               => 'Debe seleccionar una Compañía.',
			// 'warehouse_movement_id.required'    => 'El Nº de Parte es obligatorio.',
		];

		$rules = [
			'company_id'            => 'required',
			// 'warehouse_movement_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getWarehouseMovements(Request $request) {
			$company_id = request('company_id');
			$warehouse_type_id = request('warehouse_type_id');

			$elements = WarehouseMovement::select(
				'id',
				'movement_number',
				'referral_guide_series',
				'referral_guide_number',
				'license_plate',
				'license_plate_2',
				'created_at',
				'scop_number'
			)
					->where('company_id', $company_id)
					->where('warehouse_type_id', $warehouse_type_id)
					->where('movement_type_id', 11)
					->where('stock_pend', '>', 0)
					->where('state', 0)
					->orderBy('movement_number', 'asc')
					->get();

			$elements->map(function ($item, $index) {
					$item->creation_date = date('d-m-Y', strtotime($item->created_at));
			});

			return $elements;
	}

	public function list() {
		$company_id = request('model.company_id');
		$warehouse_type_id = request('model.warehouse_type_id');
		// $warehouse_movement_id = request('model.warehouse_movement_id');

		// $saleWarehouseMovement = WarehouseMovement::select('id', 'referral_serie_number', 'referral_voucher_number','stock_pend')
		// 										->where('id', $warehouse_movement_id)
		// 										->where('company_id', $company_id)
		// 										->first();

		// $movementDetails = WarehouseMovementDetail::select('id',
		// 										'warehouse_movement_id',
		// 										'item_number',
		// 										'article_code',
		// 										'converted_amount',
		// 										'new_stock_return',
		// 										'article_num',
		// 										'sale_value')
		// 										->where('warehouse_movement_id', $warehouse_movement_id)
		// 										->orderBy('item_number', 'asc')
		// 										->get();

		// $movementDetails->map(function ($item, $index)  {
		// 	$warehouse_type_id = request('model.warehouse_type_id');
		// 				$item->sale_warehouse_movement_id = $item->warehouse_movement_id;
		// 				$item->article_id = $item->article->id;
		// 				$item->article_code = $item->article->code;

		// 	$cantidad=Article::select('warehouse_type_id', 'code', 'stock_good')
		// 	->where('warehouse_type_id', $warehouse_type_id)
		// 		->where('code', 1)
		// 		->sum('stock_good');

		// 	if ($item->article_code != 3){
		// 		$item->article_stock_good = $item->article->stock_good;
		// 	}
		// 	elseif($item->article_code == 3){
		// 		if ($item->sale_value) {
		// 			$item->article_stock_good = $cantidad/$item->sale_value;
		// 		} else {
		// 			$item->article_stock_good = 0;
		// 		}
		// 	}

		// 		$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
		// 		$item->presale_converted_amount = $item->converted_amount;
		// 		$item->sale_converted_amount = number_format(0, 2, '.', '');
		// 		$item->return_converted_amount = $item->new_stock_return;
		// 		$item->balance_converted_amount = number_format($item->warehouse_movement->stock_pend - $item->return_converted_amount, 2, '.', '');
		// 		$item->new_balance_converted_amount = number_format( $item->article_stock_good - $item->sale_converted_amount, 2, '.', '');

		// 		unset($item->warehouse_movement_id);
		// 		unset($item->converted_amount);
		// });

		$stocks = Article::select('id',
						'code',
						'name',
						'stock_good')
						->where('warehouse_type_id', $warehouse_type_id)
						->get();

		$stocks->map(function ($item, $index) {
			$item->stock_good = floatval($item->stock_good);
		});

		return $stocks;
	}

	public function getClients() {
			$company_id = request('company_id');
			$client_id = request('client_id');
			$q = request('q');

			if ( isset($client_id) ) {
					$elements = Client::select('id', 'code', 'business_name', 'payment_id', 'perception_percentage_id', 'credit_limit','credit_limit_days')
							->where('id', $client_id)
							->first();

					$elements->text = $elements->business_name;
					unset($elements->business_name);
			} else {
					$elements = Client::select('id', 'code', 'business_name', 'document_type_id', 'payment_id', 'perception_percentage_id', 'credit_limit','credit_limit_days')
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

	public function getGlpSeries() {
		$warehouse_type_id = request('warehouse_type_id');

		$glp_series = GlpSeries::select('id', 'num_serie', 'correlative', 'warehouse_type_id', 'warehouse_document_type_id')
								->where('warehouse_type_id', $warehouse_type_id)
								->get();

		$series = array();

		foreach ($glp_series as $glp_serie) {
			$obj = new stdClass();
			$obj->id = $glp_serie->id;
			$obj->num_serie = $glp_serie->num_serie;
			$obj->last_correlative = 0;
			$obj->correlative = $glp_serie->correlative + 1;
			$obj->warehouse_type_id = $glp_serie->warehouse_type_id;
			$obj->warehouse_document_type_id = $glp_serie->warehouse_document_type_id;
			array_push($series, $obj);
		}

		return $series;
	}

	public function getArticlePrice() {
			$article_id = request('article_id');
			$client_id = request('client_id');
			$warehouse_movement_id = request('warehouse_movement_id');
	    //	$warehouse_movement = WarehouseMovement::find($warehouse_movement_id, ['id', 'created_at']);
			$today = Carbon::now()->startOfDay();
			$current_date = date('Y-m-d', strtotime($today));


			$article = Article::select('code')
							->where('id', $article_id)
							->first();

			$article_det = Article::select('id')
								->where('code', $article->code)
								->where('warehouse_type_id', 5)
								->first();

			$element = PriceList::select('id', 'article_id', 'price_igv')
								->where('client_id', $client_id)
								->where('warehouse_type_id', 5)
								->where('article_id', $article_det->id)
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

		$today = Carbon::now()->startOfDay();
		$warehouse_movement_traslate_date = date('Y-m-d', strtotime($today));

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

		$today = Carbon::now()->startOfDay();
		$current_date = date('Y-m-d', strtotime($today));
		$model = request('model');
		$sales = request('sales');

		$rate = Rate::where('description', 'IGV')
								->where('state', 1)
								->select('id', 'value')
								->first();

		$employe = Employee::find($model['warehouse_account_id']);

		$warehouse_type_id = $model['warehouse_type_id'];

		$igv_percentage = ( $rate->value / 100 ) + 1;

		foreach ($sales as $sale) {
			$total_sale_amount = $sale['total'];

			GlpSeries::where('id', $sale['sale_serie_id'])
							->update(
								['correlative' => $sale['referral_voucher_number']]
							);

			$client = Client::find(
				$sale['client_id'],
				[
					'id',
					'code',
					'business_name',
					'link_client_id',
					'payment_id',
					'credit_limit_days',
					'credit_limit',
					'credit_balance',
					'route_id'
				]
			);
			$client_address = ClientAddress::where('client_id', $client->id)
				->where('address_type_id', 1)
				->select('id', 'address')
				->first();

			// $client->credit_limit_days = $client->credit_limit_days ? $client->credit_limit_days : 0;
			$sale_date = date('Y-m-d', strtotime($sale['sale_date']));
			$expiry_date = $sale_date;
			if ( $sale['payment_id'] == 2 ) {
				$expiry_date = CarbonImmutable::createFromFormat('Y-m-d', $sale_date)->addDays($client->credit_limit_days);
			}

			$sale_model = new Sale();
			$sale_model->company_id = $model['company_id'];
			$sale_model->sale_date = $sale_date;
			$sale_model->expiry_date = $expiry_date;
			$sale_model->client_id = $client->id;
			$sale_model->client_code = $client->code;
			$sale_model->route_id = $client->route_id;
			$sale_model->payment_id =  $sale['payment_id'];
			$sale_model->currency_id = $sale['currency_id'];
			$sale_model->guide_series = $sale['referral_guide_series'];
			$sale_model->guide_number = $sale['referral_guide_number'];
			$sale_model->warehouse_document_type_id = $sale['warehouse_document_type_id'];
			$sale_model->warehouse_account_type_id = 3;
			$sale_model->account_id = $employe ? $employe->id : null;
			$sale_model->account_document_number = $employe ? $employe->document_number : null;
			$sale_model->account_name = $employe ? $employe->first_name . ' ' . $employe->last_name : '';
			$sale_model->credit_limit_days = $client->credit_limit_days;
			$sale_model->cede = 1;

			if ( $sale['warehouse_document_type_id'] == 4 || $sale['warehouse_document_type_id'] == 5 || $sale['warehouse_document_type_id'] == 18 ) {
				switch ($sale['warehouse_document_type_id']) {
					case 4:
						$voucher_type_id = 5;
						break;
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
						$voucher_type_id = 7;
						break;
					case 9:
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
				$voucher->issue_date = $sale_date;
				// $voucher->issue_hour = date('H:i:s', strtotime($warehouse_movement->created_at));
				$voucher->expiry_date = $expiry_date;
				$voucher->currency_id = $sale['currency_id'];
				$voucher->payment_id = $sale['payment_id'];
				$voucher->total = $sale['total'];
				$voucher->igv_perception = $sale['perception'];
				$voucher->total_perception = $sale['total_perception'];
				$voucher->igv_percentage = $rate->value;
				$voucher->igv_perception_percentage = $sale['perception_percentage'] / 100;
				$voucher->scop = $scop;

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

					//validar que se actualize el stock del article
					// Article::where('id', $article->article_id)
					// ->update([
					// 	'stock_good' => DB::raw('stock_good + ' . $article->prestamo),
					// 	'stock_repair' => DB::raw('stock_repair - ' . $article->prestamo),
					// 	'stock_minimum' => DB::raw('stock_minimum + ' . $article->cesion),
					// ]);


				    // $article = Article::find($detail['article_id'], ['id','name', 'stock_good']);

					// $article->stock_good= $article->stock_good-$detail['quantity'];
					// $article->save();

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

			if ( $sale['payment_id'] == 2 ) {
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

			// if ( $client->payment_id == 2 ) {
			$client->credit_balance += $total_perception;
			$client->save();
			// }

			foreach ($sale['details'] as $index => $detail) {
				// validar tipo de articulo por su warehouse_unit_id
				// el warehouse_type_id determina el almacen
				$article = Article::find($detail['article_id']);
				$quantity = $detail['quantity'];

				$warehouse_type_id = $article->warehouse_type_id;
				$warehouse_unit_id = $article->warehouse_unit_id;

				$article_code_1 = Article::where('warehouse_type_id', $warehouse_type_id)->where('code', 1)->first();
				$article_code_2 = Article::where('warehouse_type_id', $warehouse_type_id)->where('code', 2)->first();
				$article_code_3 = Article::where('warehouse_type_id', $warehouse_type_id)->where('code', 3)->first();

				if ($warehouse_unit_id == 2) {
					if ($article_code_2->stock_good > 0) {
						$article_code_2->stock_good -= $quantity / 2.018;
						$article_code_2->save();
					} else if ($article_code_1->stock_good > 0) {
						$article_code_1->stock_good -= $quantity / 2.018;
						$article_code_1->save();
					} else {
						$article_code_2->stock_good -= $quantity / 2.018;
						$article_code_2->save();
					};

					$article_code_3->stock_good -= $quantity;
					$article_code_3->save();
				};

				if ($warehouse_unit_id == 4) {
					if ($article_code_2->stock_good > 0) {
						$article_code_2->stock_good -= $quantity;
						echo $article_code_2->stock_good . 'code 2';
						$article_code_2->save();
					} else if ($article_code_1->stock_good > 0) {
						$article_code_1->stock_good -= $quantity;
						echo $article_code_1->stock_good . 'code 1';
						$article_code_1->save();
					} else {
						$article_code_2->stock_good -= $quantity;
						echo $article_code_2->stock_good . 'code 2';
						$article_code_2->save();
					};

					$article_code_3->stock_good -= $quantity * 2.018;
					$article_code_3->save();
				};

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
				$sale_detail->igv_perception_percentage = $sale['perception_percentage'];
				$sale_detail->referential_convertion = $article->convertion;
				$sale_detail->kg = $detail['quantity'] * $sale_detail['referential_convertion'];
				$sale_detail->created_at_user = Auth::user()->user;
				$sale_detail->updated_at_user = Auth::user()->user;
				$sale_detail->save();
			}

			if ( array_key_exists('liquidations', $sale) ) {
				if ( count($sale['liquidations']) > 0 ) {
					foreach ($sale['liquidations'] as $liquidation) {
						$total_sale_amount -= $liquidation['amount'];
						$payment_method_id = $liquidation['payment_method']['id'];

						$liquidation_model = new Liquidation();
						$liquidation_model->sale_id = $sale_model->id;
						$liquidation_model->company_id = $model['company_id'];
						$liquidation_model->payment_method_id = $liquidation['payment_method']['id'];
						$liquidation_model->currency_id = $liquidation['currency']['id'];
						$liquidation_model->exchange_rate = $liquidation['exchange_rate'];
						$liquidation_model->bank_account_id = $liquidation['bank_account'];
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
						$liquidation_model->cede = 1;
						$liquidation_model->save();

						if ($liquidation['payment_method'] == 7) {
							$sale_model->pend = $sale_model->pend + round($liquidation['amount'], 4);
						}

						if ($liquidation['payment_method'] == 7 || $liquidation['payment_method'] == 5) {
							$sale_model->balance = $sale_model->balance + round($liquidation['amount'], 4);
						}

						if ( $liquidation['payment_id'] == 1 ) {
							if ($liquidation['payment_method'] == 1) {
									$sale_model->payment_method_efective = 1;
									$sale_model->efective += round($liquidation['amount'], 4);
							} elseif ($liquidation['payment_method'] == 2) {
									$sale_model->payment_method_deposit = 4;
									$sale_model->deposit += round($liquidation['amount'], 4);
							}

							$sale_model->save();
						}

						if ( $sale['payment_id'] ==2 ) {
							$sale_model->balance -= $liquidation['amount'];
							$sale_model->pre_balance -= $liquidation['amount'];
							$sale_model->paid += $liquidation['amount'];
							$sale_model->payment_method_credit = 3;
							$sale_model->save();
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
				$sale_saldo_favor->balance = 0;
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

	public function getGuideNumber() {
		$params = request('params');
		$guide_serie = $params['serie_number'];
		$guide_number = $params['guide_number'];

		$count = Voucher::where('referral_guide_series', $guide_serie)
							->where('referral_guide_number', $guide_number)
							->count();

		if ($count > 0) {
			return response()->json([], 422);
		};

		return response()->json([], 200);
	}

	public function getScopNumber() {
		$params = request('params');
		$scop_number = $params['scop_number'];

		$count = Voucher::where('scop', $scop_number)
							->count();

		if ($count > 0) {
			return response()->json([], 422);
		};

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

	public function getAccounts()
	{
		$company_id = request('company_id');
		$q = request('q');

		$clients = Employee::select('id', 'first_name', 'last_name')
			->where(function ($query) use ($q) {
				$query->where('first_name', 'like', '%' . $q . '%')
					->orWhere('last_name', 'like', '%' . $q . '%');
			})
			->get();

		$clients->map(function ($item, $index) {
			$item->text = $item->first_name . ' ' . $item->last_name;
			unset($item->first_name);
			unset($item->last_name);

			return $item;
		});

		return $clients;
	}
}
