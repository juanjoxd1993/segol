<?php

namespace App\Http\Controllers\Backend;

use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\SaleDetail;
use App\Sale;
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


class 	DaysGlpReportController extends Controller
{
    public function index() {
		
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.days_glp_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			
			'initial_date.required'	=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			
			'initial_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	

	public function list() {

		$export = request('export');

	    $mes_date = CarbonImmutable::createFromDate(request('model.initial_date'))->endOfDay()->format('Y-m-d');
        $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->format('Y-m-01');
		$final_date = CarbonImmutable::createFromDate(request('model.initial_date'))->endOfDay()->format('m');

		DB::enableQueryLog();
		
		
			$elements = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->leftjoin('warehouse_types', 'warehouse_movements.warehouse_type_id', '=', 'warehouse_types.id')
			->leftjoin('articles', 'warehouse_movement_details.article_code', '=', 'articles.id')
			->where('warehouse_movements.price_mes', $final_date)
            ->where('warehouse_movements.movement_type_id', 30)
           // ->where('warehouse_movements.created_at', '<=', $mes_date)
			->whereIn('warehouse_movements.warehouse_type_id',[8,9,10,11])
			->where('warehouse_movements.movement_class_id', 2)
			->select(
			'warehouse_movements.id',
			'warehouse_movements.warehouse_type_id', 
			'warehouse_movements.movement_class_id', 
		    'warehouse_movements.movement_type_id', 
		    'warehouse_movements.movement_stock_type_id', 
			DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as issue_date'),
		    'warehouse_movements.account_name as account_name', 
		    'warehouse_movements.license_plate as license_plate', 
		    DB::Raw('CONCAT(referral_serie_number, "-", referral_voucher_number) as factura'),
			'warehouse_movements.referral_serie_number', 
			'warehouse_movements.referral_voucher_number', 
			'warehouse_movements.price_mes',
			'warehouse_movements.scop_number',
			'warehouse_movements.total',
			'warehouse_movements.igv',
			'warehouse_types.name as proveedor',
			'warehouse_movement_details.converted_amount as cantidad',
			'articles.name as producto')
            ->orderBy('issue_date')
            ->get();
			$response=[];

			


		foreach ($elements as $saledetail) {
				
		   $saledetail->issue_date = $saledetail ['issue_date'];
		   $saledetail->factura = $saledetail ['factura'];
		   $saledetail->proveedor = $saledetail ['proveedor'];
		   $saledetail->producto = $saledetail ['producto'];
           $saledetail->placa = $saledetail ['license_plate'];
		   $saledetail->cantidad = $saledetail ['cantidad'];
		   $saledetail->cost_kg= $saledetail ['igv'];
		   $saledetail->soles = $saledetail ['total'];
		   $saledetail->scop_number = $saledetail ['scop_number'];
		   $saledetail->account_name = $saledetail ['account_name'];


		   $response[] = $saledetail;

			}


		/*$totals = new stdClass();
		$totals->soles_glp_e = '';
		$totals->kgs_glp_e = '';
		$totals->cost_glp = '';
		
		$response[] = $totals;*/






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:K1');
			$sheet->setCellValue('A1', 'REPORTE DE COSTO GLP AL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'FECHA');
			$sheet->setCellValue('C3', 'FACTURA');
			$sheet->setCellValue('D3', 'PROVEEDOR');
			$sheet->setCellValue('E3', 'PRODUCTO');
			$sheet->setCellValue('F3', 'CISTERNA');
			$sheet->setCellValue('G3', 'CANTIDAD');
			$sheet->setCellValue('H3', 'COSTO KG');
			$sheet->setCellValue('I3', 'COSTO SOLES');
			$sheet->setCellValue('J3', 'SCOP');
			$sheet->setCellValue('K3', 'CONDUCTOR');
           
			
			$sheet->getStyle('A3:K3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->issue_date);
				$sheet->setCellValue('C'.$row_number, $element->factura);
				$sheet->setCellValue('D'.$row_number, $element->proveedor);
				$sheet->setCellValue('E'.$row_number, $element->producto);
				$sheet->setCellValue('F'.$row_number, $element->placa);
				$sheet->setCellValue('G'.$row_number, $element->cantidad);
				$sheet->setCellValue('H'.$row_number, $element->cost_kg);
				$sheet->setCellValue('I'.$row_number, $element->soles);
				$sheet->setCellValue('J'.$row_number, $element->scop_number);
				$sheet->setCellValue('K'.$row_number, $element->account_name);
              

		
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
			


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








