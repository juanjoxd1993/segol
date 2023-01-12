<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
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


class FacturationTotalSalesVolumeReportController extends Controller
{
    public function index() {
	//	$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.facturations_total_sales_volume_report')->with(compact('companies', 'current_date'));
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
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d H:i:s');
	//	$company_id = request('model.company_id');
	//	$client_id = request('model.client_id');
		

	                           


						$elements = Sale::leftjoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
					    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')    
                        ->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')                     
                        ->leftjoin('companies', 'sales.company_id', '=', 'companies.id')		
						->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
						->whereIn('sales.warehouse_document_type_id', [4,5,6,7,13])
			            ->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
			            ->where('sales.sale_date', '>=', $initial_date)
			            ->where('sales.sale_date', '<=', $final_date)			            
			            ->select('sales.sale_date as fecha_emision','clients.business_unit_id as business_unit_id','business_units.name as business_unit_name')
			        
				
			
			->groupBy('sales.sale_date','business_unit_id')
            ->get();

			$response=[];

            
            $totals_sum_igv = 0;
			$totals_sum_sale_value = 0;
		    $totals_sum_total = 0;
            $totals_sum_24k = 0;
		    $totals_sum_1k = 0;
		    $totals_sum_5k = 0;
		    $totals_sum_10k = 0;
		    $totals_sum_15k = 0;
			$totals_sum_45k = 0; 
		    $totals_sum_tm_total = 0;
			$totals_sum_24kf = 0;
			$totals_sum_1kf = 0;
		    $totals_sum_5kf = 0;
		    $totals_sum_10kf = 0;
		    $totals_sum_15kf = 0;
			$totals_sum_45kf = 0; 
			$totals_sum_tm_total_f = 0;
			$totals_sum_1kr = 0;
		    $totals_sum_5kr = 0;
		    $totals_sum_10kr = 0;
		    $totals_sum_15kr = 0;
			$totals_sum_45kr = 0;
			$totals_sum_tm_total_r = 0; 



			foreach ($elements as $facturation) {

				$facturation->fecha_emision = $facturation['fecha_emision'];
				$facturation->company_name = $facturation['company_name'];
			    $facturation->business_unit_name = $facturation['business_unit_name'];
				$facturation->business_unit_id = $facturation['business_unit_id'];
		       


				$sum_total=Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->select ('total')
				->sum('total');

				$sum_igv=Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->select ('igv')
				->sum('igv');


				$sum_24k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('sale_details.article_id',24)
				->select('quantity')
				->sum('quantity');

				$sum_1k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('sale_details.article_id',23)
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');

				$sum_5k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('articles.subgroup_id', 55)
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->select('quantity')
				->sum('quantity');

				$sum_10k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('articles.subgroup_id', 56)
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->select('quantity')
				->sum('quantity');

				$sum_15k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('articles.subgroup_id', 57)
				->select('quantity')
				->sum('quantity');

				$sum_45k = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->where('sales.sale_date', '=', $facturation['fecha_emision'])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('articles.subgroup_id', 58)
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');


				$sum_24kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
				->where('voucher_details.article_id',24)
				->where('vouchers.voucher_type_id',1)
				->whereNotIn('vouchers.client_id', [13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');



				$sum_1kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
				->where('voucher_details.article_id',23)
				->where('vouchers.voucher_type_id',1)
				->whereNotIn('vouchers.client_id', [13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');

				$sum_5kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
				->whereIn('voucher_details.article_id', [1,5,9,13,17])
				->where('vouchers.voucher_type_id',1)
				->select('quantity')
				->sum('quantity');

				$sum_10kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
			    ->where('articles.subgroup_id', 56)
				->where('vouchers.voucher_type_id',1)
				->whereNotIn('vouchers.client_id', [ 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');

				$sum_15kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
			    ->where('articles.subgroup_id', 57)
				->where('vouchers.voucher_type_id',1)
				->select('quantity')
				->sum('quantity');

				$sum_45kf = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
				->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
				->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
				->whereIn('vouchers.company_id',[1,3])
				->where('clients.business_unit_id', '=', $facturation['business_unit_id'])
				->where('vouchers.issue_date', '=', $facturation['fecha_emision'])
			    ->where('articles.subgroup_id', 58)
				->where('vouchers.voucher_type_id',1)
				->whereNotIn('vouchers.client_id', [ 13326, 13775, 14072])
				->select('quantity')
				->sum('quantity');

				

		

			
			$facturation->company_name = $facturation['company_name'];
			$facturation->business_unit_name = $facturation['business_unit_name'];
			             
			$facturation->sum_igv = $sum_igv;
			$facturation->sum_total = $sum_total;
			$facturation->sum_sale_value = $facturation->sum_total-$facturation->sum_igv;
			$facturation->sum_24k =$sum_24k;
			$facturation->sum_1k =$sum_1k;
			$facturation->sum_5k =$sum_5k;
			$facturation->sum_10k =$sum_10k;
			$facturation->sum_15k =$sum_15k;
			$facturation->sum_45k =$sum_45k;
			$facturation->sum_tm_total =($facturation->sum_24k*2.017)+$facturation->sum_1k+($facturation->sum_5k*5)+($facturation->sum_10k*10)+($facturation->sum_15k*15)+($facturation->sum_45k*45);
			$facturation->sum_24kf =$sum_24kf;
			$facturation->sum_1kf =$sum_1kf;
			$facturation->sum_5kf =$sum_5kf;
			$facturation->sum_10kf =$sum_10kf;
			$facturation->sum_15kf =$sum_15kf;
			$facturation->sum_45kf =$sum_45kf;
			$facturation->sum_tm_total_f =($facturation->sum_24kf*2.017)+$facturation->sum_1kf+($facturation->sum_5kf*5)+($facturation->sum_10kf*10)+($facturation->sum_15kf*15)+($facturation->sum_45kf*45);
			$facturation->sum_1kr =$facturation->sum_1k-$facturation->sum_1kf ;
			$facturation->sum_5kr =$facturation->sum_5k-$facturation->sum_5kf;
			$facturation->sum_10kr =$facturation->sum_10k-$facturation->sum_10kf;
			$facturation->sum_15kr =$facturation->sum_15k-$facturation->sum_15kf;
			$facturation->sum_45kr =$facturation->sum_45k-$facturation->sum_45kf;
			$facturation->sum_tm_total_r =$facturation->sum_tm_total-$facturation->sum_tm_total_f;



			$totals_sum_igv += $facturation['sum_igv'];
			$totals_sum_sale_value += $facturation['sum_sale_value'];
		    $totals_sum_total += $facturation['sum_total'];
            $totals_sum_24k += $facturation['sum_24k'];
		    $totals_sum_1k += $facturation['sum_1k'];
		    $totals_sum_5k += $facturation['sum_5k'];
		    $totals_sum_10k += $facturation ['sum_10k'];
		    $totals_sum_15k += $facturation['sum_15k'];
			$totals_sum_45k += $facturation ['sum_45k'];
		    $totals_sum_tm_total += $facturation['sum_tm_total'];
			$totals_sum_24kf += $facturation['sum_24kf'];
			$totals_sum_1kf += $facturation['sum_1kf'];
		    $totals_sum_5kf += $facturation['sum_5kf'];
		    $totals_sum_10kf += $facturation ['sum_10kf'];
		    $totals_sum_15kf += $facturation['sum_15kf'];
			$totals_sum_45kf += $facturation ['sum_45kf'];
			$totals_sum_tm_total_f += $facturation ['sum_tm_total_f'];
			$totals_sum_1kr += $facturation['sum_1kr'];
		    $totals_sum_5kr += $facturation['sum_5kr'];
		    $totals_sum_10kr += $facturation ['sum_10kr'];
		    $totals_sum_15kr += $facturation['sum_15kr'];
			$totals_sum_45kr += $facturation ['sum_45kr'];
			$totals_sum_tm_total_r += $facturation ['sum_tm_total_r'];
               
			


				$response[] = $facturation;

			}


		$totals = new stdClass();
		
		$totals->company_name = 'TOTAL';
		$totals->business_unit_name= '';								
	//	$totals->serie_number = '';
	//	$totals->voucher_number ='' ;				
		$totals->fecha_emision = '';
	//	$totals->price ='' ;
		$totals->sum_sale_value =$totals_sum_sale_value ;
		$totals->sum_igv = $totals_sum_igv ;
		$totals->sum_total =$totals_sum_total;
	//	$totals->perception = '';
	//	$totals->total_perception = '';
	//	$totals->document_number = '';
	//	$totals->client_code = '';
	//	$totals->client_business_name ='';
		$totals->sum_24k = $totals_sum_24k;
		$totals->sum_1k = $totals_sum_1k;
		$totals->sum_5k = $totals_sum_5k;
		$totals->sum_10k =$totals_sum_10k;
		$totals->sum_15k = $totals_sum_15k;
		$totals->sum_45k =$totals_sum_45k;
		$totals->sum_tm_total =$totals_sum_tm_total;
		$totals->sum_24kf = $totals_sum_24kf;
		$totals->sum_1kf = $totals_sum_1kf;
		$totals->sum_5kf = $totals_sum_5kf;
		$totals->sum_10kf =$totals_sum_10kf;
		$totals->sum_15kf = $totals_sum_15kf;
		$totals->sum_45kf =$totals_sum_45kf;
		$totals->sum_tm_total_f =$totals_sum_tm_total_f;
		$totals->sum_1kr = $totals_sum_1kr;
		$totals->sum_5kr = $totals_sum_5kr;
		$totals->sum_10kr =$totals_sum_10kr;
		$totals->sum_15kr = $totals_sum_15kr;
		$totals->sum_45kr =$totals_sum_45kr;
		$totals->sum_tm_total_r =$totals_sum_tm_total_r;
	//	$totals->business_unit_name = '';
	//	$totals->payment_name ='';
	//	$totals->guide ='';
	//	$totals->ose ='';
	//	$totals->low_number ='';

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'REPORTE DE FACTURACIONES DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);


	    $sheet->mergeCells('E2:G2');
		$sheet->setCellValue('E2', 'VENTA GRAL. SOLES');
		$sheet->getStyle('E2')->applyFromArray([
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

		$sheet->mergeCells('H2:N2');
		$sheet->setCellValue('H2', 'VENTA GENERAL');
		$sheet->getStyle('H2')->applyFromArray([
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
				'startColor' => array('rgb' => '1E90FF')
			]
		]);

		$sheet->mergeCells('O2:U2');
		$sheet->setCellValue('O2', 'VENTA FACTURADA');
		$sheet->getStyle('O2')->applyFromArray([
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
				'startColor' => array('rgb' => '7FFF00')
			]
		]);

		$sheet->mergeCells('V2:AA2');
		$sheet->setCellValue('V2', 'VENTA POR BOLETEAR');
		$sheet->getStyle('V2')->applyFromArray([
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
				'startColor' => array('rgb' => 'FF8C00')
			]
		]);


			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'Compañia');
			$sheet->setCellValue('C3', 'Unidad de Negocio');
		//	$sheet->setCellValue('C3', 'Serie');
		//	$sheet->setCellValue('D3', 'N°');
			$sheet->setCellValue('D3', 'Fecha de Emisión');		
         //   $sheet->setCellValue('I3', 'Codigo');
		 //	$sheet->setCellValue('F3', 'RUC');
		 //	$sheet->setCellValue('G3', 'Razón Social');
		 //	$sheet->setCellValue('H3', 'Precio');
			$sheet->setCellValue('E3', 'Valor Venta');
			$sheet->setCellValue('F3', 'IGV');
			$sheet->setCellValue('G3', 'Total');
		 //	$sheet->setCellValue('L3', 'Percepción');
         // $sheet->setCellValue('M3', 'Total percepción');
			$sheet->setCellValue('H3', 'Galones');
			$sheet->setCellValue('I3', 'Granel');
			$sheet->setCellValue('J3', '5K');
			$sheet->setCellValue('K3', '10K');
			$sheet->setCellValue('L3', '15K');
			$sheet->setCellValue('M3', '45K');
			$sheet->setCellValue('N3', 'Total Kgs');
			$sheet->setCellValue('O3', 'Glns Fact');
			$sheet->setCellValue('P3', 'Granel Fact');
			$sheet->setCellValue('Q3', '5K F');
			$sheet->setCellValue('R3', '10K F');
			$sheet->setCellValue('S3', '15K F');
			$sheet->setCellValue('T3', '45K F');
			$sheet->setCellValue('U3', 'Total Kgs Facturados');
			$sheet->setCellValue('V3', 'Granel X Fact');
			$sheet->setCellValue('W3', '5K XF');
			$sheet->setCellValue('X3', '10K XF');
			$sheet->setCellValue('Y3', '15K XF');
			$sheet->setCellValue('Z3', '45K XF');
			$sheet->setCellValue('AA3', 'Total Kgs Por Facturar');
		//	$sheet->setCellValue('U3', 'Unidad de Negocio');
		//	$sheet->setCellValue('V3', 'Condicíón de pago');
		//	$sheet->setCellValue('W3', 'Guía');
		//	$sheet->setCellValue('X3', 'Envio OSE');
		//	$sheet->setCellValue('Y3', 'Número de baja');
			$sheet->getStyle('A3:AA3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_name);
				$sheet->setCellValue('C'.$row_number, $element->business_unit_name);
			//	$sheet->setCellValue('C'.$row_number, $element->serie_number);
			//	$sheet->setCellValue('D'.$row_number, $element->voucher_number);
                $sheet->setCellValue('D'.$row_number, $element->fecha_emision);
			//	$sheet->setCellValue('F'.$row_number, $element->document_number);
			//	$sheet->setCellValue('G'.$row_number, $element->client_business_name);
            //  $sheet->setCellValue('H'.$row_number, $element->price);
				$sheet->setCellValue('E'.$row_number, $element->sum_sale_value);
				$sheet->setCellValue('F'.$row_number, $element->sum_igv);
                $sheet->setCellValue('G'.$row_number, $element->sum_total);
            //  $sheet->setCellValue('L'.$row_number, $element->perception);
			//	$sheet->setCellValue('M'.$row_number, $element->total_perception);
                $sheet->setCellValue('H'.$row_number, $element->sum_24k);
                $sheet->setCellValue('I'.$row_number, $element->sum_1k);
				$sheet->setCellValue('J'.$row_number, $element->sum_5k); 
				$sheet->setCellValue('K'.$row_number, $element->sum_10k);
				$sheet->setCellValue('L'.$row_number, $element->sum_15k);
				$sheet->setCellValue('M'.$row_number, $element->sum_45k);
				$sheet->setCellValue('N'.$row_number, $element->sum_tm_total);
				$sheet->setCellValue('O'.$row_number, $element->sum_24kf);
				$sheet->setCellValue('P'.$row_number, $element->sum_1kf);
				$sheet->setCellValue('Q'.$row_number, $element->sum_5kf); 
				$sheet->setCellValue('R'.$row_number, $element->sum_10kf);
				$sheet->setCellValue('S'.$row_number, $element->sum_15kf);
				$sheet->setCellValue('T'.$row_number, $element->sum_45kf);
				$sheet->setCellValue('U'.$row_number, $element->sum_tm_total_f);
				$sheet->setCellValue('V'.$row_number, $element->sum_1kr);
				$sheet->setCellValue('W'.$row_number, $element->sum_5kr); 
				$sheet->setCellValue('X'.$row_number, $element->sum_10kr);
				$sheet->setCellValue('Y'.$row_number, $element->sum_15kr);
				$sheet->setCellValue('Z'.$row_number, $element->sum_45kr);
				$sheet->setCellValue('AA'.$row_number, $element->sum_tm_total_r);
							
                $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








