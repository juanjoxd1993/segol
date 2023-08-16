<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class FinanceSettlementsController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
        return view('backend.finance_settlements_report')->with(compact('companies', 'current_date'));
    }

    public function getArticles() {
		$q = request('q');
		$warehouse_type_id = request('warehouse_type_id');
		
		$articles = Article::select('id', 'code', 'name', 'package_warehouse', 'warehouse_unit_id')
			->where('warehouse_type_id', $warehouse_type_id)
			->when($q, function($query, $q) {
				return $query->where('code', 'like', '%'.$q.'%')
					->orWhere('name', 'like', '%'.$q.'%');
			})
			->orderBy('code', 'asc')
			->get();

		$articles->map(function($item, $index) {
			$item->text = $item->code . ' - ' . $item->name . ' ' . $item->warehouse_unit->name . ' x ' . $item->package_warehouse;
		});

		return $articles;
	}

	public function getAccounts() {
		$company_id = request('company_id');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$q = request('q');
		
		if ( $warehouse_account_type_id == 1 ) {
			$clients = Client::select('id', 'business_name')
				->when($company_id, function($query, $company_id) {
					return $query->where('company_id', $company_id);
				})
				->where('business_name', 'like', '%'.$q.'%')
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 2 ) {			
			$clients = Provider::select('id', 'business_name')
				->where('business_name', 'like', '%'.$q.'%')
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 3 ) {
			$clients = Employee::select('id', 'first_name', 'last_name')
				->where(function ($query) use ($q) {
					$query->where('first_name', 'like', '%'.$q.'%')
						->orWhere('last_name', 'like', '%'.$q.'%');
				})
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->first_name . ' ' . $item->last_name;
				unset($item->first_name);
				unset($item->last_name);

				return $item;
			});
		}

		return $clients;
	}

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

    public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
			'final_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			'initial_date'	=> 'required',
			'final_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$export = request('export');
		error_log('Some message here.');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d H:i:s');
		// $company_id = request('model.company_id');
		// $client_id = request('model.client_id');

		DB::enableQueryLog();

		$elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
		->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
		->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
		->leftjoin('payments', 'sales.payment_id', '=', 'payments.id')
		->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
		->leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
		->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
		->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
		->leftjoin('warehouse_movements', 'sales.warehouse_movement_id', '=', 'warehouse_movements.id')
		->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
		->leftjoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
		->where('sales.created_at', '>=', $initial_date)
		->where('sales.created_at', '<=', $final_date)
		->select('sales.id', 'companies.short_name as company_short_name', DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") as liquidation_date'),  
		DB::Raw('(SELECT SUM(liquidations.amount) FROM liquidations WHERE sales.id = sales.id AND liquidations.payment_method_id = 9) as remesa'),  

		'sale_date',DB::Raw('IFNULL(sales.efective, 0) as efective'),DB::Raw('IFNULL(sales.deposit, 0) as deposit'),DB::Raw('IFNULL(sales.pre_balance, 0) as pre_balance'),DB::Raw('IFNULL(sales.payment_method_efective, 0) as payment_method_efective'),DB::Raw('IFNULL(sales.payment_method_deposit, 0) as payment_method_deposit'), 'business_units.name as business_unit_name', 'warehouse_document_types.short_name as warehouse_document_type_short_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_value', 'sales.igv', 'sales.total', DB::Raw('(sales.total_perception - sales.total) as perception'), 'sales.total_perception', 'payments.name as payment_name', 'banks.short_name as bank_short_name', 'clients.code as client_code', 'clients.business_name as client_business_name', 'document_types.name as document_type_name', 'clients.document_number as client_document_number', 'warehouse_movements.movement_number as warehouse_movement_movement_number', 'movent_types.name as movement_type_name', DB::Raw('CONCAT(sales.guide_series, "-", sales.guide_number) as guide'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND sale_details.article_id = 24) as gallons'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND sale_details.article_id = 23) as sum_1k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 55) AS sum_5k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 56) AS sum_10k'), DB::Raw('(SELECT SUM(sale_details.quantity) from sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 57) AS sum_15k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 58) AS sum_45k'), DB::Raw('(SELECT SUM(sale_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = sale_details.article_id AND sale_details.article_id <> 24)) FROM sale_details WHERE sale_details.sale_id = sales.id) AS sum_total'))
		// ->when($company_id, function($query, $company_id) {
		// 	return $query->where('sales.company_id', $company_id);
		// })
		// ->when($client_id, function($query, $client_id) {
		// 	return $query->where('sales.client_id', $client_id);
		// })
		->groupBy('sales.id')
		->orderBy('sales.company_id')
		->orderBy('liquidation_date')
		->orderBy('business_unit_name')
		->orderBy('sale_date')
		->orderBy('warehouse_document_type_short_name')
		->orderBy('sales.referral_serie_number')
		->orderBy('sales.referral_voucher_number')
		->get();

		$response = [];
		$totals_sale_value = 0;
		$totals_igv = 0;
		$totals_total = 0;
		$totals_perception = 0;
		$totals_total_perception = 0;
		$totals_remesa = 0;
		$totals_efective = 0;
		$totals_deposit = 0;
		$totals_pre_balance = 0;
		$totals_payment_method_efective = 0;
		$totals_payment_method_deposit = 0;
		$totals_total_efective_cobranza = 0;
		$totals_total_deposit_cobranza = 0;
		$totals_credit = 0;
		$totals_cash_liquidation_amount = 0;
		$totals_deposit_liquidation_amount = 0;
		$totals_gallons = 0;
		$totals_sum_1k = 0;
		$totals_sum_5k = 0;
		$totals_sum_10k = 0;
		$totals_sum_15k = 0;
		$totals_sum_45k = 0;
		$totals_sum_total = 0;

		// foreach ($elements as $sale) {
		// 	$cash_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
		// 		->where('payment_method_id', 1)
		// 		->select('amount')
		// 		->sum('amount');

		// 	$deposit_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
		// 		->where('payment_method_id', '!=', 1)
		// 		->select('amount')
		// 		->sum('amount');

		// 	$totals_sale_value += $sale['sale_value'];
		// 	$totals_igv += $sale['igv'];
		// 	$totals_total += $sale['total'];
		// 	$totals_perception += $sale['perception'];
		// 	$totals_total_perception += $sale['total_perception'];
		// 	$totals_efective += $sale['efective'];
		// 	$totals_pre_balance += $sale['pre_balance'];
		// 	$totals_cash_liquidation_amount += $cash_liquidation_amount;
		// 	$totals_deposit_liquidation_amount += $deposit_liquidation_amount;
		// 	$totals_gallons += $sale['gallons'];
		// 	$totals_sum_1k += $sale['sum_1k'];
		// 	$totals_sum_5k += $sale['sum_5k'];
		// 	$totals_sum_10k += $sale['sum_10k'];
		// 	$totals_sum_15k += $sale['sum_15k'];
		// 	$totals_sum_45k += $sale['sum_45k'];
		// 	$totals_sum_total += $sale['sum_total'];

		// 	$liquidations = Liquidation::leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
		// 		->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
		// 		->where('sale_id', $sale['id'])
		// 		->select('sale_id', 'payment_method_id', 'bank_account_id', 'operation_number', 'amount', 'banks.short_name as bank_short_name')
		// 		->get();
			
		// 	foreach ($liquidations as $liquidation) {
		// 		$liquidation->company_short_name = $sale['company_short_name'];
		// 		$liquidation->liquidation_date = $sale['liquidation_date'];
		// 		$liquidation->sale_date = $sale['sale_date'];
		// 		$liquidation->business_unit_name = $sale['business_unit_name'];
		// 		$liquidation->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
		// 		$liquidation->referral_serie_number = $sale['referral_serie_number'];
		// 		$liquidation->referral_voucher_number = $sale['referral_voucher_number'];
		// 		$liquidation->sale_value = '';
		// 		$liquidation->igv = '';
		// 		$liquidation->total = '';
		// 		$liquidation->perception = '';
		// 		$liquidation->total_perception = '';
		// 		$liquidation->payment_name = $sale['payment_name'];
		// 		$liquidation->credit = '';
		// 		$liquidation->cash_liquidation_amount = $liquidation->payment_method_id == 1 ? $liquidation->amount : '';
		// 		$liquidation->deposit_liquidation_amount = $liquidation->payment_method_id !== 1 ? $liquidation->amount : '';
		// 		$liquidation->bank_short_name = $liquidation['bank_short_name'];
		// 		$liquidation->operation_number =$liquidation['operation_number'];
		// 		$liquidation->client_code = $sale['client_code'];
		// 		$liquidation->client_business_name = $sale['client_business_name'];
		// 		$liquidation->document_type_name = $sale['document_type_name'];
		// 		$liquidation->client_document_number = $sale['client_document_number'];
		// 		$liquidation->warehouse_movement_movement_number = $sale['warehouse_movement_movement_number'];
		// 		$liquidation->movement_type_name = $sale['movement_type_name'];
		// 		$liquidation->guide = $sale['guide'];
		// 		$liquidation->gallons = '';
		// 		$liquidation->sum_1k = '';
		// 		$liquidation->sum_5k = '';
		// 		$liquidation->sum_10k = '';
		// 		$liquidation->sum_15k = '';
		// 		$liquidation->sum_45k = '';
		// 		$liquidation->sum_total = '';

		// 		if ( $liquidation == $liquidations[0] ) {
		// 			$liquidation->sale_value = $sale['sale_value'];
		// 			$liquidation->igv = $sale['igv'];
		// 			$liquidation->total = $sale['total'];
		// 			$liquidation->perception = $sale['perception'];
		// 			$liquidation->total_perception = $sale['total_perception'];
		// 			$liquidation->gallons = $sale['gallons'];
		// 			$liquidation->sum_1k = $sale['sum_1k'];
		// 			$liquidation->sum_5k = $sale['sum_5k'];
		// 			$liquidation->sum_10k = $sale['sum_10k'];
		// 			$liquidation->sum_15k = $sale['sum_15k'];
		// 			$liquidation->sum_45k = $sale['sum_45k'];
		// 			$liquidation->sum_total = $sale['sum_total'];
		// 		}

		// 		$response[] = $liquidation;
		// 	}

		// 	$totals_credit = number_format($sale['total_perception'] - $cash_liquidation_amount - $deposit_liquidation_amount, 2, '.', '');

		// 	if ( $totals_credit > 0 ) {
		// 		$credit = new stdClass();
		// 		$credit->company_short_name = $sale['company_short_name'];
		// 		$credit->liquidation_date = $sale['liquidation_date'];
		// 		$credit->sale_date = $sale['sale_date'];
		// 		$credit->business_unit_name = $sale['business_unit_name'];
		// 		$credit->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
		// 		$credit->referral_serie_number = $sale['referral_serie_number'];
		// 		$credit->referral_voucher_number = $sale['referral_voucher_number'];
		// 		$credit->sale_value = '';
		// 		$credit->igv = '';
		// 		$credit->total = '';
		// 		$credit->perception = '';
		// 		$credit->total_perception = '';
		// 		$credit->payment_name = $sale['payment_name'];
		// 		$credit->credit = $totals_credit;
		// 		$credit->cash_liquidation_amount = '';
		// 		$credit->deposit_liquidation_amount = '';
		// 		$credit->bank_short_name = '';
		// 		$credit->operation_number = '';
		// 		$credit->client_code = $sale['client_code'];
		// 		$credit->client_business_name = $sale['client_business_name'];
		// 		$credit->document_type_name = $sale['document_type_name'];
		// 		$credit->client_document_number = $sale['client_document_number'];
		// 		$credit->warehouse_movement_movement_number = $sale['warehouse_movement_movement_number'];
		// 		$credit->movement_type_name = $sale['movement_type_name'];
		// 		$credit->guide = $sale['guide'];
		// 		$credit->gallons = '';
		// 		$credit->sum_1k = '';
		// 		$credit->sum_5k = '';
		// 		$credit->sum_10k = '';
		// 		$credit->sum_15k = '';
		// 		$credit->sum_45k = '';
		// 		$credit->sum_total = '';
		// 		$credit->efective = $sale['efective'];
		// 		$credit->pre_balance = $sale['pre_balance'];

		// 		if ( $liquidations->count() == 0 ) {
		// 			$totals_credit = $sale['total_perception'];

		// 			$credit->sale_id = $sale['id'];
		// 			$credit->payment_method_id = $sale['payment_method_id'];
		// 			$credit->bank_account_id = $sale['bank_account_id'];
		// 			$credit->operation_number = $sale['operation_number'];
		// 			$credit->credit = $totals_credit;
		// 			$credit->cash_liquidation_amount = $cash_liquidation_amount;
		// 			$credit->deposit_liquidation_amount = $deposit_liquidation_amount;
		// 			$credit->sale_value = $sale['sale_value'];
		// 			$credit->igv = $sale['igv'];
		// 			$credit->total = $sale['total'];
		// 			$credit->perception = $sale['perception'];
		// 			$credit->total_perception = $sale['total_perception'];
		// 			$credit->gallons = $sale['gallons'];
		// 			$credit->sum_1k = $sale['sum_1k'];
		// 			$credit->sum_5k = $sale['sum_5k'];
		// 			$credit->sum_10k = $sale['sum_10k'];
		// 			$credit->sum_15k = $sale['sum_15k'];
		// 			$credit->sum_45k = $sale['sum_45k'];
		// 			$credit->sum_total = $sale['sum_total'];
		// 		}

		// 		$totals_credit += $totals_credit;

		// 		$response[] = $credit;
		// 	}
		// }
		
		foreach ($elements as $sale) {
			$totals_sale_value += $sale['sale_value'];
			$totals_igv += $sale['igv'];
			$totals_total += $sale['total'];
			$totals_perception += $sale['perception'];
			$totals_total_perception += $sale['total_perception'];
			$totals_remesa += $sale['remesa'];
			$totals_efective += $sale['efective'];
			$totals_deposit += $sale['deposit'];
			$totals_pre_balance += $sale['pre_balance'];
			$totals_payment_method_efective += $sale['payment_method_efective'];
			$totals_payment_method_deposit += $sale['payment_method_deposit'];
			$totals_total_efective_cobranza += ($sale['efective'] + $sale['payment_method_efective']);
			$totals_total_deposit_cobranza += ($sale['deposit'] + $sale['payment_method_deposit']);
			$totals_sum_total += $sale['sum_total'];

			$credit = new stdClass();
			$credit->company_short_name = $sale['company_short_name'];
			$credit->sale_date = $sale['sale_date'];
			$credit->perception = $sale['perception'];
			$credit->total_perception = $sale['total_perception'];
			$credit->efective = $sale['efective'];
			$credit->remesa = $sale['remesa'];
			$credit->deposit = $sale['deposit'];
			$credit->pre_balance = $sale['pre_balance'];
			$credit->payment_method_efective = number_format($sale['payment_method_efective'], 2, '.', '');
			$credit->payment_method_deposit = number_format($sale['payment_method_deposit'], 2, '.', '');
			$credit->total_efective_cobranza = number_format($sale['remesa'] +$sale['efective'] + $sale['payment_method_efective'], 2, '.', '');
			$credit->total_deposit_cobranza = number_format($sale['deposit'] + $sale['payment_method_deposit'], 2, '.', '');
			
			$response[] = $credit;
		}
		$totals = new stdClass();
		$totals->sale_date = 'TOTAL';
		// $totals->liquidation_date = '';
		// $totals->business_unit_name = '';
		// $totals->warehouse_document_type_short_name = '';
		// $totals->referral_serie_number = '';
		// $totals->referral_voucher_number = '';
		// $totals->sale_value = $totals_sale_value;
		// $totals->igv = $totals_igv;
		// $totals->total = $totals_total;
		// $totals->perception = $totals_perception;
		$totals->total_perception = number_format($totals_total_perception, 2, '.', '');
		$totals->efective = number_format($totals_efective, 2, '.', '');
		$totals->deposit = number_format($totals_deposit, 2, '.', '');
		$totals->remesa = number_format($totals_remesa, 2, '.', '');
		$totals->pre_balance = number_format($totals_pre_balance, 2, '.', '');
		$totals->payment_method_efective = number_format($totals_payment_method_efective, 2, '.', '');
		$totals->payment_method_deposit = number_format($totals_payment_method_deposit, 2, '.', '');
		$totals->total_efective_cobranza = number_format($totals_total_efective_cobranza, 2, '.', '');
		$totals->total_deposit_cobranza = number_format($totals_total_deposit_cobranza, 2, '.', '');
		// $totals->payment_name = '';
		// $totals->credit = $totals_credit;
		// $totals->cash_liquidation_amount = $totals_cash_liquidation_amount;
		// $totals->deposit_liquidation_amount = $totals_deposit_liquidation_amount;
		// $totals->bank_short_name = '';
		// $totals->operation_number = '';
		// $totals->client_code = '';
		// $totals->client_business_name = '';
		// $totals->document_type_name = '';
		// $totals->client_document_number = '';
		// $totals->warehouse_movement_movement_number = '';
		// $totals->movement_type_name = '';
		// $totals->guide = '';
		// $totals->gallons = $totals_gallons;
		// $totals->sum_1k = $totals_sum_1k;
		// $totals->sum_5k = $totals_sum_5k;
		// $totals->sum_10k = $totals_sum_10k;
		// $totals->sum_15k = $totals_sum_15k;
		// $totals->sum_45k = $totals_sum_45k;
		// $totals->sum_total = $totals_sum_total;
		$response[] = $totals;

		if ( $export ) {
			$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('d/m/Y');
			$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('d/m/Y');

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:J1');
			$sheet->setCellValue('A1', 'REPORTE DE FINANZAS Y LIQUIDACIONES DEL '.$initial_date.' AL '.$final_date);
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);
			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'Fecha');
			$sheet->setCellValue('C3', 'Total Soles');
			$sheet->setCellValue('D3', 'Remesa Forma Pago');
			$sheet->setCellValue('E3', 'Efectivo Forma Pago');
			$sheet->setCellValue('F3', 'Depósito Forma Pago');
			$sheet->setCellValue('G3', 'Venta a Credito');
			$sheet->setCellValue('H3', 'Efectivo Cobranza');
			$sheet->setCellValue('I3', 'Depósito Cobranza');
			$sheet->setCellValue('J3', 'Efectivo Total Cobranza');
			$sheet->setCellValue('K3', 'Depósito Total Cobranza');
			$sheet->getStyle('A3:K3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->sale_date);
				$sheet->setCellValue('C'.$row_number, $element->total_perception);
				$sheet->setCellValue('D'.$row_number, $element->remesa);
				$sheet->setCellValue('E'.$row_number, $element->efective);
				$sheet->setCellValue('F'.$row_number, $element->deposit);
				$sheet->setCellValue('G'.$row_number, $element->pre_balance);
				$sheet->setCellValue('H'.$row_number, $element->payment_method_efective);
				$sheet->setCellValue('I'.$row_number, $element->payment_method_deposit);
				$sheet->setCellValue('J'.$row_number, $element->total_efective_cobranza);
				$sheet->setCellValue('K'.$row_number, $element->total_deposit_cobranza);
				

				$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0');
				$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('T'.$row_number)->getNumberFormat()->setFormatCode('0');
				$sheet->getStyle('W'.$row_number)->getNumberFormat()->setFormatCode('0');
				$sheet->getStyle('X'.$row_number)->getNumberFormat()->setFormatCode('0');
				$sheet->getStyle('AA'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AB'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AC'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AD'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AE'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AF'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AG'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

				$row_number++;
			}

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
			$sheet->getColumnDimension('H')->setAutoSize(true);
			$sheet->getColumnDimension('I')->setAutoSize(true);
			$sheet->getColumnDimension('J')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}

}
