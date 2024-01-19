<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Exports\LiquidationReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\Sale;
use App\SaleDetail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class LiquidationReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.liquidations_report')->with(compact('companies', 'current_date'));
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

		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d H:i:s');
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');

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
			->where('sales.sale_date', '>=', $initial_date)
			->where('sales.sale_date', '<=', $final_date)
			->whereNotIn('sales.warehouse_document_type_id', [7,30])
			->select('sales.id', 'companies.short_name as company_short_name', DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") as liquidation_date'), 'sale_date', 'business_units.name as business_unit_name', 'warehouse_document_types.short_name as warehouse_document_type_short_name', 'sales.scop_number as scop_number','sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_value', 'sales.igv', 'sales.total', DB::Raw('(sales.total_perception - sales.total) as perception'), 'sales.total_perception', 'payments.name as payment_name', 'banks.short_name as bank_short_name', 'clients.code as client_code', 'clients.business_name as client_business_name', 'document_types.name as document_type_name', 'clients.document_number as client_document_number', 'warehouse_movements.movement_number as warehouse_movement_movement_number', 'movent_types.name as movement_type_name', DB::Raw('CONCAT(sales.guide_series, "-", sales.guide_number) as guide'), 
			DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.code FROM articles WHERE articles.id = sale_details.article_id) = 3) as gallons'), 
			DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.code FROM articles WHERE articles.id = sale_details.article_id) = 1) as sum_1k'), 
			DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 55) AS sum_5k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 56) AS sum_10k'), DB::Raw('(SELECT SUM(sale_details.quantity) from sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 57) AS sum_15k'), DB::Raw('(SELECT SUM(sale_details.quantity) FROM sale_details WHERE sale_details.sale_id = sales.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = sale_details.article_id) = 58) AS sum_45k'), DB::Raw('(SELECT SUM(sale_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = sale_details.article_id AND sale_details.article_id <> 24)) FROM sale_details WHERE sale_details.sale_id = sales.id) AS sum_total'))
			->when($company_id, function($query, $company_id) {
				return $query->where('sales.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('sales.client_id', $client_id);
			})
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
		$totals_credit = 0;
		$totals_cash_liquidation_amount = 0;
		$totals_remesa_liquidation_amount = 0;
		$totals_deposit_liquidation_amount = 0;
		$totals_yape_liquidation_amount = 0;
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
				->select('amount')
				->sum('amount');

			$deposit_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->whereIn('payment_method_id', [2,3] )
				->select('amount')
				->sum('amount');

			$remesa_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->where('payment_method_id', 9)
				->select('amount')
				->sum('amount');

			$yape_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->where('payment_method_id', 11)
				->select('amount')
				->sum('amount');
					



			$totals_sale_value += $sale['sale_value'];
			$totals_igv += $sale['igv'];
			$totals_total += $sale['total'];
			$totals_perception += $sale['perception'];
			$totals_total_perception += $sale['total_perception'];
			$totals_cash_liquidation_amount += $cash_liquidation_amount;
			$totals_remesa_liquidation_amount += $remesa_liquidation_amount;
			$totals_yape_liquidation_amount += $yape_liquidation_amount;
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
				->get();
			
			foreach ($liquidations as $liquidation) {
				$liquidation->company_short_name = $sale['company_short_name'];
				$liquidation->liquidation_date = $sale['liquidation_date'];
				$liquidation->sale_date = $sale['sale_date'];
				$liquidation->business_unit_name = $sale['business_unit_name'];
				$liquidation->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
				$liquidation->referral_serie_number = $sale['referral_serie_number'];
				$liquidation->referral_voucher_number = $sale['referral_voucher_number'];
				$liquidation->sale_value = '';
				$liquidation->igv = '';
				$liquidation->total = '';
				$liquidation->perception = '';
				$liquidation->total_perception = '';
				$liquidation->payment_name = 'Contado';
				$liquidation->credit = '';
				$liquidation->cash_liquidation_amount = $liquidation->payment_method_id == 1 ? $liquidation->amount : '';
				$liquidation->deposit_liquidation_amount = $liquidation->payment_method_id == 2 || $liquidation->payment_method_id == 3 ? $liquidation->amount : '';
				$liquidation->remesa_liquidation_amount = $liquidation->payment_method_id == 9 ? $liquidation->amount : '';
				$liquidation->yape_liquidation_amount = $liquidation->payment_method_id == 11 ? $liquidation->amount : '';
				$liquidation->bank_short_name = $liquidation['bank_short_name'];
				$liquidation->operation_number =$liquidation['operation_number'];
				$liquidation->client_code = $sale['client_code'];
				$liquidation->client_business_name = $sale['client_business_name'];
				$liquidation->document_type_name = $sale['document_type_name'];
				$liquidation->client_document_number = $sale['client_document_number'];
				$liquidation->warehouse_movement_movement_number = $sale['warehouse_movement_movement_number'];
				$liquidation->movement_type_name = $sale['movement_type_name'];
				$liquidation->guide = $sale['guide'];
				$liquidation->scop_number = $sale['scop_number'];
				$liquidation->gallons = '';
				$liquidation->sum_1k = '';
				$liquidation->sum_5k = '';
				$liquidation->sum_10k = '';
				$liquidation->sum_15k = '';
				$liquidation->sum_45k = '';
				$liquidation->sum_total = '';

				if ( $liquidation == $liquidations[0] ) {
					$liquidation->sale_value = $sale['sale_value'];
					$liquidation->igv = $sale['igv'];
					$liquidation->total = $sale['total'];
					$liquidation->perception = $sale['perception'];
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

			$totals_credit = number_format($sale['total_perception'] - $cash_liquidation_amount - $deposit_liquidation_amount - $remesa_liquidation_amount - $yape_liquidation_amount, 2, '.', '');

			if ( $totals_credit > 0 ) {
				$credit = new stdClass();
				$credit->company_short_name = $sale['company_short_name'];
				$credit->liquidation_date = $sale['liquidation_date'];
				$credit->sale_date = $sale['sale_date'];
				$credit->business_unit_name = $sale['business_unit_name'];
				$credit->warehouse_document_type_short_name = $sale['warehouse_document_type_short_name'];
				$credit->referral_serie_number = $sale['referral_serie_number'];
				$credit->referral_voucher_number = $sale['referral_voucher_number'];
				$credit->sale_value = '';
				$credit->igv = '';
				$credit->total = '';
				$credit->perception = '';
				$credit->total_perception = '';
				$credit->payment_name = $sale['payment_name'];
				$credit->credit = $totals_credit;
				$credit->cash_liquidation_amount = '';
				$credit->remesa_liquidation_amount = '';
				$credit->deposit_liquidation_amount = '';
				$credit->yape_liquidation_amount = '';
				$credit->bank_short_name = '';
				$credit->operation_number = '';
				$credit->client_code = $sale['client_code'];
				$credit->client_business_name = $sale['client_business_name'];
				$credit->document_type_name = $sale['document_type_name'];
				$credit->client_document_number = $sale['client_document_number'];
				$credit->warehouse_movement_movement_number = $sale['warehouse_movement_movement_number'];
				$credit->movement_type_name = $sale['movement_type_name'];
				$credit->guide = $sale['guide'];
				$credit->scop_number = $sale['scop_number'];
				$credit->gallons = '';
				$credit->sum_1k = '';
				$credit->sum_5k = '';
				$credit->sum_10k = '';
				$credit->sum_15k = '';
				$credit->sum_45k = '';
				$credit->sum_total = '';

				if ( $liquidations->count() == 0 ) {
					$totals_credit = $sale['total_perception'];

					$credit->sale_id = $sale['id'];
					$credit->payment_method_id = $sale['payment_method_id'];
					$credit->bank_account_id = $sale['bank_account_id'];
					$credit->operation_number = $sale['operation_number'];
					$credit->credit = $totals_credit;
					$credit->cash_liquidation_amount = $cash_liquidation_amount;
					$credit->remesa_liquidation_amount = $remesa_liquidation_amount;
					$credit->deposit_liquidation_amount = $deposit_liquidation_amount;
					$credit->yape_liquidation_amount = $yape_liquidation_amount;
					$credit->sale_value = $sale['sale_value'];
					$credit->igv = $sale['igv'];
					$credit->total = $sale['total'];
					$credit->perception = $sale['perception'];
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
		$totals->business_unit_name = '';
		$totals->warehouse_document_type_short_name = '';
		$totals->referral_serie_number = '';
		$totals->referral_voucher_number = '';
		$totals->sale_value = $totals_sale_value;
		$totals->igv = $totals_igv;
		$totals->total = $totals_total;
		$totals->perception = $totals_perception;
		$totals->total_perception = $totals_total_perception;
		$totals->payment_name = '';
		$totals->credit = $totals_credit;
		$totals->cash_liquidation_amount = $totals_cash_liquidation_amount;
		$totals->remesa_liquidation_amount = $totals_remesa_liquidation_amount;
		$totals->deposit_liquidation_amount = $totals_deposit_liquidation_amount;
		$totals->yape_liquidation_amount = $totals_yape_liquidation_amount;
		$totals->bank_short_name = '';
		$totals->operation_number = '';
		$totals->client_code = '';
		$totals->client_business_name = '';
		$totals->document_type_name = '';
		$totals->client_document_number = '';
		$totals->warehouse_movement_movement_number = '';
		$totals->movement_type_name = '';
		$totals->guide = '';
		$totals->scop_number = '';
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
			$sheet->mergeCells('A1:AG1');
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
			$sheet->setCellValue('E3', 'Unidad de Negocio');
			$sheet->setCellValue('F3', 'Tipo');
			$sheet->setCellValue('G3', '# Serie');
			$sheet->setCellValue('H3', '# Documento');
			$sheet->setCellValue('I3', 'SCOP');
			$sheet->setCellValue('J3', 'Valor Venta');
			$sheet->setCellValue('K3', 'IGV');
			$sheet->setCellValue('L3', 'Total');
			$sheet->setCellValue('M3', 'Total Ventas');
			$sheet->setCellValue('N3', 'Condición de Pago');
			$sheet->setCellValue('O3', 'Remesa');
			$sheet->setCellValue('P3', 'Crédito');
			$sheet->setCellValue('Q3', 'Efectivo');
			$sheet->setCellValue('R3', 'Yape');
			$sheet->setCellValue('S3', 'Depósito/Transferencia');
			$sheet->setCellValue('T3', 'Banco');
			$sheet->setCellValue('U3', 'Nº Operación');
			$sheet->setCellValue('V3', 'Código del Cliente');
			$sheet->setCellValue('W3', 'Razón Social');
			$sheet->setCellValue('X3', 'Tipo de Doc.');
			$sheet->setCellValue('Y3', '# de Doc.');
			$sheet->setCellValue('Z3', '# de Parte');
			$sheet->setCellValue('AA3', 'Tipo Movimiento');
			$sheet->setCellValue('AB3', 'Guía');
			$sheet->setCellValue('AC3', 'Galones');
			$sheet->setCellValue('AD3', '1K');
			$sheet->setCellValue('AE3', '5K');
			$sheet->setCellValue('AF3', '10K');
			$sheet->setCellValue('AG3', '15K');
			$sheet->setCellValue('AH3', '45K');
			$sheet->setCellValue('AI3', 'Total Kg.');
			$sheet->getStyle('A3:AI3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;

				$saleDateYear = null;
				$saleDateMonth = null;
			//	$saleDateDay = null;

				if ($element->sale_date) {
					$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
					$saleDateYear = $saleDateObject;
			//		$saleDateMonth = str_pad($saleDateObject->month, 2, '0', STR_PAD_LEFT);
			//		$saleDateDay = str_pad($saleDateObject->day, 2, '0', STR_PAD_LEFT);
				}

				if ($element->liquidation_date) {
					$saleDateObject = date('d/m/Y',strtotime($element->liquidation_date) );
					$saleDateMonth = $saleDateObject;
				}



				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $saleDateMonth);
				$sheet->setCellValue('D'.$row_number, $saleDateYear);
				$sheet->setCellValue('E'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('F'.$row_number, $element->warehouse_document_type_short_name);
				$sheet->setCellValue('G'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('H'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('I'.$row_number, $element->scop_number);
				$sheet->setCellValue('J'.$row_number, $element->sale_value);
				$sheet->setCellValue('K'.$row_number, $element->igv);
				$sheet->setCellValue('L'.$row_number, $element->total);
				$sheet->setCellValue('M'.$row_number, $element->total_perception);
				$sheet->setCellValue('N'.$row_number, $element->payment_name);
				$sheet->setCellValue('O'.$row_number, $element->remesa_liquidation_amount);
				$sheet->setCellValue('P'.$row_number, $element->credit);
				$sheet->setCellValue('Q'.$row_number, $element->cash_liquidation_amount);
				$sheet->setCellValue('R'.$row_number, $element->yape_liquidation_amount);		
				$sheet->setCellValue('S'.$row_number, $element->deposit_liquidation_amount);
				$sheet->setCellValue('T'.$row_number, $element->bank_short_name);
				$sheet->setCellValue('U'.$row_number, $element->operation_number);
				$sheet->setCellValue('V'.$row_number, $element->client_code);
				$sheet->setCellValue('W'.$row_number, $element->client_business_name);
				$sheet->setCellValue('X'.$row_number, $element->document_type_name);
				$sheet->setCellValue('Y'.$row_number, $element->client_document_number);
				$sheet->setCellValue('Z'.$row_number, $element->warehouse_movement_movement_number);
				$sheet->setCellValue('AA'.$row_number, $element->movement_type_name);
				$sheet->setCellValue('AB'.$row_number, $element->guide);
				$sheet->setCellValue('AC'.$row_number, $element->gallons);
				$sheet->setCellValue('AD'.$row_number, $element->sum_1k);
				$sheet->setCellValue('AE'.$row_number, $element->sum_5k);
				$sheet->setCellValue('AF'.$row_number, $element->sum_10k);
				$sheet->setCellValue('AG'.$row_number, $element->sum_15k);
				$sheet->setCellValue('AH'.$row_number, $element->sum_45k);
				$sheet->setCellValue('AI'.$row_number, $element->sum_total);

			
				$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('AD'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AE'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AF'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AG'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('AH'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

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
			$sheet->getColumnDimension('AA')->setAutoSize(true);
			$sheet->getColumnDimension('AB')->setAutoSize(true);
			$sheet->getColumnDimension('AC')->setAutoSize(true);
			$sheet->getColumnDimension('AD')->setAutoSize(true);
			$sheet->getColumnDimension('AE')->setAutoSize(true);
			$sheet->getColumnDimension('AF')->setAutoSize(true);
			$sheet->getColumnDimension('AG')->setAutoSize(true);
			$sheet->getColumnDimension('AH')->setAutoSize(true);


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
