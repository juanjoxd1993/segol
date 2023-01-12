<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherDetail;
use App\Sale;
use App\SaleDetail;
use App\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class FacturationTotalSalesVolumeReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
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
		$company_id = request('model.company_id');
		$client_id = request('model.client_id');
		
					$elements = Voucher::leftjoin('voucher_details', 'vouchers.id', '=', 'voucher_details.voucher_id')
					    ->leftjoin('articles', 'voucher_details.article_id', '=', 'articles.id')
                        ->leftjoin('companies', 'vouchers.company_id', '=', 'companies.id')
						->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
						->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')						
						->leftjoin('client_addresses', function($join) {
							$join->on('clients.id', '=', 'client_addresses.client_id')
								->where('client_addresses.address_type_id', 1);})
						->leftjoin('ubigeos', 'client_addresses.ubigeo_id', '=', 'ubigeos.id')
						->leftjoin('payments', 'vouchers.payment_id', '=', 'payments.id')
			            ->where('vouchers.issue_date', '>=', $initial_date)
			            ->where('vouchers.issue_date', '<=', $final_date)
			->select( 'issue_date', 'business_units.name as business_unit_name',/*'voucher_types.type as voucher_type_type', 'voucher_types.name as voucher_type_name',*/  DB::Raw('SUM(vouchers.taxed_operation) as sum_sale_value'), DB::Raw('SUM(vouchers.igv) as sum_igv'), DB::Raw('SUM(vouchers.total) as sum_total'), DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 24) as gallons'), DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 23) as sum_1k'), DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id = 55 FROM articles WHERE articles.id = voucher_details.article_id ) AS sum_5k'), DB::Raw('(SELECT SUM(voucher_details.quantity) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 56) AS sum_10k'), DB::Raw('(SELECT SUM(voucher_details.quantity) from vouchers WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 57) AS sum_15k'), DB::Raw('(SELECT SUM(voucher_details.quantity)  AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 58) AS sum_45k'), DB::Raw('(SELECT SUM(voucher_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = voucher_details.article_id AND voucher_details.article_id <> 24)) FROM vouchers WHERE voucher_details.voucher_id = vouchers.id) AS sum_tm_total'))
            

            ->where('vouchers.voucher_type_id',1)
			->when($company_id, function($query, $company_id) {
				return $query->where('vouchers.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('vouchers.client_id', $client_id);
			})
			->groupBy('issue_date','business_units.id')
		//	->orderBy('vouchers.company_id')
			->orderBy('issue_date')
		//	->orderBy('business_unit_name')
		//	->orderBy('vouchers.serie_number')
		//	->orderBy('vouchers.voucher_number')
			
			
            ->get();
			$response=[];

            
            $totals_sum_igv = 0;
			$totals_sum_sale_value = 0;
		    $totals_sum_total = 0;
            $totals_gallons = 0;
		    $totals_sum_1k = 0;
		    $totals_sum_5k = 0;
		    $totals_sum_10k = 0;
		    $totals_sum_15k = 0;
		    $totals_sum_45k = 0;
		    $totals_sum_tm_total = 0;



			foreach ($elements as $voucher) {


				$cash_liquidation_amount = Liquidation::where('sale_id', $sale['id'])
				->where('payment_method_id', 1)
				->select('amount')
				->sum('amount');






            $totals_sum_igv += $voucher['sum_igv'];
			$totals_sum_sale_value += $voucher['sum_sale_value'];
		    $totals_sum_total += $voucher['sum_total'];
            $totals_gallons += $voucher['gallons'];
		    $totals_sum_1k += $voucher['sum_1k'];
		    $totals_sum_5k +=$voucher['sum_5k'];
		    $totals_sum_10k += $voucher ['sum_10k'];
		    $totals_sum_15k += $voucher['sum_15k'];
		    $totals_sum_45k += $voucher ['sum_45k'];
		    $totals_sum_tm_total += $voucher ['sum_tm_total'];


			//	$voucher->company_short_name = $voucher['company_short_name'];								
			//	$voucher->serie_number = $voucher['serie_number'];
			//	$voucher->voucher_number = $voucher['voucher_number'];				
			//	$voucher->quantity = $voucher['quantity'];
			//	$voucher->price = $voucher['price'];
                $voucher->business_unit_name = $voucher['business_unit_name'];
                $voucher->issue_date = $voucher['issue_date'];
				$voucher->sum_sale_value = $voucher['sum_sale_value'];               
				$voucher->sum_igv = $voucher['sum_igv'];
				$voucher->sum_total = $voucher['sum_total'];
            //  $voucher->perception = $voucher['perception'];
			//	$voucher->total_perception = $voucher['total_perception'];
			//	$voucher->document_number = $voucher['document_number'];
			//	$voucher->client_code = $voucher['client_code'];
			//	$voucher->client_business_name =$voucher['client_business_name'];
                $voucher->gallons = $voucher['gallons'];
				$voucher->sum_1k = $voucher['sum_1k'];
				$voucher->sum_5k = $voucher['sum_5k'];
				$voucher->sum_10k =$voucher ['sum_10k'];
				$voucher->sum_15k = $voucher['sum_15k'];
				$voucher->sum_45k =$voucher ['sum_45k'];
				$voucher->sum_tm_total =$voucher ['sum_tm_total'];
               
			//	$voucher->payment =$voucher['payment_name'];
            //    $voucher->guide =$voucher['guide'];
                
            //    $voucher->ose =$voucher['ose'];
			//	$voucher->low_number =$voucher['low_number'];


				$response[] = $voucher;

			}


		$totals = new stdClass();
		$totals->business_unit_name = 'TOTAL';								
	//	$totals->serie_number = '';
	//	$totals->voucher_number ='' ;				
		$totals->issue_date = '';
	//	$totals->price ='' ;
		$totals->sum_sale_value =$totals_sum_sale_value ;
		$totals->sum_igv = $totals_sum_igv ;
		$totals->sum_total =$totals_sum_total;
	//	$totals->perception = '';
	//	$totals->total_perception = '';
	//	$totals->document_number = '';
	//	$totals->client_code = '';
	//	$totals->client_business_name ='';
		$totals->gallons = $totals_gallons;
		$totals->sum_1k = $totals_sum_1k;
		$totals->sum_5k = $totals_sum_5k;
		$totals->sum_10k =$totals_sum_10k;
		$totals->sum_15k = $totals_sum_15k;
		$totals->sum_45k =$totals_sum_45k;
		$totals->sum_tm_total =$totals_sum_tm_total;
	//	$totals->business_unit_name = '';
	//	$totals->payment_name ='';
	//	$totals->guide ='';
	//	$totals->ose ='';
	//	$totals->low_number ='';

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Y1');
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


			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'Unidad de Negocio');
		//	$sheet->setCellValue('C3', 'Serie');
		//	$sheet->setCellValue('D3', 'N°');
			$sheet->setCellValue('C3', 'Fecha de Emisión');		
         //   $sheet->setCellValue('I3', 'Codigo');
		 //	$sheet->setCellValue('F3', 'RUC');
		 //	$sheet->setCellValue('G3', 'Razón Social');
		 //	$sheet->setCellValue('H3', 'Precio');
			$sheet->setCellValue('D3', 'Valor Venta');
			$sheet->setCellValue('E3', 'IGV');
			$sheet->setCellValue('F3', 'Total');
		 //	$sheet->setCellValue('L3', 'Percepción');
         // $sheet->setCellValue('M3', 'Total percepción');
			$sheet->setCellValue('G3', 'Galones');
			$sheet->setCellValue('H3', 'Granel');
			$sheet->setCellValue('I3', '5K');
			$sheet->setCellValue('J3', '10K');
			$sheet->setCellValue('K3', '15K');
			$sheet->setCellValue('L3', '45K');
			$sheet->setCellValue('M3', 'Total Kgs');
		//	$sheet->setCellValue('U3', 'Unidad de Negocio');
		//	$sheet->setCellValue('V3', 'Condicíón de pago');
		//	$sheet->setCellValue('W3', 'Guía');
		//	$sheet->setCellValue('X3', 'Envio OSE');
		//	$sheet->setCellValue('Y3', 'Número de baja');
			$sheet->getStyle('A3:Y3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_unit_name);
			//	$sheet->setCellValue('C'.$row_number, $element->serie_number);
			//	$sheet->setCellValue('D'.$row_number, $element->voucher_number);
                $sheet->setCellValue('C'.$row_number, $element->issue_date);
			//	$sheet->setCellValue('F'.$row_number, $element->document_number);
			//	$sheet->setCellValue('G'.$row_number, $element->client_business_name);
            //  $sheet->setCellValue('H'.$row_number, $element->price);
				$sheet->setCellValue('D'.$row_number, $element->sum_sale_value);
				$sheet->setCellValue('E'.$row_number, $element->sum_igv);
                $sheet->setCellValue('F'.$row_number, $element->sum_total);
            //  $sheet->setCellValue('L'.$row_number, $element->perception);
			//	$sheet->setCellValue('M'.$row_number, $element->total_perception);
                $sheet->setCellValue('G'.$row_number, $element->gallons);
                $sheet->setCellValue('H'.$row_number, $element->sum_1k);
				$sheet->setCellValue('I'.$row_number, $element->sum_5k); 
				$sheet->setCellValue('J'.$row_number, $element->sum_10k);
				$sheet->setCellValue('K'.$row_number, $element->sum_15k);
				$sheet->setCellValue('L'.$row_number, $element->sum_45k);
				$sheet->setCellValue('M'.$row_number, $element->sum_tm_total);
			//	$sheet->setCellValue('U'.$row_number, $element->business_unit_name);
			//	$sheet->setCellValue('V'.$row_number, $element->payment_name);
			//	$sheet->setCellValue('W'.$row_number, $element->guide);	
			//	$sheet->setCellValue('X'.$row_number, $element->ose);	
			//	$sheet->setCellValue('Y'.$row_number, $element->low_number);				
                $sheet->getStyle('D'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








