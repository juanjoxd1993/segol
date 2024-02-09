<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use App\Container;
use App\ContainerDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherDetail;
use App\Sale;
use App\Facturation;
use App\SaleDetail;
use App\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class StockAteReportController extends Controller
{
    public function index() {
	//	$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.stock_ate_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
			
		];

		$rules = [
			'initial_date'	=> 'required',
			
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

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
	
		
						$elements = Article::leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
                        ->where('articles.warehouse_type_id', '=', 4)	            
			            ->select('articles.id as article_id','articles.name as article_name','classifications.name as class_name')
			        		
			->groupBy('article_id')
            ->get();

			$response=[];

            
            $totals_sum_stock_good = 0;
			$totals_sum_stock_good_env = 0;
		    $totals_sum_stock_llenos = 0;
            $totals_sum_stock_prest = 0;
		    $totals_sum_stock_cu = 0;
		    $totals_sum_stock_cambio = 0;
		    $totals_sum_stock_bad = 0;
		
           
			foreach ($elements as $facturation) {

				$facturation->article_id = $facturation['article_id'];
                $facturation->article_name = $facturation['article_name'];
                $facturation->class_name = $facturation['class_name'];
               		       
				$stock_good = Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->where('articles.id', '=', $facturation['article_id'])
                ->whereNotIn ('articles.family_id',[7])
				->select('stock_good')
				->sum('stock_good');

                $facturation->stock_good= $stock_good;

                $stock_good_env = Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->where('articles.id', '=', $facturation['article_id'])
                ->whereIn ('articles.family_id',[7])
				->select('stock_good')
				->sum('stock_good');

                $facturation->stock_good_env= $stock_good_env;

                $stock_llenos = Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->whereIn ('articles.family_id',[7])
                ->where('articles.id', '=', $facturation['article_id'])
				->select('stock_return')
				->sum('stock_return');

                $facturation->stock_llenos= $stock_llenos;

                $stock_prest= Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->whereIn ('articles.family_id',[7])
                ->where('articles.id', '=', $facturation['article_id'])
				->select('stock_repair')
				->sum('stock_repair');

                $facturation->stock_prest= $stock_prest;

                $stock_cu= Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->whereIn ('articles.family_id',[7])
                ->where('articles.id', '=', $facturation['article_id'])
				->select('stock_damaged')
				->sum('stock_damaged');

                $facturation->stock_cu= $stock_cu;


                $stock_cambio= Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->where('articles.id', '=', $facturation['article_id'])
                ->whereIn ('articles.family_id',[7])
				->select('stock_minimum')
				->sum('stock_minimum');

                $facturation->stock_cambio= $stock_cambio;

                $stock_bad= Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id') 
                ->leftjoin('classifications', 'articles.family_id', '=', 'classifications.id')
				->where('articles.warehouse_type_id', '=', 4)
                ->where('articles.id', '=', $facturation['article_id'])
                ->whereIn ('articles.family_id',[7])
				->select('stock_gran')
				->sum('stock_gran');

                $facturation->stock_bad= $stock_bad;
                                  
                $totals_sum_stock_good += $facturation['sum_stock_good'];
                $totals_sum_stock_good_env += $facturation['sum_stock_good_env'];
                $totals_sum_stock_llenos += $facturation['sum_llenos'];
                $totals_sum_stock_prest += $facturation['sum_prest'];
                $totals_sum_stock_cu +=$facturation['sum_cu'];
                $totals_sum_stock_cambio += $facturation['sum_cambio'];
                $totals_sum_stock_bad += $facturation['sum_bad']; 
            
				$response[] = $facturation;

			}


		    $totals = new stdClass();
		
		    $totals->article_name = 'TOTAL';								
		    $totals->class_name = '';
            $totals->stock_good = $totals_sum_stock_good;
            $totals->stock_good_env = $totals_sum_stock_good_env;
            $totals->stock_llenos = $totals_sum_stock_llenos;
            $totals->stock_prest =$totals_sum_stock_prest;
            $totals->stock_cu = $totals_sum_stock_cu;
            $totals->stock_cambio = $totals_sum_stock_cambio;
            $totals->stock_bad = $totals_sum_stock_bad;
		    $response[] = $totals;


		 if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'REPORTE DE STOCKS ATE  DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'Articulo');
			$sheet->setCellValue('C3', 'Tipo');
			$sheet->setCellValue('D3', 'Stock');	
			$sheet->setCellValue('E3', 'Balones Vacios');
			$sheet->setCellValue('F3', 'Balones Llenos');
			$sheet->setCellValue('G3', 'Prestamos');
			$sheet->setCellValue('H3', 'Cesión de Uso');
			$sheet->setCellValue('I3', 'Cambios');
			$sheet->setCellValue('J3', 'Mal Estado');
			$sheet->getStyle('A3:J3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->article_name);
				$sheet->setCellValue('C'.$row_number, $element->class_name);
                $sheet->setCellValue('D'.$row_number, $element->stock_good);
				$sheet->setCellValue('E'.$row_number, $element->stock_good_env);
                $sheet->setCellValue('F'.$row_number, $element->stock_llenos);               
				$sheet->setCellValue('G'.$row_number, $element->stock_prest);
                $sheet->setCellValue('H'.$row_number, $element->stock_cu);
                $sheet->setCellValue('I'.$row_number, $element->stock_cambio);
                $sheet->setCellValue('J'.$row_number, $element->stock_bad);						
            //    $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');
			//	$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
			
			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}