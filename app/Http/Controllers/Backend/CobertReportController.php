<?php

namespace App\Http\Controllers\Backend;

use App\Cobert;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
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


class 	CobertReportController extends Controller
{
    public function index() {
		
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.cobert_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			
			'final_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			
			'final_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	

	public function list() {

		$export = request('export');

	   
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('m');
		
		
					$elements = Cobert::select('coberts.id','coberts.mes as mes', 'coberts.canal as canal', 'coberts.cobertura as cobertura',
					'coberts.mes_name as mes_name','coberts.canal_name as canal_name', 'coberts.ruta_name as ruta_name', 'coberts.cuota as cuota','coberts.udid as udid')
			            ->where('coberts.mes', '=', $final_date)
			
		
			->get();
			$response=[];

			


			foreach ($elements as $saledetail) {
				

	
				$cobertura_1 = Cobert::select('cobertura')
				->where('mes', '01')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				

				$cobertura_2 = Cobert::select('cobertura')
				->where('coberts.mes', '02')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				$cobertura_3 = Cobert::select('cobertura')
				->where('coberts.mes', '03')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_4 = Cobert::select('cobertura')
				->where('coberts.mes', '04')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_5 = Cobert::select('cobertura')
				->where('coberts.mes', '05')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_6 = Cobert::select('cobertura')
				->where('coberts.mes', '06')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_7 = Cobert::select('cobertura')
				->where('coberts.mes', '07')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');

				$cobertura_8 = Cobert::select('cobertura')
				->where('coberts.mes', '08')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');
				$cobertura_9 = Cobert::select('cobertura')
				->where('coberts.mes', '09')
				->where('udid', $saledetail ['udid'])
				->sum('cobertura');



				$saledetail->canal_name = $saledetail['canal_name'];
				$saledetail->ruta_name = $saledetail ['ruta_name'];
				$saledetail->cobertura_1 = $cobertura_1;
				$saledetail->cobertura_2 = $cobertura_2;
				$saledetail->cobertura_3 = $cobertura_3;
				$saledetail->cobertura_4 = $cobertura_4;
				$saledetail->cobertura_5 = $cobertura_5;
				$saledetail->cobertura_6 = $cobertura_6;
				$saledetail->cobertura_7 = $cobertura_7;
				$saledetail->cobertura_8 = $cobertura_8;
				$saledetail->cobertura_9 = $cobertura_9;
				$saledetail->cuota = $saledetail['cuota'];

			


				$response[] = $saledetail;

			}


		$totals = new stdClass();
		$totals->canal_name = 'TOTAL';
		$totals->ruta_name = '';
		$totals->cobertura_1 = '';
		$totals->cobertura_2 = '';
		$totals->cobertura_3 = '';
		$totals->cobertura_4 = '';
		$totals->cobertura_5 = '';
		$totals->cobertura_6 = '';
		$totals->cobertura_7 = '';
		$totals->cobertura_8 = '';
		$totals->cobertura_9 = '';
		$totals->cuota ='';


		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:O1');
			$sheet->setCellValue('A1', 'REPORTE DE COBERTURAS AL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'Tipo');
			$sheet->setCellValue('C3', 'Ruta');
			$sheet->setCellValue('D3', 'Enero');
            $sheet->setCellValue('E3', 'Febrero'); 
            $sheet->setCellValue('F3', 'Marzo');
            $sheet->setCellValue('G3', 'Abril');
			$sheet->setCellValue('H3', 'Mayo');
			$sheet->setCellValue('I3', 'Junio');
			$sheet->setCellValue('J3', 'Julio');
			$sheet->setCellValue('K3', 'Agosto');
			$sheet->setCellValue('L3', 'Septiembre');
			$sheet->setCellValue('M3', 'Cuota');
			$sheet->setCellValue('N3', 'Dif%');
            $sheet->setCellValue('O3', 'Dif');
           
			
			$sheet->getStyle('A3:O3')->applyFromArray([
				'font' => ['bold' => true],
				'name'      =>  'Calibri',
				'size'      =>  10,
			]);


	
		
		$sheet->getStyle('A3:C3')->applyFromArray([
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
		$sheet->getStyle('D3:L3')->applyFromArray([
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
		$sheet->getStyle('M3:O3')->applyFromArray([
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


			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
		



				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->canal_name);
				$sheet->setCellValue('C'.$row_number, $element->ruta_name);
				$sheet->setCellValue('D'.$row_number, $element->cobertura_1);
                $sheet->setCellValue('E'.$row_number, $element->cobertura_2);
                $sheet->setCellValue('F'.$row_number, $element->cobertura_3);
                $sheet->setCellValue('G'.$row_number, $element->cobertura_4);
				$sheet->setCellValue('H'.$row_number, $element->cobertura_5);
				$sheet->setCellValue('I'.$row_number, $element->cobertura_6);
				$sheet->setCellValue('J'.$row_number, $element->cobertura_7);
				$sheet->setCellValue('K'.$row_number, $element->cobertura_8);
				$sheet->setCellValue('L'.$row_number, $element->cobertura_9);
                $sheet->setCellValue('M'.$row_number, $element->cuota);
            //   $sheet->setCellValue('N'.$row_number, $element->);
			//	$sheet->setCellValue('O'.$row_number, $element->price); 			
            //    $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
						

		
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
		


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








