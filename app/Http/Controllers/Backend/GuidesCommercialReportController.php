<?php

namespace App\Http\Controllers\Backend;


use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseMovementDetail;
use App\WarehouseMovement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class GuidesCommercialReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.guides_commercial_report')->with(compact('companies', 'current_date'));
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
		$warehouse_movement_id = request('model.warehouse_movement_id');
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');
		
					$elements = WarehouseMovement::leftjoin('companies', 'warehouse_movements.company_id', '=', 'companies.id')
                        ->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
			            ->where('warehouse_movements.created_at', '>=', $initial_date)
			            ->where('warehouse_movements.created_at', '<=', $final_date)
			->select('warehouse_movements.id', 'companies.short_name as company_short_name', DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as guide_date'),'traslate_date', DB::Raw('CONCAT("R-", warehouse_movements.route_id) as route_id'), 'movent_types.name as movement_type_name', DB::Raw('CONCAT(warehouse_movements.referral_guide_series, "-", warehouse_movements.referral_guide_number) as guide'),'warehouse_movements.state as state','warehouse_movements.license_plate as plate')

			->when($company_id, function($query, $company_id) {
				return $query->where('warehouse_movements.company_id', $company_id);
			})
			->when($warehouse_movement_id, function($query, $warehouse_movement_id) {
				return $query->where('warehouse_movements.id', $warehouse_movement_id);
			})
			->when($referral_guide_series, function($query, $referral_guide_series) {
				return $query->where('referral_guide_series', $referral_guide_series);
			})
			->when($referral_guide_number, function($query, $referral_guide_number) {
				return $query->where('referral_guide_number', $referral_guide_number);
			})
			->groupBy('warehouse_movements.id')
			->orderBy('warehouse_movements.company_id')
            ->orderBy('guide_date')
			->get();
			$response=[];


			foreach ($elements as $warehouse_movement) {


				$warehouse_movement->warehouse_movement_id = $warehouse_movement['id'];
				$warehouse_movement->company_short_name = $warehouse_movement['company_short_name'];
				$warehouse_movement->created_at = $warehouse_movement['guide_date'];
                $warehouse_movement->traslate_date = $warehouse_movement['traslate_date'];
				$warehouse_movement->route_id = $warehouse_movement['route_id'];
				$warehouse_movement->movement_type_name = $warehouse_movement['movement_type_name'];
				$warehouse_movement->guide = $warehouse_movement['guide'];
                $warehouse_movement->state = $warehouse_movement['state'];
				$warehouse_movement->plate = $warehouse_movement['plate'];
			



				$response[] = $warehouse_movement;

			}








		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'REPORTE DE GUIAS DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
            $sheet->setCellValue('E3', 'Ruta');
			$sheet->setCellValue('F3', 'Tipo Movimiento');
            $sheet->setCellValue('G3', 'Estado');
			$sheet->setCellValue('H3', 'Guía');
			$sheet->setCellValue('I3', 'Placa');
			$sheet->getStyle('A3:I3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->guide_date);
				$sheet->setCellValue('D'.$row_number, $element->traslate_date);
                $sheet->setCellValue('E'.$row_number, $element->route_id);
				$sheet->setCellValue('F'.$row_number, $element->movement_type_name);
                $sheet->setCellValue('G'.$row_number, $element->state);
				$sheet->setCellValue('H'.$row_number, $element->guide);
				$sheet->setCellValue('I'.$row_number, $element->plate);
							

		
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
		
			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}

	public function detail() {
		$id = request('id');
		$element = WarehouseMovement::select('id', 'scop_number')
		->findOrFail($id);
		
		
   
	   return $element;
   
	   
	   }
   	   
		   public function update() {
			   $id = request('id');
			   $scop_number = request('scop_number');
			  
			   
			   $element = WarehouseMovement::findOrFail($id);
			   $element->scop_number =$scop_number;
			   $element->save();
	   
			   $data = new stdClass();
			   $data->type = 1;
			   $data->title = '¡Ok!';
			   $data->msg = 'Registro actualizado exitosamente.';
	   
			   return response()->json($data);
	   }
}
	








