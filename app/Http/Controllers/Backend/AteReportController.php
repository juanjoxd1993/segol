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


class MasaReportController extends Controller
{
    public function index() {
	
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.masa_report')->with(compact('companies', 'current_date'));
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

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
	
		
						$elements = Article::leftjoin('warehouse_types', 'articles.warehouse_type_id', '=', 'warehouse_types.id')
					    ->leftjoin('classifications', 'articles.group_id', '=', 'classifications.id') 
                        ->leftjoin('warehouse_types','articles.warehouse_type_id','warehouse_types.id')               
			            ->select('articles.id  as article_id',
                        'articles.convertion as convertion',
                        'articles.group_id as group_id',
                        'classifications.name as group_name',
                        'articles.name as article_name',
                        'articles.stock_good as stock_good',
                        'articles.stock_repair as stock_prestamo',
                        'articles.stock_return as stock_transito',
                        'articles.stock_damaged as stock_cu',
                        'articles.presentacion as presentacion',
                        'articles.stock_rep as stock_rep',
                        'articles.stock_don as stock_don',
                        'articles.stock_buy as stock_buy',
                        'articles.stock_2022 as stock_2022',
                        'articles.stock_loss as stock_loss',
                        'articles.stock_aje as stock_aje',
                        'articles.stock_gran as stock_gran',
                        'articles.stock_como as stock_como')
			        				
			->OrderBy('group_name')
            ->get();

			$response=[];

            
         /*   $totals_sum_5kt = 0;
			$totals_sum_10kt = 0;
		    $totals_sum_15kt = 0;
            $totals_sum_45kt = 0;
		    $totals_sum_m15t = 0;
		    $totals_sum_5kc = 0;
		    $totals_sum_10kc = 0;
		    $totals_sum_15kc = 0;
			$totals_sum_45kc = 0; 
		    $totals_sum_m15c = 0;
			$totals_sum_5kd = 0;
			$totals_sum_10kd = 0;
		    $totals_sum_15kd = 0;
		    $totals_sum_45kd = 0;
		    $totals_sum_m15kd = 0;
			$totals_sum_1kr = 0;
		    $totals_sum_5kr = 0;
		    $totals_sum_10kr = 0;
		    $totals_sum_15kr = 0;
			$totals_sum_45kr = 0;
            $totals_sum_m15r = 0;*/



			foreach ($elements as $facturation) {

				$facturation->article_name = $facturation['article_name'];
                $facturation->article_id = $facturation['article_id'];
                $facturation->convertion = $facturation['convertion'];
                $facturation->presentacion = $facturation['presentacion'];
                $facturation->group_name = $facturation['group_name'];
                $facturation->stock_good = $facturation['stock_good'];
                $facturation->stock_tran = $facturation['stock_transito'];
                $facturation->stock_pres = $facturation['stock_prestamo'];
                $facturation->stock_cu = $facturation['stock_cu'];
                $facturation->stock_aje = $facturation['stock_aje'];

				$response[] = $facturation;

			}


		    $totals = new stdClass();
		
		    $totals->article_name = '';								
		    
		    $response[] = $totals;


		 if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'REPORTE DE STOCKS '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

            $sheet->mergeCells('G2:H2');
            $sheet->setCellValue('G2', 'Mercaderia');
            $sheet->getStyle('G2')->applyFromArray([
                'font' => [
                    'color' => array('rgb' => 'FFFFFF'),
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '44546a')
                ]
            ]);

            $sheet->mergeCells('I2:M2');
            $sheet->setCellValue('I2', 'Balones');
            $sheet->getStyle('I2')->applyFromArray([
                'font' => [
                    'color' => array('rgb' => 'FFFFFF'),
                    'bold' => true,
                    'size' => 12,
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => array('rgb' => '44546a')
                ]
            ]);


        

   
			$sheet->setCellValue('A4', '#');
			$sheet->setCellValue('B4', 'Codigo');
			$sheet->setCellValue('C4', 'Producto');
			$sheet->setCellValue('D4', 'Tipo');	
			$sheet->setCellValue('E4', 'Stock');
			$sheet->setCellValue('F4', 'Stock GLP');
			$sheet->setCellValue('G4', 'Stock');
			$sheet->setCellValue('H4', 'Cambios');
			$sheet->setCellValue('I4', 'Stock Vacios');
			$sheet->setCellValue('J4', 'Stock Llenos');
			$sheet->setCellValue('K4', 'Stock Prestamo');
			$sheet->setCellValue('L4', 'Stock CU');
			$sheet->setCellValue('M4', 'Stock Ajenos');
			
			$sheet->getStyle('A30:V30')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 5;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_name);
				$sheet->setCellValue('C'.$row_number, $element->sum_5kt);
                $sheet->setCellValue('D'.$row_number, $element->sum_10kt);
				$sheet->setCellValue('E'.$row_number, $element->sum_15kt);
                $sheet->setCellValue('F'.$row_number, $element->sum_45kt);               
				$sheet->setCellValue('G'.$row_number, $element->sum_m15t);
                $sheet->setCellValue('H'.$row_number, $element->sum_5kc);
                $sheet->setCellValue('I'.$row_number, $element->sum_10kc);
                $sheet->setCellValue('J'.$row_number, $element->sum_15kc);
				$sheet->setCellValue('K'.$row_number, $element->sum_45kc); 
				$sheet->setCellValue('L'.$row_number, $element->sum_m15c);
				$sheet->setCellValue('M'.$row_number, $element->sum_5kd);
				$sheet->setCellValue('N'.$row_number, $element->sum_10kd);
				$sheet->setCellValue('O'.$row_number, $element->sum_15kd);
				$sheet->setCellValue('P'.$row_number, $element->sum_45kd);
				$sheet->setCellValue('Q'.$row_number, $element->sum_m15kd);
				$sheet->setCellValue('R'.$row_number, $element->sum_5kr); 
				$sheet->setCellValue('S'.$row_number, $element->sum_10kr);
				$sheet->setCellValue('T'.$row_number, $element->sum_15kr);
				$sheet->setCellValue('U'.$row_number, $element->sum_45kr);
				$sheet->setCellValue('V'.$row_number, $element->sum_m15r);


               
							
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
	








