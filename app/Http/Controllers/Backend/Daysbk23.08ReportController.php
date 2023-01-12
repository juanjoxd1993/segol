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
			
		];

		$rules = [
			'initial_date'	=> 'required',
			
		];

		request()->validate($rules, $messages);
		return request()->all();
	}


	public function list() {
		$export = request('export');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
		$mes_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-01');
		$result = AcRep::leftjoin('cons_reports', 'ac_reps.udid', '=', 'cons_reports.udid')
		->leftjoin('cglp_finals', 'ac_reps.fecha_consulta', '=', 'cglp_finals.fecha_consulta')
		->where('ac_reps.fecha_consulta', '=', $initial_date)
		->select(
			'ac_reps.udid as udid',
			'ac_reps.fecha_consulta as sale_date',
			'ac_reps.año as year',
			'ac_reps.cost_glp',
			'cglp_finals.aventajada as aprov',
			'ac_reps.mes as month',
			'ac_reps.dia as day',
			'ac_reps.channel_name as client_channel_name',
			'ac_reps.name as name',
			'ac_reps.ac_so as total',
			'cons_reports.soles as last_total',
			'cons_reports.tm as last_tm',
			'cons_reports.tm_ppto as tm_ppto',
			 'cons_reports.mb_ppto as mb_ppto',
			 'cons_reports.mb as last_mb',
			 'cons_reports.dias as dias_mes',
			 'ac_reps.sale_option as option'
		 )

		 ->orderBy('ac_reps.sale_option')
		 ->orderBy('ac_reps.orden')		 
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
			$sheet->mergeCells('A1:AK1');
			
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
				$row_number++;

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
				$sheet->setCellValue('Z' . $row_number, 'DIF CX');
	            $sheet->setCellValue('AA' . $row_number, 'DIF PX');
	            $sheet->setCellValue('AB' . $row_number, 'DIF MBU');
	            $sheet->setCellValue('AC' . $row_number, 'RATIO PPTO');
				$sheet->setCellValue('AD' . $row_number, 'RATIO TM');
				$sheet->setCellValue('AE' . $row_number, 'RATIO %');
				$sheet->setCellValue('AF' . $row_number, 'C.Glp M_A');
				$sheet->setCellValue('AG' . $row_number, 'C.Aprov.M_A');
				$sheet->setCellValue('AH' . $row_number, 'C. Total M_A');
				$sheet->setCellValue('AI' . $row_number, 'C.Glp');
				$sheet->setCellValue('AJ' . $row_number, 'C.Aprov');
				$sheet->setCellValue('AK' . $row_number, 'C. Total');




				$sheet->getStyle('A' . $row_number . ':AK' . $row_number)->applyFromArray([
					'font' => ['bold' => true],
				]);


		
			
			$sheet->getStyle('A'. $row_number . ':F' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '00008B')
				]
			]);
			$sheet->getStyle('G'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);
			$sheet->getStyle('H'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '00008B')
				]
			]);

			$sheet->getStyle('I'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);

			$sheet->getStyle('J'. $row_number . ':L' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '00008B')
				]
			]);

		
			$sheet->getStyle('M'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);

			
			$sheet->getStyle('N'. $row_number )->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '00008B')
				]
			]);

			$sheet->getStyle('O'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);

			$sheet->getStyle('P'. $row_number . ':R' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '00008B')
				]
			]);

			$sheet->getStyle('S'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);

			$sheet->getStyle('T'. $row_number . ':V' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '7CFC00')
				]
			]);

			$sheet->getStyle('W'. $row_number . ':Y' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '8000')
				]
			]);

			$sheet->getStyle('Z'. $row_number . ':AB' . $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'DC143C')
				]
			]);

			$sheet->getStyle('AC'. $row_number)->applyFromArray([
				'font' => [
					'color' => array('rgb' => 'FFFFFF'),
				//	'bold' => true,
					'size' => 12,
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
				//	'bold' => true,
					'size' => 12,
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
				//	'bold' => true,
					'size' => 12,
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
				//	'bold' => true,
					'size' => 12,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '4B0082')
				]
			]);


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

				if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811'||$report->udid == '20220911'||$report->udid == '20221011') {
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
		
					elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'||$report->udid == '20220812'||$report->udid == '20220912' ||$report->udid == '20221012') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.business_unit_id', 2)
					->select('kg')
					->sum('kg');
				} 

				elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '20220721' ||$report->udid == '20220821'||$report->udid == '20220921'||$report->udid == '20221021') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '1')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '20220722'||$report->udid == '20220822'||$report->udid == '20220922'||$report->udid == '20221022' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '2')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '20220723'||$report->udid == '20220823'||$report->udid == '20220923'||$report->udid == '20221023' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '3')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '20220724' ||$report->udid == '20220824' ||$report->udid == '20220924' ||$report->udid == '20221024' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '4')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '20220725'||$report->udid == '20220825' ||$report->udid == '20220925'||$report->udid == '20221025' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '5')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '20220726'||$report->udid == '20220826' ||$report->udid == '20220926' ||$report->udid == '20221026' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '6')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '20220727'||$report->udid == '20220827'||$report->udid == '20220927' ||$report->udid == '20221027') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '7')
				 ->select('kg')
				 ->sum('kg');
			 } 
			 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '20220728' || $report->udid == '20220828' || $report->udid == '20220928' || $report->udid == '20221028') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'|| $report->udid == '20220729' || $report->udid == '20220829' || $report->udid == '20220929' || $report->udid == '20221029'   ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id', '9')
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
				 ->where('clients.sector_id',10)
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
				 ->where('clients.sector_id',13)
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
				 ->where('clients.sector_id',12)
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
				 ->where('clients.sector_id',11)
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
				 ->where('clients.sector_id',14)
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '20220737' ||$report->udid == '20220837' ||$report->udid == '20220937' ||$report->udid == '20221037' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '20220738' ||$report->udid == '20220838'||$report->udid == '20220738'||$report->udid == '20220738') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '2022073OFICINA PLANTA'    ) {
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
			 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '2022074R-1'||$report->udid == '2022084R-1' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','1')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '2022074R-2'||$report->udid == '2022084R-2' ||$report->udid == '20220942'||$report->udid == '2022084R-2') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','2')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '2022074R-3' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','3')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '2022074R-4'||$report->udid == '2022084R-4' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','4')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '2022074R-5'||$report->udid == '2022084R-5' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','5')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '2022074R-6'||$report->udid == '2022084R-6' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','6')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '2022074R-7'||$report->udid == '2022084R-7' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '2022074R-9'||$report->udid == '2022084R-9' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '2022074R-10' ||$report->udid == '2022084R-10'   ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','10')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '2022074R-11'||$report->udid == '2022084R-11') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','11')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '2022074R-12'||$report->udid == '2022084R-12' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','12')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '2022074R-13'||$report->udid == '2022084R-13' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','13')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '2022074R-23' ||$report->udid == '2022084R-23'  ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','23')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '2022074R-29'||$report->udid == '2022084R-29' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','29')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '2022074R-15' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','15')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '2022074R-16'||$report->udid == '2022084R-16' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','16')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '2022074R-17'||$report->udid == '2022084R-17' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','17')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '2022074R-18'||$report->udid == '2022084R-18' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','18')
				 ->select('kg')
				 ->sum('kg');
			 }

			 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '2022074R-24'|| $report->udid == '2022084R-24' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','24')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '2022074R-33'||$report->udid == '2022084R-33') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','33')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074205'||$report->udid == '2022084205' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','5')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074CALLAO'||$report->udid == '2022084CALLAO' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','1')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074LIMA ESTE'||$report->udid == '2022084LIMA ESTE' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','3')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '2022074NORTE'||$report->udid == '2022084NORTE' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','10')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '2022074SUR'|| $report->udid == '2022084SUR' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','20')
				 ->where('clients.zone_id','11')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074CENTRO'|| $report->udid == '2022084CENTRO' ) {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('clients.route_id','20')
				->where('clients.zone_id','9')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '202207434'|| $report->udid == '2022084VDD-1' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','34')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '2022074VDD-2'||$report->udid == '2022084VDD-2' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','35')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '2022074VDD-6'||$report->udid == '2022084VDD-6' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','36')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '2022074VDD-7'||$report->udid == '2022084VDD-7' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','37')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022065Massiel AlorPRIMARIO'||$report->udid == '202207557'||$report->udid == '202208557'  ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id','5')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '2022075Massiel AlorCONVENCIONAL' ||$report->udid == '2022085Massiel AlorCONVENCIONAL'   ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022046Omar CasachaguaPRIMARIO'||$report->udid == '2022056Omar CasachaguaPRIMARIO'||$report->udid == '2022066Omar CasachaguaPRIMARIO'||$report->udid == '2022076Omar CasachaguaPRIMARIO') {
				$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->where('sales.sale_date', '>=', $mes_date)
				->where('sales.sale_date', '<=', $initial_date)
				->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('clients.channel_id','5')
				->where('clients.manager_id','8')
				->select('kg')
				->sum('kg');
			}
			 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '2022076Omar CasachaguaCONVENCIONAL'    ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '2022077CONVENCIONAL' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.channel_id','6')
				 ->where('clients.manager_id','9')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '2022078AUTOMOCION'||$report->udid == '2022088AUTOMOCION' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.sector_id','2')
				 ->where('clients.manager_id','7')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '2022079AUTOMOCION'||$report->udid == '2022089AUTOMOCION' ) {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.sector_id','2')
				 ->where('clients.manager_id','8')
				 ->select('kg')
				 ->sum('kg');
			 }
			 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '20220710MINERIA'||$report->udid == '20220810MINERIA') {
				 $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '>=', $mes_date)
				 ->where('sales.sale_date', '<=', $initial_date)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.sector_id','9')
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

	if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711') {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.business_unit_id', 1)
		->select('total')
		->sum('total');
	}

		elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.business_unit_id', 2)
		->select('total')
		->sum('total');
	} 

	elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '2022072Percy Velaochaga' ||$report->udid == '2022082Percy Velaochaga') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager_id', 1)
	 ->select('total')
	 ->sum('total');
 } 
 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '2022072Cesar Coronado' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager_id', 2)
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '2022072Mauricio Diaz' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Mauricio Diaz')
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '2022072Anthony Valencia' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Anthony Valencia')
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '2022072Yimmy León'||$report->udid == '2022082Yimmy León' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Yimmy León')
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '2022072Genesis Adreani'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Genesis Adreani')
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '2022072Massiel Alor' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Massiel Alor')
	 ->select('total')
	 ->sum('total');
 } 
 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '2022072Omar Casachahua') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'Omar Casachahua')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'||$report->udid == '2022072OFICINA PLANTA' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager', 'OFICINA PLANTA')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id',10)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id',13)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id',12)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id',11)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314'     ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id',14)
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '2022073Massiel Alor' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager','Massiel Alor')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '2022073Omar Casachahua' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager','Omar Casachahua')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '2022073OFICINA PLANTA'    ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.manager','OFICINA PLANTA')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '2022074R-1'||$report->udid == '2022084R-1' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','1')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '2022074R-2'||$report->udid == '2022084R-2' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')

	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','2')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '2022074R-3' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','3')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '2022074R-4'||$report->udid == '2022084R-4' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','4')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '2022084R-5' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','5')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '2022074R-6'||$report->udid == '2022084R-6' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','6')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '2022074R-7'||$report->udid == '2022084R-7' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','7')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '2022074R-9'||$report->udid == '2022084R-9' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','9')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '2022074R-10' ||$report->udid == '2022084R-10'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','10')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '2022074R-11'||$report->udid == '2022084R-11') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','11')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '2022074R-12'||$report->udid == '2022084R-12' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','12')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '2022074R-13'||$report->udid == '2022084R-13' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','13')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '2022074R-23' ||$report->udid == '2022084R-23'  ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','23')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '2022074R-29'||$report->udid == '2022084R-29' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','29')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '2022074R-15' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','15')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '2022074R-16'||$report->udid == '2022084R-16' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','16')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '2022074R-17'||$report->udid == '2022084R-17' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','17')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '2022074R-18'||$report->udid == '2022084R-18' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','18')
	 ->select('total')
	 ->sum('total');
 }

 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '2022074R-24'|| $report->udid == '2022084R-24' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','24')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '2022074R-33'||$report->udid == '2022084R-33') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','33')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074LIMA NORTE'||$report->udid == '2022084LIMA NORTE' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','20')
	 ->where('clients.zone_id','5')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074CALLAO'||$report->udid == '2022084CALLAO' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','20')
	 ->where('clients.zone_id','1')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074LIMA ESTE'||$report->udid == '2022084LIMA ESTE' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','20')
	 ->where('clients.zone_id','3')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '2022074NORTE'||$report->udid == '2022084NORTE' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','20')
	 ->where('clients.zone_id','10')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '2022074SUR'|| $report->udid == '2022084SUR' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','20')
	 ->where('clients.zone_id','11')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074CENTRO'|| $report->udid == '2022084CENTRO' ) {
	$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
   
	->where('sales.sale_date', '>=', $mes_date)
	->where('sales.sale_date', '<=', $initial_date)
	->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	->where('clients.route_id','20')
	->where('clients.zone_id','9')
	->select('total')
	->sum('total');
}
 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '2022074VDD-1'|| $report->udid == '2022084VDD-1' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','34')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '2022074VDD-2'||$report->udid == '2022084VDD-2' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','35')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '2022074VDD-6'||$report->udid == '2022084VDD-6' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','36')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '2022074VDD-7'||$report->udid == '2022084VDD-7' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.route_id','37')
	 ->select('total')
	 ->sum('total');
 }
 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022075Massiel AlorPRIMARIO'||$report->udid == '2022085Massiel AlorPRIMARIO'  ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.channel_id','5')
	 ->where('clients.manager','Massiel Alor')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '2022075Massiel AlorCONVENCIONAL' ||$report->udid == '2022085Massiel AlorCONVENCIONAL'   ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.channel_id','6')
	 ->where('clients.manager','Massiel Alor')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '2022076Omar CasachaguaCONVENCIONAL'    ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.channel_id','6')
	 ->where('clients.manager','Omar Casachahua')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '2022077CONVENCIONAL' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.channel_id','6')
	 ->where('clients.manager','OFICINA PLANTA')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '2022078AUTOMOCION'||$report->udid == '2022088AUTOMOCION' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id','2')
	 ->where('clients.manager','Massiel Alor')
	 ->select('total')
	 ->sum('total');
 }
 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '2022079AUTOMOCION'||$report->udid == '2022089AUTOMOCION' ) {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	 
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id','2')
	 ->where('clients.manager','Omar Casachahua')
	 ->select('total')
	 ->sum('total');
 }
 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '20220710MINERIA'||$report->udid == '20220810MINERIA') {
	 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
	 ->where('sales.sale_date', '>=', $mes_date)
	 ->where('sales.sale_date', '<=', $initial_date)
	 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
	 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
	 ->where('clients.sector_id','9')
	 ->where('clients.manager','OFICINA PLANTA')
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



	$report->cost_glp = $report ['cglp_e']*1000;
	$report->aprov = $report ['aprov'];
	$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

	$report->name = $report ['name'];
	$report->last_tm = $report ['last_tm'];
	$report->tm = $tm_fin/1000;
	$report->proy_tm =  ($report ['tm']/ $report ['day'])* $report ['dias_mes'];

	if ( $report->last_tm != 0)
	$report->last_percentaje_tm = number_format( ($report  ['tm']/ $report['last_tm'] )* 100, 2, '.', '');
	else
	{
		$report->last_percentaje_tm=0;
	}

	$report->tm_ppto = $report ['tm_ppto'];
	if ( $report->tm_ppto != 0)
	$report->percentaje_tm = number_format( ($report ['proy_tm']/ $report ['tm_ppto']) * 100, 2, '.', '');
	else
	{
		$report->percentaje_tm=0;
	}
	
	$report->last_mb = $report ['last_mb'];
	$report->mb = $report ['cost_glp_final']*$report ['tm'];
	$report->proy_mb = ($report ['mb']/ $report ['day'])* $report ['dias_mes'];

	if ( $report->last_mb != 0)
	$report->last_percentaje_mb =number_format( ($report  ['mb']/ $report ['last_mb'])* 100, 2, '.', '');
	else
	{
		$report->last_percentaje_mb=0;
	}
	$report->mb_ppto = $report ['mb_ppto'];
	if ( $report->mb_ppto != 0)
	$report->percentaje_mb = number_format( ($report ['proy_mb']/ $report ['mb_ppto']) * 100, 2, '.', '');
	else
	{
		$report->percentaje_mb=0;
	}
	$report->last_total =  $report ['last_total'];
	$report->total =  $soles;
	$report->proy_soles = ($report ['total']/ $report ['day'])* $report ['dias_mes'];

	if ( $report->last_total != 0)
	$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']);
	else
	{
		$report->percentaje_soles=0;
	}

	if ( $report->last_tm != 0)
	$report->px_ma =  $report ['last_total']/ $report ['last_tm'];
	else
	{
		$report->px_ma=0;
	}
	if ( $report->last_tm != 0)
	$report->mbu_ma = $report ['last_mb']/ $report ['last_tm'];
	else
	{
		$report->mbu_ma=0;
	}

	$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];

	if ( $report->tm != 0)
	$report->px= $report ['total']/($report ['tm']/100);
	else
	{
		$report->px=0;
	}

	if ( $report->tm != 0)
	$report->mbu = $report ['mb']/ $report ['tm'];
	else
	{
		$report->mbu=0;
	}
	
	
	$report->costo = $report->px- $report->mbu;
	$report->dif_cx =  $report['costo'] - $report['costo_ma'];
	$report->dif_px = $report ['px']- $report ['px_ma'];
	$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];

	if ( $report->tm_ppto != 0)
	$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
	else
	{
		$report->ratio_ppto=0;
	}
	$report->ratio_tm = ($report ['tm']/ $report ['day']);
	

	$report->last_cost_glp = $report ['last_cost_glp_final']-$report ['aprov'];
	$report->last_aprov = $report ['aprov'];

	if ( $report->last_tm != 0)
	$report->last_cost_glp_final = $report ['last_mb']/$report ['last_tm'];
	else
	{
		$report->last_cost_glp_final=0;
	}






					
/*
					$report->cost_glp = $report ['cost_glp']*1000;
					$report->aprov = $report ['aprov'];
					$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

					$report->name = $report ['name'];
					$report->last_tm = $report ['last_tm'];
				
					$report->tm = $report ['tm'];
					$report->tm = $tm_fin/1000;
					$report->proy_tm =  ($report ['tm']/ $report ['day'])* $report ['dias_mes'];
					$report->last_percentaje_tm = number_format( ($report  ['tm']/ $report['last_tm'] )* 100, 2, '.', '');
					$report->tm_ppto = $report ['tm_ppto'];
					$report->percentaje_tm = number_format( ($report ['proy_tm']/ $report ['tm_ppto']) * 100, 2, '.', '');
					$report->last_mb = $report ['last_mb'];
					$report->mb = ($report ['total']/$report ['tm']-$report ['cost_glp_final'])*$report ['tm'];
					$report->proy_mb = ($report ['mb']/ $report ['day'])* $report ['dias_mes'];
					$report->last_percentaje_mb =number_format( ($report  ['mb']/ $report ['last_mb'])* 100, 2, '.', '');
					$report->mb_ppto = $report ['mb_ppto'];
					$report->percentaje_mb = number_format( ($report ['proy_mb']/ $report ['mb_ppto']) * 100, 2, '.', '');
					$report->last_total =  $report ['last_total'];
					$report->total =  $soles;
					$report->proy_soles = ($report ['total']/ $report ['day'])* $report ['dias_mes'];
					$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']); 
					$report->px_ma =  $report ['last_total']/ $report ['last_tm']/100;
					$report->mbu_ma = $report ['last_mb']/ $report ['last_tm']/100;
					$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];
					$report->px= $report ['total']/$report ['tm']/100;
					$report->mbu = $report ['mb']/ $report ['tm']/100;
					$report->costo = $report->px- $report->mbu;
					$report->dif_cx =  $report['costo'] - $report['costo_ma'];
					$report->dif_px = $report ['px']- $report ['px_ma'];
					$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];
					$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
					$report->ratio_tm = ($report ['tm']/ $report ['day']);
					
	                $report->last_cost_glp_final = ($report ['last_total']-$report ['last_mb'])/$report ['last_tm'];
					$report->last_cost_glp = $report ['last_cost_glp_final']-$report ['aprov'];
					$report->last_aprov = $report ['aprov'];*/
					
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

				$sheet->getStyle('A' . ($row_number + count($group) + 1) . ':AK' . ($row_number + count($group) + 1))->applyFromArray([
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
					$sheet->setCellValue('Z'.$row_number, $element->dif_cx);
					$sheet->setCellValue('AA'.$row_number, $element->dif_px);
					$sheet->setCellValue('AB'.$row_number, $element->dif_mbu);
					$sheet->setCellValue('AC'.$row_number, $element->ratio_ppto);
					$sheet->setCellValue('AD'.$row_number, $element->ratio_tm);
					$sheet->setCellValue('AE'.$row_number, $element->ratio);
					$sheet->setCellValue('AF'.$row_number, $element->last_cost_glp);
					$sheet->setCellValue('AG'.$row_number, $element->last_aprov);
					$sheet->setCellValue('AH'.$row_number, $element->last_cost_glp_final);
					$sheet->setCellValue('AI'.$row_number, $element->cost_glp);
					$sheet->setCellValue('AJ'.$row_number, $element->aprov);
					$sheet->setCellValue('AK'.$row_number, $element->cost_glp_final);
					

					$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.00 %');

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
			$sheet->getColumnDimension('Z')->setAutoSize(true);
			$sheet->getColumnDimension('AA')->setAutoSize(true);
			$sheet->getColumnDimension('AB')->setAutoSize(true);
			$sheet->getColumnDimension('AC')->setAutoSize(true);
			$sheet->getColumnDimension('AD')->setAutoSize(true);
			$sheet->getColumnDimension('AE')->setAutoSize(true);
			$sheet->getColumnDimension('AF')->setAutoSize(true);
			$sheet->getColumnDimension('AG')->setAutoSize(true);
			$sheet->getColumnDimension('AH')->setAutoSize(true);
			$sheet->getColumnDimension('AI')->setAutoSize(true);
			$sheet->getColumnDimension('AJ')->setAutoSize(true);
			$sheet->getColumnDimension('AK')->setAutoSize(true);
			$writer = new Xls($spreadsheet);

			return $writer->save('php://output');
		} else {
			foreach ($result as $report) {





				if ( $report->year == '2022'){


// <<--------------------------------------------------------------CALCULO DE TM EXCEL-------------------------------------------------------------------------------->>

					if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711') {
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
		   
					   elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'  ) {
					   $tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					   ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					   ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					   ->where('sales.sale_date', '>=', $mes_date)
					   ->where('sales.sale_date', '<=', $initial_date)
					   ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					   ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					   ->where('clients.business_unit_id', 2)
					   ->select('kg')
					   ->sum('kg');
				   } 

				   elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '2022072Percy Velaochaga' ||$report->udid == '2022082Percy Velaochaga') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Percy Velaochaga')
					->select('kg')
					->sum('kg');
				} 
				elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '2022072Cesar Coronado' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Cesar Coronado')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '2022072Mauricio Diaz' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Mauricio Diaz')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '2022072Anthony Valencia' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Anthony Valencia')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '2022072Yimmy León'||$report->udid == '2022082Yimmy León' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Yimmy León')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '2022072Genesis Adreani'   ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Genesis Adreani')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '2022072Massiel Alor' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Massiel Alor')
					->select('kg')
					->sum('kg');
				} 
				elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '2022072Omar Casachahua') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'Omar Casachahua')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'||$report->udid == '2022072OFICINA PLANTA' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager', 'OFICINA PLANTA')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id',10)
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id',13)
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id',12)
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202206311'||$report->udid == '202207311'||$report->udid == '202208311' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id',11)
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314'     ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id',14)
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '2022073Massiel Alor' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager','Massiel Alor')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '2022073Omar Casachahua' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager','Omar Casachahua')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '2022073OFICINA PLANTA'    ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.manager','OFICINA PLANTA')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '2022074R-1'||$report->udid == '2022084R-1' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','1')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '2022074R-2'||$report->udid == '2022084R-2' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','2')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '2022074R-3' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','3')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '2022074R-4'||$report->udid == '2022084R-4' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','4')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '2022084R-5' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','5')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '2022074R-6'||$report->udid == '2022084R-6' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','6')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '2022074R-7'||$report->udid == '2022084R-7' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','7')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '2022074R-9'||$report->udid == '2022084R-9' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','9')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '2022074R-10' ||$report->udid == '2022084R-10'   ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','10')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '2022074R-11'||$report->udid == '2022084R-11') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','11')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '2022074R-12'||$report->udid == '2022084R-12' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','12')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '2022074R-13'||$report->udid == '2022084R-13' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','13')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '2022074R-23' ||$report->udid == '2022084R-23'  ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','23')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '2022074R-29'||$report->udid == '2022084R-29' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','29')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '2022074R-15' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','15')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '2022074R-16'||$report->udid == '2022084R-16' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','16')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '2022074R-17'||$report->udid == '2022084R-17' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','17')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '2022074R-18'||$report->udid == '2022084R-18' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','18')
					->select('kg')
					->sum('kg');
				}

				elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '2022074R-24'|| $report->udid == '2022084R-24' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','24')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '2022074R-33'||$report->udid == '2022084R-33') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','33')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074LIMA NORTE'||$report->udid == '2022084LIMA NORTE' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','5')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074CALLAO'||$report->udid == '2022084CALLAO' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','1')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074LIMA ESTE'||$report->udid == '2022084LIMA ESTE' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','3')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '2022074NORTE'||$report->udid == '2022084NORTE' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','10')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '2022074SUR'|| $report->udid == '2022084SUR' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','11')
					->select('kg')
					->sum('kg');
				}

				elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074CENTRO'|| $report->udid == '2022084CENTRO' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','20')
					->where('clients.zone_id','9')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '2022074VDD-1'|| $report->udid == '2022084VDD-1' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','34')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '2022074VDD-2'||$report->udid == '2022084VDD-2' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','35')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '2022074VDD-6'||$report->udid == '2022084VDD-6' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','36')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '2022074VDD-7'||$report->udid == '2022084VDD-7' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.route_id','37')
					->select('kg')
					->sum('kg');
				}
				elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022075Massiel AlorPRIMARIO'||$report->udid == '2022085Massiel AlorPRIMARIO'  ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id','5')
					->where('clients.manager','Massiel Alor')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '2022075Massiel AlorCONVENCIONAL' ||$report->udid == '2022085Massiel AlorCONVENCIONAL'   ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id','6')
					->where('clients.manager','Massiel Alor')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022046Omar CasachaguaPRIMARIO'||$report->udid == '2022056Omar CasachaguaPRIMARIO'||$report->udid == '2022066Omar CasachaguaPRIMARIO'||$report->udid == '2022076Omar CasachaguaPRIMARIO' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id','5')
					->where('clients.manager','Omar Casachahua')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '2022076Omar CasachaguaCONVENCIONAL'    ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id','6')
					->where('clients.manager','Omar Casachahua')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '2022077CONVENCIONAL' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.channel_id','6')
					->where('clients.manager','OFICINA PLANTA')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '2022078AUTOMOCION'||$report->udid == '2022088AUTOMOCION' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id','2')
					->where('clients.manager','Massiel Alor')
					->select('kg')
					->sum('kg');
				}
				elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '2022079AUTOMOCION'||$report->udid == '2022089AUTOMOCION' ) {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id','2')
					->where('clients.manager','Omar Casachahua')
					->select('kg')
					->sum('kg');
				}
				elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '20220710MINERIA'||$report->udid == '20220810MINERIA') {
					$tm_fin = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
					->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
					->where('sales.sale_date', '>=', $mes_date)
					->where('sales.sale_date', '<=', $initial_date)
					->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
					->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
					->where('clients.sector_id','9')
					->where('clients.manager','OFICINA PLANTA')
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


	

		if ( $report->udid == '20220411' ||$report->udid == '20220511' ||$report->udid == '20220611'||$report->udid == '20220711'||$report->udid == '20220811') {
			$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->where('sales.sale_date', '>=', $mes_date)
			->where('sales.sale_date', '<=', $initial_date)
			->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
			->where('clients.business_unit_id', 1)
			->select('total')
			->sum('total');
		}
	
			elseif (  $report->udid == '20220412'||$report->udid == '20220512' ||$report->udid == '20220612' ||$report->udid == '20220712'  ) {
			 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->where('sales.sale_date', '>=', $mes_date)
			->where('sales.sale_date', '<=', $initial_date)
			->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
			->where('clients.business_unit_id', 2)
			->select('total')
			->sum('total');
		} 
	
		elseif (  $report->udid == '2022052Percy Velaochaga'||$report->udid == '2022062Percy Velaochaga'||$report->udid == '2022072Percy Velaochaga' ||$report->udid == '2022082Percy Velaochaga') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Percy Velaochaga')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif ( $report->udid == '2022042Cesar Coronado'|| $report->udid == '2022052Cesar Coronado'||$report->udid == '2022062Cesar Coronado'||$report->udid == '2022072Cesar Coronado' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Cesar Coronado')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Mauricio Diaz'|| $report->udid == '2022052Mauricio Diaz'||$report->udid == '2022062Mauricio Diaz'||$report->udid == '2022072Mauricio Diaz' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Mauricio Diaz')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Anthony Valencia'||$report->udid == '2022052Anthony Valencia'||$report->udid == '2022062Anthony Valencia'||$report->udid == '2022072Anthony Valencia' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Anthony Valencia')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Yimmy León'||$report->udid == '2022052Yimmy León'||$report->udid == '2022062Yimmy León'||$report->udid == '2022072Yimmy León'||$report->udid == '2022082Yimmy León' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Yimmy León')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Genesis Adreani' ||$report->udid == '2022052Genesis Adreani'||$report->udid == '2022062Genesis Adreani'||$report->udid == '2022072Genesis Adreani'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Genesis Adreani')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Massiel Alor' ||$report->udid == '2022052Massiel Alor' ||$report->udid == '2022062Massiel Alor' ||$report->udid == '2022072Massiel Alor' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Massiel Alor')
		 ->select('total')
		 ->sum('total');
	 } 
	 elseif (  $report->udid == '2022042Omar Casachahua' ||$report->udid == '2022052Omar Casachahua'||$report->udid == '2022062Omar Casachahua'||$report->udid == '2022072Omar Casachahua') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'Omar Casachahua')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022042OFICINA PLANTA'||$report->udid == '2022052OFICINA PLANTA'||$report->udid == '2022062OFICINA PLANTA'||$report->udid == '2022072OFICINA PLANTA' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager', 'OFICINA PLANTA')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204310'||$report->udid == '202205310'||$report->udid == '202206310'||$report->udid == '202207310' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id',10)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204313'||$report->udid == '202205313'||$report->udid == '202206313'||$report->udid == '202207313'||$report->udid == '202208313' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id',13)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204312'||$report->udid == '202205312'||$report->udid == '202206312'||$report->udid == '202207312' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id',12)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204311'||$report->udid == '202205311'||$report->udid == '202207311'||$report->udid == '202208311' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id',11)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '202204314'|| $report->udid == '202205314'|| $report->udid == '202206314'|| $report->udid == '202207314' ||$report->udid == '202208314'     ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id',14)
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043Massiel Alor'||$report->udid == '2022053Massiel Alor'||$report->udid == '2022063Massiel Alor'||$report->udid == '2022073Massiel Alor' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager','Massiel Alor')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043Omar Casachahua'||$report->udid == '2022053Omar Casachahua'||$report->udid == '2022063Omar Casachahua'||$report->udid == '2022073Omar Casachahua' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager','Omar Casachahua')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022043OFICINA PLANTA'||$report->udid == '2022053OFICINA PLANTA'||$report->udid == '2022063OFICINA PLANTA'||$report->udid == '2022073OFICINA PLANTA'    ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.manager','OFICINA PLANTA')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-1'||$report->udid == '2022054R-1'||$report->udid == '2022064R-1'||$report->udid == '2022074R-1'||$report->udid == '2022084R-1' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','1')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-2'||$report->udid == '2022054R-2'||$report->udid == '2022064R-2'||$report->udid == '2022074R-2'||$report->udid == '2022084R-2' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','2')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-3'||$report->udid == '2022054R-3'||$report->udid == '2022064R-3'||$report->udid == '2022074R-3' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','3')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022054R-4'||$report->udid == '2022054R-4'||$report->udid == '2022064R-4'||$report->udid == '2022074R-4'||$report->udid == '2022084R-4' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','4')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-5'||$report->udid == '2022054R-5'||$report->udid == '2022064R-5'||$report->udid == '2022084R-5' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','5')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-6'||$report->udid == '2022054R-6'||$report->udid == '2022064R-6'||$report->udid == '2022074R-6'||$report->udid == '2022084R-6' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','6')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-7'||$report->udid == '2022054R-7'||$report->udid == '2022064R-7'||$report->udid == '2022074R-7'||$report->udid == '2022084R-7' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','7')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-9'||$report->udid == '2022054R-9'||$report->udid == '2022064R-9'||$report->udid == '2022074R-9'||$report->udid == '2022084R-9' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','9')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-10'||$report->udid == '2022054R-10'||$report->udid == '2022064R-10' ||$report->udid == '2022074R-10' ||$report->udid == '2022084R-10'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','10')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-11'|| $report->udid == '2022054R-11'||$report->udid == '2022064R-11'||$report->udid == '2022074R-11'||$report->udid == '2022084R-11') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','11')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-12'||$report->udid == '2022054R-12'||$report->udid == '2022064R-12'||$report->udid == '2022074R-12'||$report->udid == '2022084R-12' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','12')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-13'||$report->udid == '2022054R-13'||$report->udid == '2022064R-13'||$report->udid == '2022074R-13'||$report->udid == '2022084R-13' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','13')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-23'||$report->udid == '2022054R-23' ||$report->udid == '2022064R-23' ||$report->udid == '2022074R-23' ||$report->udid == '2022084R-23'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','23')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-29'||$report->udid == '2022054R-29'||$report->udid == '2022064R-29'||$report->udid == '2022074R-29'||$report->udid == '2022084R-29' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','29')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-15'||$report->udid == '2022054R-15' ||$report->udid == '2022064R-15'||$report->udid == '2022074R-15' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','15')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-16'||$report->udid == '2022054R-16'||$report->udid == '2022064R-16'||$report->udid == '2022074R-16'||$report->udid == '2022084R-16' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','16')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-17'||$report->udid == '2022054R-17'||$report->udid == '2022064R-17'||$report->udid == '2022074R-17'||$report->udid == '2022084R-17' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','17')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-18'||$report->udid == '2022054R-18'||$report->udid == '2022064R-18'||$report->udid == '2022074R-18'||$report->udid == '2022084R-18' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','18')
		 ->select('total')
		 ->sum('total');
	 }
	
	 elseif (  $report->udid == '2022044R-24'|| $report->udid == '2022054R-24'|| $report->udid == '2022064R-24'|| $report->udid == '2022074R-24'|| $report->udid == '2022084R-24' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','24')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044R-33'||$report->udid == '2022054R-33'||$report->udid == '2022064R-33'||$report->udid == '2022074R-33'||$report->udid == '2022084R-33') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','33')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022054LIMA NORTE'||$report->udid == '2022064LIMA NORTE'||$report->udid == '2022074LIMA NORTE'||$report->udid == '2022084LIMA NORTE' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','20')
		 ->where('clients.zone_id','5')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044CALLAO'||$report->udid == '2022054CALLAO'||$report->udid == '2022064CALLAO'||$report->udid == '2022074CALLAO'||$report->udid == '2022084CALLAO' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','20')
		 ->where('clients.zone_id','1')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044LIMA ESTE'||$report->udid == '2022054LIMA ESTE'||$report->udid == '2022064LIMA ESTE'||$report->udid == '2022074LIMA ESTE'||$report->udid == '2022084LIMA ESTE' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','20')
		 ->where('clients.zone_id','3')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044NORTE'||$report->udid == '2022054NORTE'||$report->udid == '2022064NORTE'||$report->udid == '2022074NORTE'||$report->udid == '2022084NORTE' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','20')
		 ->where('clients.zone_id','10')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044SUR'|| $report->udid == '2022054SUR'|| $report->udid == '2022064SUR'|| $report->udid == '2022074SUR'|| $report->udid == '2022084SUR' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','20')
		 ->where('clients.zone_id','11')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044CENTRO'|| $report->udid == '2022054CENTRO'|| $report->udid == '2022064CENTRO'|| $report->udid == '2022074CENTRO'|| $report->udid == '2022084CENTRO' ) {
		$soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
	   
		->where('sales.sale_date', '>=', $mes_date)
		->where('sales.sale_date', '<=', $initial_date)
		->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		->where('clients.route_id','20')
		->where('clients.zone_id','9')
		->select('total')
		->sum('total');
	}
	 elseif (  $report->udid == '2022044VDD-1'|| $report->udid == '2022054VDD-1'|| $report->udid == '2022064VDD-1'|| $report->udid == '2022074VDD-1'|| $report->udid == '2022084VDD-1' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','34')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-2'||$report->udid == '2022054VDD-2'||$report->udid == '2022064VDD-2'||$report->udid == '2022074VDD-2'||$report->udid == '2022084VDD-2' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','35')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-6'||$report->udid == '2022054VDD-6'||$report->udid == '2022064VDD-6'||$report->udid == '2022074VDD-6'||$report->udid == '2022084VDD-6' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','36')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022044VDD-7'||$report->udid == '2022054VDD-7'||$report->udid == '2022064VDD-7'||$report->udid == '2022074VDD-7'||$report->udid == '2022084VDD-7' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.route_id','37')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif ( $report->udid == '2022045Massiel AlorPRIMARIO'||$report->udid == '2022055Massiel AlorPRIMARIO'||$report->udid == '2022075Massiel AlorPRIMARIO'||$report->udid == '2022085Massiel AlorPRIMARIO'  ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.channel_id','5')
		 ->where('clients.manager','Massiel Alor')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022045Massiel AlorCONVENCIONAL'||$report->udid == '2022055Massiel AlorCONVENCIONAL'||$report->udid == '2022065Massiel AlorCONVENCIONAL' ||$report->udid == '2022075Massiel AlorCONVENCIONAL' ||$report->udid == '2022085Massiel AlorCONVENCIONAL'   ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.channel_id','6')
		 ->where('clients.manager','Massiel Alor')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022046Omar CasachaguaCONVENCIONAL'||$report->udid == '2022056Omar CasachaguaCONVENCIONAL'||$report->udid == '2022066Omar CasachaguaCONVENCIONAL'||$report->udid == '2022076Omar CasachaguaCONVENCIONAL'    ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.channel_id','6')
		 ->where('clients.manager','Omar Casachahua')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022047CONVENCIONAL'||$report->udid == '2022057CONVENCIONAL'||$report->udid == '2022067CONVENCIONAL'||$report->udid == '2022077CONVENCIONAL' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.channel_id','6')
		 ->where('clients.manager','OFICINA PLANTA')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022048AUTOMOCION'||$report->udid == '2022058AUTOMOCION'||$report->udid == '2022068AUTOMOCION'||$report->udid == '2022078AUTOMOCION'||$report->udid == '2022088AUTOMOCION' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id','2')
		 ->where('clients.manager','Massiel Alor')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif (  $report->udid == '2022049AUTOMOCION'||$report->udid == '2022059AUTOMOCION'||$report->udid == '2022069AUTOMOCION'||$report->udid == '2022079AUTOMOCION'||$report->udid == '2022089AUTOMOCION' ) {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		 
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id','2')
		 ->where('clients.manager','Omar Casachahua')
		 ->select('total')
		 ->sum('total');
	 }
	 elseif ( $report->udid == '20220410MINERIA'||$report->udid == '20220510MINERIA'||$report->udid == '20220610MINERIA'||$report->udid == '20220710MINERIA'||$report->udid == '20220810MINERIA') {
		 $soles = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
		
		 ->where('sales.sale_date', '>=', $mes_date)
		 ->where('sales.sale_date', '<=', $initial_date)
		 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
		 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
		 ->where('clients.sector_id','9')
		 ->where('clients.manager','OFICINA PLANTA')
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
	
				


				   

					$report->cost_glp = $report ['cglp_e']*1000;
					$report->aprov = $report ['aprov'];
					$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

					$report->name = $report ['name'];
					$report->last_tm = $report ['last_tm'];
					$report->tm = $tm_fin/1000;
					$report->proy_tm =  ($report ['tm']/ $report ['day'])* $report ['dias_mes'];

					if ( $report->last_tm != 0)
					$report->last_percentaje_tm = number_format( ($report  ['tm']/ $report['last_tm'] )* 100, 2, '.', '');
					else
                    {
						$report->last_percentaje_tm=0;
                    }

					$report->tm_ppto = $report ['tm_ppto'];
                    if ( $report->tm_ppto != 0)
					$report->percentaje_tm = number_format( ($report ['proy_tm']/ $report ['tm_ppto']) * 100, 2, '.', '');
					else
                    {
						$report->percentaje_tm=0;
                    }
					
					$report->last_mb = $report ['last_mb'];
					$report->mb = $report ['cost_glp_final']*$report ['tm'];
					$report->proy_mb = ($report ['mb']/ $report ['day'])* $report ['dias_mes'];

					if ( $report->last_mb != 0)
					$report->last_percentaje_mb =number_format( ($report  ['mb']/ $report ['last_mb'])* 100, 2, '.', '');
                    else
                    {
						$report->last_percentaje_mb=0;
                    }
					$report->mb_ppto = $report ['mb_ppto'];
					if ( $report->mb_ppto != 0)
					$report->percentaje_mb = number_format( ($report ['proy_mb']/ $report ['mb_ppto']) * 100, 2, '.', '');
					else
                    {
						$report->percentaje_mb=0;
                    }
					$report->last_total =  $report ['last_total'];
					$report->total =  $soles;
					$report->proy_soles = ($report ['total']/ $report ['day'])* $report ['dias_mes'];

                    if ( $report->last_total != 0)
					$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']);
					else
                    {
						$report->percentaje_soles=0;
                    }

					if ( $report->last_tm != 0)
					$report->px_ma =  $report ['last_total']/ $report ['last_tm'];
					else
                    {
						$report->px_ma=0;
                    }
					if ( $report->last_tm != 0)
					$report->mbu_ma = $report ['last_mb']/ $report ['last_tm'];
					else
                    {
						$report->mbu_ma=0;
                    }

					$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];

					if ( $report->tm != 0)
					$report->px= $report ['total']/($report ['tm']/100);
                    else
                    {
						$report->px=0;
                    }

					if ( $report->tm != 0)
					$report->mbu = $report ['mb']/ $report ['tm'];
                    else
                    {
						$report->mbu=0;
                    }
					
					
					$report->costo = $report->px- $report->mbu;
					$report->dif_cx =  $report['costo'] - $report['costo_ma'];
					$report->dif_px = $report ['px']- $report ['px_ma'];
					$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];

					if ( $report->tm_ppto != 0)
					$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
					else
                    {
						$report->ratio_ppto=0;
                    }
					$report->ratio_tm = ($report ['tm']/ $report ['day']);
					

					$report->last_cost_glp = $report ['last_cost_glp_final']-$report ['aprov'];
					$report->last_aprov = $report ['aprov'];

					if ( $report->last_tm != 0)
					$report->last_cost_glp_final = $report ['last_mb']/$report ['last_tm'];
					else
                    {
						$report->last_cost_glp_final=0;
                    }




					$totals_last_tm += $report['last_tm'];
		            $totals_tm += $report['tm'];
		            $totals_proy_tm += $report['proy_tm'];
		            $totals_tm_ppto += $report['tm_ppto'];
		            $totals_last_mb += $report['last_mb'];
		            $totals_mb += $report['mb'];
		            $totals_proy_mb += $report['proy_mb'];
		            $totals_mb_ppto += $report['mb_ppto'];
		            $totals_last_total += $report['last_total'];
		            $totals_total += $report['total'];
		            $totals_proy_soles += $report['proy_soles'];


					$report->ratio = ($report ['tm']/ $totals_tm);

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






