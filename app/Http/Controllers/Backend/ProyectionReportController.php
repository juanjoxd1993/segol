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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;


class 	ProyectionReportController extends Controller
{
	const START_AT_LINE = 4;
	const MONTH_NAMES = [
		'01' => 'Ene',
		'02' => 'Feb',
		'03' => 'Mar',
		'04' => 'Abr',
		'05' => 'May',
		'06' => 'Jun',
		'07' => 'Jul',
		'08' => 'Ago',
		'09' => 'Sep',
		'10' => 'Oct',
		'11' => 'Nov',
		'12' => 'Dic',
	];

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
        $price_year = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y');
        $day = request('model.day');
        $subtotalCells = [];

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
		'ac_reps.orden as orden',
		'ac_reps.manager_id as manager',
		'ac_reps.channel_id as channel'
		 )->where('ac_reps.mes', '=', $price_mes)
		 ->where('ac_reps.año', '=', $price_year)
		 ->orderBy('ac_reps.sale_option')
		 ->orderBy('ac_reps.orden')		 
		 ->get();

    	$grouppedSaleDetails = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->where('sale_date', '>=', $untilDate->startOfMonth()->toDateString())
			->where('sale_date', '<=', $untilDate->toDateString())
			->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
			->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
			->groupBy('sales.sale_date', 'sales.client_id')
			->select(
				DB::raw('
					sum(sale_details.kg) as total_kg,
					clients.route_id,
					clients.manager_id,
					clients.channel_id,
					clients.business_unit_id,
					clients.sector_id,
					sales.sale_date
				')
			)->get();

		foreach ($result as $key => $report) {
			$index = 1;
            $report->route_id = $report['route_id'];
			$report->option= $report['option'];

            while ($index <= $colMaxNumber) {
	        	$compositeIndex = 'tm_' . str_pad($index, 2, "0", STR_PAD_LEFT);
	        	$tmpDate = $untilDate->startOfDay()->format('Y-m-' . str_pad($index, 2, "0", STR_PAD_LEFT));
	        	$tmpTotal = 0.00;

				if ($report->option >= 1 && $report->option <= 5) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('route_id', $report->route_id)->sum('total_kg');
				} elseif ($report->option >= 6 && $report->option <= 8) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('manager_id', $report->manager)
													->where('channel_id', $report->channel)->sum('total_kg');
				} elseif ($report->option == 9) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('business_unit_id', $report->business_unit_id)->sum('total_kg');
				} elseif ($report->option >= 10 && $report->option <= 11) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('sector_id', $report->sector_id)->sum('total_kg');
				} elseif ($report->option >= 12 && $report->option <= 13) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('route_id', $report->route_id)
													->where('sector_id', $report->sector_id)->sum('total_kg');
				} elseif ($report->option == 14) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('sector_id', $report->sector_id)->sum('total_kg');
				} elseif ($report->option == 15) {
					$tmpTotal = $grouppedSaleDetails->where('sale_date', $tmpDate)
													->where('business_unit_id', $report->business_unit_id)->sum('total_kg');
				}

				$report->{$compositeIndex} = $tmpTotal / 1000.00;

				$index++;
            }

			

			if ($key > 0) {
				$result[$key - 1]->isLastOfOption = $lastOption != $report->option;
			}

			$lastOption = $report->option;

		   	$response[] = $report;
		}

		$result[count($result) - 1]->isLastOfOption = true;

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

			/* Headers */
			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'Canal');
			$sheet->setCellValue('C3', 'Ruta');

			$sheet->getStyle('A3:C3')->applyFromArray([
	            'fill' => [
	                'fillType' => Fill::FILL_SOLID,
	                'startColor' => ['argb' => Color::COLOR_DARKBLUE],
	            ],
	            'font' => [
	            	'color' => ['argb' => Color::COLOR_WHITE],
	            	'bold' => true,
	            ],
			]);

			$sheet->getStyle('D3:' . Coordinate::stringFromColumnIndex($colMaxNumber + 3) . '3')->applyFromArray([
				'font' => [
					'bold' => true,
					'color' => ['argb' => Color::COLOR_WHITE],
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => ['argb' => Color::COLOR_DARKGREEN],
				],
			]);

			for ($i=1; $i <= $colMaxNumber ; $i++) { 
				$subtotalCells[$i] = [];
				$sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3) . '3', $i . '-' . self::MONTH_NAMES[$price_mes]);

				if (CarbonImmutable::createFromFormat('Y-m-d', $price_year . '-' . $price_mes . '-' . $i)->format('D') == 'Sun') {
					$sheet->getStyle(Coordinate::stringFromColumnIndex($i + 3) . '3')->applyFromArray([
						'fill' => [
							'fillType' => Fill::FILL_SOLID,
							'startColor' => ['argb' => Color::COLOR_RED],
						],
					]);
				}
			}

			/* End of headers */

			$row_number = self::START_AT_LINE;
			$subTotalFromRow = $row_number;
			$index = 0;

			foreach ($response as $element) {
				$index++;
				$sheet->setCellValueExplicit('A'. $row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B' .$row_number, $element->client_channel_name);
				$sheet->setCellValue('C'. $row_number, $element->name);

				for ($i = 1; $i <= $colMaxNumber; $i++) { // Days from 1 to n
					$tmpProp = 'tm_' . str_pad($i, 2, "0", STR_PAD_LEFT);

					$sheet->setCellValue(Coordinate::stringFromColumnIndex($i + 3) . $row_number, $element->{$tmpProp});
					$sheet->getStyle(Coordinate::stringFromColumnIndex($i + 3) . $row_number)->getNumberFormat()->setFormatCode('0.0');
				}

				if ($element->isLastOfOption) {
					$row_number++;
					$index++;

					$sheet->setCellValueExplicit('A'. $row_number, $index, DataType::TYPE_NUMERIC);
					$sheet->setCellValue('B'. $row_number, 'Total');

					for ($i = 1; $i <= $colMaxNumber; $i++) { // SubTotals from 1 to n
						$columnName = Coordinate::stringFromColumnIndex($i + 3);
						$subtotalCells[$i][] = $columnName . $row_number;

						$sheet->setCellValue($columnName . $row_number, '=SUM(' . $columnName . $subTotalFromRow . ':' . $columnName . ($row_number - 1) . ')');
						$sheet->getStyle($columnName . $row_number)->getNumberFormat()->setFormatCode('0.0');
					}

					$sheet->getStyle('A' . $row_number . ':' . Coordinate::stringFromColumnIndex($colMaxNumber + 3) . $row_number)->applyFromArray([
						'font' => [
							'bold' => true,
						],
						'fill' => [
							'fillType' => Fill::FILL_SOLID,
							'startColor' => ['argb' => Color::COLOR_YELLOW],
						],
					]);

					$subTotalFromRow = $row_number + 1;
				}

				$row_number++;
			}

			$sheet->setCellValueExplicit('A'. $row_number, $index + 1, DataType::TYPE_NUMERIC);
			$sheet->setCellValue('B'. $row_number, 'TOTAL');

			for ($i = 1; $i <= $colMaxNumber; $i++) { // Totals from 1 to n
				$columnName = Coordinate::stringFromColumnIndex($i + 3);

				$sheet->setCellValue($columnName . $row_number, '=SUM(' . implode(',', $subtotalCells[$i]) . ')');
				$sheet->getStyle($columnName . $row_number)->getNumberFormat()->setFormatCode('0.0');
			}

			$sheet->getStyle('A' . $row_number . ':' . Coordinate::stringFromColumnIndex($colMaxNumber + 3) . $row_number)->applyFromArray([
				'font' => [
					'bold' => true,
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => ['argb' => Color::COLOR_DARKYELLOW],
				],
			]);


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
	








