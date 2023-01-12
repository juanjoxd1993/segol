<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Report;
use App\ConsReport;
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
	$result = AcRep::leftjoin('cons_reports', 'ac_reps.udid', '=', 'cons_reports.udid')
	->leftjoin('cglp_finals', 'ac_reps.fecha_consulta', '=', 'cglp_finals.fecha_consulta')
	->where('ac_reps.fecha_consulta', '=', $initial_date)
	->select(
		'ac_reps.fecha_consulta as sale_date',
		'ac_reps.año as year',
		'ac_reps.cost_glp',
		'cglp_finals.aventajada as aprov',
		'ac_reps.mes as month',
		'ac_reps.dia as day',
		'ac_reps.channel_name as client_channel_name',
		'ac_reps.name as name',
		'ac_reps.ac_tm as tm',
		'ac_reps.ac_so as total',
		'cons_reports.soles as last_total',
		'cons_reports.tm as last_tm',
		'cons_reports.tm_ppto as tm_ppto',
		 'cons_reports.mb_ppto as mb_ppto',
		 'cons_reports.mb as last_mb',
	//	 'cons_reports.percentaje_tm as last_percentaje_tm',
	//	 'cons_reports.percentaje_mb as last_percentaje_mb',
		 'cons_reports.dias as dias_mes',
		 'ac_reps.sale_option as option'
	 )
//	 ->groupBy('ac_reps.fecha_consulta')
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
		$row_number = 3;
		

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

		$sheet->getStyle('A6:AK6')->applyFromArray([
			'font' => [
				'color' => array('rgb' => '000000'),
			//	'bold' => true,
				'size' => 12,
			],
			
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => 'FFD700')
			]
		]);

		foreach($options as $option => $group) {
			$response = [];


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




			foreach ($group as $report) {

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

				$report->cost_glp = $report ['cost_glp']*1000;
				$report->aprov = $report ['aprov'];
				$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

				$report->name = $report ['name'];
				$report->last_tm = $report ['last_tm'];
				$report->tm = $report ['tm'];
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
				$report->total =  $report ['total'];
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
				$report->ratio = ($report ['tm']/ $totals_tm);
                $report->last_cost_glp_final = ($report ['last_total']-$report ['last_mb'])/$report ['last_tm'];
				$report->last_cost_glp = $report ['last_cost_glp_final']-$report ['aprov'];
				$report->last_aprov = $report ['aprov'];
				

				

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

				$report->cost_glp = $report ['cglp_e']*1000;
				$report->aprov = $report ['aprov'];
				$report->cost_glp_final = $report ['cost_glp'] +$report ['aprov'];

				$report->name = $report ['name'];
				$report->last_tm = $report ['last_tm'];
				$report->tm = $report ['tm'];
				$report->proy_tm =  ($report ['tm']/ $report ['day'])* $report ['dias_mes'];
				$report->last_percentaje_tm = number_format( ($report  ['tm']/ $report['last_tm'] )* 100, 2, '.', '');
				$report->tm_ppto = $report ['tm_ppto'];
				$report->percentaje_tm = number_format( ($report ['proy_tm']/ $report ['tm_ppto']) * 100, 2, '.', '');
				$report->last_mb = $report ['last_mb'];
				$report->mb = $report ['cost_glp_final']*$report ['tm'];
				$report->proy_mb = ($report ['mb']/ $report ['day'])* $report ['dias_mes'];
				$report->last_percentaje_mb =number_format( ($report  ['mb']/ $report ['last_mb'])* 100, 2, '.', '');
				$report->mb_ppto = $report ['mb_ppto'];
				$report->percentaje_mb = number_format( ($report ['proy_mb']/ $report ['mb_ppto']) * 100, 2, '.', '');
				$report->last_total =  $report ['last_total'];
				$report->total =  $report ['total'];
				$report->proy_soles = ($report ['total']/ $report ['day'])* $report ['dias_mes'];
				$report->percentaje_soles = ($report ['proy_soles']/$report ['last_total']); 
				$report->px_ma =  $report ['last_total']/ $report ['last_tm'];
				$report->mbu_ma = $report ['last_mb']/ $report ['last_tm'];
				$report->costo_ma =$report ['px_ma'] - $report ['mbu_ma'];
				$report->px= $report ['total']/($report ['tm']/100);
				$report->mbu = $report ['mb']/ $report ['tm'];
				$report->costo = $report->px- $report->mbu;
				$report->dif_cx =  $report['costo'] - $report['costo_ma'];
				$report->dif_px = $report ['px']- $report ['px_ma'];
				$report->dif_mbu = $report ['mbu']- $report ['mbu_ma'];
				$report->ratio_ppto = ($report ['mb_ppto']/ $report ['tm_ppto']);
				$report->ratio_tm = ($report ['tm']/ $report ['day']);
				$report->ratio = ($report ['tm']/ $totals_tm);

				$report->last_cost_glp = $report ['last_cost_glp_final']-$report ['aprov'];
				$report->last_aprov = $report ['aprov'];
				$report->last_cost_glp_final = $report ['last_mb']/$report ['last_tm'];

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






