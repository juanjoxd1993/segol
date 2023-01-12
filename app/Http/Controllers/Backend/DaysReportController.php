<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Report;
use App\ConsReport;
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


class DaysReportController extends Controller
{
    public function index() {

		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.days_report')->with(compact('current_date'));
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


		$result = ConsReport::select(
			'cons_reports.udid as udid',
			'cons_reports.año as year',
			'cons_reports.mes as month',
			'cons_reports.channel_name as client_channel_name',
			'cons_reports.name as name',
			'cons_reports.soles as last_total',
			'cons_reports.tm as last_tm',
			'cons_reports.tm_ppto as tm_ppto',
			 'cons_reports.mb_ppto as mb_ppto',
			 'cons_reports.mb as last_mb',
			 'cons_reports.dias as dias_mes',
			 'cons_reports.unit as unit',
			 'cons_reports.sale_option as option',
			 'cons_reports.orden as orden'
		 )

		 ->where('cons_reports.mes', '=', $price_mes)

		 ->orderBy('cons_reports.sale_option')
		 ->orderBy('cons_reports.orden')		 
		 ->get();

	
		$response = [];
		$totals_last_tm = 0;
		$totals_tm = 0;
		$totals_proy_tm = 0;
		$totals_tm_ppto = 0;
		$totals_last_mb = 0;
		$totals_mb = 0;
		$totals_proy_mb = 0;
		$totals_mb_ppto = 0;
		$totals_last_total = 0;
		$totals_total = 0;
		$totals_proy_soles = 0;

	    
		if ($export) {
			$options = $result->groupBy('option');
			$row_number = 4;
			

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Y1');
			
			$sheet->setCellValue('A1', 'REPORTE DE VENTA CANALES DEL '.CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d'));
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
				$sheet->setCellValue('C' . ($row_number-1), '');
				$sheet->setCellValue('D' . ($row_number-1), '');
				$sheet->setCellValue('E' . ($row_number-1),'');
				$sheet->setCellValue('F' . ($row_number-1), 'Ventas');
				$sheet->setCellValue('G' . ($row_number-1), 'TM');
				$sheet->setCellValue('H' . ($row_number-1), '');
				$sheet->setCellValue('I' . ($row_number-1), '');
				
				$sheet->setCellValue('J' . ($row_number-1), '');
				$sheet->setCellValue('K' . ($row_number-1), '');
				$sheet->setCellValue('L' . ($row_number-1), 'MB');
				$sheet->setCellValue('M' . ($row_number-1), '');
				$sheet->setCellValue('N' . ($row_number-1),'');
				$sheet->setCellValue('O' . ($row_number-1), '');
				$sheet->setCellValue('P' . ($row_number-1), '');
				$sheet->setCellValue('Q' . ($row_number-1), 'Venta');
				$sheet->setCellValue('R' . ($row_number-1), 'Soles');
				$sheet->setCellValue('S' . ($row_number-1), '');
				$sheet->setCellValue('T' . ($row_number-1), '');
	            $sheet->setCellValue('U' . ($row_number-1), '');
	            $sheet->setCellValue('V' . ($row_number-1), 'Costos');
				$sheet->setCellValue('W' . ($row_number-1), 'Unitarios');
	            $sheet->setCellValue('X' . ($row_number-1), '');
	            $sheet->setCellValue('Y' . ($row_number-1), '');
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


				$sheet->getStyle('D' . ($row_number-1). ':I' . ($row_number-1))->applyFromArray([
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

				$sheet->getStyle('J' . ($row_number-1). ':O' . ($row_number-1))->applyFromArray([
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

				$sheet->getStyle('P' . ($row_number-1). ':S' . ($row_number-1))->applyFromArray([
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
				$sheet->getStyle('T' . ($row_number-1). ':Y' . ($row_number-1))->applyFromArray([
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
				$sheet->setCellValue('H' . $row_number, 'PPTO');
				$sheet->setCellValue('I' . $row_number, 'TM%PPTO');
				$sheet->setCellValue('J' . $row_number, 'MB_MA');
				$sheet->setCellValue('K' . $row_number, 'MB');
				$sheet->setCellValue('L' . $row_number, 'MB_PROY');
				$sheet->setCellValue('M' . $row_number, 'MB%MA');
				$sheet->setCellValue('N' . $row_number, 'PPTO_MB');
				$sheet->setCellValue('O' . $row_number, 'MB%PPTO');
				$sheet->setCellValue('P' . $row_number, 'S./ MES_ANT');
				$sheet->setCellValue('Q' . $row_number, 'S./ MES');
				$sheet->setCellValue('R' . $row_number, 'PROY');
				$sheet->setCellValue('S' . $row_number, 'S./%MA');
				$sheet->setCellValue('T' . $row_number, 'CX_MA');
	            $sheet->setCellValue('U' . $row_number, 'PX_MA');
	            $sheet->setCellValue('V' . $row_number, 'MBU_MA');
				$sheet->setCellValue('W' . $row_number, 'CX');
	            $sheet->setCellValue('X' . $row_number, 'PX');
	            $sheet->setCellValue('Y' . $row_number, 'MBU');
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




				$sheet->getStyle('A' . $row_number . ':Y' . $row_number)->applyFromArray([
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

		/*	$sheet->getStyle('Z'. $row_number . ':AB' . $row_number)->applyFromArray([
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

			$sheet->getStyle('AC'. $row_number)->applyFromArray([
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
					'startColor' => array('rgb' => 'FF8C00')
				]
			]);

			$sheet->getStyle('AD'. $row_number . ':AE' . $row_number)->applyFromArray([
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
					'startColor' => array('rgb' => 'CD5C5C')
				]
			]);

			$sheet->getStyle('AF'. $row_number . ':AH' . $row_number)->applyFromArray([
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
					'startColor' => array('rgb' => '483D8B')
				]
			]);

			$sheet->getStyle('AI'. $row_number . ':AK' . $row_number)->applyFromArray([
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
					'startColor' => array('rgb' => '4B0082')
				]
			]);*/


				$totals_last_tm = 0.00;
	            $totals_tm = 0.00;
	            $totals_proy_tm = 0.00;
	            $totals_tm_ppto = 0.00;
	            $totals_last_mb = 0.00;
	            $totals_mb = 0.00;
	            $totals_proy_mb = 0.00;
	            $totals_mb_ppto = 0.00;
	            $totals_last_total = 0.00;
	            $totals_total = 0.00;
	            $totals_proy_soles = 0.00;


				foreach ($group as $report) {



					if ( $report->year == '2022'  ){

				


				// <<--------------------------------------------------------------CALCULO DE TM EXCEL-------------------------------------------------------------------------------->>

				if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011' ||$report->udid == '20221111'||$report->udid == '20221211') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
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
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					->where('clients.business_unit_id', 2)
					->select('kg')
					->sum('kg');
				} 

				elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121'||$report->udid == '20221221') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '1')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122'||$report->udid == '20221222') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '2')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '202210211' ||$report->udid == '202211211' ||$report->udid == '202212211') {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.manager_id', '11')
				->select('kg')
				->sum('kg');
			} 
			 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ||$report->udid == '20221223') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258, 14258])
				 ->where('clients.manager_id', '3')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ||$report->udid == '20221124'||$report->udid == '20221224' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '4')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025'||$report->udid == '20221125'||$report->udid == '20221225' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '5')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026'||$report->udid == '20221126'||$report->udid == '20221226' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '6')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '202210212'||$report->udid == '202211212'||$report->udid == '202212212' ) {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.manager_id', '12')
				->select('kg')
				->sum('kg');
			} 
			 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027'||$report->udid == '20221127'||$report->udid == '20221227') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '7')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028'|| $report->udid == '20221128'|| $report->udid == '20221228') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id', '8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'|| $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029' || $report->udid == '20221129' || $report->udid == '20221229'  ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				 ->where('clients.manager_id', '9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310'||$report->udid == '202211310'||$report->udid == '202212310' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				 ->where('clients.sector_id',10)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313'||$report->udid == '202211313' ||$report->udid == '202212313') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id',13)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312' ||$report->udid == '202211312'||$report->udid == '202212312' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id',12)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ||$report->udid == '202211311'||$report->udid == '202212311' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id',11)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314' ||$report->udid == '202211314' ||$report->udid == '202212314' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id',14)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ||$report->udid == '20221137' ||$report->udid == '20221237' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '20220739'||$report->udid == '20220839'||$report->udid == '20220939'||$report->udid == '20221039'||$report->udid == '20221139'||$report->udid == '20221239' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.manager_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '20220741'||$report->udid == '20220841'||$report->udid == '20220941'||$report->udid == '20221041'||$report->udid == '20221141'||$report->udid == '20221241') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','1')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '20220742'||$report->udid == '20220842'||$report->udid == '20220942'||$report->udid == '20221042'||$report->udid == '20221142'||$report->udid == '20221242' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','2')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '20220743'||$report->udid == '20220843'||$report->udid == '20220943'||$report->udid == '20221043'||$report->udid == '20221143'||$report->udid == '20221243' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','3')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '20220744'||$report->udid == '20220844'||$report->udid == '20220944'||$report->udid == '20221044'||$report->udid == '20221144'||$report->udid == '20221244' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','4')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '20220745'||$report->udid == '20220845'||$report->udid == '20220945'||$report->udid == '20221045'||$report->udid == '20221145'||$report->udid == '20221245' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','5')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '20220746'||$report->udid == '20220846'||$report->udid == '20220946'||$report->udid == '20221046'||$report->udid == '20221146'||$report->udid == '20221246' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','6')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '20220747'||$report->udid == '20220847'||$report->udid == '20220947'||$report->udid == '20221047'||$report->udid == '20221147'||$report->udid == '20221247' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-8'||$report->udid == '2022054R-8'||$report->udid == '2022064R-8'||$report->udid == '20220748'||$report->udid == '20220848'||$report->udid == '20220948'||$report->udid == '20221048'||$report->udid == '20221148'||$report->udid == '20221248' ) {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.route_id','8')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '20220749'||$report->udid == '20220849'||$report->udid == '20220949'||$report->udid == '20221049'||$report->udid == '20221149'||$report->udid == '20221249' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '202207410' ||$report->udid == '202208410'||$report->udid == '202209410'||$report->udid == '202210410'||$report->udid == '202211410'||$report->udid == '202212410' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','10')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '202207411'||$report->udid == '202208411'||$report->udid == '202209411'||$report->udid == '202210411'||$report->udid == '202211411'||$report->udid == '202212411') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','11')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '202207412'||$report->udid == '202208412'||$report->udid == '202209412'||$report->udid == '202210412'||$report->udid == '202211412'||$report->udid == '202212412' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','12')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '202207413'||$report->udid == '202208413'||$report->udid == '202209413'||$report->udid == '202210413'||$report->udid == '202211413'||$report->udid == '202212413' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','13')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '202207423' ||$report->udid == '202208423'||$report->udid == '202209423'||$report->udid == '202210423'||$report->udid == '202211423'||$report->udid == '202212423'  ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','23')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '202207429'||$report->udid == '202208429'||$report->udid == '202209429'||$report->udid == '202210429'||$report->udid == '202211429'||$report->udid == '202212429' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','29')
				 ->select('kg')
				 ->sum('kg');
			 }

			 elseif (  $report->udid == '2022044R-31'||$report->udid == '2022054R-31'||$report->udid == '2022064R-31'||$report->udid == '202207431'||$report->udid == '202208431'||$report->udid == '202209431'||$report->udid == '202210431'||$report->udid == '202211431'||$report->udid == '202212431' ) {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.route_id','31')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '202207415'||$report->udid == '202208415'||$report->udid == '202209415'||$report->udid == '202210415'||$report->udid == '202211415'||$report->udid == '202212415' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','15')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '202207416'||$report->udid == '202208416'||$report->udid == '202209416'||$report->udid == '202210416'||$report->udid == '202211416'||$report->udid == '202212416' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','16')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '202207417'||$report->udid == '202208417'||$report->udid == '202209417'||$report->udid == '202210417'||$report->udid == '202211417'||$report->udid == '202212417' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','17')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '202207418'||$report->udid == '202208418'||$report->udid == '202209418'||$report->udid == '202210418'||$report->udid == '202211418'||$report->udid == '202212418' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','18')
				 ->select('kg')
				 ->sum('kg');
			 }

			 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '202207424'|| $report->udid == '202208424'|| $report->udid == '202209424'|| $report->udid == '202210424'|| $report->udid == '202211424'|| $report->udid == '202212424' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','24')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '202207433'||$report->udid == '202208433'||$report->udid == '202208433'||$report->udid == '202209433'||$report->udid == '202210433'||$report->udid == '202211433'||$report->udid == '202212433') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','33')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074205'||$report->udid == '2022084205'||$report->udid == '2022094205'||$report->udid == '2022104205'||$report->udid == '2022114205'||$report->udid == '2022124205' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','5')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074201'||$report->udid == '2022084201'||$report->udid == '2022094201'||$report->udid == '2022104201'||$report->udid == '2022114201'||$report->udid == '2022124201' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','1')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074203'||$report->udid == '2022084203'||$report->udid == '2022094203'||$report->udid == '2022104203'||$report->udid == '2022114203'||$report->udid == '2022124203' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','3')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '20220742010'||$report->udid == '20220842010'||$report->udid == '20220942010'||$report->udid == '20221042010'||$report->udid == '20221142010'||$report->udid == '20221242010' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','10')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '20220742011'|| $report->udid == '20220842011'|| $report->udid == '20220942011'|| $report->udid == '20221042011'|| $report->udid == '20221142011'|| $report->udid == '20221242011' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','11')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074209'|| $report->udid == '2022084209'|| $report->udid == '2022094209'|| $report->udid == '2022104209'|| $report->udid == '2022114209'|| $report->udid == '2022124209' ) {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.route_id','20')
				->where('clients.zone_id','9')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '202207434'|| $report->udid == '202208434'|| $report->udid == '202209434'|| $report->udid == '202210434'|| $report->udid == '202211434'|| $report->udid == '202212434' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','34')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '202207435'||$report->udid == '202208435'||$report->udid == '202209435'||$report->udid == '202210435'||$report->udid == '202211435'||$report->udid == '202212435' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','35')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '202207436'||$report->udid == '202208436'||$report->udid == '202209436'||$report->udid == '202210436'||$report->udid == '202211436'||$report->udid == '202212436' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.route_id','36')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '202207437'||$report->udid == '202208437'||$report->udid == '202209437'||$report->udid == '202210437'||$report->udid == '202211437'||$report->udid == '202212437' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				 ->where('clients.route_id','37')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022065Massiel AlorPRIMARIO'||$report->udid == '202207557'||$report->udid == '202208557'||$report->udid == '202209557'||$report->udid == '202210557'||$report->udid == '202211557'||$report->udid == '202212557'  ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				 ->where('clients.channel_id','5')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '202207567' ||$report->udid == '202208567'||$report->udid == '202209567'||$report->udid == '202210567'||$report->udid == '202211567'||$report->udid == '202212567'   ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022046Omar CasachaguaPRIMARIO'||$report->udid == '2022056Omar CasachaguaPRIMARIO'||$report->udid == '2022066Omar CasachaguaPRIMARIO'||$report->udid == '202207658'||$report->udid == '202208658'||$report->udid == '202209658'||$report->udid == '202210658'||$report->udid == '202211658'||$report->udid == '202212658') {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				->where('clients.channel_id','5')
				->where('clients.manager_id','8')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '202207668'||$report->udid == '202208668'||$report->udid == '202209668'||$report->udid == '202210668'||$report->udid == '202211668'||$report->udid == '202212668' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '202207769'||$report->udid == '202208769'||$report->udid == '202209769'||$report->udid == '202210769'||$report->udid == '202211769'||$report->udid == '202212769' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '202207827'||$report->udid == '202208827'||$report->udid == '202209827'||$report->udid == '202210827'||$report->udid == '202211827'||$report->udid == '202212827' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				 ->where('clients.sector_id','2')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '202207928'||$report->udid == '202208928'||$report->udid == '202209928'||$report->udid == '202210928'||$report->udid == '202211928'||$report->udid == '202212928' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id','2')
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '2022071099'||$report->udid == '2022081099'||$report->udid == '2022091099'||$report->udid == '2022101099'||$report->udid == '2022111099'||$report->udid == '2022121099') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
				 ->where('clients.sector_id','9')
				 ->where('clients.manager_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
				 }

				
				else {
					$tm_fin = 0;
				}

// <<--------------------------------------------------------------CALCULO DE SOLES EXCEL-------------------------------------------------------------------------------->>



if ( $report->year == '2022'){

	if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011'||$report->udid == '20221111'||$report->udid == '20221211') {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258 ])
		->where('clients.business_unit_id', 1)
		->select('total')
		->sum('total');
	}

		elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'||$report->udid == '20220812'||$report->udid == '20220912'||$report->udid == '20221012'||$report->udid == '20221112'||$report->udid == '20221212'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.business_unit_id', 2)
		->select('total')
		->sum('total');
	} 

	elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021'||$report->udid == '20221121'||$report->udid == '20221221') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 1)
	 ->select('total')
	 ->sum('total');
 } 
 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022'||$report->udid == '20221122'||$report->udid == '20221222' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 2)
	 ->select('total')
	 ->sum('total');
 } 

 elseif ( $report->udid == '202210211' ||$report->udid == '202211211' ||$report->udid == '202212211') {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	->where('clients.manager_id', 11)
	->select('total')
	->sum('total');
} 
 

 
 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023'||$report->udid == '20221123'||$report->udid == '20221223' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 3)
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '20220724'||$report->udid == '20220824'||$report->udid == '20220924'||$report->udid == '20221024'||$report->udid == '20221124'||$report->udid == '20221224' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 4)
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '20220725'||$report->udid == '20220825'||$report->udid == '20220925'||$report->udid == '20221025'||$report->udid == '20221125'||$report->udid == '20221225' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 5)
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '20220726'||$report->udid == '20220826'||$report->udid == '20220926'||$report->udid == '20221026'||$report->udid == '20221126'||$report->udid == '20221226'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 6)
	 ->select('total')
	 ->sum('total');
 } 
 elseif ($report->udid == '202210212'||$report->udid == '202211212'||$report->udid == '202212212' ) {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
   
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	->where('clients.manager_id', 12)
	->select('total')
	->sum('total');
} 
 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927'||$report->udid == '20221027'||$report->udid == '20221127'||$report->udid == '20221227' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 7)
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '20220728'||$report->udid == '20220828'||$report->udid == '20220928'||$report->udid == '20221028'||$report->udid == '20221128'||$report->udid == '20221228') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 8)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'||$report->udid == '20220729'||$report->udid == '20220829'||$report->udid == '20220929'||$report->udid == '20221029'||$report->udid == '20221129'||$report->udid == '20221229' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.manager_id', 9)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310'||$report->udid == '202209310'||$report->udid == '202210310'||$report->udid == '202211310'||$report->udid == '202212310' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',10)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313'||$report->udid == '202209313'||$report->udid == '202210313'||$report->udid == '202211313'||$report->udid == '202212313' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',13)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312'||$report->udid == '202208312'||$report->udid == '202209312'||$report->udid == '202210312'||$report->udid == '202211312'||$report->udid == '202212312' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',12)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311'||$report->udid == '202209311'||$report->udid == '202210311'||$report->udid == '202211311'||$report->udid == '202212311' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.sector_id',11)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314'||$report->udid == '202209314'||$report->udid == '202210314'||$report->udid == '202211314'||$report->udid == '202212314' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.sector_id',14)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '20220737'||$report->udid == '20220837'||$report->udid == '20220937'||$report->udid == '20221037'||$report->udid == '20221137'||$report->udid == '20221237' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.manager_id',7)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.manager_id',8)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '20220739'||$report->udid == '20220839'||$report->udid == '20220939'||$report->udid == '20221039'||$report->udid == '20221139'||$report->udid == '20221239') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.manager',9)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '20220741'||$report->udid == '20220841'||$report->udid == '20220941'||$report->udid == '20221041'||$report->udid == '20221141'||$report->udid == '20221241') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',1)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '20220742'||$report->udid == '20220842'||$report->udid == '20220942'||$report->udid == '20221042'||$report->udid == '20221142'||$report->udid == '20221242' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',2)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '20220743'||$report->udid == '20220843'||$report->udid == '20220943'||$report->udid == '20221043'||$report->udid == '20221143'||$report->udid == '20221243' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',3)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '20220744'||$report->udid == '20220844'||$report->udid == '20220944'||$report->udid == '20221044'||$report->udid == '20221144'||$report->udid == '20221244' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',4)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '20220745'||$report->udid == '20220845'||$report->udid == '20220945'||$report->udid == '20221045'||$report->udid == '20221145'||$report->udid == '20221245' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',5)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '20220746'||$report->udid == '20220846'||$report->udid == '20220946'||$report->udid == '20221046'||$report->udid == '20221146'||$report->udid == '20221246' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',6)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '20220747'||$report->udid == '20220847'||$report->udid == '20220947'||$report->udid == '20221047'||$report->udid == '20221147'||$report->udid == '20221247' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',7)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-8'||$report->udid == '2022054R-8'||$report->udid == '2022064R-8'||$report->udid == '20220748'||$report->udid == '20220848'||$report->udid == '20220948'||$report->udid == '20221048'||$report->udid == '20221148'||$report->udid == '20221248' ) {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
   
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	->where('clients.route_id',8)
	->select('total')
	->sum('total');
}
 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '20220749'||$report->udid == '20220849'||$report->udid == '20220949'||$report->udid == '20221049'||$report->udid == '20221149'||$report->udid == '20221249' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',9)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '202207410' ||$report->udid == '202208410'||$report->udid == '202209410'||$report->udid == '202210410'||$report->udid == '202211410'||$report->udid == '202212410'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',10)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '202207411'||$report->udid == '202208411'||$report->udid == '202209411'||$report->udid == '202210411'||$report->udid == '202211411'||$report->udid == '202212411') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',11)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '202207412'||$report->udid == '202208412'||$report->udid == '202209412'||$report->udid == '202210412'||$report->udid == '202211412'||$report->udid == '202212412' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',12)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '202207413'||$report->udid == '202208413'||$report->udid == '202209413'||$report->udid == '202210413'||$report->udid == '202211413'||$report->udid == '202212413' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',13)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '202207423' ||$report->udid == '202208423'||$report->udid == '202209423'||$report->udid == '202210423'||$report->udid == '202211423'||$report->udid == '202212423'  ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',23)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '202207429'||$report->udid == '202208429'||$report->udid == '202209429'||$report->udid == '202210429'||$report->udid == '202211429'||$report->udid == '202212429' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',29)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '202207415'||$report->udid == '202208415'||$report->udid == '202209415'||$report->udid == '202210415'||$report->udid == '202211415'||$report->udid == '202212415' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',15)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '202207416'||$report->udid == '202208416'||$report->udid == '202209416'||$report->udid == '202210416'||$report->udid == '202211416'||$report->udid == '202212416' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',16)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '202207417'||$report->udid == '202208417'||$report->udid == '202209417'||$report->udid == '202210417'||$report->udid == '202211417'||$report->udid == '202212417' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',17)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '202207418'||$report->udid == '202208418'||$report->udid == '202209418'||$report->udid == '202210418'||$report->udid == '202211418'||$report->udid == '202212418' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',18)
	 ->select('total')
	 ->sum('total');
 }

 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '202207424'|| $report->udid == '202208424'|| $report->udid == '202209424'|| $report->udid == '202210424'|| $report->udid == '202211424'|| $report->udid == '202212424' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',24)
	 ->select('total')
	 ->sum('total');
 }

 elseif (  $report->udid == '2022044R-31'|| $report->udid == '2022054R-31'|| $report->udid == '2022064R-31'|| $report->udid == '202207431'|| $report->udid == '202208431'|| $report->udid == '202209431'|| $report->udid == '202210431'|| $report->udid == '202211431'|| $report->udid == '202212431' ) {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	->where('clients.route_id',31)
	->select('total')
	->sum('total');
}
 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '202207433'||$report->udid == '202208433'||$report->udid == '202209433'||$report->udid == '202210433'||$report->udid == '202211433'||$report->udid == '202212433') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',33)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074205'||$report->udid == '2022084205'||$report->udid == '2022094205'||$report->udid == '2022104205'||$report->udid == '2022114205'||$report->udid == '2022124205' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',20)
	 ->where('clients.zone_id',5)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074201'||$report->udid == '2022084201'||$report->udid == '2022094201'||$report->udid == '2022104201'||$report->udid == '2022114201'||$report->udid == '2022124201' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',20)
	 ->where('clients.zone_id',1)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074203'||$report->udid == '2022084203'||$report->udid == '2022094203'||$report->udid == '2022104203'||$report->udid == '2022114203'||$report->udid == '2022124203' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',20)
	 ->where('clients.zone_id',3)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '20220742010'||$report->udid == '20220842010'||$report->udid == '20220942010'||$report->udid == '20221042010'||$report->udid == '20221142010'||$report->udid == '20221242010' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',20)
	 ->where('clients.zone_id',10)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '20220742011'|| $report->udid == '20220842011'|| $report->udid == '20220942011'|| $report->udid == '20221042011'|| $report->udid == '20221142011'|| $report->udid == '20221242011' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
	 ->where('clients.route_id',20)
	 ->where('clients.zone_id',11)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074209'|| $report->udid == '2022084209'|| $report->udid == '2022094209'|| $report->udid == '2022104209'|| $report->udid == '2022114209'|| $report->udid == '2022124209' ) {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
   
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	->where('clients.route_id',20)
	->where('clients.zone_id',9)
	->select('total')
	->sum('total');
}
 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '202207434'|| $report->udid == '202208434'|| $report->udid == '202209434'|| $report->udid == '202210434'|| $report->udid == '202211434'|| $report->udid == '202212434' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',34)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '202207435'||$report->udid == '202208435'||$report->udid == '202209435'||$report->udid == '202210435'||$report->udid == '202211435'||$report->udid == '202212435' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',35)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '202207436'||$report->udid == '202208436'||$report->udid == '202209436'||$report->udid == '202210436'||$report->udid == '202211436'||$report->udid == '202212436' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',36)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '202207437'||$report->udid == '202208437'||$report->udid == '202209437'||$report->udid == '202210437'||$report->udid == '202211437'||$report->udid == '202212437' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.route_id',37)
	 ->select('total')
	 ->sum('total');
 }
 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '202207557'||$report->udid == '202208557'||$report->udid == '202209557'||$report->udid == '202210557'||$report->udid == '202211557'||$report->udid == '202212557'  ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.channel_id',5)
	 ->where('clients.manager_id',7)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '202207567' ||$report->udid == '202208567'||$report->udid == '202209567'||$report->udid == '202210567'||$report->udid == '202211567'||$report->udid == '202212567'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.channel_id',6)
	 ->where('clients.manager_id',7)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '202207668'||$report->udid == '202208668'||$report->udid == '202209668'||$report->udid == '202210668'||$report->udid == '202211668'||$report->udid == '202212668'    ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.channel_id',6)
	 ->where('clients.manager_id',8)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '202207769'||$report->udid == '202208769'||$report->udid == '202209769'||$report->udid == '202210769'||$report->udid == '202211769'||$report->udid == '202212769' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.channel_id',6)
	 ->where('clients.manager_id',9)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '202207827'||$report->udid == '202208827'||$report->udid == '202209827'||$report->udid == '202210827'||$report->udid == '202211827'||$report->udid == '202212827' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',2)
	 ->where('clients.manager_id',7)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '202207928'||$report->udid == '202208928'||$report->udid == '202209928'||$report->udid == '202210928'||$report->udid == '202211928'||$report->udid == '202212928' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',2)
	 ->where('clients.manager_id',8)
	 ->select('total')
	 ->sum('total');
 }
 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '2022071099'||$report->udid == '2022081099'||$report->udid == '2022091099'||$report->udid == '2022101099'||$report->udid == '2022111099'||$report->udid == '2022121099') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
	 ->where('clients.sector_id',9)
	 ->where('clients.manager_id',9)
	 ->select('total')
	 ->sum('total');
 }
	 }

	
	else {
	 $soles = 0;
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

	$report->tm_ppto = $report ['tm_ppto'];
	if ( $report->tm_ppto != 0)
	$report->percentaje_tm =($report ['proy_tm']/ $report ['tm_ppto']);
	else
	{
		$report->percentaje_tm=0;
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
	$report->mb_ppto = $report ['mb_ppto'];
	if ( $report->mb_ppto != 0)
	$report->percentaje_mb =  ($report ['proy_mb']/ $report ['mb_ppto']);
	else
	{
		$report->percentaje_mb=0;
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
		            $totals_tm_ppto += $report->tm_ppto;
		            $totals_last_mb += $report->last_mb;
		            $totals_mb += $report->mb;
		            $totals_proy_mb += $report->proy_mb;
		            $totals_mb_ppto += $report->mb_ppto;
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
				$totals->tm_ppto =$totals_tm_ppto;
				$totals->percentaje_tm = '';
				$totals->last_mb = $totals_last_mb;
				$totals->mb = $totals_mb;
				$totals->proy_mb = $totals_proy_mb;
				$totals->last_percentaje_mb = '';
				$totals->mb_ppto = $totals_mb_ppto;
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
			//	$totals->dif_cx = '';
			//	$totals->dif_px = '';
			//	$totals->dif_mbu = '';
			//	$totals->ratio_ppto = '';
			//	$totals->ratio_tm = '';
			//	$totals->ratio = '';
			//	$totals->last_cost_glp = '';
				$totals->last_aprov = '';
				$totals->last_cost_glp_final = '';
			//	$totals->cost_glp = '';
			//	$totals->aprov = '';
			//	$totals->cost_glp_final = '';

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
					$sheet->setCellValue('H'.$row_number, $element->tm_ppto);
					$sheet->setCellValue('I'.$row_number, $element->percentaje_tm);
					$sheet->setCellValue('J'.$row_number, $element->last_mb);
					$sheet->setCellValue('K'.$row_number, $element->mb);
					$sheet->setCellValue('L'.$row_number, $element->proy_mb);
					$sheet->setCellValue('M'.$row_number, $element->last_percentaje_mb);
					$sheet->setCellValue('N'.$row_number, $element->mb_ppto);
					$sheet->setCellValue('O'.$row_number, $element->percentaje_mb);
					$sheet->setCellValue('P'.$row_number, $element->last_total);
					$sheet->setCellValue('Q'.$row_number, $element->total);
					$sheet->setCellValue('R'.$row_number, $element->proy_soles);
					$sheet->setCellValue('S'.$row_number, $element->percentaje_soles);
					$sheet->setCellValue('T'.$row_number, $element->costo_ma);
					$sheet->setCellValue('U'.$row_number, $element->px_ma);
					$sheet->setCellValue('V'.$row_number, $element->mbu_ma);
					$sheet->setCellValue('W'.$row_number, $element->costo);
					$sheet->setCellValue('X'.$row_number, $element->px);
					$sheet->setCellValue('Y'.$row_number, $element->mbu);
				//	$sheet->setCellValue('Z'.$row_number, $element->dif_cx);
				//	$sheet->setCellValue('AA'.$row_number, $element->dif_px);
				//	$sheet->setCellValue('AB'.$row_number, $element->dif_mbu);
				//	$sheet->setCellValue('AC'.$row_number, $element->ratio_ppto);
				//	$sheet->setCellValue('AD'.$row_number, $element->ratio_tm);
				//	$sheet->setCellValue('AE'.$row_number, $element->ratio);
				//	$sheet->setCellValue('AF'.$row_number, $element->last_cost_glp);
					$sheet->setCellValue('AG'.$row_number, $element->last_aprov);
					$sheet->setCellValue('AH'.$row_number, $element->last_cost_glp_final);
				//	$sheet->setCellValue('AI'.$row_number, $element->cost_glp);
				//	$sheet->setCellValue('AJ'.$row_number, $element->aprov);
				//	$sheet->setCellValue('AK'.$row_number, $element->cost_glp_final);
					
                    $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
					$sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
					$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
					$sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
					$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0,0');
					$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.00'.'%');
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

					if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011' ||$report->udid == '20221111'||$report->udid == '20221211') {
						$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
						->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
						->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
						->where('sales.sale_date', '>=', $mes_date)
						->where('sales.sale_date', '<=', $initial_date)
						->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
						->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
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
						->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
						->where('clients.business_unit_id', 2)
						->select('kg')
						->sum('kg');
					} 
	
					elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021' ||$report->udid == '20221121'||$report->udid == '20221221') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '1')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ||$report->udid == '20221122'||$report->udid == '20221222') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '2')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif ( $report->udid == '202210211' ||$report->udid == '202211211' ||$report->udid == '202212211') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.manager_id', '11')
					->select('kg')
					->sum('kg');
				} 
				 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ||$report->udid == '20221123' ||$report->udid == '20221223') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258, 14258])
					 ->where('clients.manager_id', '3')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ||$report->udid == '20221124'||$report->udid == '20221224' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '4')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025'||$report->udid == '20221125'||$report->udid == '20221225' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '5')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026'||$report->udid == '20221126'||$report->udid == '20221226' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '6')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif ( $report->udid == '202210212'||$report->udid == '202211212'||$report->udid == '202212212' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.manager_id', '12')
					->select('kg')
					->sum('kg');
				} 
				 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027'||$report->udid == '20221127'||$report->udid == '20221227') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '7')
					 ->select('kg')
					 ->sum('kg');
				 } 
				 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028'|| $report->udid == '20221128'|| $report->udid == '20221228') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id', '8')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'|| $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029' || $report->udid == '20221129' || $report->udid == '20221229'  ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					 ->where('clients.manager_id', '9')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310' ||$report->udid == '202209310' ||$report->udid == '202210310'||$report->udid == '202211310'||$report->udid == '202212310' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					 ->where('clients.sector_id',10)
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ||$report->udid == '202209313' ||$report->udid == '202210313'||$report->udid == '202211313' ||$report->udid == '202212313') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id',13)
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ||$report->udid == '202208312' ||$report->udid == '202209312'||$report->udid == '202210312' ||$report->udid == '202211312'||$report->udid == '202212312' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id',12)
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ||$report->udid == '202209311' ||$report->udid == '202210311' ||$report->udid == '202211311'||$report->udid == '202212311' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id',11)
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314' ||$report->udid == '202209314' ||$report->udid == '202210314' ||$report->udid == '202211314' ||$report->udid == '202212314' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id',14)
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ||$report->udid == '20221137' ||$report->udid == '20221237' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id','7')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id','8')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '20220739'||$report->udid == '20220839'||$report->udid == '20220939'||$report->udid == '20221039'||$report->udid == '20221139'||$report->udid == '20221239' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.manager_id','9')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '20220741'||$report->udid == '20220841'||$report->udid == '20220941'||$report->udid == '20221041'||$report->udid == '20221141'||$report->udid == '20221241') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','1')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '20220742'||$report->udid == '20220842'||$report->udid == '20220942'||$report->udid == '20221042'||$report->udid == '20221142'||$report->udid == '20221242' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','2')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '20220743'||$report->udid == '20220843'||$report->udid == '20220943'||$report->udid == '20221043'||$report->udid == '20221143'||$report->udid == '20221243' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','3')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '20220744'||$report->udid == '20220844'||$report->udid == '20220944'||$report->udid == '20221044'||$report->udid == '20221144'||$report->udid == '20221244' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','4')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '20220745'||$report->udid == '20220845'||$report->udid == '20220945'||$report->udid == '20221045'||$report->udid == '20221145'||$report->udid == '20221245' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','5')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '20220746'||$report->udid == '20220846'||$report->udid == '20220946'||$report->udid == '20221046'||$report->udid == '20221146'||$report->udid == '20221246' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','6')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '20220747'||$report->udid == '20220847'||$report->udid == '20220947'||$report->udid == '20221047'||$report->udid == '20221147'||$report->udid == '20221247' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','7')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-8'||$report->udid == '2022054R-8'||$report->udid == '2022064R-8'||$report->udid == '20220748'||$report->udid == '20220848'||$report->udid == '20220948'||$report->udid == '20221048'||$report->udid == '20221148'||$report->udid == '20221248' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.route_id','8')
					->select('kg')
					->sum('kg');
				}
				 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '20220749'||$report->udid == '20220849'||$report->udid == '20220949'||$report->udid == '20221049'||$report->udid == '20221149'||$report->udid == '20221249' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','9')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '202207410' ||$report->udid == '202208410'||$report->udid == '202209410'||$report->udid == '202210410'||$report->udid == '202211410'||$report->udid == '202212410' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','10')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '202207411'||$report->udid == '202208411'||$report->udid == '202209411'||$report->udid == '202210411'||$report->udid == '202211411'||$report->udid == '202212411') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','11')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '202207412'||$report->udid == '202208412'||$report->udid == '202209412'||$report->udid == '202210412'||$report->udid == '202211412'||$report->udid == '202212412' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','12')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '202207413'||$report->udid == '202208413'||$report->udid == '202209413'||$report->udid == '202210413'||$report->udid == '202211413'||$report->udid == '202212413' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','13')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '202207423' ||$report->udid == '202208423'||$report->udid == '202209423'||$report->udid == '202210423'||$report->udid == '202211423'||$report->udid == '202212423'  ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','23')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '202207429'||$report->udid == '202208429'||$report->udid == '202209429'||$report->udid == '202210429'||$report->udid == '202211429'||$report->udid == '202212429' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','29')
					 ->select('kg')
					 ->sum('kg');
				 }
	
				 elseif (  $report->udid == '2022044R-31'||$report->udid == '2022054R-31'||$report->udid == '2022064R-31'||$report->udid == '202207431'||$report->udid == '202208431'||$report->udid == '202209431'||$report->udid == '202210431'||$report->udid == '202211431'||$report->udid == '202212431' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.route_id','31')
					->select('kg')
					->sum('kg');
				}
				 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '202207415'||$report->udid == '202208415'||$report->udid == '202209415'||$report->udid == '202210415'||$report->udid == '202211415'||$report->udid == '202212415' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','15')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '202207416'||$report->udid == '202208416'||$report->udid == '202209416'||$report->udid == '202210416'||$report->udid == '202211416'||$report->udid == '202212416' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','16')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '202207417'||$report->udid == '202208417'||$report->udid == '202209417'||$report->udid == '202210417'||$report->udid == '202211417'||$report->udid == '202212417' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','17')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '202207418'||$report->udid == '202208418'||$report->udid == '202209418'||$report->udid == '202210418'||$report->udid == '202211418'||$report->udid == '202212418' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','18')
					 ->select('kg')
					 ->sum('kg');
				 }
	
				 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '202207424'|| $report->udid == '202208424'|| $report->udid == '202209424'|| $report->udid == '202210424'|| $report->udid == '202211424'|| $report->udid == '202212424' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','24')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '202207433'||$report->udid == '202208433'||$report->udid == '202208433'||$report->udid == '202209433'||$report->udid == '202210433'||$report->udid == '202211433'||$report->udid == '202212433') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','33')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074205'||$report->udid == '2022084205'||$report->udid == '2022094205'||$report->udid == '2022104205'||$report->udid == '2022114205'||$report->udid == '2022124205' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','20')
					 ->where('clients.zone_id','5')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074201'||$report->udid == '2022084201'||$report->udid == '2022094201'||$report->udid == '2022104201'||$report->udid == '2022114201'||$report->udid == '2022124201' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','20')
					 ->where('clients.zone_id','1')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074203'||$report->udid == '2022084203'||$report->udid == '2022094203'||$report->udid == '2022104203'||$report->udid == '2022114203'||$report->udid == '2022124203' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','20')
					 ->where('clients.zone_id','3')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '20220742010'||$report->udid == '20220842010'||$report->udid == '20220942010'||$report->udid == '20221042010'||$report->udid == '20221142010'||$report->udid == '20221242010' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','20')
					 ->where('clients.zone_id','10')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '20220742011'|| $report->udid == '20220842011'|| $report->udid == '20220942011'|| $report->udid == '20221042011'|| $report->udid == '20221142011'|| $report->udid == '20221242011' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','20')
					 ->where('clients.zone_id','11')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074209'|| $report->udid == '2022084209'|| $report->udid == '2022094209'|| $report->udid == '2022104209'|| $report->udid == '2022114209'|| $report->udid == '2022124209' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.route_id','20')
					->where('clients.zone_id','9')
					->select('kg')
					->sum('kg');
				}
				 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '202207434'|| $report->udid == '202208434'|| $report->udid == '202209434'|| $report->udid == '202210434'|| $report->udid == '202211434'|| $report->udid == '202212434' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','34')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '202207435'||$report->udid == '202208435'||$report->udid == '202209435'||$report->udid == '202210435'||$report->udid == '202211435'||$report->udid == '202212435' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','35')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '202207436'||$report->udid == '202208436'||$report->udid == '202209436'||$report->udid == '202210436'||$report->udid == '202211436'||$report->udid == '202212436' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.route_id','36')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '202207437'||$report->udid == '202208437'||$report->udid == '202209437'||$report->udid == '202210437'||$report->udid == '202211437'||$report->udid == '202212437' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					 ->where('clients.route_id','37')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022065Massiel AlorPRIMARIO'||$report->udid == '202207557'||$report->udid == '202208557'||$report->udid == '202209557'||$report->udid == '202210557'||$report->udid == '202211557'||$report->udid == '202212557'  ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					 ->where('clients.channel_id','5')
					 ->where('clients.manager_id','7')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '202207567' ||$report->udid == '202208567'||$report->udid == '202209567'||$report->udid == '202210567'||$report->udid == '202211567'||$report->udid == '202212567'   ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.channel_id','6')
					 ->where('clients.manager_id','7')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022046Omar CasachaguaPRIMARIO'||$report->udid == '2022056Omar CasachaguaPRIMARIO'||$report->udid == '2022066Omar CasachaguaPRIMARIO'||$report->udid == '202207658'||$report->udid == '202208658'||$report->udid == '202209658'||$report->udid == '202210658'||$report->udid == '202211658'||$report->udid == '202212658') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					->where('clients.channel_id','5')
					->where('clients.manager_id','8')
					->select('kg')
					->sum('kg');
				}
				 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '202207668'||$report->udid == '202208668'||$report->udid == '202209668'||$report->udid == '202210668'||$report->udid == '202211668'||$report->udid == '202212668' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.channel_id','6')
					 ->where('clients.manager_id','8')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '202207769'||$report->udid == '202208769'||$report->udid == '202209769'||$report->udid == '202210769'||$report->udid == '202211769'||$report->udid == '202212769' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.channel_id','6')
					 ->where('clients.manager_id','9')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '202207827'||$report->udid == '202208827'||$report->udid == '202209827'||$report->udid == '202210827'||$report->udid == '202211827'||$report->udid == '202212827' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
					 ->where('clients.sector_id','2')
					 ->where('clients.manager_id','7')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '202207928'||$report->udid == '202208928'||$report->udid == '202209928'||$report->udid == '202210928'||$report->udid == '202211928'||$report->udid == '202212928' ) {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id','2')
					 ->where('clients.manager_id','8')
					 ->select('kg')
					 ->sum('kg');
				 }
				 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '2022071099'||$report->udid == '2022081099'||$report->udid == '2022091099'||$report->udid == '2022101099'||$report->udid == '2022111099'||$report->udid == '2022121099') {
					 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					 ->where('sales.sale_date', '>=', $mes_date)
					 ->where('sales.sale_date', '<=', $initial_date)
					 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
					 ->where('clients.sector_id','9')
					 ->where('clients.manager_id','9')
					 ->select('kg')
					 ->sum('kg');
				 }
					 }
	
					
					else {
						$tm_fin = 0;
					}
	
	// <<--------------------------------------------------------------CALCULO DE SOLES EXCEL-------------------------------------------------------------------------------->>
	
	
	
	if ( $report->year == '2022'){
	
		if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011'||$report->udid == '20221111'||$report->udid == '20221211') {
			$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->where('sales.sale_date', '>=', $mes_date)
			->where('sales.sale_date', '<=', $initial_date)
			->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258 ])
			->where('clients.business_unit_id', 1)
			->select('total')
			->sum('total');
		}
	
			elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'||$report->udid == '20220812'||$report->udid == '20220912'||$report->udid == '20221012'||$report->udid == '20221112'||$report->udid == '20221212'  ) {
			 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->where('sales.sale_date', '>=', $mes_date)
			->where('sales.sale_date', '<=', $initial_date)
			->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
			->where('clients.business_unit_id', 2)
			->select('total')
			->sum('total');
		} 
	
		elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021'||$report->udid == '20221121'||$report->udid == '20221221') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 1)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022'||$report->udid == '20221122'||$report->udid == '20221222' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 2)
		 ->select('total')
		 ->sum('total');
	 } 
	
	 elseif ( $report->udid == '202210211' ||$report->udid == '202211211' ||$report->udid == '202212211') {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.manager_id', 11)
		->select('total')
		->sum('total');
	} 
	 
	
	 
	 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023'||$report->udid == '20221123'||$report->udid == '20221223' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 3)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '20220724'||$report->udid == '20220824'||$report->udid == '20220924'||$report->udid == '20221024'||$report->udid == '20221124'||$report->udid == '20221224' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 4)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '20220725'||$report->udid == '20220825'||$report->udid == '20220925'||$report->udid == '20221025'||$report->udid == '20221125'||$report->udid == '20221225' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 5)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '20220726'||$report->udid == '20220826'||$report->udid == '20220926'||$report->udid == '20221026'||$report->udid == '20221126'||$report->udid == '20221226'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 6)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif ($report->udid == '202210212'||$report->udid == '202211212'||$report->udid == '202212212' ) {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	   
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.manager_id', 12)
		->select('total')
		->sum('total');
	} 
	 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927'||$report->udid == '20221027'||$report->udid == '20221127'||$report->udid == '20221227' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 7)
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '20220728'||$report->udid == '20220828'||$report->udid == '20220928'||$report->udid == '20221028'||$report->udid == '20221128'||$report->udid == '20221228') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 8)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'||$report->udid == '20220729'||$report->udid == '20220829'||$report->udid == '20220929'||$report->udid == '20221029'||$report->udid == '20221129'||$report->udid == '20221229' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.manager_id', 9)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310'||$report->udid == '202208310'||$report->udid == '202209310'||$report->udid == '202210310'||$report->udid == '202211310'||$report->udid == '202212310' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',10)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313'||$report->udid == '202209313'||$report->udid == '202210313'||$report->udid == '202211313'||$report->udid == '202212313' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',13)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312'||$report->udid == '202208312'||$report->udid == '202209312'||$report->udid == '202210312'||$report->udid == '202211312'||$report->udid == '202212312' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',12)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311'||$report->udid == '202209311'||$report->udid == '202210311'||$report->udid == '202211311'||$report->udid == '202212311' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.sector_id',11)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314'||$report->udid == '202209314'||$report->udid == '202210314'||$report->udid == '202211314'||$report->udid == '202212314' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.sector_id',14)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '20220737'||$report->udid == '20220837'||$report->udid == '20220937'||$report->udid == '20221037'||$report->udid == '20221137'||$report->udid == '20221237' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.manager_id',7)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '20220738'||$report->udid == '20220838'||$report->udid == '20220938'||$report->udid == '20221038'||$report->udid == '20221138'||$report->udid == '20221238' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.manager_id',8)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '20220739'||$report->udid == '20220839'||$report->udid == '20220939'||$report->udid == '20221039'||$report->udid == '20221139'||$report->udid == '20221239') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.manager',9)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '20220741'||$report->udid == '20220841'||$report->udid == '20220941'||$report->udid == '20221041'||$report->udid == '20221141'||$report->udid == '20221241') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',1)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '20220742'||$report->udid == '20220842'||$report->udid == '20220942'||$report->udid == '20221042'||$report->udid == '20221142'||$report->udid == '20221242' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',2)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '20220743'||$report->udid == '20220843'||$report->udid == '20220943'||$report->udid == '20221043'||$report->udid == '20221143'||$report->udid == '20221243' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',3)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '20220744'||$report->udid == '20220844'||$report->udid == '20220944'||$report->udid == '20221044'||$report->udid == '20221144'||$report->udid == '20221244' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')	
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',4)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '20220745'||$report->udid == '20220845'||$report->udid == '20220945'||$report->udid == '20221045'||$report->udid == '20221145'||$report->udid == '20221245' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',5)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '20220746'||$report->udid == '20220846'||$report->udid == '20220946'||$report->udid == '20221046'||$report->udid == '20221146'||$report->udid == '20221246' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',6)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '20220747'||$report->udid == '20220847'||$report->udid == '20220947'||$report->udid == '20221047'||$report->udid == '20221147'||$report->udid == '20221247' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',7)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-8'||$report->udid == '2022054R-8'||$report->udid == '2022064R-8'||$report->udid == '20220748'||$report->udid == '20220848'||$report->udid == '20220948'||$report->udid == '20221048'||$report->udid == '20221148'||$report->udid == '20221248' ) {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	   
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.route_id',8)
		->select('total')
		->sum('total');
	}
	 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '20220749'||$report->udid == '20220849'||$report->udid == '20220949'||$report->udid == '20221049'||$report->udid == '20221149'||$report->udid == '20221249' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',9)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '202207410' ||$report->udid == '202208410'||$report->udid == '202209410'||$report->udid == '202210410'||$report->udid == '202211410'||$report->udid == '202212410'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',10)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '202207411'||$report->udid == '202208411'||$report->udid == '202209411'||$report->udid == '202210411'||$report->udid == '202211411'||$report->udid == '202212411') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',11)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '202207412'||$report->udid == '202208412'||$report->udid == '202209412'||$report->udid == '202210412'||$report->udid == '202211412'||$report->udid == '202212412' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',12)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '202207413'||$report->udid == '202208413'||$report->udid == '202209413'||$report->udid == '202210413'||$report->udid == '202211413'||$report->udid == '202212413' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',13)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '202207423' ||$report->udid == '202208423'||$report->udid == '202209423'||$report->udid == '202210423'||$report->udid == '202211423'||$report->udid == '202212423'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',23)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '202207429'||$report->udid == '202208429'||$report->udid == '202209429'||$report->udid == '202210429'||$report->udid == '202211429'||$report->udid == '202212429' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',29)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '202207415'||$report->udid == '202208415'||$report->udid == '202209415'||$report->udid == '202210415'||$report->udid == '202211415'||$report->udid == '202212415' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',15)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '202207416'||$report->udid == '202208416'||$report->udid == '202209416'||$report->udid == '202210416'||$report->udid == '202211416'||$report->udid == '202212416' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',16)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '202207417'||$report->udid == '202208417'||$report->udid == '202209417'||$report->udid == '202210417'||$report->udid == '202211417'||$report->udid == '202212417' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',17)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '202207418'||$report->udid == '202208418'||$report->udid == '202209418'||$report->udid == '202210418'||$report->udid == '202211418'||$report->udid == '202212418' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',18)
		 ->select('total')
		 ->sum('total');
	 }
	
	 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '202207424'|| $report->udid == '202208424'|| $report->udid == '202209424'|| $report->udid == '202210424'|| $report->udid == '202211424'|| $report->udid == '202212424' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',24)
		 ->select('total')
		 ->sum('total');
	 }
	
	 elseif (  $report->udid == '2022044R-31'|| $report->udid == '2022054R-31'|| $report->udid == '2022064R-31'|| $report->udid == '202207431'|| $report->udid == '202208431'|| $report->udid == '202209431'|| $report->udid == '202210431'|| $report->udid == '202211431'|| $report->udid == '202212431' ) {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.route_id',31)
		->select('total')
		->sum('total');
	}
	 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '202207433'||$report->udid == '202208433'||$report->udid == '202209433'||$report->udid == '202210433'||$report->udid == '202211433'||$report->udid == '202212433') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',33)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074205'||$report->udid == '2022084205'||$report->udid == '2022094205'||$report->udid == '2022104205'||$report->udid == '2022114205'||$report->udid == '2022124205' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',20)
		 ->where('clients.zone_id',5)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074201'||$report->udid == '2022084201'||$report->udid == '2022094201'||$report->udid == '2022104201'||$report->udid == '2022114201'||$report->udid == '2022124201' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',20)
		 ->where('clients.zone_id',1)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074203'||$report->udid == '2022084203'||$report->udid == '2022094203'||$report->udid == '2022104203'||$report->udid == '2022114203'||$report->udid == '2022124203' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',20)
		 ->where('clients.zone_id',3)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '20220742010'||$report->udid == '20220842010'||$report->udid == '20220942010'||$report->udid == '20221042010'||$report->udid == '20221142010'||$report->udid == '20221242010' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',20)
		 ->where('clients.zone_id',10)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '20220742011'|| $report->udid == '20220842011'|| $report->udid == '20220942011'|| $report->udid == '20221042011'|| $report->udid == '20221142011'|| $report->udid == '20221242011' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
		 ->where('clients.route_id',20)
		 ->where('clients.zone_id',11)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074209'|| $report->udid == '2022084209'|| $report->udid == '2022094209'|| $report->udid == '2022104209'|| $report->udid == '2022114209'|| $report->udid == '2022124209' ) {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	   
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		->where('clients.route_id',20)
		->where('clients.zone_id',9)
		->select('total')
		->sum('total');
	}
	 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '202207434'|| $report->udid == '202208434'|| $report->udid == '202209434'|| $report->udid == '202210434'|| $report->udid == '202211434'|| $report->udid == '202212434' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',34)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '202207435'||$report->udid == '202208435'||$report->udid == '202209435'||$report->udid == '202210435'||$report->udid == '202211435'||$report->udid == '202212435' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',35)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '202207436'||$report->udid == '202208436'||$report->udid == '202209436'||$report->udid == '202210436'||$report->udid == '202211436'||$report->udid == '202212436' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',36)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '202207437'||$report->udid == '202208437'||$report->udid == '202209437'||$report->udid == '202210437'||$report->udid == '202211437'||$report->udid == '202212437' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.route_id',37)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '202207557'||$report->udid == '202208557'||$report->udid == '202209557'||$report->udid == '202210557'||$report->udid == '202211557'||$report->udid == '202212557'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.channel_id',5)
		 ->where('clients.manager_id',7)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '202207567' ||$report->udid == '202208567'||$report->udid == '202209567'||$report->udid == '202210567'||$report->udid == '202211567'||$report->udid == '202212567'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.channel_id',6)
		 ->where('clients.manager_id',7)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '202207668'||$report->udid == '202208668'||$report->udid == '202209668'||$report->udid == '202210668'||$report->udid == '202211668'||$report->udid == '202212668'    ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.channel_id',6)
		 ->where('clients.manager_id',8)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '202207769'||$report->udid == '202208769'||$report->udid == '202209769'||$report->udid == '202210769'||$report->udid == '202211769'||$report->udid == '202212769' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.channel_id',6)
		 ->where('clients.manager_id',9)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '202207827'||$report->udid == '202208827'||$report->udid == '202209827'||$report->udid == '202210827'||$report->udid == '202211827'||$report->udid == '202212827' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',2)
		 ->where('clients.manager_id',7)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '202207928'||$report->udid == '202208928'||$report->udid == '202209928'||$report->udid == '202210928'||$report->udid == '202211928'||$report->udid == '202212928' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',2)
		 ->where('clients.manager_id',8)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '2022071099'||$report->udid == '2022081099'||$report->udid == '2022091099'||$report->udid == '2022101099'||$report->udid == '2022111099'||$report->udid == '2022121099') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
		 ->where('clients.sector_id',9)
		 ->where('clients.manager_id',9)
		 ->select('total')
		 ->sum('total');
	 }
		 }
	
		
		else {
		 $soles = 0;
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

	$report->tm_ppto = $report ['tm_ppto'];
	if ( $report->tm_ppto != 0)
	$report->percentaje_tm =($report ['proy_tm']/ $report ['tm_ppto']);
	else
	{
	$report->percentaje_tm=0;
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
	$report->mb_ppto = $report ['mb_ppto'];
	if ( $report->mb_ppto != 0)
	$report->percentaje_mb =  ($report ['proy_mb']/ $report ['mb_ppto']);
	else
	{
	$report->percentaje_mb=0;
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
		            $totals_tm_ppto += $report->tm_ppto;
		            $totals_last_mb += $report->last_mb;
		            $totals_mb += $report->mb;
		            $totals_proy_mb += $report->proy_mb;
		            $totals_mb_ppto += $report->mb_ppto;
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
			$totals->tm_ppto = '';
			$totals->percentaje_tm = '';
			$totals->last_mb = '';
			$totals->last_percentaje_mb = '';
			$totals->mb_ppto = '';
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






