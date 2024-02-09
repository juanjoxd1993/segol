<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\Article;
use App\Sale;
use App\Client;
use Carbon\CarbonImmutable;

class ReportsSaldosFavorController extends Controller {
  public function index() {
		$plantas = WarehouseType::select('id', 'name')
                          ->where('type', 3)
                          ->get();

		return view('backend.reports_saldos_favor')->with(compact('plantas'));
	}

	public function getSaldosFavor() {
		$warehouse_type_id = request('warehouse_type_id');
		$date_init = CarbonImmutable::createFromDate(request('date_init'))->startOfDay()->format('Y-m-d');
		$date_end = CarbonImmutable::createFromDate(request('date_end'))->endOfDay()->format('Y-m-d');

		if ($warehouse_type_id) {
			$warehouse_type_ids = WarehouseType::select('id')
																					->where('id', $warehouse_type_id)
																					->get();
		} else {
			$warehouse_type_ids = WarehouseType::select('id')
																				->where('type', 3)
																				->get();
		}

		$warehouse_types = [];

		foreach ($warehouse_type_ids as $id) {
			$warehouse_types[] = $id->id;
		}

		$saldos_favor = Sale::where('warehouse_document_type_id', 30)
												->where('total_perception', '>', 0)
												->whereIn('cede', $warehouse_type_ids)
												->where('sale_date', '>=', $date_init)
												->where('sale_date', '<=', $date_end)
												->select('id',
																'sale_date',
																'referral_serie_number',
																'referral_voucher_number',
																'currency_id',
																'total_perception',
																'client_id',
																'cede')
												->get();

		foreach ($saldos_favor as $saldo_favor) {
			$client = Client::find($saldo_favor->client_id);
			$warehouse_type = WarehouseType::find($saldo_favor->cede);

			$saldo_favor->client_name = $client->business_name;
			$saldo_favor->warehouse_name = $warehouse_type->name;
		}

		return $saldos_favor;
	}
}