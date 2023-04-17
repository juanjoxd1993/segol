<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Bank;
use App\BankAccount;
use App\Deposit;
use App\Exports\LiquidationReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\Sale;
use App\ClientRoute;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class LiquidationCreditsReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
        $client_routes = ClientRoute::select('id','short_name')->get();

		return view('backend.liquidations_credits_report')->with(compact('companies', 'current_date','client_routes'));
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

	public function list() {
		$export = request('export');

        $route_id = request('model.route_id');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d H:i:s');
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');

		DB::enableQueryLog();

		$elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
			->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
            ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			->leftjoin('payments', 'sales.payment_id', '=', 'payments.id')
			->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
			->leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
            ->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
			->leftjoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
			->where('sales.sale_date', '>=', $initial_date)
			->where('sales.sale_date', '<=', $final_date)
            ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14258])
			->select('sales.id', 'companies.short_name as company_short_name', DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") as liquidation_date'), 'sale_date', 'client_routes.short_name as client_route_id', 'warehouse_document_types.short_name as warehouse_document_type_short_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.total', 'sales.total_perception', 'payments.name as payment_name', 'banks.short_name as bank_short_name', 'clients.code as client_code', 'clients.id as client_id','clients.business_name as client_business_name', 'sales.deposit as deposit','sales.efective as efective',DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND sale_details.article_id = 24) as gallons'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND sale_details.article_id = 23) as sum_1k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 55) AS sum_5k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 56) AS sum_10k'), DB::Raw('(SELECT SUM(sale_details.quantity) from sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 57) AS sum_15k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 58) AS sum_45k'), DB::Raw('(SELECT SUM(sale_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = sale_details.article_id AND sale_details.article_id <> 24)) FROM sale_details WHERE sale_details.sale_id = sales.id) AS sum_total'),'sales.pre_balance as pre_balance')
			->when($company_id, function($query, $company_id) {
				return $query->where('sales.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('sales.client_id', $client_id);
			})
            ->when($route_id, function($query, $route_id) {
				return $query->where('sales.route_id', $route_id);
			})
			->groupBy('sales.id')
			->orderBy('sales.company_id')
			->orderBy('sale_date')
			->get();

		$response = [];
		$totals_total = 0;
		$totals_total_perception = 0;
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

		foreach ($elements as $sale) {
			$cash_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->where('payment_method_id', 1)
				->where('collection',0)
				->select('amount')
				->sum('amount');

			$deposit_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->where('payment_method_id', '!=', 1)
				->select('amount')
				->sum('amount');

			$totals_total += $sale['total'];
			$totals_total_perception += $sale['total_perception'];
			$totals_cash_liquidation_amount += $cash_liquidation_amount;
			$totals_deposit_liquidation_amount += $deposit_liquidation_amount;
			$totals_gallons += $sale['gallons'];
			$totals_sum_1k += $sale['sum_1k'];
			$totals_sum_5k += $sale['sum_5k'];
			$totals_sum_10k += $sale['sum_10k'];
			$totals_sum_15k += $sale['sum_15k'];
			$totals_sum_45k += $sale['sum_45k'];
			$totals_sum_total += $sale['sum_total'];

			$liquidations = Liquidation::leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
				->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
				->where('sale_id', $sale['id'])
				->select('sale_id', 'payment_method_id', 'bank_account_id', 'operation_number', 'amount', 'banks.short_name as bank_short_name')
				->where('collection',0)
				->get();
			
			foreach ($liquidations as $liquidation) {
				$liquidation->company_short_name = $sale['company_short_name'];
				$liquidation->liquidation_date = $sale['liquidation_date'];
				$liquidation->sale_date = $sale['sale_date'];
				$liquidation->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
				$liquidation->referral_serie_number = $sale['referral_serie_number'];
				$liquidation->referral_voucher_number = $sale['referral_voucher_number'];
				$liquidation->total = '';
				$liquidation->perception = '';
				$liquidation->total_perception = '';
			//	$liquidation->payment_name = $sale['payment_name'];
				$liquidation->credit = '';
				$liquidation->cash_liquidation_amount = $liquidation->payment_method_id == 1 ? $liquidation->amount : '';
				$liquidation->deposit_liquidation_amount = $liquidation->payment_method_id !== 1 ? $liquidation->amount : '';
				$liquidation->bank_short_name = $liquidation['bank_short_name'];
				$liquidation->operation_number =$liquidation['operation_number'];
                $liquidation->client_route_id = $sale['client_route_id'];
				$liquidation->client_code = $sale['client_code'];
                $liquidation->client_id = $sale['client_id'];
				$liquidation->client_business_name = $sale['client_business_name'];
				$liquidation->gallons = '';
				$liquidation->sum_1k = '';
				$liquidation->sum_5k = '';
				$liquidation->sum_10k = '';
				$liquidation->sum_15k = '';
				$liquidation->sum_45k = '';
				$liquidation->sum_total = '';

				if ( $liquidation == $liquidations[0] ) {
					$liquidation->total = $sale['total'];
					$liquidation->total_perception = $sale['total_perception'];
					$liquidation->gallons = $sale['gallons'];
					$liquidation->sum_1k = $sale['sum_1k'];
					$liquidation->sum_5k = $sale['sum_5k'];
					$liquidation->sum_10k = $sale['sum_10k'];
					$liquidation->sum_15k = $sale['sum_15k'];
					$liquidation->sum_45k = $sale['sum_45k'];
					$liquidation->sum_total = $sale['sum_total'];
				}

				$response[] = $liquidation;
			}

			$totals_credit = number_format($sale['pre_balance'] , 2, '.', '');

			if ( $totals_credit > 0 ) {
				$credit = new stdClass();
				$credit->company_short_name = $sale['company_short_name'];
				$credit->liquidation_date = $sale['liquidation_date'];
				$credit->sale_date = $sale['sale_date'];
				$credit->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
				$credit->referral_serie_number = $sale['referral_serie_number'];
				$credit->referral_voucher_number = $sale['referral_voucher_number'];
				$credit->total = '';
				$credit->total_perception = '';
			//	$credit->payment_name = $sale['payment_name'];
				$credit->credit = $totals_credit;
				$credit->cash_liquidation_amount = '';
				$credit->deposit_liquidation_amount = '';
				$credit->bank_short_name = '';
				$credit->operation_number = '';
                $credit->client_route_id = $sale['client_route_id'];
				$credit->client_code = $sale['client_code'];
                $credit->client_id = $sale['client_id'];
				$credit->client_business_name = $sale['client_business_name'];
				$credit->gallons = '';
				$credit->sum_1k = '';
				$credit->sum_5k = '';
				$credit->sum_10k = '';
				$credit->sum_15k = '';
				$credit->sum_45k = '';
				$credit->sum_total = '';

				if ( $liquidations->count() == 0 ) {
					$totals_credit = $sale['pre_balance'];

					$credit->sale_id = $sale['id'];
					$credit->payment_method_id = $sale['payment_method_id'];
					$credit->bank_account_id = $sale['bank_account_id'];
					$credit->operation_number = $sale['operation_number'];
					$credit->credit = $totals_credit;
					$credit->cash_liquidation_amount = $cash_liquidation_amount;
					$credit->deposit_liquidation_amount = $deposit_liquidation_amount;
					$credit->total = $sale['total'];
					$credit->total_perception = $sale['total_perception'];
					$credit->gallons = $sale['gallons'];
					$credit->sum_1k = $sale['sum_1k'];
					$credit->sum_5k = $sale['sum_5k'];
					$credit->sum_10k = $sale['sum_10k'];
					$credit->sum_15k = $sale['sum_15k'];
					$credit->sum_45k = $sale['sum_45k'];
					$credit->sum_total = $sale['sum_total'];
				}

				$totals_credit += $totals_credit;

				$response[] = $credit;
			}
		}

		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->liquidation_date = '';
		$totals->sale_date = '';
        $totals->client_route_id = '';
        $totals->client_id = '';
		$totals->client_code = '';
		$totals->client_business_name = '';
		$totals->warehouse_document_type_short_name = '';
		$totals->referral_serie_number = '';
		$totals->referral_voucher_number = '';
		$totals->total = $totals_total;
		$totals->total_perception = $totals_total_perception;
	//	$totals->payment_name = '';
		$totals->credit = $totals_credit;
		$totals->cash_liquidation_amount = $totals_cash_liquidation_amount;
		$totals->deposit_liquidation_amount = $totals_deposit_liquidation_amount;
		$totals->bank_short_name = '';
		$totals->operation_number = '';       
		$totals->gallons = $totals_gallons;
		$totals->sum_1k = $totals_sum_1k;
		$totals->sum_5k = $totals_sum_5k;
		$totals->sum_10k = $totals_sum_10k;
		$totals->sum_15k = $totals_sum_15k;
		$totals->sum_45k = $totals_sum_45k;
		$totals->sum_total = $totals_sum_total;
		$response[] = $totals;

		if ( $export ) {
			$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('d/m/Y');
			$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('d/m/Y');

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Z1');
			$sheet->setCellValue('A1', 'REPORTE DE LIQUIDACIONES DEL '.$initial_date.' AL '.$final_date);
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
			$sheet->setCellValue('B3', 'Compañía');
			$sheet->setCellValue('C3', 'Fecha de Liquidación');
			$sheet->setCellValue('D3', 'Fecha de Despacho');
            $sheet->setCellValue('E3', 'RUTA');
            $sheet->setCellValue('F3', 'ID');
            $sheet->setCellValue('G3', 'Código del Cliente');
			$sheet->setCellValue('H3', 'Razón Social');
			$sheet->setCellValue('I3', 'Tipo');
			$sheet->setCellValue('J3', '# Serie');
			$sheet->setCellValue('K3', '# Documento');
			$sheet->setCellValue('L3', 'Total');
			$sheet->setCellValue('M3', 'Total Percepción');
	//		$sheet->setCellValue('N3', 'Condición de Pago');
			$sheet->setCellValue('O3', 'Crédito');
			$sheet->setCellValue('P3', 'Efectivo');
			$sheet->setCellValue('Q3', 'Depósito/Transferencia');
			$sheet->setCellValue('R3', 'Banco');
			$sheet->setCellValue('S3', 'Nº Operación');			
			$sheet->setCellValue('T3', 'Galones');
			$sheet->setCellValue('U3', '1K');
			$sheet->setCellValue('V3', '5K');
			$sheet->setCellValue('W3', '10K');
			$sheet->setCellValue('X3', '15K');
			$sheet->setCellValue('Y3', '45K');
			$sheet->setCellValue('Z3', 'Total Kg.');
			$sheet->getStyle('A3:Z3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->liquidation_date);
				$sheet->setCellValue('D'.$row_number, $element->sale_date);
                $sheet->setCellValue('E'.$row_number, $element->client_route_id);
                $sheet->setCellValue('F'.$row_number, $element->client_id);
                $sheet->setCellValue('G'.$row_number, $element->client_code);
				$sheet->setCellValue('H'.$row_number, $element->client_business_name);
				$sheet->setCellValue('I'.$row_number, $element->warehouse_document_type_short_name);
				$sheet->setCellValue('J'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('K'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('L'.$row_number, $element->total);
				$sheet->setCellValue('M'.$row_number, $element->total_perception);
		//		$sheet->setCellValue('N'.$row_number, $element->payment_name);
				$sheet->setCellValue('O'.$row_number, $element->credit);
				$sheet->setCellValue('P'.$row_number, $element->cash_liquidation_amount);
				$sheet->setCellValue('Q'.$row_number, $element->deposit_liquidation_amount);
				$sheet->setCellValue('R'.$row_number, $element->bank_short_name);
				$sheet->setCellValue('S'.$row_number, $element->operation_number);
				$sheet->setCellValue('T'.$row_number, $element->gallons);
				$sheet->setCellValue('U'.$row_number, $element->sum_1k);
				$sheet->setCellValue('V'.$row_number, $element->sum_5k);
				$sheet->setCellValue('W'.$row_number, $element->sum_10k);
				$sheet->setCellValue('X'.$row_number, $element->sum_15k);
				$sheet->setCellValue('Y'.$row_number, $element->sum_45k);
				$sheet->setCellValue('Z'.$row_number, $element->sum_total);

		//		$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0');
		//		$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');
		//		$sheet->getStyle('T'.$row_number)->getNumberFormat()->setFormatCode('0');
		//		$sheet->getStyle('W'.$row_number)->getNumberFormat()->setFormatCode('0');
		//		$sheet->getStyle('X'.$row_number)->getNumberFormat()->setFormatCode('0');
		//		$sheet->getStyle('AA'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AB'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AC'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AD'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AE'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AF'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
		//		$sheet->getStyle('AG'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

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
			$sheet->getColumnDimension('K')->setAutoSize(true);
			$sheet->getColumnDimension('L')->setAutoSize(true);
			$sheet->getColumnDimension('M')->setAutoSize(true);
			$sheet->getColumnDimension('N')->setAutoSize(true);
			$sheet->getColumnDimension('O')->setAutoSize(true);
			$sheet->getColumnDimension('P')->setAutoSize(true);
			$sheet->getColumnDimension('Q')->setAutoSize(true);
			$sheet->getColumnDimension('R')->setAutoSize(true);
			$sheet->getColumnDimension('S')->setAutoSize(true);
			$sheet->getColumnDimension('T')->setAutoSize(true);
			$sheet->getColumnDimension('U')->setAutoSize(true);
			$sheet->getColumnDimension('V')->setAutoSize(true);
			$sheet->getColumnDimension('W')->setAutoSize(true);
			$sheet->getColumnDimension('X')->setAutoSize(true);
			$sheet->getColumnDimension('Y')->setAutoSize(true);
			$sheet->getColumnDimension('Z')->setAutoSize(true);
			

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}

	public function showRegisterVoucher()
	{
		$data = [
			'banks' => Bank::all(),
			'bank_accounts' => BankAccount::all(),
		];

		return view('backend.voucher_register', $data);
	}

	public function registerVoucher()
	{
		request()->validate([
			'model.bank' => ['required'],
			'model.bank_account' => ['required'],
			'model.bank_date' => ['required'],
			'model.operation_number' => ['required'],
			'model.total' => ['required'],
		], [
			'model.bank.required' => 'Debe seleccionar banco',
			'model.bank_account.required' => 'Debe seleccionar cuenta',
			'model.bank_date.required' => 'Debe seleccionar fecha',
			'model.operation_number.required' => 'Debe ingresar número de operación',
			'model.total.required' => 'Debe ingresar total',
		]);

		$deposit = new Deposit();

		$deposit->bank_id = request('model.bank');
		$deposit->bank_account_id = request('model.bank_account');
		$deposit->bank_date = request('model.bank_date');
		$deposit->operation_number = request('model.operation_number');
		$deposit->total = request('model.total');
		$deposit->total_pend = request('model.total');
		$deposit->save();

		return response()->json(null, 200);
	}
}