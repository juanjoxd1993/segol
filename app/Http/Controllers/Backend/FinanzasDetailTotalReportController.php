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

		$export = request('export');

		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');

		$response=[];
		$totals_total = 0;
		$totals_sum_total = 0;

		$warehouse_document_type_ids = [13,5,7,4,22,23];
		$client_ids = [1031, 427, 13326, 13775, 14072,14258];

		$total_venta_del_dia = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
															->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
															->whereNotIn('sales.client_id', $client_ids)
															->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=',  $initial_date)
															->select('sales.total_perception')
															->sum('sales.total_perception');

		$efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
										->whereNotIn('sales.client_id', $client_ids)
										->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=',  $initial_date)
										->select('sales.efective')
										->sum('sales.efective');

		$deposit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
									->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
									->whereNotIn('sales.client_id', $client_ids)
									->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
									->select('sales.deposit')
									->sum('sales.deposit');

		$credit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
								->whereIn('sales.warehouse_document_type_id', $warehouse_document_type_ids)
								->whereNotIn('sales.client_id', $client_ids)
								->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
								->select('sales.pre_balance')
								->sum('sales.pre_balance');

		$total_liquidado = $efective + $deposit + $credit;

		$diference = $total_venta_del_dia - $total_liquidado;

		$cobranza_efective =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
																	->leftjoin('clients', 'sales.client_id', '=', 'clients.id')				
																	->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $initial_date)
																	->whereNotIn('sales.client_id', $client_ids)	 
																	->where('liquidations.payment_method_id',[1])
																	->Where('liquidations.collection',[1])
																	->select('liquidations.amount')
																	->sum('liquidations.amount');

		$cobranza_deposit =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
																	->leftjoin('clients', 'sales.client_id', '=', 'clients.id')	
																	->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $initial_date)
																	->whereNotIn('sales.client_id', $client_ids)	 
																	->where('liquidations.payment_method_id',[2])
																	->Where('liquidations.collection',[1])
																	->select('liquidations.amount')				
																	->sum('liquidations.amount');

		$total_cobranza = $cobranza_efective + $cobranza_deposit;

		$cesion_uso_efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
															->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
															->whereNotIn('sales.client_id', $client_ids)
															->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
															->where('liquidations.payment_method_id',[1])	
															->where('sales.warehouse_document_type_id',[14])			
															->select('liquidations.amount')
															->sum('liquidations.amount');

		$cesion_uso_deposit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
															->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
															->whereNotIn('sales.client_id', $client_ids)
															->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
															->where('liquidations.payment_method_id',[2])	
															->where('sales.warehouse_document_type_id',[14])			
															->select('liquidations.amount')
															->sum('liquidations.amount');

		$otros_efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
												->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
												->whereNotIn('sales.client_id', $client_ids)
												->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
												->where('liquidations.payment_method_id',[1])	
												->where('sales.warehouse_document_type_id',[22])			
												->select('liquidations.amount')
												->sum('liquidations.amount');

		$otros_deposit = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
												->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
												->whereNotIn('sales.client_id', $client_ids)
												->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)
												->where('liquidations.payment_method_id',[2])	
												->where('sales.warehouse_document_type_id',[22])			
												->select('liquidations.amount')
												->sum('liquidations.amount');

		$total_otros_ingresos = $cesion_uso_efective + $cesion_uso_deposit + $otros_efective + $otros_deposit;

		$total_recaudado = $total_liquidado + $total_cobranza + $total_otros_ingresos;

		$egresos_caja = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
											->whereNotIn('sales.client_id', $client_ids)
											->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $initial_date)	
											->where('sales.warehouse_document_type_id',[23])			
											->select('sales.total_perception')
											->sum('sales.total_perception');

		$total_efective_day = $total_recaudado - $egresos_caja;

		$remesa_hermes = 0;

		$cuadre = $total_efective_day - $remesa_hermes;

		$response = [
			[
				'company_short_name' => 'Total Venta del Día',
				'total' => $total_venta_del_dia
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
				'company_short_name' => 'Total Liquidado',
				'total' => $total_liquidado
			],
			[
				'company_short_name' => 'Diferencia',
				'total' => $diference
			],
			[
				'company_short_name' => 'Cobranza en Efectivo',
				'total' => $cobranza_efective
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
				'company_short_name' => 'Cesión de Uso en Efectivo',
				'total' => $cesion_uso_efective
			],
			[
				'company_short_name' => 'Cesión de Uso en Deposito',
				'total' => $cesion_uso_deposit
			],
			[
				'company_short_name' => 'Otros Ingresos en Efectivo',
				'total' => $otros_efective
			],
			[
				'company_short_name' => 'Otros Ingresos en Deposito',
				'total' => $otros_deposit
			],
			[
				'company_short_name' => 'Total Otros Ingresos',
				'total' => $total_otros_ingresos
			],
			[
				'company_short_name' => 'Total Recaudado del Día',
				'total' => $total_recaudado
			],
			[
				'company_short_name' => 'Egresos de Caja',
				'total' => $egresos_caja
			],
			[
				'company_short_name' => 'Total Efectivo del Día',
				'total' => $total_efective_day
			],
			[
				'company_short_name' => 'Remesa Hermes',
				'total' => $remesa_hermes
			],
			[
				'company_short_name' => 'Cuadre',
				'total' => $cuadre
			],
		];

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:L1');
			$sheet->setCellValue('A1', 'REPORTE DE FINANZAS DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

			// $sheet->setCellValue('A3', '#');
            // $sheet->setCellValue('B3', 'Caja');		
            // $sheet->setCellValue('C3', 'Fecha de Liquidación');
			// $sheet->setCellValue('D3', 'Total');

			// $sheet->getStyle('A3:M3')->applyFromArray([
			// 	'font' => [
			// 		'bold' => true,
			// 	],
			// ]);

			// $row_number = 4;
			// foreach ($response as $index => $element) {
			// 	$index++;
			// 	$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
			// 	$sheet->setCellValue('B'.$row_number, $element->company_short_name);
			// 	$sheet->setCellValue('C'.$row_number, $initial_date );
			// 	$sheet->setCellValue('D'.$row_number, $element->total);

            //     $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.00');

			// 	$row_number++;
			// }
			// $sheet->setCellValueExplicit('A4', 1, DataType::TYPE_NUMERIC);
			// Total venta del dia
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
			$sheet->setCellValue('F6', 'EFECTIVO');
			$sheet->setCellValue('G6', $efective );

			// Deposito
			$sheet->setCellValue('F7', 'DEPOSITO');
			$sheet->setCellValue('G7', $deposit );

			// Credito
			$sheet->setCellValue('F8', 'CREDITO');
			$sheet->setCellValue('G9', $credit );

			// Total liquidado
			$sheet->setCellValue('F10', 'TOTAL LIQUIDADO');
			$sheet->setCellValue('G10', $total_liquidado );

			// Diferencia
			$sheet->setCellValue('F12', 'DIFERENCIA');
			$sheet->setCellValue('G12', $diference );

			// Cobranzas de creditos
			$sheet->setCellValue('F13', 'Cobranzas de Creditos');

			$sheet->getStyle('F13')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			// Cobranza en efectivo
			$sheet->setCellValue('F14', 'COBRANZA EN EFECTIVO');
			$sheet->setCellValue('G14', $cobranza_efective );

			// Cobranza en deposito
			$sheet->setCellValue('F15', 'COBRANZA EN DEPOSITO');
			$sheet->setCellValue('G15', $cobranza_deposit );

			// Total cobranza
			$sheet->setCellValue('F15', 'TOTAL COBRANZA');
			$sheet->setCellValue('G15', $total_cobranza );

			// Otros ingresos de caja
			$sheet->setCellValue('F17', 'Otros Ingresos de Caja');

			$sheet->getStyle('F17')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			// Cesion de Uso en Efectivo
			$sheet->setCellValue('F18', 'CESION DE USO EN EFECTIVO');
			$sheet->setCellValue('G18', $cesion_uso_efective );

			// Cesion de Uso en Deposito
			$sheet->setCellValue('F19', 'CESION DE USO EN DEPOSITO');
			$sheet->setCellValue('G19', $cesion_uso_deposit );

			// Otros ingresos efectivo
			$sheet->setCellValue('F20', 'OTROS INGRESOS EN EFECTIVO');
			$sheet->setCellValue('G20', $otros_efective );

			// Otros ingresos deposito
			$sheet->setCellValue('F21', 'OTROS INGRESOS EN DEPOSITO');
			$sheet->setCellValue('G21', $otros_deposit );

			// Total otros ingresos
			$sheet->setCellValue('F22', 'TOTAL OTROS INGRESOS');
			$sheet->setCellValue('G22', $total_otros_ingresos );

			// Total recaudado del dia
			$sheet->setCellValue('F24', 'TOTAL RECAUDADO');
			$sheet->setCellValue('G24', $total_recaudado );

			// Egresos de caja
			$sheet->setCellValue('F26', 'EGRESOS DE CAJA');
			$sheet->setCellValue('G26', $egresos_caja );

			// Total efectivo del dìa
			$sheet->setCellValue('F28', 'TOTAL EFECTIVO DEL DIA');
			$sheet->setCellValue('G28', $total_efective_day );

			// Remesa Hermes
			$sheet->setCellValue('F30', 'REMESA HERMES');
			$sheet->setCellValue('G30', $remesa_hermes );

			// Cuadre
			$sheet->setCellValue('F32', 'CUADRE');
			$sheet->setCellValue('G32', $cuadre );

			// $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.00');

			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
			// $sheet->getColumnDimension('C')->setAutoSize(true);
			// $sheet->getColumnDimension('D')->setAutoSize(true);
            // $sheet->getColumnDimension('E')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
		
	}
}
	








