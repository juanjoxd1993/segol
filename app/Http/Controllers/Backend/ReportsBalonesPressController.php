<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\WarehouseMovement;
use App\Article;
use App\Sale;
use App\Client;
use App\Container;
use App\ContainerDetail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ReportsBalonesPressController extends Controller {
  public function index() {
		$plantas = WarehouseType::select('id', 'name')
                          ->where('type', 3)
                          ->get();

		return view('backend.reports_balones_press')->with(compact('plantas'));
	}

	public function getContainers() {
		
		$movement_type_id = request('movement_type_id');
		$export = request('export');
		$date_init = CarbonImmutable::createFromDate(request('date_init'))->startOfDay()->format('Y-m-d');
		$date_end = CarbonImmutable::createFromDate(request('date_end'))->endOfDay()->format('Y-m-d');
	
		$containers = Container::where('date', '>=', $date_init)
							->where('date', '<=', $date_end)
							->where('if_devol', $movement_type_id)
							->select('id', 'client_id', 'if_devol', 'warehouse_movement', 'date')
							->get();
	
		// Usar una colección temporal para almacenar los contenedores válidos
		$validContainers = collect();
	
		foreach ($containers as $container) {
			$container_detail = ContainerDetail::where('container_id', $container->id)->first();
	
			if ($container_detail) {
				$client = Client::find($container->client_id);
				$warehouse_movement = WarehouseMovement::find($container->warehouse_movement);
	
				if ($warehouse_movement) {
					$article = Article::find($container_detail->article_id);
					$warehouse_type = WarehouseType::find($warehouse_movement->warehouse_type_id);
	
					if ($warehouse_type) {
						$container->client_name = $client->business_name;
						$container->stock = $container_detail->devol;
						$container->article_name = $article->name;
						$container->warehouse_type_name = $warehouse_type->name;
	
						// Solo añadir el contenedor a la colección si cumple con todos los criterios
						$validContainers->push($container);
					}
				}
			}
		}
							

    if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'REPORTE DE BALONES PRESTADOS '.$date_init.' AL '.$date_end.'  '.'CONSULTADO EL'.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'Planta');
			$sheet->setCellValue('C3', 'Cliente');
			$sheet->setCellValue('D3', 'Fecha de Registro'); 
			$sheet->setCellValue('E3', 'Stock');

			$sheet->getStyle('A3:E3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($validContainers as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->warehouse_type_name);
				$sheet->setCellValue('C'.$row_number, $element->client_name);
				$sheet->setCellValue('D'.$row_number, $element->date);
				$sheet->setCellValue('D'.$row_number, CarbonImmutable::createFromDate($element->date)->format('d/m/Y'));
				$sheet->setCellValue('E'.$row_number, $element->stock);

				$row_number++;
			}

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return $validContainers;
    }
	}
}