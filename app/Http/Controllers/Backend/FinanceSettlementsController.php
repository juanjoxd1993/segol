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
		->select('sales.id', 
		'companies.short_name as company_short_name', 
		DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") as liquidation_date'),  
		'sale_date')
		->groupBy('liquidation_date')

		->get();

		$response = [];
		$totals_sum_soles = 0;
		$totals_remesa = 0;
		$totals_efective = 0;
		$totals_deposits = 0;
		$totals_pre_balance = 0;
		$totals_payment_method_efective = 0;
		$totals_payment_method_deposit = 0;
		$totals_total_efective_cobranza = 0;
		$totals_total_deposit_cobranza = 0;
		
	

		
		foreach ($elements as $sale) {

            $warehouse_document_type_ids = [13,5,7];

			$remesa = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		                                ->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
										->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
										->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $sale['liquidation_date'])
										->whereIn('liquidations.payment_method_id', [9])
										->select('liquidations.amount')
										->sum('liquidations.amount');


									
						
			$sum_soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->whereIn('sales.warehouse_document_type_id',[13,5,7,4])			
										->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $sale['liquidation_date'])
										->select('sales.total_perception')
										->sum('sales.total_perception');
						
			$sum_deposits = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
										->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=',$sale['liquidation_date'])
										->where('clients.business_unit_id', '=', $sale['business_unit_id'])
										->where('clients.channel_id', '=', $sale['channel_id'])
										->select('sales.deposit')
										->sum('sales.deposit');
						
			$sum_efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
										->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $sale['liquidation_date'])
										->select('sales.efective')
										->sum('sales.efective');
						
			$sum_pre_balance = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
										->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $sale['liquidation_date'])
										->select('sales.pre_balance')
										->sum('sales.pre_balance');


			$cobranza_efective =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
										->leftjoin('clients', 'sales.client_id', '=', 'clients.id')				
										->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $sale['liquidation_date'])	 
										->where('liquidations.payment_method_id',[1])
										->Where('liquidations.collection',[1])
										->select('liquidations.amount')
										->sum('liquidations.amount');

			$cobranza_deposit =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
										->leftjoin('clients', 'sales.client_id', '=', 'clients.id')	
										->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=',$sale['liquidation_date'])	 
										->where('liquidations.payment_method_id',[2])
										->where('liquidations.collection',[1])
										->select('liquidations.amount')				
										->sum('liquidations.amount');


			$total_cobranza = $remesa+$sum_soles+$sum_efective;
			$total_depositos = $sum_deposits+$cobranza_deposit;




			$totals_sum_soles += $sum_soles;
			$totals_remesa += $remesa;
			$totals_efective += $sum_efective;
			$totals_deposits += $sum_deposits;
			$totals_pre_balance += $sum_pre_balance;
			$totals_payment_method_efective += $cobranza_efective;
			$totals_payment_method_deposit += $cobranza_deposit;
			$totals_total_efective_cobranza += $total_cobranza;
			$totals_total_deposit_cobranza += $total_depositos;
	

			$credit = new stdClass();
			$credit->company_short_name = $sale['company_short_name'];
			$credit->liquidation_date = $sale['liquidation_date'];
			$credit->sum_soles= $sale['sum_soles'];
			$credit->efective = $sale['efective'];
			$credit->remesa = $remesa;
			$credit->deposits = $sale['deposit'];
			$credit->pre_balance = $sale['pre_balance'];
			$credit->payment_method_efective = number_format($sale['payment_method_efective'], 2, '.', '');
			$credit->payment_method_deposit = number_format($sale['payment_method_deposit'], 2, '.', '');
			$credit->total_efective_cobranza = number_format($sale['remesa'] +$sale['efective'] + $sale['payment_method_efective'], 2, '.', '');
			$credit->total_deposit_cobranza = number_format($sale['deposit'] + $sale['payment_method_deposit'], 2, '.', '');
			
			$response[] = $credit;
		}
		$totals = new stdClass();
		$totals->liquidation_date = 'TOTAL';
		$totals->sum_soles = number_format($totals_sum_soles, 2, '.', '');
		$totals->remesa = number_format($totals_remesa, 2, '.', '');
		$totals->efective = number_format($totals_efective, 2, '.', '');
		$totals->deposits = number_format($totals_deposits, 2, '.', '');
		$totals->pre_balance = number_format($totals_pre_balance, 2, '.', '');
		$totals->payment_method_efective = number_format($totals_payment_method_efective, 2, '.', '');
		$totals->payment_method_deposit = number_format($totals_payment_method_deposit, 2, '.', '');
		$totals->total_efective_cobranza = number_format($totals_total_efective_cobranza, 2, '.', '');
		$totals->total_deposit_cobranza = number_format($totals_total_deposit_cobranza, 2, '.', '');
	
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
			$sheet->setCellValue('B3', 'Fecha de Liquidación');
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
				$sheet->setCellValue('B'.$row_number, $element->liquidation_date);
				$sheet->setCellValue('C'.$row_number, $sum_soles);
				$sheet->setCellValue('D'.$row_number, $remesa);
				$sheet->setCellValue('E'.$row_number, $sum_efective);
				$sheet->setCellValue('F'.$row_number, $sum_deposits);
				$sheet->setCellValue('G'.$row_number, $sum_pre_balance);
				$sheet->setCellValue('H'.$row_number, $cobranza_efective);
				$sheet->setCellValue('I'.$row_number, $cobranza_deposit);
				$sheet->setCellValue('J'.$row_number, $total_cobranza);
				$sheet->setCellValue('K'.$row_number, $total_depositos);
				


				$sheet->getStyle('C'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				

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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}

}
