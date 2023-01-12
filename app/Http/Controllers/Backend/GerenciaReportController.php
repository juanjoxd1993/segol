<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Report;
use App\GerReport;
use App\Sale;
use App\SaleDetail;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\AcRep;
use App\GlpGeneral;
use App\BusinessUnit;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use stdClass;


class GerenciaReportController extends Controller
{
    public function index() {

		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.gerencia_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha.',
			'day.required'			=> 'Debe ingresar un día.',
			'day.numeric'				=> 'El día debe ser mayor a 0.',
			'day.min'					=> 'El día debe ser mayor a 0.',
			'day.not_in'				=> 'El día debe ser mayor a 0.',
			
		];

		$rules = [
			'initial_date'	=> 'required',
			'day'				=> 'required|numeric|min:0|not_in:0',
			
			
		];

		request()->validate($rules, $messages);
		return request()->all();
	}


	public function list() {
		$export = request('export');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
    	$mes_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-01');
		$price_mes = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('m');
		$day = request('model.day');


		$result = GerReport::select(
			'ger_reports.udid as udid',
			'ger_reports.año as year',
			'ger_reports.mes as month',
			'ger_reports.channel_name as client_channel_name',
			'ger_reports.name as name',
			'ger_reports.soles as last_total',
			'ger_reports.tm as last_tm',
			 'ger_reports.mb as last_mb',
			 'ger_reports.dias as dias_mes',
			 'ger_reports.sale_option as option',
			 'ger_reports.orden as orden',
			 'ger_reports.unit as unit'
		 )

		 ->where('ger_reports.mes', '=', $price_mes)

		 ->orderBy('ger_reports.sale_option')
		 ->orderBy('ger_reports.orden')		 
		 ->get();

	
		$response = [];
		$totals_last_tm = 0;
		$totals_tm = 0;
		$totals_last_mb = 0;
		$totals_mb = 0;
		$totals_proy_mb = 0;
		$totals_last_total = 0;
		$totals_total = 0;
		$totals_proy_soles = 0;

	    
		if ($export) {
			$options = $result->groupBy('option');
			$row_number = 4;
			

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Y1');
			
			$sheet->setCellValue('A1', 'REPORTE DE GERENCIA DEL '.CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]

			]);

		


			   


			foreach($options as $option => $group) {
				$response = [];
			//	$row_number++;
			    $row_number+=2;

				

				

				$sheet->setCellValue('A' . ($row_number-1), '');
				$sheet->setCellValue('B' . ($row_number-1), '');
				$sheet->setCellValue('C' . ($row_number-1), 'Ventas');
				$sheet->setCellValue('D' . ($row_number-1), 'TM');
				$sheet->setCellValue('E' . ($row_number-1),'');
				$sheet->setCellValue('F' . ($row_number-1), '');
				$sheet->setCellValue('G' . ($row_number-1), '');
			//	$sheet->setCellValue('H' . ($row_number-1), '');
			//	$sheet->setCellValue('I' . ($row_number-1), '');
				
				$sheet->setCellValue('H' . ($row_number-1), '');
				$sheet->setCellValue('I' . ($row_number-1), '');
				$sheet->setCellValue('J' . ($row_number-1), 'MB');
				$sheet->setCellValue('K' . ($row_number-1), '');
				$sheet->setCellValue('L' . ($row_number-1),'');
			//	$sheet->setCellValue('O' . ($row_number-1), '');
			//	$sheet->setCellValue('P' . ($row_number-1), '');
				$sheet->setCellValue('M' . ($row_number-1), '');
				$sheet->setCellValue('N' . ($row_number-1), 'Venta');
				$sheet->setCellValue('O' . ($row_number-1), 'soles');
				$sheet->setCellValue('P' . ($row_number-1), '');
	            $sheet->setCellValue('Q' . ($row_number-1), '');
	            $sheet->setCellValue('R' . ($row_number-1), 'Costos');
				$sheet->setCellValue('S' . ($row_number-1), 'Unitarios');
	            $sheet->setCellValue('T' . ($row_number-1), '');
	            $sheet->setCellValue('U' . ($row_number-1), '');
			//	$sheet->setCellValue('Z' . ($row_number-1), '');
	        //  $sheet->setCellValue('AA' . ($row_number-1), '');
	        //  $sheet->setCellValue('AB' . ($row_number-1), '');
	        //  $sheet->setCellValue('AC' . ($row_number-1), '');
			//	$sheet->setCellValue('AD' . ($row_number-1), '');
			//	$sheet->setCellValue('AE' . ($row_number-1), '');
			//	$sheet->setCellValue('AF' . ($row_number-1), '');
			//	$sheet->setCellValue('AG' . ($row_number-1), '');
			//	$sheet->setCellValue('AH' . ($row_number-1), '');
			//	$sheet->setCellValue('AI' . ($row_number-1), '');
			//	$sheet->setCellValue('AJ' . ($row_number-1), '');
			//	$sheet->setCellValue('AK' . ($row_number-1), '');


				$sheet->getStyle('D' . ($row_number-1). ':G' . ($row_number-1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => 'FFFFFF'),
						'name'      =>  'Calibri',
						'size'      =>  10,
						
					],
					'alignment' => [
						'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
					],
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => '0F6626')
					]
				]);

				$sheet->getStyle('H' . ($row_number-1). ':K' . ($row_number-1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => 'FFFFFF'),
						'name'      =>  'Calibri',
						'size'      =>  10,
						
					],
					'alignment' => [
						'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
					],
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => '5CD618')
					]
				]);

				$sheet->getStyle('L' . ($row_number-1). ':Q' . ($row_number-1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => 'FFFFFF'),
						'name'      =>  'Calibri',
						'size'      =>  10,
						
					],
					'alignment' => [
						'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
					],
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => '4C621B')
					]
				]);
				$sheet->getStyle('P' . ($row_number-1). ':U' . ($row_number-1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => 'FFFFFF'),
						'name'      =>  'Calibri',
						'size'      =>  10,
						
					],
					'alignment' => [
						'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
					],
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => '66D56B')
					]
				]);


				$sheet->setCellValue('A' . $row_number, '#');
				$sheet->setCellValue('B' . $row_number, 'Tipo');
				$sheet->setCellValue('C' . $row_number, 'Nombre');
				$sheet->setCellValue('D' . $row_number, 'TM_MA');
				$sheet->setCellValue('E' . $row_number, 'TM');
				$sheet->setCellValue('F' . $row_number, 'PROY');
				$sheet->setCellValue('G' . $row_number, 'TM%MA');
			//	$sheet->setCellValue('H' . $row_number, 'PPTO');
			//	$sheet->setCellValue('I' . $row_number, 'TM%PPTO');
				$sheet->setCellValue('H' . $row_number, 'MB_MA');
				$sheet->setCellValue('I' . $row_number, 'MB');
				$sheet->setCellValue('J' . $row_number, 'MB_PROY');
				$sheet->setCellValue('K' . $row_number, 'MB%MA');
			//	$sheet->setCellValue('N' . $row_number, 'PPTO_MB');
			//	$sheet->setCellValue('O' . $row_number, 'MB%PPTO');
				$sheet->setCellValue('L' . $row_number, 'S./ MES_ANT');
				$sheet->setCellValue('M' . $row_number, 'S./ MES');
				$sheet->setCellValue('N' . $row_number, 'PROY');
				$sheet->setCellValue('O' . $row_number, 'S./%MA');
				$sheet->setCellValue('P' . $row_number, 'CX_MA');
	            $sheet->setCellValue('Q' . $row_number, 'PX_MA');
	            $sheet->setCellValue('R' . $row_number, 'MBU_MA');
				$sheet->setCellValue('S' . $row_number, 'CX');
	            $sheet->setCellValue('T' . $row_number, 'PX');
	            $sheet->setCellValue('U' . $row_number, 'MBU');
			//	$sheet->setCellValue('Z' . $row_number, 'DIF CX');
	        //  $sheet->setCellValue('AA' . $row_number, 'DIF PX');
	        //  $sheet->setCellValue('AB' . $row_number, 'DIF MBU');
	        //  $sheet->setCellValue('AC' . $row_number, 'RATIO PPTO');
			//	$sheet->setCellValue('AD' . $row_number, 'RATIO TM');
			//	$sheet->setCellValue('AE' . $row_number, 'RATIO %');
			//	$sheet->setCellValue('AF' . $row_number, 'C.Glp M_A');
			//	$sheet->setCellValue('AG' . $row_number, 'C.Aprov.M_A');
			//	$sheet->setCellValue('AH' . $row_number, 'C. Total M_A');
			//	$sheet->setCellValue('AI' . $row_number, 'C.Glp');
			//	$sheet->setCellValue('AJ' . $row_number, 'C.Aprov');
			//	$sheet->setCellValue('AK' . $row_number, 'C. Total');




				$sheet->getStyle('A' . $row_number . ':U' . $row_number)->applyFromArray([
					'font' => ['bold' => true],
					'name'      =>  'Calibri',
					'size'      =>  10,
				]);


		
			
			$sheet->getStyle('A'. $row_number . ':F' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
					
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);
			$sheet->getStyle('G'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'E91010')
				]
			]);
			$sheet->getStyle('H'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);

			$sheet->getStyle('I'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'E91010')
				]
			]);

			$sheet->getStyle('J'. $row_number . ':L' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);

		
			$sheet->getStyle('M'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'E91010')
				]
			]);

			
			$sheet->getStyle('N'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);

			$sheet->getStyle('O'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'E91010')
				]
			]);

			$sheet->getStyle('P'. $row_number . ':R' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);

			$sheet->getStyle('S'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '0B2861')
				]
			]);

			$sheet->getStyle('T'. $row_number . ':V' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '11B464')
				]
			]);

			$sheet->getStyle('W'. $row_number . ':Y' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
					'name'      =>  'Calibri',
					'size'      =>  10,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '4B700D')
				]
			]);

		


				$totals_last_tm = 0.00;
	            $totals_tm = 0.00;
	            $totals_proy_tm = 0.00;
	            $totals_last_mb = 0.00;
	            $totals_mb = 0.00;
	            $totals_proy_mb = 0.00;
	            $totals_last_total = 0.00;
	            $totals_total = 0.00;
	            $totals_proy_soles = 0.00;


				foreach ($group as $report) {



					if ( $report->year == '2022'  ){

				


				// <<--------------------------------------------------------------CALCULO DE TM EXCEL-------------------------------------------------------------------------------->>

				if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011' ||$report->udid == '20221111') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 1)
					->select('kg')
					->sum('kg');
				}
		
					elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712' ||$report->udid == '20220812' ||$report->udid == '20220912' ||$report->udid == '20221012' ||$report->udid == '20221112'||$report->udid == '20221212' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id', 5)
                    ->where('clients.route_id', 19)
					->select('kg')
					->sum('kg');
				} 

				elseif ( $report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 19)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
                 ->where('clients.channel_id', 5)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ($report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', 9)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ($report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id', 10)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ($report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '7')
                 ->where('clients.channel_id', 5)
                 ->where('clients.route_id', 19)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '7')
                 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 19)
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '7')
                 ->where('clients.channel_id', 5)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029'   ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
                 ->where('clients.manager_id', '7')
                 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
                 ->where('clients.manager_id', '7')
				 ->where('clients.channel_id',10)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '8')
                 ->where('clients.channel_id', 5)
                 ->where('clients.route_id', 19)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '8')
                 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 19)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '8')
                 ->where('clients.channel_id', 5)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314'    ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '8')
                 ->where('clients.channel_id', 6)
                 ->where('clients.route_id', 26)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id','8')
                 ->where('clients.channel_id', 10)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			
				 }

				
				else {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.company_id', 2)
					->select('kg')
					->sum('kg');
				}

// <<--------------------------------------------------------------CALCULO DE SOLES EXCEL-------------------------------------------------------------------------------->>



if ( $report->year == '2022'){

	if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011'||$report->udid == '20221111'||$report->udid == '20221211') {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.business_unit_id', 1)
		->select('total')
		->sum('total');
	}

		elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'||$report->udid == '20220812'||$report->udid == '20220912'||$report->udid == '20221012'||$report->udid == '20221112'||$report->udid == '20221212'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.channel_id', 5)
        ->where('clients.route_id', 19)
		->select('total')
		->sum('total');
	} 

	elseif ( $report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121') {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 19)
        ->select('total')
		->sum('total');
    } 
    elseif ( $report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122') {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.channel_id', 5)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    } 
    elseif ($report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    } 
    elseif ( $report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', 9)
        ->select('total')
		->sum('total');
    } 
    elseif ($report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.channel_id', 10)
        ->select('total')
		->sum('total');
    } 
    elseif ($report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '7')
        ->where('clients.channel_id', 5)
        ->where('clients.route_id', 19)
        ->select('total')
		->sum('total');
    } 
    elseif ( $report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027') {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '7')
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 19)
        ->select('total')
		->sum('total');
    } 
    elseif ( $report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028') {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '7')
        ->where('clients.channel_id', 5)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    }
    elseif ( $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029'   ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '7')
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    }
    elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '7')
        ->where('clients.channel_id',10)
        ->select('total')
		->sum('total');
    }
    elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '8')
        ->where('clients.channel_id', 5)
        ->where('clients.route_id', 19)
        ->select('total')
		->sum('total');
    }
    elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312') {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
         ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '8')
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 19)
        ->select('total')
		->sum('total');
    }
    elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
        ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '8')
        ->where('clients.channel_id', 5)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    }
    elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314'    ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
        ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id', '8')
        ->where('clients.channel_id', 6)
        ->where('clients.route_id', 26)
        ->select('total')
		->sum('total');
    }
    elseif ( $report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
        ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id','8')
        ->where('clients.channel_id', 10)
        ->select('total')
		->sum('total');
    }
    elseif ( $report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
        $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
        ->where('sales.sale_date', '>=', $mes_date)
        ->where('sales.sale_date', '<=', $initial_date)
        ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
        ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
        ->where('clients.manager_id','9')
        ->select('total')
		->sum('total');
    }
	 }

	
	else {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.company_id', 2)
		->select('total')
		->sum('total');
	}




	// <<--------------------------------------------------------------CALCULO DE MB -------------------------------------------------------------------------------->>
	$report->unit = $report ['unit'];
	
	
	$movements_e = WarehouseMovement::select('id', 'warehouse_type_id', 'movement_type_id', 'referral_serie_number', 'referral_voucher_number','origin' ,'created_at')
	->where('price_mes', $price_mes)
	->where('movement_type_id', 30)
	->where('origin', 26)
	->where('created_at', '<=', $initial_date)
	->get();

	$total_e = WarehouseMovement::whereIn('id', $movements_e->pluck('id'))
   ->select('total')
   ->sum('total');

	$quantity_e = WarehouseMovementDetail::whereIn('warehouse_movement_id', $movements_e->pluck('id'))
   ->select('converted_amount')
   ->sum('converted_amount');   

   $report->soles_glp_e +=$total_e;
   $report->kgs_glp_e +=$quantity_e;

   $movements_g = WarehouseMovement::select('id', 'warehouse_type_id', 'movement_type_id', 'referral_serie_number', 'referral_voucher_number','origin' ,'created_at')
   ->where('price_mes', $price_mes)
   ->where('movement_type_id', 30)
   ->where('origin', 27)
   ->where('created_at', '<=', $initial_date)
   ->get();

   $total_g = WarehouseMovement::whereIn('id', $movements_g->pluck('id'))
  ->select('total')
  ->sum('total');

   $quantity_g = WarehouseMovementDetail::whereIn('warehouse_movement_id', $movements_g->pluck('id'))
  ->select('converted_amount')
  ->sum('converted_amount');   

  $report->soles_glp_g +=$total_g;
  $report->kgs_glp_g +=$quantity_g;

  if ( $report->unit != 2)

   $report->cost_glp = $report['kgs_glp_e'] > 0 ? ($report['soles_glp_e']/($report['kgs_glp_e']/1000)/100): null;
   else
	{
	$report->cost_glp = $report['kgs_glp_g'] > 0 ? ($report['soles_glp_g']/($report['kgs_glp_g']/1000)/100): null;	
	}



//<<-------------------------------------------------------------------------------------------------------------------------------------------------------------------->>


//PERMITE HALLAR EL MB DEL MES CONSULTADO
$report->costo = $report->cost_glp;

//------------------------------------------------------


					
    
//	$report->aprov = $report ['aprov'];
//	$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

	$report->name = $report ['name'];
	$report->last_tm = $report ['last_tm'];
	$report->tm = $tm_fin/1000;
	$report->proy_tm =  ($report ['tm']/ $day)* $report ['dias_mes'];

	if ( $report->last_tm != 0)
	$report->last_percentaje_tm = ($report  ['tm']/ $report['last_tm'] );
	else
	{
		$report->last_percentaje_tm=0;
	}

	
	
	$report->last_mb = $report ['last_mb'];
	
	
	$report->last_total =  $report ['last_total'];
	$report->total =  $soles;
	$report->proy_soles = ($report ['total']/ $day)* $report ['dias_mes'];

	if ( $report->last_total != 0)
	$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']);
	else
	{
		$report->percentaje_soles=0;
	}

	if ($report->tm !=0)
	$report->mb = ((($report->total/($report->tm *100))-$report->costo) *($report->tm*100));
	else {
		$report->mb =0;
	}


	$report->proy_mb = ($report ['mb']/ $day)* $report ['dias_mes'];

	if ( $report->last_mb != 0)
	$report->last_percentaje_mb =($report  ['mb']/ $report ['last_mb']);
	else
	{
		$report->last_percentaje_mb=0;
	}
	

	if ( $report->last_tm != 0)
	$report->px_ma =  ($report ['last_total']/ $report ['last_tm'])/100;
	else
	{
		$report->px_ma=0;
	}
	if ( $report->last_tm != 0)
	$report->mbu_ma = ($report ['last_mb']/ $report ['last_tm'])/100;
	else
	{
		$report->mbu_ma=0;
	}

	$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];

	if ( $report->tm != 0)
	$report->px= $report ['total']/$report ['tm']/100;
	else
	{
		$report->px=0;
	}

	if ( $report->tm != 0)
	$report->mbu = ($report ['mb']/ $report ['tm'])/100;
	else
	{
		$report->mbu=0;
	}
	
	
	$report->dif_cx =  $report['costo'] - $report['costo_ma'];
	$report->dif_px = $report ['px']- $report ['px_ma'];
	$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];

	if ( $report->tm_ppto != 0)
	$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
	else
	{
		$report->ratio_ppto=0;
	}
	$report->ratio_tm = ($report ['tm']/ $day);
	

	$report->last_cost_glp =  $report->soles_glp_e;
	$report->last_aprov = $report->kgs_glp_e;

	if ( $report->last_tm != 0)
	$report->last_cost_glp_final = $report ['last_mb']/$report ['last_tm'];
	else
	{
		$report->last_cost_glp_final=0;
	}


					
					/* Totals*/
					$totals_last_tm += $report->last_tm;
					$totals_tm += $report->tm;
		            $totals_proy_tm += $report->proy_tm;
		            $totals_last_mb += $report->last_mb;
		            $totals_mb += $report->mb;
		            $totals_proy_mb += $report->proy_mb;
		            $totals_last_total += $report->last_total;
		            $totals_total += $report->total;
		            $totals_proy_soles += $report->proy_soles;

                    if ( $totals_tm!= 0)
					$report->ratio = ($report ['tm']/ $totals_tm);
					else
	                {
					$report->ratio=0;
	                }
					

					$response[] = $report;
				}


				$totals = new stdClass();
				$totals->client_channel_name = 'TOTAL';
				$totals->name = '';
				$totals->last_tm = $totals_last_tm;
				$totals->tm =$totals_tm;
				$totals->proy_tm =$totals_proy_tm;
				$totals->last_percentaje_tm = '';
				$totals->percentaje_tm = '';
				$totals->last_mb = $totals_last_mb;
				$totals->mb = $totals_mb;
				$totals->proy_mb = $totals_proy_mb;
				$totals->last_percentaje_mb = '';
				$totals->percentaje_mb = '';
				$totals->last_total = $totals_last_total;
				$totals->total = $totals_total;
				$totals->proy_soles = $totals_proy_soles;
				$totals->percentaje_soles = '';
				$totals->costo_ma = '';
				$totals->px_ma = '';
				$totals->mbu_ma = '';
				$totals->costo = '';
				$totals->px = '';
				$totals->mbu= '';
				$totals->last_aprov = '';
				$totals->last_cost_glp_final = '';


				$response[] = $totals;

				$sheet->getStyle('A' . ($row_number + count($group) + 1) . ':Y' . ($row_number + count($group) + 1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => '000000'),
					//	'bold' => true,
						'size' => 12,
					],
					
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => 'FFD700')  //#186F18 
					]
				]);

				$sheet->getStyle('Z' . ($row_number  - 1) . ':Y' . ($row_number  - 1))->applyFromArray([
					'font' => [
						'color' => array('rgb' => '000000'),
					//	'bold' => true,
						'size' => 12,
					],
					
					'fill' => [
						'fillType' => Fill::FILL_SOLID,
						'startColor' => array('rgb' => '4B700D')  //#186F18 
					]
				]);


			//	$sheet->insertNewRowBefore('A' . ($row_number + count($group) + 2) . ':AK' . ($row_number + count($group) + 2));



				$row_number++;

				foreach ($response as $index => $element) {
					$index++;
							
					$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
					$sheet->setCellValue('B'.$row_number, $element->client_channel_name);
					$sheet->setCellValue('C'.$row_number, $element->name);
					$sheet->setCellValue('D'.$row_number, $element->last_tm);
					$sheet->setCellValue('E'.$row_number, $element->tm);
					$sheet->setCellValue('F'.$row_number, $element->proy_tm);
					$sheet->setCellValue('G'.$row_number, $element->last_percentaje_tm);
					$sheet->setCellValue('H'.$row_number, $element->last_mb);
					$sheet->setCellValue('I'.$row_number, $element->mb);
					$sheet->setCellValue('J'.$row_number, $element->proy_mb);
					$sheet->setCellValue('K'.$row_number, $element->last_percentaje_mb);
					$sheet->setCellValue('L'.$row_number, $element->last_total);
					$sheet->setCellValue('M'.$row_number, $element->total);
					$sheet->setCellValue('N'.$row_number, $element->proy_soles);
					$sheet->setCellValue('O'.$row_number, $element->percentaje_soles);
					$sheet->setCellValue('P'.$row_number, $element->costo_ma);
					$sheet->setCellValue('Q'.$row_number, $element->px_ma);
					$sheet->setCellValue('R'.$row_number, $element->mbu_ma);
					$sheet->setCellValue('S'.$row_number, $element->costo);
					$sheet->setCellValue('T'.$row_number, $element->px);
					$sheet->setCellValue('U'.$row_number, $element->mbu);
					$sheet->setCellValue('AG'.$row_number, $element->last_aprov);
					$sheet->setCellValue('AH'.$row_number, $element->last_cost_glp_final);
				
					
                    $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
					$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.0');
					$sheet->getStyle('T'.$row_number)->getNumberFormat()->setFormatCode('0.00');
					$sheet->getStyle('U'.$row_number)->getNumberFormat()->setFormatCode('0.00');
					$sheet->getStyle('V'.$row_number)->getNumberFormat()->setFormatCode('0.00');
					$sheet->getStyle('W'.$row_number)->getNumberFormat()->setFormatCode('0.00');
					$sheet->getStyle('X'.$row_number)->getNumberFormat()->setFormatCode('0.00');
					$sheet->getStyle('Y'.$row_number)->getNumberFormat()->setFormatCode('0.00');

					$row_number++;
				}
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
		//	$sheet->getColumnDimension('Z')->setAutoSize(true);
		//	$sheet->getColumnDimension('AA')->setAutoSize(true);
		//	$sheet->getColumnDimension('AB')->setAutoSize(true);
		//	$sheet->getColumnDimension('AC')->setAutoSize(true);
		//	$sheet->getColumnDimension('AD')->setAutoSize(true);
		//	$sheet->getColumnDimension('AE')->setAutoSize(true);
		//	$sheet->getColumnDimension('AF')->setAutoSize(true);
			$sheet->getColumnDimension('AG')->setAutoSize(true);
			$sheet->getColumnDimension('AH')->setAutoSize(true);
		//	$sheet->getColumnDimension('AI')->setAutoSize(true);
		//	$sheet->getColumnDimension('AJ')->setAutoSize(true);
		//	$sheet->getColumnDimension('AK')->setAutoSize(true);
			$writer = new Xls($spreadsheet);

			return $writer->save('php://output');
		} else {
			foreach ($result as $report) {





				if ( $report->year == '2022'){


if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011' ||$report->udid == '20221111') {
    $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
    ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
    ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
    ->where('clients.business_unit_id', 1)
    ->select('kg')
    ->sum('kg');
     }

    elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712' ||$report->udid == '20220812' ||$report->udid == '20220912' ||$report->udid == '20221012' ||$report->udid == '20221112'||$report->udid == '20221212' ) {
    $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
    ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
    ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
    ->where('clients.channel_id', 5)
    ->where('clients.route_id', 19)
    ->select('kg')
    ->sum('kg');
} 

elseif ( $report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121') {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 19)
 ->select('kg')
 ->sum('kg');
} 
elseif ( $report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122') {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.channel_id', 5)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
} 
elseif ($report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
} 
elseif ( $report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', 9)
 ->select('kg')
 ->sum('kg');
} 
elseif ($report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.channel_id', 10)
 ->select('kg')
 ->sum('kg');
} 
elseif ($report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '7')
 ->where('clients.channel_id', 5)
 ->where('clients.route_id', 19)
 ->select('kg')
 ->sum('kg');
} 
elseif ( $report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027') {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '7')
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 19)
 ->select('kg')
 ->sum('kg');
} 
elseif ( $report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028') {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '7')
 ->where('clients.channel_id', 5)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
}
elseif ( $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029'   ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '7')
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
}
elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '7')
 ->where('clients.channel_id',10)
 ->select('kg')
 ->sum('kg');
}
elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '8')
 ->where('clients.channel_id', 5)
 ->where('clients.route_id', 19)
 ->select('kg')
 ->sum('kg');
}
elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312') {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '8')
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 19)
 ->select('kg')
 ->sum('kg');
}
elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '8')
 ->where('clients.channel_id', 5)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
}
elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314'    ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id', '8')
 ->where('clients.channel_id', 6)
 ->where('clients.route_id', 26)
 ->select('kg')
 ->sum('kg');
}
elseif ( $report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id','8')
 ->where('clients.channel_id', 10)
 ->select('kg')
 ->sum('kg');
}
elseif ( $report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
 ->where('sales.sale_date', '>=', $mes_date)
 ->where('sales.sale_date', '<=', $initial_date)
 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
 ->where('clients.manager_id','9')
 ->select('kg')
 ->sum('kg');
}

 }


else {
    $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
    ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
    ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
    ->where('clients.company_id', 2)
    ->select('kg')
    ->sum('kg');
}

// <<--------------------------------------------------------------CALCULO DE SOLES EXCEL-------------------------------------------------------------------------------->>



if ( $report->year == '2022'){

if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011'||$report->udid == '20221111'||$report->udid == '20221211') {
$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
->where('sales.sale_date', '>=', $mes_date)
->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.business_unit_id', 1)
->select('total')
->sum('total');
}

elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'||$report->udid == '20220812'||$report->udid == '20220912'||$report->udid == '20221012'||$report->udid == '20221112'||$report->udid == '20221212'  ) {
$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
->where('sales.sale_date', '>=', $mes_date)
->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.channel_id', 5)
->where('clients.route_id', 19)
->select('total')
->sum('total');
} 

elseif ( $report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121') {
$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
->where('sales.sale_date', '>=', $mes_date)
->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.channel_id', 6)
->where('clients.route_id', 19)
->select('total')
->sum('total');
} 
elseif ( $report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122') {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.channel_id', 5)
->where('clients.route_id', 26)
->select('total')
->sum('total');
} 
elseif ($report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.channel_id', 6)
->where('clients.route_id', 26)
->select('total')
->sum('total');
} 
elseif ( $report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', 9)
->select('total')
->sum('total');
} 
elseif ($report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.channel_id', 10)
->select('total')
->sum('total');
} 
elseif ($report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '7')
->where('clients.channel_id', 5)
->where('clients.route_id', 19)
->select('total')
->sum('total');
} 
elseif ( $report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027') {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '7')
->where('clients.channel_id', 6)
->where('clients.route_id', 19)
->select('total')
->sum('total');
} 
elseif ( $report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028') {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '7')
->where('clients.channel_id', 5)
->where('clients.route_id', 26)
->select('total')
->sum('total');
}
elseif ( $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029'   ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '7')
->where('clients.channel_id', 6)
->where('clients.route_id', 26)
->select('total')
->sum('total');
}
elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '7')
->where('clients.channel_id',10)
->select('total')
->sum('total');
}
elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '8')
->where('clients.channel_id', 5)
->where('clients.route_id', 19)
->select('total')
->sum('total');
}
elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312') {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '8')
->where('clients.channel_id', 6)
->where('clients.route_id', 19)
->select('total')
->sum('total');
}
elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '8')
->where('clients.channel_id', 5)
->where('clients.route_id', 26)
->select('total')
->sum('total');
}
elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314'    ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id', '8')
->where('clients.channel_id', 6)
->where('clients.route_id', 26)
->select('total')
->sum('total');
}
elseif ( $report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id','8')
->where('clients.channel_id', 10)
->select('total')
->sum('total');
}
elseif ( $report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
    $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
    ->where('sales.sale_date', '>=', $mes_date)
    ->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.manager_id','9')
->select('total')
->sum('total');
}

}


else {
$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

->where('sales.sale_date', '>=', $mes_date)
->where('sales.sale_date', '<=', $initial_date)
->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
->where('clients.company_id', 2)
->select('total')
->sum('total');
}

$report->unit = $report ['unit'];
	
	
	$movements_e = WarehouseMovement::select('id', 'warehouse_type_id', 'movement_type_id', 'referral_serie_number', 'referral_voucher_number','origin' ,'created_at')
	->where('price_mes', $price_mes)
	->where('movement_type_id', 30)
	->where('origin', 26)
	->where('created_at', '<=', $initial_date)
	->get();

	$total_e = WarehouseMovement::whereIn('id', $movements_e->pluck('id'))
   ->select('total')
   ->sum('total');

	$quantity_e = WarehouseMovementDetail::whereIn('warehouse_movement_id', $movements_e->pluck('id'))
   ->select('converted_amount')
   ->sum('converted_amount');   

   $report->soles_glp_e +=$total_e;
   $report->kgs_glp_e +=$quantity_e;

   $movements_g = WarehouseMovement::select('id', 'warehouse_type_id', 'movement_type_id', 'referral_serie_number', 'referral_voucher_number','origin' ,'created_at')
   ->where('price_mes', $price_mes)
   ->where('movement_type_id', 30)
   ->where('origin', 27)
   ->where('created_at', '<=', $initial_date)
   ->get();

   $total_g = WarehouseMovement::whereIn('id', $movements_g->pluck('id'))
  ->select('total')
  ->sum('total');

   $quantity_g = WarehouseMovementDetail::whereIn('warehouse_movement_id', $movements_g->pluck('id'))
  ->select('converted_amount')
  ->sum('converted_amount');   

  $report->soles_glp_g +=$total_g;
  $report->kgs_glp_g +=$quantity_g;

  if ( $report->unit != 2)

   $report->cost_glp = $report['kgs_glp_e'] > 0 ? ($report['soles_glp_e']/($report['kgs_glp_e']/1000)/100): null;
   else
	{
	$report->cost_glp = $report['kgs_glp_g'] > 0 ? ($report['soles_glp_g']/($report['kgs_glp_g']/1000)/100): null;	
	}




//PERMITE HALLAR EL MB DEL MES CONSULTADO
   $report->costo = $report->cost_glp;

//------------------------------------------------------------------
//	$report->aprov = $report ['aprov'];
//	$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

	$report->name = $report ['name'];
	$report->last_tm = $report ['last_tm'];
	$report->tm = $tm_fin/1000;
	$report->proy_tm =  ($report ['tm']/ $day)* $report ['dias_mes'];

	if ( $report->last_tm != 0)
	$report->last_percentaje_tm = ($report  ['tm']/ $report['last_tm'] );
	else
	{
	$report->last_percentaje_tm=0;
	}

	
	$report->last_mb = $report ['last_mb'];
	
	$report->last_total =  $report ['last_total'];
	$report->total =  $soles;
	$report->proy_soles = ($report ['total']/ $day)* $report ['dias_mes'];

	if ( $report->last_total != 0)
	$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']);
	else
	{
		$report->percentaje_soles=0;
	}

    if ($report->tm !=0)
	$report->mb = ((($report->total/($report->tm *100))-$report->costo) *($report->tm*100));
	else {
		$report->mb =0;
	}

	$report->proy_mb = ($report ['mb']/ $day)* $report ['dias_mes'];
	if ( $report->last_mb != 0)
	$report->last_percentaje_mb =($report  ['mb']/ $report ['last_mb']);
	else
	{
	$report->last_percentaje_mb=0;
	}
	

	if ( $report->last_tm != 0)
	$report->px_ma =  ($report ['last_total']/ $report ['last_tm'])/100;
	else
	{
		$report->px_ma=0;
	}
	if ( $report->last_tm != 0)
	$report->mbu_ma = ($report ['last_mb']/ $report ['last_tm'])/100;
	else
	{
		$report->mbu_ma=0;
	}

	$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];

	if ( $report->tm != 0)
	$report->px= $report ['total']/$report ['tm']/100;
	else
	{
		$report->px=0;
	}

	if ( $report->tm != 0)
	$report->mbu = ($report ['mb']/ $report ['tm'])/100;
	else
	{
		$report->mbu=0;
	}
	
	
	$report->dif_cx =  $report['costo'] - $report['costo_ma'];
	$report->dif_px = $report ['px']- $report ['px_ma'];
	$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];

	if ( $report->tm_ppto != 0)
	$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
	else
	{
		$report->ratio_ppto=0;
	}
	$report->ratio_tm = ($report ['tm']/ $day);
	

	$report->last_cost_glp =  $report->soles_glp_e;
	$report->last_aprov = $report->kgs_glp_e;

	if ( $report->last_tm != 0)
	$report->last_cost_glp_final = $report ['last_mb']/$report ['last_tm'];
	else
	{
		$report->last_cost_glp_final=0;
	}

					
					/* Totals*/
					$totals_last_tm += $report->last_tm;
					$totals_tm += $report->tm;
		        //    $totals_proy_tm += $report->proy_tm;
		        //   $totals_tm_ppto += $report->tm_ppto;
		            $totals_last_mb += $report->last_mb;
		            $totals_mb += $report->mb;
		            $totals_proy_mb += $report->proy_mb;
		        //   $totals_mb_ppto += $report->mb_ppto;
		            $totals_last_total += $report->last_total;
		            $totals_total += $report->total;
		            $totals_proy_soles += $report->proy_soles;

                    if ( $totals_tm!= 0)
					$report->ratio = ($report ['tm']/ $totals_tm);
					else
	                {
					$report->ratio=0;
	                }
					

					$response[] = $report;
				}
			

			
			$totals = new stdClass();
			$totals->client_channel_name = 'TOTAL';
			$totals->name = '';
			$totals->last_tm = '';
			$totals->tm = '';
			$totals->proy_tm = '';
			$totals->last_percentaje_tm = '';
		//	$totals->tm_ppto = '';
		//	$totals->percentaje_tm = '';
			$totals->last_mb = '';
			$totals->last_percentaje_mb = '';
		//	$totals->mb_ppto = '';
			$totals->last_total = '';
			$totals->total = '';
			$totals->proy_soles = '';
			$totals->percentaje_soles = '';
			$totals->costo_ma = '';
				$totals->px_ma = '';
				$totals->mbu_ma = '';
				$totals->costo = '';
				$totals->px = '';
				$totals->mbu= '';
				$totals->dif_cx = '';
				$totals->dif_px = '';
				$totals->dif_mbu = '';
				$totals->ratio_ppto = '';
				$totals->ratio_tm = '';
				$totals->ratio = '';
				$totals->last_cost_glp = '';
				$totals->last_aprov = '';
				$totals->last_cost_glp_final = '';
				$totals->cost_glp = '';
				$totals->aprov = '';
				$totals->cost_glp_final = '';

			$response[] = $totals;

			

			return response()->json($response);
		}
	}
}





