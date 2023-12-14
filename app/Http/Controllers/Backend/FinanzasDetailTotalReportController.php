<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use App\PaymentReport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SaleDetail;
use App\Sale;
use App\BusinessUnit;
use App\Liquidation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class FinanzasDetailTotalReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date =CarbonImmutable::now()->toAtomString();
		return view('backend.finanzas_detail_total_report')->with(compact('companies', 'current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
		];

		$rules = [
			'initial_date'	=> 'required',
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
		// $this->validateForm();
		$export = request('export');

		$warehouse_types = request('model.warehouse_types');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');

		$response=[];
		$totals_total = 0;
		$totals_sum_total = 0;

		$warehouse_document_type_ids = [13,5,31];
	//	$client_ids = [1031, 427, 13326, 13775, 14072,14258];

		$total_venta_del_dia = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
														//	->whereNotIn('sales.if_bol', [1])
															->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
															->whereIn('sales.cede', $warehouse_types)
															->where('sales.sale_date', '=',  $initial_date)
															->select('sales.total_perception')
															->sum('sales.total_perception');

		$efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		                                ->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
									//	->whereNotIn('sales.if_bol', [1])
										->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
										->whereIn('sales.cede', $warehouse_types)
										->where('sales.sale_date', '=',  $initial_date)
										->whereIn('liquidations.payment_method_id', [1])
										->where('liquidations.collection',0)
										->select('liquidations.amount')
										->sum('liquidations.amount');



			$remesa = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		                                ->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
									//	->whereNotIn('sales.if_bol', [1])
										->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
										->whereIn('sales.cede', $warehouse_types)
										->where('sales.sale_date', '=',  $initial_date)
										->whereIn('liquidations.payment_method_id', [9])									
								//	->where('liquidations.collection',0)
										->select('liquidations.amount')
										->sum('liquidations.amount');


		$deposit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
									->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
								//	->whereNotIn('sales.if_bol', [1])
									->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
									->whereIn('sales.cede', $warehouse_types)
									->where('sales.sale_date', '=',  $initial_date)
									->whereIn('liquidations.payment_method_id', [2,3])
									->whereIn('liquidations.collection',[0,1])
									->select('liquidations.amount')
									->sum('liquidations.amount');

		$credit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

								->whereIn('sales.cede', $warehouse_types)
								->where('sales.sale_date', '=',  $initial_date)
								->whereIn('sales.warehouse_document_type_id', [13,7,5])
								->select('sales.balance')
								->sum('sales.balance');

		$saldo_favor = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
								->whereIn('sales.cede', $warehouse_types)
								->where('sales.sale_date', '=',  $initial_date)
								->whereIn('sales.warehouse_document_type_id', [30])
								->select('sales.inaccurate_value')
								->sum('sales.inaccurate_value');

		$yape = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
								->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
								->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
								->whereIn('sales.cede', $warehouse_types)
								->where('sales.sale_date', '=',  $initial_date)
								->whereIn('liquidations.payment_method_id', [11])
								->whereIn('liquidations.collection',[0,1])
								->select('liquidations.amount')
								->sum('liquidations.amount');

		$total_liquidado = $remesa + $efective + $deposit + $credit+ $yape;


		$diference = number_format($total_venta_del_dia - $total_liquidado , 2, '.', '');

		$favor=number_format($saldo_favor , 2, '.', '');

		$diference_final=  number_format($diference - $favor , 2, '.', '');

		$cobranza_efective =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
																	->leftjoin('clients', 'sales.client_id', '=', 'clients.id')				
																	->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $initial_date)
																	->whereIn('liquidations.cede', $warehouse_types)
															//		->whereNotIn('sales.client_id', $client_ids)	 
																	->whereIn('liquidations.payment_method_id',[1])
																	->where('liquidations.collection',[1])
																	->select('liquidations.amount')
																	->sum('liquidations.amount');
		$cobranza_remesa =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
																	->leftjoin('clients', 'sales.client_id', '=', 'clients.id')				
																	->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $initial_date)
																	->whereIn('liquidations.cede', $warehouse_types)
															//		->whereNotIn('sales.client_id', $client_ids)	 
																	->whereIn('liquidations.payment_method_id',[9])
																	->where('liquidations.collection',[1])
																	->select('liquidations.amount')
																	->sum('liquidations.amount');

		$cobranza_deposit =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
																	->leftjoin('clients', 'sales.client_id', '=', 'clients.id')	
																	->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $initial_date)
																	->whereIn('liquidations.cede', $warehouse_types)
															//		->whereNotIn('sales.client_id', $client_ids)	 
																	->whereIn('liquidations.payment_method_id',[2,3])
																	->Where('liquidations.collection',[1])
																	->select('liquidations.amount')				
																	->sum('liquidations.amount');

		$total_cobranza = $cobranza_efective + $cobranza_deposit + $cobranza_remesa;

		$total_efective_day = $efective  + $cobranza_efective;
		$total_deposit_day = $deposit + $cobranza_deposit+ $yape;

		$total_cobranza_final=$total_efective_day+$total_deposit_day;

		$total_remesa_day = $remesa;

		

		$response = [
			[
				'company_short_name' => 'Total Venta del Día',
				'total' => $total_venta_del_dia
			],

			[
				'company_short_name' => 'Remesa',
				'total' => $remesa
			],
			[
				'company_short_name' => 'Efectivo',
				'total' => $efective
			],
			[
				'company_short_name' => 'Deposito',
				'total' => $deposit
			],
			[
				'company_short_name' => 'Credito',
				'total' => $credit
			],
			[
				'company_short_name' => 'Saldo a Favor',
				'total' => $favor
			],
			[
				'company_short_name' => 'Yape',
				'total' => $yape
			],
			
			[
				'company_short_name' => 'Total Liquidado',
				'total' => $total_liquidado
			],
			[
				'company_short_name' => 'Diferencia',
				'total' => $diference
			],
			[
				'company_short_name' => 'Diferencia Final',
				'total' => $diference_final
			],
			[
				'company_short_name' => 'Cobranza en Efectivo',
				'total' => $cobranza_efective
			],
			[
				'company_short_name' => 'Cobranza en Remesa',
				'total' => $cobranza_remesa
			],
			
			[
				'company_short_name' => 'Cobranza en Deposito',
				'total' => $cobranza_deposit
			],
			[
				'company_short_name' => 'Total Cobranza',
				'total' => $total_cobranza
			],
			
			
			[
				'company_short_name' => 'Total Efectivo del Día',
				'total' => $total_efective_day
			],
			[
				'company_short_name' => 'Total Remesa del Día',
				'total' => $total_remesa_day
			],
			[
				'company_short_name' => 'Total Deposito del Día',
				'total' => $total_deposit_day
			],
			[
				'company_short_name' => 'Total Cobranza',
				'total' => $total_cobranza_final
			],
		];

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:L1');
			$sheet->setCellValue('A1', 'REPORTE DE LIQUIDACIÓN DEL DÍA '.$initial_date.' DESCARGADO EL  '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

			
			$sheet->setCellValue('F3', 'TOTAL VENTA DEL DIA');
			$sheet->setCellValue('G3', $total_venta_del_dia );

			$sheet->getStyle('F3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			// Forma de Pago
			$sheet->setCellValue('F5', 'Forma de Pago');

			$sheet->getStyle('F5')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

           // Efectivo
			$sheet->setCellValue('F6', 'REMESA');
			$sheet->setCellValue('G6', $remesa );

			// Efectivo
			$sheet->setCellValue('F7', 'EFECTIVO');
			$sheet->setCellValue('G7', $efective );

			// Deposito
			$sheet->setCellValue('F8', 'DEPOSITO');
			$sheet->setCellValue('G8', $deposit );
			
			// Credito
			$sheet->setCellValue('F9', 'CREDITO');
			$sheet->setCellValue('G9', $credit );

			// Yape
			$sheet->setCellValue('F10', 'YAPE');
			$sheet->setCellValue('G10', $yape );

		

			// Diferencia
			$sheet->setCellValue('F13', 'TOTAL LIQUIDADO');
			$sheet->setCellValue('G13', $total_liquidado );


			// Diferencia
			$sheet->setCellValue('F15', 'DIFERENCIA');
			$sheet->setCellValue('G15', $diference );

			// Saldo a Favor
			$sheet->setCellValue('F16', 'SALDO A FAVOR');
			$sheet->setCellValue('G16', $favor );

			// Diferencia Final
			$sheet->setCellValue('F17', 'CUADRE FINAL');
			$sheet->setCellValue('G17', $diference_final );

			// Cobranzas de creditos
			$sheet->setCellValue('F20', 'Cobranzas de Creditos');

			$sheet->getStyle('F20')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			// Cobranza en efectivo
			$sheet->setCellValue('F21', 'COBRANZA EN EFECTIVO');
			$sheet->setCellValue('G21', $cobranza_efective );

			// Cobranza en REMESA
			$sheet->setCellValue('F22', 'COBRANZA EN REMESA');
			$sheet->setCellValue('G22', $cobranza_remesa );

			// Cobranza en deposito
			$sheet->setCellValue('F23', 'COBRANZA EN DEPOSITO');
			$sheet->setCellValue('G23', $cobranza_deposit );

			// Total cobranza
			$sheet->setCellValue('F24', 'TOTAL COBRANZA');
			$sheet->setCellValue('G24', $total_cobranza );

			// Otros ingresos de caja
			$sheet->setCellValue('F26', 'RESUMEN COBRANZA');

			$sheet->getStyle('F26')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			// Cesion de Uso en Efectivo
			$sheet->setCellValue('F27', 'TOTAL EFECTIVO');
			$sheet->setCellValue('G27', $total_efective_day );

           // Cesion de Uso en Deposito
			$sheet->setCellValue('F28', 'TOTAL REMESA');
			$sheet->setCellValue('G28', $total_remesa_day );


			// Cesion de Uso en Deposito
			$sheet->setCellValue('F29', 'TOTAL DEPOSITO');
			$sheet->setCellValue('G29', $total_deposit_day );

			// Otros ingresos efectivo
			$sheet->setCellValue('F30', 'TOTAL COBRANZA');
			$sheet->setCellValue('G30', $total_cobranza_final );

			
		//	$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');

			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
			

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
		
	}
}
	








