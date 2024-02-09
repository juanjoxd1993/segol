<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\BusinessUnit;
use App\Client;
use App\ClientAddress;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Rate;
use App\SaleDetail;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class VoucherMassiveGenerationController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$business_units = BusinessUnit::whereIn('id', [1, 2])->select('id', 'name')->get();
		$min_datetime = Carbon::now()->subWeek()->toAtomString();
		$today = Carbon::now()->toAtomString();
		$voucher_types = VoucherType::whereBetween('id', [1, 2])->select('id', 'name')->get();
		$articles = Article::whereBetween('subgroup_id', [54, 58])
			->where('warehouse_type_id', 5)
			->select('id', 'name', 'subgroup_id', 'convertion')
			->get();
		$user_name = Auth::user()->user;

		return view('backend.voucher_massive_generation')->with(compact('companies', 'business_units', 'min_datetime', 'today', 'voucher_types', 'articles', 'user_name'));
	}

	public function validateForm() {
		$messages = [
			'company_id.required'			=> 'Debe seleccionar una Compañía.',
			'initial_date.required'			=> 'La Fecha inicial es obligatoria.',
			'final_date.required'			=> 'La Fecha final es obligatoria.',
			'business_unit_id.required'		=> 'Debe seleccionar una Unidad de Negocio.',
		];

		$rules = [
			'company_id'		=> 'required',
			'initial_date'		=> 'required',
			'final_date'		=> 'required',
			'business_unit_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$company_id = request('model.company_id');
		$initial_date = date('Y-m-d', strtotime(request('model.initial_date')));
		$final_date = date('Y-m-d', strtotime(request('model.final_date')));
		$business_unit_id = request('model.business_unit_id');

		$elements = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
			->leftjoin('classifications', 'articles.subgroup_id', '=', 'classifications.id')
			->when($business_unit_id == 1, function ($query, $business_unit_id) {
				return $query->where('articles.subgroup_id', '>=', 55)
					->where('articles.subgroup_id', '<=', 58);
			})
			->when($business_unit_id == 2, function ($query, $business_unit_id) {
				return $query->where('articles.subgroup_id', '=', 54);
			})
			->where('sales.warehouse_document_type_id', 13)
			->where('sales.company_id', $company_id)
			->where('sales.sale_date', '>=', $initial_date)
			->where('sales.sale_date', '<=', $final_date)
			->where('sale_details.kg', '>', 0)
			->select('sales.sale_date', 'articles.subgroup_id', 'classifications.name as classification_name', DB::raw('SUM(sale_details.kg) AS sum_kg, SUM(sale_details.total) as sum_total'))
			->groupBy('sales.sale_date', 'articles.subgroup_id')
			->orderBy('sales.sale_date', 'ASC')
			->orderBy('articles.subgroup_id', 'ASC')
			->get();

		if ( $elements ) {	
			$sum_kg = $elements->sum('sum_kg');
			$sum_total = $elements->sum('sum_total');

			$total = new stdClass();
			$total->sale_date = '';
			$total->classification_name = 'Total';
			$total->sum_kg = $sum_kg;
			$total->sum_total = $sum_total;

			$elements->prepend($total);
		} else {
			$sum_kg = 0;
			$sum_total = 0;
		}

		return response()->json([
			'data'		=> $elements,
			'sum_kg'	=> $sum_kg,
			'sum_total'	=> $sum_total
		]);
	}

	public function getClients() {
		$company_id = request('company_id');
        $voucher_type_id = request('voucher_type_id');
		$q = request('q');

		$elements = Client::where('company_id', $company_id)
			->when($voucher_type_id == 1, function ($query, $voucher_type_id) {
				return $query->where('document_type_id', 1);
			})
			->when($voucher_type_id == 2, function ($query, $voucher_type_id) {
				return $query->where('document_type_id', 2);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->select('id', 'code', 'business_name')
			->orderBy('business_name', 'asc')
			->get();

        $elements->map(function ($item, $index) {
            $item->text = $item->code . ' - ' . $item->business_name;

            unset($item->code);
            unset($item->business_name);
        });

        return $elements;
	}

	public function getArticles() {
		$business_unit_id = request('business_unit_id');
		$q = request('q');

		$elements = Article::where('warehouse_type_id', 5)
			// ->when($business_unit_id == 1, function ($query, $business_unit_id) {
			// 	return $query->where('subgroup_id', '>=', 55)
			// 		->where('subgroup_id', '<=', 58);
			// })
			// ->when($business_unit_id == 2, function ($query, $business_unit_id) {
			// 	return $query->where('subgroup_id', 54);
			// })
			->whereIn('group_id', [26, 27])
			->where('subgroup_id', '>=', 54)
			->where('subgroup_id', '<=', 58)
			->where('name', 'like', '%'.$q.'%')
			->select('id', 'code', 'name', 'convertion')
			->orderBy('name', 'asc')
			->get();

        $elements->map(function ($item, $index) {
            $item->text = $item->code . ' - ' . $item->name;

            unset($item->code);
            unset($item->name);
        });

        return $elements;
	}

	public function store() {
		$company_id = request('company_id');
		$initial_date = date('Y-m-d', strtotime(request('initial_date')));
		$final_date = date('Y-m-d', strtotime(request('final_date')));
		$business_unit_id = request('business_unit_id');
		$voucher_type_id = request('voucher_type_id');
		$serie = request('serie_number');
		$client_id = request('client_id');
		$kg = request('kg') + 0;
		$price = request('price');
		$article_id = request('article_id');
		$article_convertion = request('article_convertion') + 0;
		$issue_date = date('Y-m-d', strtotime(request('issue_date')));

		$elements = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
			->when($business_unit_id == 1, function ($query, $business_unit_id) {
				return $query->where('articles.subgroup_id', '>=', 55)
					->where('articles.subgroup_id', '<=', 58);
			})
			->when($business_unit_id == 2, function ($query, $business_unit_id) {
				return $query->where('articles.subgroup_id', '=', 54);
			})
			->where('sales.warehouse_document_type_id', 13)
			->where('sales.company_id', $company_id)
			->where('sales.sale_date', '>=', $initial_date)
			->where('sales.sale_date', '<=', $final_date)
			->where('sale_details.kg', '>', 0)
			->select('sale_details.id', 'sale_details.article_id', 'sale_details.kg')
			->orderBy('sales.sale_date', 'ASC')
			->get();

		$client = Client::join('client_addresses', 'clients.id', '=', 'client_addresses.client_id')
			->where('clients.id', $client_id)
			->where('client_addresses.address_type_id', 1)
			->select('clients.id', 'business_name', 'client_addresses.address as client_address', 'perception_percentage_id')
			->first();

		$igv_percentage = Rate::where('description', 'IGV')
			->select('id', 'value')
			->first();
		
		$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type']);
		$article = Article::find($article_id, ['id', 'name', 'sale_unit_id', 'subgroup_id']);
		// Campos para cabecera
		$igv_perception_percentage = Rate::find($client->perception_percentage_id, ['id', 'value']);

		if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
			$serie_number = $voucher_type->serie_type . sprintf('%02d', $serie);
		} else {
			$serie_number = $voucher_type->serie_type . sprintf('%03d', $serie);
		}

		$last_voucher = Voucher::where('company_id', $company_id)
				->where('voucher_type_id', $voucher_type_id)
				->where('serie_number', $serie_number)
				->orderBy('voucher_number', 'DESC')
				->first();

		if ( $last_voucher ) {
			$voucher_number = $last_voucher->voucher_number + 1;
		} else {
			$voucher_number = 1;
		}

		if ( $article->subgroup_id >= 55 && $article->subgroup_id <= 58 ) {
			$maximum = 2;
			$amount_vouchers = round(( $kg / $article_convertion ) / $maximum);
			$balloons = ( $kg / $article_convertion );
			$detail_original_price = $price;
			$detail_unit_price = $price / ( $igv_percentage->value / 100 + 1 );
			$detail_sale_value = $price;
		} elseif ( $article->subgroup_id == 54 ) {
			$business_unit = BusinessUnit::find(2, ['id', 'name', 'maximum']);
			$maximum = $business_unit->maximum;
			$total_sale = $kg * $price;
			$amount_vouchers = round(( $total_sale / $maximum ));
			$detail_original_price = $price * $article_convertion;
			$detail_unit_price = ( $price * $article_convertion ) / ( $igv_percentage->value / 100 + 1 );
			$detail_sale_value = $price * $article_convertion;
		}

		// Campos para detalle
		// $detail_original_price = $price;
		// $detail_unit_price = $price / ( $igv_percentage->value / 100 + 1 );
		// $detail_sale_value = $price;

		for ($i = 1; $i <= $amount_vouchers; $i++) {
			if ( $article->subgroup_id >= 55 && $article->subgroup_id <= 58 ) {
				if ( $balloons >= $maximum ) {
					$quantity = $maximum;
					$balloons = $balloons - $maximum;
				} else {
					$quantity = $balloons;
				}
			} elseif ( $article->subgroup_id == 54 ) {
				if ( $total_sale >= $maximum ) {
					$quantity = ( $maximum / ( $price * $article_convertion ) );
					$total_sale = $total_sale - $maximum;
				} else {
					$quantity = ( $total_sale / ( $price * $article_convertion ) );
				}
			}

			// Campos para detalle
			$detail_subtotal = $detail_unit_price * $quantity;
			$detail_igv = $detail_subtotal * ( $igv_percentage->value / 100 );
			$detail_total = $detail_subtotal + $detail_igv;
			
			// Campos para cabecera
			// $last_voucher = Voucher::where('voucher_type_id', $voucher_type_id)
			// 	->where('serie_number', $serie_number)
			// 	->orderBy('voucher_number', 'DESC')
			// 	->first();
			
			// if ( $last_voucher ) {
			// 	$voucher_number = $last_voucher->voucher_number + 1;
			// } else {
			// 	$voucher_number = 1;
			// }

			$taxed_operation = $detail_subtotal;
			$igv = $detail_subtotal * ( $igv_percentage->value / 100 );
			$total = $taxed_operation + $igv;
			$igv_perception = $total * ( $igv_perception_percentage->value > 0 ? $igv_perception_percentage->value / 100 : 0 );
			$total_perception = $total + $igv_perception;

			$voucher = new Voucher();
			$voucher->company_id = $company_id;
			$voucher->client_id = $client_id;
			$voucher->original_client_id = $client_id;
			$voucher->client_name = $client->business_name;
			$voucher->client_address = $client->client_address;
			$voucher->voucher_type_id = $voucher_type_id;
			$voucher->serie_number = $serie_number;
			$voucher->voucher_number = $voucher_number;
			$voucher->issue_date = $issue_date;
			$voucher->issue_hour = date('H:i:s');
			$voucher->currency_id = 1;
			$voucher->payment_id = 1;
			$voucher->taxed_operation = $taxed_operation;
			$voucher->unaffected_operation = 0;
			$voucher->exonerated_operation = 0;
			$voucher->igv = $igv;
			$voucher->total = $total;
			$voucher->igv_perception = $igv_perception;
			$voucher->total_perception = $total_perception;
			$voucher->igv_percentage = $igv_percentage->value;
			$voucher->igv_perception_percentage = $igv_perception_percentage->value > 0 ? $igv_perception_percentage->value / 100 : $igv_perception_percentage->value;
			$voucher->ose = 0;
			$voucher->user = Auth::user()->user;
			$voucher->save();

			$voucher_detail = new VoucherDetail();
			$voucher_detail->voucher_id = $voucher->id;
			$voucher_detail->unit_id = $article->sale_unit_id;
			$voucher_detail->name = $article->name;
			$voucher_detail->quantity = $quantity;
			$voucher_detail->original_price = $detail_original_price; // 2  precio * conversion
			$voucher_detail->unit_price = $detail_unit_price; // 3.4186
			$voucher_detail->sale_value = $detail_sale_value; // 2   precio * conversion
			$voucher_detail->exonerated_value = 0;
			$voucher_detail->inaccurate_value = 0;
			$voucher_detail->igv = $detail_igv;
			$voucher_detail->total = $detail_total;
			$voucher_detail->user = Auth::user()->user;
			$voucher_detail->save();

			$voucher_number++;
		}

		foreach ($elements as $element) {
			if ( $element->kg <= $kg ) {
				$kg = $kg - $element->kg;
				$element->update(['kg' => 0]);
				// $response[] = $element;
			} else {
				$element->update(['kg' => $element->kg - $kg]);
				// $response[] = $element;
				// return $response;
			}
		}

		return response()->json([
			'type'		=> 1,
			'title'		=> '¡Ok!',
			'msg'		=> 'Comprobantes generados exitosamente.'
		]);
	}
}