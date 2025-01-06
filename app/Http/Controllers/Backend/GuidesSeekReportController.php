<?php

namespace App\Http\Controllers\Backend;


use App\Company;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class GuidesSeekReportController extends Controller
{
	public function index()
	{
		$companies = Company::select('id', 'name')->whereIn('id', [2])->get();
		return view('backend.guides_seek_report')->with(compact('companies'));
	}

	public function validateForm()
	{
		$messages = [];

		$rules = [];

		request()->validate($rules, $messages);
		return request()->all();
	}



	public function list()
	{

		$export = request('export');

		$company_id = request('model.company_id');
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');

		$elements = WarehouseMovement::leftjoin('companies', 'warehouse_movements.company_id', '=', 'companies.id')
			->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')
			->leftjoin('articles', 'warehouse_movement_details.article_code', '=', 'articles.id')
			->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
			->select(
				'warehouse_movement_details.id',
				'companies.name as company_name',
				DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as guide_date'),
				'fac_date as traslate_date',
				'movent_types.name as movement_type_name',
				DB::Raw('CONCAT(warehouse_movements.referral_guide_series, "-", warehouse_movements.referral_guide_number) as guide'),
				DB::Raw('CONCAT(warehouse_movements.referral_serie_number, "-", warehouse_movements.referral_voucher_number) as elect'),
				DB::Raw('CASE WHEN warehouse_movements.electronic = 1 THEN "Electrónico" ELSE "Físico" END as tipo_guia'),
				DB::Raw('CASE 
							WHEN warehouse_movements.state = 1 THEN "Generada" 
							WHEN warehouse_movements.state = 2 THEN "Validada" 
							WHEN warehouse_movements.state = 3 THEN "Por liquidar" 
							WHEN warehouse_movements.state = 4 THEN "Liquidada" 
							WHEN warehouse_movements.state = 5 THEN "Por Autorizar" 
							WHEN warehouse_movements.state = 6 THEN "Anulada" 
							ELSE "NA" 
							END as state_guia'),
				'warehouse_movements.license_plate as plate',
				'warehouse_movement_details.article_code as article_code',
				'articles.name as article_name',
				'warehouse_movement_details.digit_amount as quantity',
				'warehouse_movement_details.new_stock_return as return'
			)

			->when($company_id, function ($query, $company_id) {
				return $query->where('warehouse_movements.company_id', $company_id);
			})
			->when($referral_guide_series, function ($query, $referral_guide_series) {
				return $query->where('referral_guide_series', $referral_guide_series);
			})
			->when($referral_guide_number, function ($query, $referral_guide_number) {
				return $query->where('referral_guide_number', $referral_guide_number);
			})
			->whereIn('warehouse_movements.movement_type_id', [11, 12])
			->groupBy('warehouse_movement_details.id')
			->orderBy('guide_date', 'desc')
			->orderBy('warehouse_movement_details.item_number', 'asc')
			->get();
		$response = [];

		foreach ($elements as $warehouse_movement) {

			$warehouse_movement->company_name = $warehouse_movement['company_name'];
			$warehouse_movement->created_at = $warehouse_movement['guide_date'];
			$warehouse_movement->traslate_date = $warehouse_movement['traslate_date'];
			$warehouse_movement->movement_type_name = $warehouse_movement['movement_type_name'];
			$warehouse_movement->article_code = $warehouse_movement['article_code'];
			$warehouse_movement->article_name = $warehouse_movement['article_name'];
			$warehouse_movement->quantity = $warehouse_movement['quantity'];
			$warehouse_movement->return = $warehouse_movement['return'];
			$warehouse_movement->guide = $warehouse_movement['guide'];
			$warehouse_movement->state = $warehouse_movement['state_guia'];
			$warehouse_movement->plate = $warehouse_movement['plate'];

			$response[] = $warehouse_movement;
		}


		$totals = new stdClass();
		$totals->company_name = 'TOTAL';
		$totals->guide_date = '';
		$totals->traslate_date = '';
		$totals->movement_type_name = '';
		$totals->article_code = '';
		$totals->article_name = '';
		$totals->quantity = '';
		$totals->return = '';
		$totals->guide = '';
		$totals->elect = '';
		$totals->state = '';
		$totals->tipo_guia = '';
		$totals->plate = '';


		$response[] = $totals;

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:N1');
			$sheet->setCellValue('A1', 'REPORTE DE GUIAS DEL ' . CarbonImmutable::now()->format('d/m/Y H:i:s'));
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
			$sheet->setCellValue('B3', 'Compañía');
			$sheet->setCellValue('C3', 'Fecha de Despacho');
			$sheet->setCellValue('D3', 'Fecha de Traslado');
			$sheet->setCellValue('E3', 'Tipo Movimiento');
			$sheet->setCellValue('F3', 'Codigo');
			$sheet->setCellValue('G3', 'Articulo');
			$sheet->setCellValue('H3', 'Salida');
			$sheet->setCellValue('I3', 'Llenos');
			$sheet->setCellValue('J3', 'Estado');
			$sheet->setCellValue('K3', 'Tipo de Guía');
			$sheet->setCellValue('L3', 'Guía Física');
			$sheet->setCellValue('M3', 'Guía Electrónica');
			$sheet->setCellValue('N3', 'Placa');
			$sheet->getStyle('A3:N3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A' . $row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B' . $row_number, $element->company_name);
				$sheet->setCellValue('C' . $row_number, $element->guide_date);
				$sheet->setCellValue('D' . $row_number, $element->traslate_date);
				$sheet->setCellValue('E' . $row_number, $element->movement_type_name);
				$sheet->setCellValue('F' . $row_number, $element->article_code);
				$sheet->setCellValue('G' . $row_number, $element->article_name);
				$sheet->setCellValue('H' . $row_number, $element->quantity);
				$sheet->setCellValue('I' . $row_number, $element->return);
				$sheet->setCellValue('J' . $row_number, $element->state);
				$sheet->setCellValue('K' . $row_number, $element->tipo_guia);
				$sheet->setCellValue('L' . $row_number, $element->guide);
				$sheet->setCellValue('M' . $row_number, $element->elect);
				$sheet->setCellValue('N' . $row_number, $element->plate);

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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
