<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class GuidesReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.guides_bk_report')->with(compact('companies', 'current_date'));
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

        public function getWarehouseMovements() {
		   $company_id = request('company_id');
		   $q = request('q');

		   $guides = warehousemovement::select('id', 'referral_guide_number as text')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->where('referral_guide_number', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $guides;
	}

	public function list() {

		$export = request('export');

	   
		$initial_date = date_create(request('model.initial_date') . ' 00:00:00');
		$final_date = date_create(request('model.final_date') . ' 23:59:59');
		$company_id = request('model.company_id');
		$initial_date = date_format($initial_date, 'Y-m-d H:i:s');
		$final_date = date_format($final_date, 'Y-m-d H:i:s');
		$referral_guide_number= request('model.referral_guide_number');
		$warehouse_movement_id = request('model.warehouse_movement_id');


		DB::enableQueryLog();
		
					$elements = WarehouseMovement::leftjoin ('companies', 'warehouse_movements.company_id', '=', 'companies.id') 
                      //  ->leftjoin('warehouse_movement_details', 'warehouse_movements.id', '=', 'warehouse_movement_details.warehouse_movement_id')				
					//	->leftjoin('articles', 'warehouse_details.article_code', '=', 'articles.code')
			            ->where('warehouse_movements.created_at', '>=', $initial_date)
			            ->where('warehouse_movements.created_at', '<=', $final_date)
			->select('warehouse_movements.id', 'companies.short_name as company_short_name', 'created_at',/*'articles.article_code as article_code', 'articles.name as article_name',*/ DB::Raw('CONCAT(warehouse_movements.referral_guide_series, "-", warehouse_movements.referral_guide_number) as guide'),'warehouse_movements.license_plate as plate')

			->when($company_id, function($query, $company_id) {
				return $query->where('warehouse_movements.company_id', $company_id);
			})
			
			
			->groupBy('warehouse_movements.id')
			->orderBy('warehouse_movements.company_id')
			->orderBy('created_at')
			->orderBy('guide')
			->get();
			$response=[];


			foreach ($elements as $warehousemovement) {



		// $saleWarehouseMovement = WarehouseMovement::select('id', 'referral_serie_number', 'referral_voucher_number')
        //    ->where('id') 
         //   ->where('company_id', $company_id)
         //   ->first();

     //   $returnWarehouseMovement = WarehouseMovement::select('id')
     //       ->where('referral_serie_number')
      //      ->where('referral_voucher_number')
       //     ->where('action_type_id', 5)
       //     ->first();
        
       // $movementDetails = WarehouseMovementDetail::select('id', 'warehouse_movement_id', 'item_number', 'article_code', 'converted_amount')
       //     ->where('warehouse_movement_id')
      //      ->orderBy('item_number', 'asc')
      //      ->get();

       // $movementDetails->map(function ($item, $index) use ($returnWarehouseMovement) {
       //     $item->sale_warehouse_movement_id = $item->warehouse_movement_id;
        //    $item->article_id = $item->article->id;
        //    $item->article_code = $item->article->code;
		//	$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
        //    $item->presale_converted_amount = $item->converted_amount;
        //    $item->sale_converted_amount = number_format(0, 4, '.', '');

        //    if ( isset($returnWarehouseMovement) ) {
        //        $returnDetail = WarehouseMovementDetail::select('id', 'article_code', 'converted_amount')
         //           ->where('warehouse_movement_id', $returnWarehouseMovement->id)
         //           ->where('article_code', $item->article_id)
          //          ->first();
         //   }

         //   if ( isset($returnDetail) ) {
         //       $item->return_warehouse_movement_id = $returnWarehouseMovement->id;
          //      $item->return_converted_amount = number_format($returnDetail->converted_amount, 4, '.', '');
          //      $item->balance_converted_amount = number_format($item->converted_amount - $returnDetail->converted_amount, 4, '.', '');
          //      $item->new_balance_converted_amount = number_format($item->converted_amount - $returnDetail->converted_amount, 4, '.', '');
          //  } else {
          //      $item->return_converted_amount = number_format(0, 4, '.', '');
          //      $item->balance_converted_amount = number_format($item->converted_amount, 4, '.', '');
          //      $item->new_balance_converted_amount = number_format($item->converted_amount, 4, '.', '');
          //  }

           // unset($item->warehouse_movement_id);
           // unset($item->converted_amount);
      //  });


         
				$warehousemovement->company_short_name = $warehousemovement['company_short_name'];
				$warehousemovement->created_at = $warehousemovement['created_at'];
			//	$warehousemovement->article_code =$warehousemovement ['article_code'];			
			//	$warehousemovement->article_name =$warehousemovement ['article_name'];
				$warehousemovement->guide = $warehousemovement['guide'];
				$warehousemovement->plate = $warehousemovement['plate'];
			//	$warehousemovement->presale_converted_amount = $warehousemovement['presale_converted_amount'];
			//	$warehousemovement->return_converted_amount = $warehousemovement['return_converted_amount'];
			//	$warehousemovement->sale_converted_amount = $warehousemovement['sale_converted_amount'];
             //   $warehousemovement->new_balance_converted_amount = $warehousemovement['new_balance_converted_amount'];

				$response[] = $warehousemovement;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->created_at = '';
	//	$totals->article_code = '';
	//	$totals->article_name = '';
		$totals->guide = '';
		$totals->plate = '';
	//	$totals->presale_converted_amount = '';
	//	$totals->return_converted_amount = '';
	//	$totals->sale_converted_amount = '';
	//	$totals->new_balance_converted_amount = '';

		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
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
			$sheet->setCellValue('C3', 'Fecha de Despacho');
		//	$sheet->setCellValue('D3', 'Codigo');
        //  $sheet->setCellValue('E3', 'Articulo'); 
            $sheet->setCellValue('F3', 'Guía');
            $sheet->setCellValue('G3', 'Placa');
         //   $sheet->setCellValue('H3', 'Salida');
		//	$sheet->setCellValue('I3', 'Retorno');
			//$sheet->setCellValue('J3', 'Venta');
		//	$sheet->setCellValue('K3', 'Saldo');
			$sheet->getStyle('A3:G3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->created_at);
			//	$sheet->setCellValue('D'.$row_number, $element->article_code);
            //   $sheet->setCellValue('E'.$row_number, $element->article_name);
                $sheet->setCellValue('F'.$row_number, $element->guide);
                $sheet->setCellValue('G'.$row_number, $element->plate);
              //  $sheet->setCellValue('H'.$row_number, $element->presale_converted_amount);
			//	$sheet->setCellValue('I'.$row_number, $element->return_converted_amount);
			//	$sheet->setCellValue('J'.$row_number, $element->sale_converted_amount);
			//	$sheet->setCellValue('K'.$row_number, $element->new_balance_converted_amount);			
             //   $sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
				$row_number++;
			}

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
		//	$sheet->getColumnDimension('H')->setAutoSize(true);
		//	$sheet->getColumnDimension('I')->setAutoSize(true);
		//	$sheet->getColumnDimension('J')->setAutoSize(true);
		//	$sheet->getColumnDimension('K')->setAutoSize(true);
			

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








