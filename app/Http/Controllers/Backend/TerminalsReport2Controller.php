<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use App\TipoCambio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseMovementDetail;
use App\WarehouseMovement;
use App\MoventType;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class TerminalsReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.terminals_report')->with(compact('companies', 'current_date'));
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

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

	public function list() {

		$export = request('export');

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d');
		$company_id = request('model.company_id');
		
					$elements = WarehouseMovement::leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')
                        ->leftjoin('companies', 'warehouse_movements.company_id', '=', 'companies.id')		
						->leftjoin('articles', 'warehouse_movement_details.article_code', '=', 'articles.id')
			         //   ->leftjoin('warehouse_document_types', 'warehouse_movements.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			            ->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
						->leftjoin('warehouse_types', 'warehouse_movements.warehouse_type_id', '=', 'warehouse_types.id')
			            ->where('warehouse_movements.created_at', '>=', $initial_date)
	            		->where('warehouse_movements.created_at', '<=', $final_date)
			            ->where('warehouse_movements.warehouse_type_id',8) 
		                ->where('warehouse_movements.movement_class_id', 1)
                        ->where('warehouse_movements.movement_type_id', 1)
			->select('warehouse_movements.id', 
			'companies.short_name as company_short_name',
			'articles.last_price as tm_price', 
			DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as movement_date'), 
			DB::Raw('CONCAT(warehouse_movements.referral_serie_number, "-", warehouse_movements.referral_voucher_number) as factura'),
			'warehouse_types.name as warehouse_name', 
			'warehouse_types.short_name as warehouse_short_name',
			'articles.name as article_name', 
			'warehouse_movement_details.converted_amount as quantity',
			'warehouse_movements.taxed_operation as sale_value',
			'warehouse_movements.igv as igv',
			'warehouse_movements.referral_guide_number as order_sale',
			'warehouse_movements.scop_number as scop_number',
			'warehouse_movements.tc as tc',
			'warehouse_movements.total as pago_dol')

			->when($company_id, function($query, $company_id) {
				return $query->where('warehouse_movements.company_id', $company_id);
			})
		
			->groupBy('warehouse_movements.id')
		//	->orderBy('movement_date')
		//	->orderBy('business_unit_name')
		//	->orderBy('warehouse_document_type_short_name')
		//	->orderBy('sales.referral_serie_number')
		//	->orderBy('sales.referral_voucher_number')
			->get();
			$response=[];

			

			


			foreach ($elements as $warehouse_movement) {


				$warehouse_movement->company_short_name = $warehouse_movement['company_short_name'];
				$warehouse_movement->movement_date = $warehouse_movement['movement_date'];
				$warehouse_movement->factura = $warehouse_movement['factura'];
				$warehouse_movement->warehouse_name = $warehouse_movement['warehouse_name'];
				$warehouse_movement->warehouse_short_name = $warehouse_movement['warehouse_short_name'];
				$warehouse_movement->article_name = $warehouse_movement['article_name'];
				$warehouse_movement->tm_price = $warehouse_movement['tm_price'];
				$warehouse_movement->precio_tm = $warehouse_movement['pago_dol']/ $warehouse_movement['quantity']*1000;
				$warehouse_movement->tc = $warehouse_movement['tc'];
				$warehouse_movement->quantity = $warehouse_movement['quantity'];
				$warehouse_movement->sale_value = $warehouse_movement['sale_value'];
				$warehouse_movement->igv = $warehouse_movement['igv'];
				$warehouse_movement->order_sale = $warehouse_movement['order_sale'];
				$warehouse_movement->scop_number = $warehouse_movement['scop_number'];
				$warehouse_movement->conv_soles = $warehouse_movement['pago_dol']*$warehouse_movement['tc'];
	            $warehouse_movement->pago_dol = $warehouse_movement['pago_dol'];
			
				$response[] = $warehouse_movement;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->movement_date = '';
		$totals->factura = '';
		$totals->warehouse_name = '';
		$totals->warehouse_short_name = '';
		$totals->article_name  = '';
		$totals->tm_price  = '';
		$totals->tc  = '';
		$totals->quantity = '';
		$totals->sale_value = '';
		$totals->igv = '';
		$totals->order_sale = '';
		$totals->scop_number = '';
        $totals->precio_tm = '';
		$totals->pago_dol = '';
		$totals->conv_soles = '';

		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'REPORTE DE FACTURAS COMPRAS GLP DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('D3', 'Factura');
            $sheet->setCellValue('E3', 'Proveedor'); 
            $sheet->setCellValue('F3', 'Carga Terminal');
            $sheet->setCellValue('G3', 'Precio_tm Dolares');
			$sheet->setCellValue('H3', 'Tipo');
			$sheet->setCellValue('I3', 'Cantidad KG');
			$sheet->setCellValue('J3', 'TC.');
			$sheet->setCellValue('K3', 'Pago_Dolares');
			$sheet->setCellValue('L3', 'Pago_Soles');
			$sheet->setCellValue('M3', 'Conversión_soles');
			$sheet->setCellValue('N3', 'Precio Kg Soles');
            $sheet->setCellValue('O3', 'Valor Venta');
            $sheet->setCellValue('P3', 'I.G.V');
			$sheet->setCellValue('Q3', 'Dolares Kgs');
			$sheet->setCellValue('R3', 'Despacho');
			$sheet->setCellValue('S3', 'Glp_Recoger');
			$sheet->setCellValue('T3', 'TM');
			$sheet->setCellValue('U3', 'Nro° Orden Venta');
			$sheet->setCellValue('V3', 'Nro° SCOP');
		
			
			$sheet->getStyle('A3:V3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				
			//	$saleDateYear = null;
			//	$saleDateMonth = null;
			//	$saleDateDay = null;

			//	if ($element->sale_date) {
			//		$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
			//		$saleDateYear = $saleDateObject;
			//		$saleDateMonth = str_pad($saleDateObject->month, 2, '0', STR_PAD_LEFT);
			//		$saleDateDay = str_pad($saleDateObject->day, 2, '0', STR_PAD_LEFT);
			//	}

          


				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
			//	$sheet->setCellValue('C'.$row_number, $saleDateYear);
			//	$sheet->setCellValue('D'.$row_number, $saleDateMonth);
				$sheet->setCellValue('C'.$row_number, $element->movement_date);
				$sheet->setCellValue('D'.$row_number, $element->factura);
                $sheet->setCellValue('E'.$row_number, $element->warehouse_name);
                $sheet->setCellValue('F'.$row_number, $element->warehouse_short_name);
                $sheet->setCellValue('G'.$row_number, $element->precio_tm);
                $sheet->setCellValue('H'.$row_number, $element->article_name);
                $sheet->setCellValue('I'.$row_number, $element->quantity);
				$sheet->setCellValue('J'.$row_number, $element->tc);
				$sheet->setCellValue('K'.$row_number, $element->pago_dol);  
				$sheet->setCellValue('M'.$row_number, $element->conv_soles); 
				
                $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
		//	$sheet->getColumnDimension('AA')->setAutoSize(true);


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








