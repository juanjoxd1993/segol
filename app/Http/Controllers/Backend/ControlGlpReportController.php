<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseMovementDetail;
use App\WarehouseMovement;
use App\WarehouseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class ControlGlpReportController extends Controller
{
  public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$warehouse_types = WarehouseType::select('id', 'name')->whereIn('type',[2,3,4])->get();
		return view('backend.control_glp_report')->with(compact('companies', 'current_date','warehouse_types'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
			'final_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			'initial_date'	=> 'required',
			'final_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {

		$export = request('export');

		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d H:i:s');
		$company_id = request('model.company_id');
		$warehouse_type = request('model.warehouse_type');
		$warehouse_type_id = request('model.warehouse_type_id');

		$types = WarehouseType::select('id')->where('type', '=', $warehouse_type)->get();

		if ($warehouse_type_id) {
			$types[] = $warehouse_type_id;
		};

		$elements = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')
		->leftjoin('companies', 'warehouse_movements.company_id', '=', 'companies.id')
		->leftjoin('warehouse_types', 'warehouse_movements.warehouse_type_id', '=', 'warehouse_types.id')
		->leftjoin('articles', 'warehouse_movement_details.article_code', '=', 'articles.id')
		->leftjoin('warehouse_document_types', 'warehouse_movements.referral_warehouse_document_type_id', '=', 'warehouse_document_types.id')
		->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
		->leftjoin('movent_classes', 'warehouse_movements.movement_class_id', '=', 'movent_classes.id')
		->where('warehouse_movements.created_at', '>=', $initial_date)
		->where('warehouse_movements.created_at', '<=', $final_date)
		->select('warehouse_movements.id', 
		'companies.short_name as company_short_name', 
		DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as sale_date'),
		'warehouse_document_types.short_name as warehouse_document_type_short_name', 
		'warehouse_movements.referral_serie_number', 
		'warehouse_movements.referral_voucher_number',
		'articles.name as article_name',
		'warehouse_movement_details.converted_amount as sum_total',
		'warehouse_movement_details.digit_amount as digit_amount',
		'warehouse_types.short_name as warehouse_type_code',
		'warehouse_types.name as warehouse_type_name',  
		'movent_types.name as movement_type_name', 
		'movent_classes.name as movement_class_name', 
		DB::Raw('CONCAT(warehouse_movements.referral_guide_series, "-", warehouse_movements.referral_guide_number) as guide'),
		'warehouse_movements.license_plate as plate')
		->when($company_id, function($query, $company_id) {
			return $query->where('warehouse_movements.company_id', $company_id);
		})
		->when($types, function($query, $types) {
			return $query->whereIn('warehouse_movements.warehouse_type_id', $types);
		})
		->groupBy('warehouse_movements.id')
		->orderBy('sale_date')
		->orderBy('warehouse_document_type_short_name')
		->orderBy('warehouse_movements.referral_serie_number')
		->orderBy('warehouse_movements.referral_voucher_number')
		->get();

		$response=[];

			foreach ($elements as $saledetail) {

				$saledetail->company_short_name = $saledetail['company_short_name'];
				$saledetail->sale_date = $saledetail['sale_date'];
				$saledetail->warehouse_document_type_short_name = $saledetail['warehouse_document_type_short_name'];
				$saledetail->referral_serie_number = $saledetail['referral_serie_number'];
				$saledetail->referral_voucher_number = $saledetail['referral_voucher_number'];
				$saledetail->article_name =$saledetail ['article_name'];
				$saledetail->sum_total = $saledetail['sum_total'];
				$saledetail->digit_amount = $saledetail['digit_amount'];
				$saledetail->warehouse_type_code= $saledetail['warehouse_type_code'];
				$saledetail->warehouse_type_name =$saledetail ['warehouse_type_name'];
				$saledetail->movement_type_name = $saledetail['movement_type_name'];
				$saledetail->movement_class_name = $saledetail['movement_class_name'];
				$saledetail->guide = $saledetail['guide'];
				$saledetail->plate = $saledetail['plate'];

				$response[] = $saledetail;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->sale_date = '';
		$totals->movement_type_name = '';
		$totals->movement_class_name = '';
		$totals->warehouse_type_code = '';
		$totals->warehouse_type_name = '';
		$totals->warehouse_document_type_short_name = '';
		$totals->referral_serie_number = '';
		$totals->referral_voucher_number = '';
		$totals->article_name = '';
		$totals->sum_total = '';
		$totals->digit_amount = '';
		$totals->guide = '';
		$totals->plate = '';

		$response[] = $totals;

		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'REPORTE DE LIQUIDACIONES DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Fecha Emisión');
			$sheet->setCellValue('D3', 'Movimiento');
			$sheet->setCellValue('E3', 'Tipo');
			$sheet->setCellValue('F3', 'Cod');
			$sheet->setCellValue('G3', 'Almacen');
			$sheet->setCellValue('H3', 'Tipo Documento');
			$sheet->setCellValue('I3', '# Serie');
			$sheet->setCellValue('J3', '# Documento');
			$sheet->setCellValue('K3', 'Producto');
			// change name kilos get converted amount
			$sheet->setCellValue('L3', 'Kilos');
			// add cantidad get digit amount
			$sheet->setCellValue('M3', 'Cantidad');
			$sheet->setCellValue('N3', 'Guía');
			$sheet->setCellValue('O3', 'Placa');
			$sheet->getStyle('A3:O3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->sale_date);
				$sheet->setCellValue('D'.$row_number, $element->movement_type_name);
				$sheet->setCellValue('E'.$row_number, $element->movement_class_name);
				$sheet->setCellValue('F'.$row_number, $element->warehouse_type_code);
				$sheet->setCellValue('G'.$row_number, $element->warehouse_type_name);
				$sheet->setCellValue('H'.$row_number, $element->warehouse_document_type_short_name);
				$sheet->setCellValue('I'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('J'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('K'.$row_number, $element->article_name);
				$sheet->setCellValue('L'.$row_number, $element->sum_total);			
				$sheet->setCellValue('M'.$row_number, $element->digit_amount);			
				$sheet->setCellValue('N'.$row_number, $element->guide);
				$sheet->setCellValue('O'.$row_number, $element->plate);			
				$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');			
				$sheet->getStyle('M'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

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