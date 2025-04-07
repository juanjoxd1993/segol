<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SaleDetail;
use App\Sale;
use App\WarehouseMovementDetail;
use App\BusinessUnit;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use App\WarehouseMovement;
use Illuminate\Support\Arr;
use App\Inventory;
use stdClass;


class AndExcedentReportController extends Controller
{
	public function index()
	{
		$business_units = BusinessUnit::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$companies = Company::select('id', 'name')->get();
		return view('backend.and_excedent_report')->with(compact('business_units', 'current_date', 'companies'));
	}

	public function validateForm()
	{
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



	public function list()
	{

		$export = request('export');

		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-01');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d');

		$elements = Inventory::leftjoin('companies', 'inventories.company_id', '=', 'companies.id')
			->leftjoin('articles', 'inventories.article_id', '=', 'articles.id')
			->leftjoin('warehouse_types', 'inventories.warehouse_type_id', '=', 'warehouse_types.id')
			->where('inventories.creation_date', '>=', $initial_date)
			->where('inventories.creation_date', '<=', $final_date)
			->select(
				'inventories.creation_date as creation_date',
				'companies.name as company_name',
				'articles.name as article_name',
				'inventories.found_stock_good as stock',
				'warehouse_types.name as warehouse_name'
			)

			->groupBy('inventories.creation_date')
			->get();
		$response = [];

		foreach ($elements as $inventory) {
			$inventory->creation_date = $inventory['creation_date'];
			$inventory->stock = $inventory['stock'];

			$stock_est = Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', 'warehouse_types.id');

			$fecha_anterior = date("Y-m-d", strtotime($inventory->creation_date . "-1 days"));
			$stock_anterior = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->where('inventories.creation_date', $fecha_anterior)
				->whereIn('articles.id', [4791,4792])
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$inventory->stock_anterior = $stock_anterior;

			$stock_piso_5k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [9292, 9296, 9300, 9304, 9308])
				->where('inventories.creation_date', $fecha_anterior)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_piso_10k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					4841,
					4846,
					4954,
					4956
				])
				->where('inventories.creation_date', $fecha_anterior)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_piso_15k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					9294,
					9298,
					9302,
					9306,
					9310,
					9975,
				])
				->where('inventories.creation_date', $fecha_anterior)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_piso_45k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					4844,
					4848,
					4957,
					4958
				])
				->where('inventories.creation_date', $fecha_anterior)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_piso = ($stock_piso_5k * 5) + ($stock_piso_10k * 10) + ($stock_piso_15k * 15) + ($stock_piso_45k * 45);
			$inventory->stock_piso = $stock_piso;

			$stock_inicial = $inventory->stock_anterior + $inventory->stock_piso;
			$inventory->stock_inicial = $stock_inicial;
			$ingresos_glp = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movements.id', 'warehouse_movement_details.warehouse_movement_id')
                ->where('warehouse_movements.movement_type_id', 30)
                ->where('warehouse_movements.created_at',  $inventory->creation_date)
                ->select('warehouse_movement_details.converted_amount')
                ->sum('warehouse_movement_details.converted_amount');
            $inventory->ingresos_glp = $ingresos_glp;

            $stock_tienda_10k = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movements.id', 'warehouse_movement_details.warehouse_movement_id')
                ->leftjoin('articles', 'warehouse_movement_details.article_code', 'articles.id')
                ->whereIn('articles.id', [
                    4841,
                    4846
                ])
                ->where('warehouse_movements.created_at', $fecha_anterior)
                ->select('warehouse_movement_details.converted_amount')
                ->sum('warehouse_movement_details.converted_amount');

            $stock_tienda_45k = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movements.id', 'warehouse_movement_details.warehouse_movement_id')
            ->leftjoin('articles', 'warehouse_movement_details.article_code', 'articles.id')
            ->whereIn('articles.id', [
                4844,
                4848
            ])
            ->where('warehouse_movements.created_at', $fecha_anterior)
            ->select('warehouse_movement_details.converted_amount')
            ->sum('warehouse_movement_details.converted_amount');


			$stock_tienda =  ($stock_tienda_10k * 10)  + ($stock_tienda_45k * 45);

			$inventory->stock_tienda = $stock_tienda;


			$stock_venta_5k = Sale::leftjoin('sale_details', 'sales.id', 'sale_details.sale_id')
				->leftjoin('articles', 'sale_details.article_id', 'articles.id')
				->whereIn('sale_details.article_id', [9292, 9296, 9300, 9304, 9308])
				->whereIn('sales.warehouse_document_type_id', [31,5])
				->where('sales.sale_date', '=', $fecha_anterior)
				->select('sale_details.quantity')
				->sum('sale_details.quantity');

			$stock_venta_10k = Sale::leftjoin('sale_details', 'sales.id', 'sale_details.sale_id')
				->leftjoin('articles', 'sale_details.article_id', 'articles.id')
				->whereIn('sale_details.article_id', [
					4773,
					4777
				])
				->where('sales.sale_date', '=', $fecha_anterior)
				->whereIn('sales.warehouse_document_type_id', [31,5])
				->select('sale_details.quantity')
				->sum('sale_details.quantity');

			$stock_venta_15k = Sale::leftjoin('sale_details', 'sales.id', 'sale_details.sale_id')
				->leftjoin('articles', 'sale_details.article_id', 'articles.id')
				->whereIn('sale_details.article_id', [
					4774
				])
				->whereIn('sales.warehouse_document_type_id', [31,5])
				->where('sales.sale_date', '=', $fecha_anterior)
				->select('sale_details.quantity')
				->sum('sale_details.quantity');

			$stock_venta_45k = Sale::leftjoin('sale_details', 'sales.id', 'sale_details.sale_id')
				->leftjoin('articles', 'sale_details.article_id', 'articles.id')
				->whereIn('sale_details.article_id', [
					4775,4779
				])
				->whereIn('sales.warehouse_document_type_id', [31,5])
				->where('sales.sale_date', '=', $fecha_anterior)
				->select('sale_details.quantity')
				->sum('sale_details.quantity');

			$stock_venta = ($stock_venta_5k * 5) + ($stock_venta_10k * 10) + ($stock_venta_15k * 15) + ($stock_venta_45k * 45);

			$stock_teorico= $inventory->stock_inicial+$inventory->ingresos_glp-$inventory->stock_tienda-$stock_venta;
			$inventory->stock_teorico =$stock_teorico;

			$stock_tanque = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
			->where('inventories.creation_date', $inventory->creation_date)
			->whereIn('articles.id', [4791,4792])
			->select('inventories.found_stock_good')
			->sum('inventories.found_stock_good');
			$inventory->stock_tanque= $stock_tanque;


			$stock_planta_5k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [9292, 9296, 9300, 9304, 9308])
				->where('inventories.creation_date', $inventory->creation_date)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_planta_10k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					4841,
					4954,
					4956,
					4846])
				->where('inventories.creation_date', $inventory->creation_date)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_planta_15k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					9294,
					9298,
					9302,
					9306,
					9310,
					9975
				])
				->where('inventories.creation_date', $inventory->creation_date)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_planta_45k = Inventory::leftjoin('articles', 'inventories.article_id', 'articles.id')
				->whereIn('articles.id', [
					4844,
					4848,
					4957,
					4958
				])
				->where('inventories.creation_date', $inventory->creation_date)
				->select('inventories.found_stock_good')
				->sum('inventories.found_stock_good');

			$stock_planta = ($stock_planta_5k * 5) + ($stock_planta_10k * 10) + ($stock_planta_15k * 15) + ($stock_planta_45k * 45);
			$inventory->stock_planta = $stock_planta;



			$stock_fisico_final=$inventory->stock_tanque+$inventory->stock_planta;
			$inventory->stock_fisico_final = $stock_fisico_final;




			$inventory->stock_venta = $stock_venta;
			$inventory->stock_venta_5k = $stock_venta_5k;
			$inventory->stock_venta_10k = $stock_venta_10k;
			$inventory->stock_venta_15k = $stock_venta_15k;
			$inventory->stock_venta_45k = $stock_venta_45k;


			$inventory->company_name = $inventory['company_name'];
			$inventory->article_name = $inventory['article_name'];
			$inventory->warehouse_name = $inventory['warehouse_name'];
			$response[] = $inventory;
		}

		$totals = new stdClass();
		$totals->creation_date = 'TOTAL';
		$totals->stock = '';
		$totals->company_name = '';
		$totals->article_name = '';
		$totals->warehouse_name = '';
		$totals->article_name = '';		
		$totals->stock_fisico_final = '';
		$totals->stock_planta = '';
		$totals->stock_tanque = '';
		$totals->stock_teorico = '';
		$totals->stock_anterior = '';
		$totals->stock_piso = '';
		$totals->stock_inicial = '';
		$totals->ingresos_glp = '';
		$totals->stock_tienda = '';
		$totals->stock_venta_5k = '';
		$totals->stock_venta_10k = '';
		$totals->stock_venta_15k = '';
		$totals->stock_venta_45k = '';
		$totals->stock_venta = '';

		$response[] = $totals;

		if ($export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->mergeCells('B2:D2');
			$sheet->mergeCells('E2:G2');
			$sheet->mergeCells('H2:M2');
			$sheet->mergeCells('O2:Q2');
			$sheet->mergeCells('R2:U2');
			$sheet->mergeCells('V2:AA2');

			$sheet->setCellValue('A1', 'EXCEDENTE DE GLP ' . CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'color' => array('rgb' => '000000'),
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'fcf3cf')
				]
			]);

			$sheet->setCellValue('B2', 'Stock Inicial');
			$sheet->getStyle('B2')->applyFromArray([
				'font' => [
					'Verdana' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f9e79f')
				]
			]);

			$sheet->setCellValue('E2', 'Ingreso / Salidas');
			$sheet->getStyle('E2')->applyFromArray([
				'font' => [
					'Verdana' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f7dc6f')
				]
			]);

			$sheet->setCellValue('H2', 'Despachos');
			$sheet->getStyle('H2')->applyFromArray([
				'font' => [
					'Verdana' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f4d03f')
				]
			]);

			

			$sheet->setCellValue('O2', 'Stock Físico');
			$sheet->getStyle('O2')->applyFromArray([
				'font' => [
					'Verdana' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' =>  array('rgb' => 'd4ac0d')
				]
			]);

			$sheet->setCellValue('V2', 'Cálculos');
			$sheet->getStyle('V2')->applyFromArray([
				'font' => [
					'Verdana' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				],
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '9a7d0a')
				]
			]);

			$sheet->setCellValue('A3', 'FECHA');

			$sheet->setCellValue('B3', 'Stock en estacionarios');
			$sheet->getStyle('B3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f5eef8')
				]
			]);
			$sheet->setCellValue('C3', 'Stock en piso');
			$sheet->getStyle('C3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'ebdef0')
				]
			]);
			$sheet->setCellValue('D3', 'Stock inicial');
			$sheet->getStyle('D3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'd7bde2')
				]
			]);
			$sheet->setCellValue('E3', 'Ingresos');
			$sheet->getStyle('E3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'd7bde2')
				]
			]);
			$sheet->setCellValue('F3', 'GLP Granel a Terceros');
			$sheet->getStyle('F3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'ebdef0')
				]
			]);
			$sheet->setCellValue('G3', 'Graneleras');
			$sheet->getStyle('G3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f5eef8')
				]
			]);
		
			$sheet->setCellValue('H3', 'Local de Venta');
			$sheet->getStyle('H3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'f5eef8')
				]
			]);
			$sheet->setCellValue('I3', '5 Kg');
			$sheet->getStyle('I3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			$sheet->setCellValue('J3', '10 Kg');
			$sheet->getStyle('J3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'd4e6f1')
				]
			]);
			$sheet->setCellValue('K3', '15 Kg');
			$sheet->getStyle('K3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'a9cce3')
				]
			]);
			$sheet->setCellValue('L3', '45 Kg');
			$sheet->getStyle('L3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '7fb3d5')
				]
			]);
			$sheet->setCellValue('M3', 'Total Kg');
			$sheet->getStyle('M3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '5499c7')
				]
			]);
			/*
			$sheet->setCellValue('M3', '5 Kg');
			$sheet->getStyle('M3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '5499c7')
				]
			]);
			$sheet->setCellValue('N3', '10 Kg');
			$sheet->getStyle('N3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => '7fb3d5')
				]
			]);
			$sheet->setCellValue('O3', '15 Kg');
			$sheet->getStyle('O3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'a9cce3')
				]
			]);
			$sheet->setCellValue('P3', '45 Kg');
			$sheet->getStyle('P3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'd4e6f1')
				]
			]);
			*/
			$sheet->setCellValue('N3', 'Stock Teorico GLP Kg');
			$sheet->getStyle('N3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);

			$sheet->setCellValue('O3', 'Stock en estacionario');
			$sheet->getStyle('O3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			$sheet->setCellValue('P3', 'Stock en piso');
			$sheet->getStyle('P3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			$sheet->setCellValue('Q3', 'Stock Físico');
			$sheet->getStyle('Q3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			$sheet->setCellValue('R3', 'Diferencial');
			$sheet->getStyle('R3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			$sheet->setCellValue('S3', 'Acumulado');

			$sheet->getStyle('S3')->applyFromArray([
				'fill' => [
					'fillType' => Fill::FILL_SOLID,
					'startColor' => array('rgb' => 'eaf2f8')
				]
			]);
			

			$sheet->getStyle('A3:AA3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;

				$sheet->setCellValue('A' . $row_number, $element->creation_date);
				$sheet->setCellValue('B' . $row_number, $element->stock_anterior);
				$sheet->setCellValue('C' . $row_number, $element->stock_piso);
				$sheet->setCellValue('D' . $row_number, $element->stock_inicial);
				$sheet->setCellValue('E' . $row_number, $element->ingresos_glp);
				$sheet->setCellValue('H' . $row_number, $element->stock_tienda);
				$sheet->setCellValue('I' . $row_number, $element->stock_venta_5k);
				$sheet->setCellValue('J' . $row_number, $element->stock_venta_10k);
				$sheet->setCellValue('K' . $row_number, $element->stock_venta_15k);
				$sheet->setCellValue('L' . $row_number, $element->stock_venta_45k);
				$sheet->setCellValue('M' . $row_number, $element->stock_venta);
				$sheet->setCellValue('N' . $row_number, $element->stock_teorico);
				$sheet->setCellValue('O' . $row_number, $element->stock_tanque);
				$sheet->setCellValue('P' . $row_number, $element->stock_planta);
				$sheet->setCellValue('Q' . $row_number, $element->stock_fisico_final);
				
	
				//   $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O' . $row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P' . $row_number)->getNumberFormat()->setFormatCode('0.00');


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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
