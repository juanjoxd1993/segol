<?php

namespace App\Http\Controllers\Backend;

use App\Sale;
use App\SaleDetail;
use App\AcRep;
use App\Article;
use App\Client;
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
use stdClass;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;


class 	ProyectionReportController extends Controller
{
    public function index() {

		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.proyection_report')->with(compact('current_date'));
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
			'day'			=> 'required|numeric|min:0|not_in:0',
			
			
		];

		request()->validate($rules, $messages);
		return request()->all();
	}
	public function list() {
		$untilDate = CarbonImmutable::createFromDate(request('model.initial_date'));
		$colMaxNumber = (int) $untilDate->format('d');
		$response = [];
        $export = request('export');
        $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
        $price_mes = CarbonImmutable::createFromDate(request('model.initial_date'))->format('m');
        $day = request('model.day');

		DB::enableQueryLog();
		
		$result = AcRep::select(
		'ac_reps.udid as udid',
		'ac_reps.año as year',
		'ac_reps.mes as month',
		'ac_reps.channel_name as client_channel_name',
		'ac_reps.name as name',
		 'ac_reps.dias as dias_mes',
		 'ac_reps.sale_option as option',
		 'ac_reps.route_id as route_id',
		 'ac_reps.zone_id as zone_id',
		 'ac_reps.sector_id as sector_id',
		 'ac_reps.orden as orden'
		 )->where('ac_reps.mes', '=', $price_mes)
		 ->orderBy('ac_reps.sale_option')
		 ->orderBy('ac_reps.orden')		 
		 ->get();

		foreach ($result as $report) {
			$index = 0;
            $report->route_id = $report ['route_id'];

            while ($index <= $colMaxNumber) {
	        	$index++;
	        	$compositeIndex = 'tm_' . str_pad($index, 2, "0", STR_PAD_LEFT);
	        	$tmpDate = $untilDate->startOfDay()->format('Y-m-' . str_pad($index, 2, "0", STR_PAD_LEFT));

	        	$tmpSadeDetail = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				 ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				 ->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				 ->where('sales.sale_date', '=', $tmpDate)
				 ->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
				 ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				 ->where('clients.route_id','=',$report->route_id)
				 ->select('kg')
				 ->sum('kg');

				$report->{$compositeIndex} = $tmpSadeDetail/1000;
            }

		   	$response[] = $report;
		}

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AH1');
			
			$sheet->setCellValue('A1', 'REPORTE DE PROYECCIONES DEL '.CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d'));
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
			$sheet->setCellValue('C3', 'Nombre');

			for ($i=1; $i <= $colMaxNumber ; $i++) { 
				$sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3) . '3', str_pad($i, 2, "0", STR_PAD_LEFT));

				if ($i == $colMaxNumber) {
					$sheet->getStyle('A3:' . Coordinate::stringFromColumnIndex($i + 3) . '3')->applyFromArray([
						'font' => [
							'bold' => true,
						],
					]);
				}
			}

			$row_number = 4;

			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->client_channel_name);
				$sheet->setCellValue('B'.$row_number, $element->client_channel_name);
				$sheet->setCellValue('C'.$row_number, $element->name);

				for ($i = 1; $i <= $colMaxNumber; $i++) {
					$tmpProp = 'tm_' . str_pad($i, 2, "0", STR_PAD_LEFT);

					$sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3) . $row_number, $element->{$tmpProp});
					$sheet->getStyle(Coordinate::stringFromColumnIndex($i + 3) . $row_number)->getNumberFormat()->setFormatCode('0.0');
				}

				$row_number++;
			}


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
	








