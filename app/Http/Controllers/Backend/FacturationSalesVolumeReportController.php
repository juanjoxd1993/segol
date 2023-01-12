<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\VoucherDetail;
use App\Voucher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class FacturationSalesVolumeReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.facturations_sales_volume_report')->with(compact('companies', 'current_date'));
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
						->leftjoin('voucher_types', 'vouchers.voucher_type_id', '=', 'voucher_types.id')
						->leftjoin('payments', 'vouchers.payment_id', '=', 'payments.id')
			            ->where('vouchers.issue_date', '>=', $initial_date)
			            ->where('vouchers.issue_date', '<=', $final_date)
			->select('vouchers.id', 
			'voucher_types.id as type_id',
			'companies.short_name as company_short_name',
			 'issue_date', 'expiry_date','credit_note_reference_serie',
			 'credit_note_reference_number','business_units.name as business_unit_name',
			 'vouchers.serie_number', 'vouchers.voucher_number', 'quantity as quantity',
			 'voucher_details.original_price as price','vouchers.total',
			 'vouchers.taxed_operation as taxed_operation',
			 'vouchers.igv as igv','vouchers.total_perception as total_perception',
			 'vouchers.igv_perception as perception',
			 'clients.document_number as document_number' ,
			 'clients.document_number as client_code', 
			 'clients.business_name as client_business_name',
			  DB::Raw('CONCAT(vouchers.referral_guide_series, "-", vouchers.referral_guide_number) as guide'),
			  'vouchers.ose as ose','vouchers.low_number as low_number','payments.name as payment_name' ,
			  DB::Raw('(SELECT SUM(voucher_details.quantity) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 24) as gallons'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id AND voucher_details.article_id = 23) as sum_1k'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 55) AS sum_5k'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 56) AS sum_10k'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity) from voucher_details WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id) = 57) AS sum_15k'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id AND (SELECT articles.subgroup_id FROM articles WHERE articles.id = voucher_details.article_id ) = 58) AS sum_45k'), 
			  DB::Raw('(SELECT SUM(voucher_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = voucher_details.article_id )) FROM voucher_details WHERE voucher_details.voucher_id = vouchers.id) AS sum_total'))
        //    ->where('vouchers.voucher_type_id',1)
			->whereNotIn('vouchers.voucher_type_id', [2, 5, 6, 7,8,9,10,11,12])
			
			->when($company_id, function($query, $company_id) {
				return $query->where('vouchers.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('vouchers.client_id', $client_id);
			})
			->groupBy('vouchers.id')
			->orderBy('vouchers.company_id')
			->orderBy('issue_date')
			->orderBy('business_unit_name')
			->orderBy('vouchers.serie_number')
			->orderBy('vouchers.voucher_number')
		    ->get();
			$response=[];


			foreach ($elements as $voucher) {

				$voucher->company_short_name = $voucher['company_short_name'];
				$voucher->type_id = $voucher['type_id'];								
				$voucher->serie_number = $voucher['serie_number'];
				$voucher->voucher_number = $voucher['voucher_number'];				
				$voucher->quantity = $voucher['quantity'];
				$voucher->price = $voucher['price'];
				$voucher->sale_value = $voucher['taxed_operation'];
				$voucher->igv = $voucher['igv'];
				$voucher->total = $voucher['total'];
                $voucher->perception = $voucher['perception'];
				$voucher->total_perception = $voucher['total_perception'];
				$voucher->document_number = $voucher['document_number'];
				$voucher->client_code = $voucher['client_code'];
				$voucher->client_business_name =$voucher['client_business_name'];
                $voucher->gallons = $voucher['gallons'];
				$voucher->sum_1k = $voucher['sum_1k'];
				$voucher->sum_5k = $voucher['sum_5k'];
				$voucher->sum_10k =$voucher ['sum_10k'];
				$voucher->sum_15k = $voucher['sum_15k'];
				$voucher->sum_45k =$voucher ['sum_45k'];
				$voucher->sum_total =$voucher ['sum_total'];
                $voucher->business_unit_name = $voucher['business_unit_name'];
				$voucher->payment =$voucher['payment_name'];
				$voucher->expiry_date =$voucher['expiry_date'];
				$voucher->credit_note_reference_serie =$voucher['credit_note_reference_serie'];
				$voucher->credit_note_reference_number =$voucher['credit_note_reference_number'];
                $voucher->guide =$voucher['guide'];
             // $voucher->issue_date = $voucher['issue_date'];
                $voucher->ose =$voucher['ose'];
				$voucher->low_number =$voucher['low_number'];


				$response[] = $voucher;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';	
		$totals->type_id = '';							
		$totals->serie_number = '';
		$totals->voucher_number ='' ;				
		$totals->issue_date = '';
		$totals->price ='' ;
		$totals->taxed_operation ='' ;
		$totals->igv ='' ;
		$totals->total = '';
		$totals->perception = '';
		$totals->total_perception = '';
		$totals->document_number = '';
		$totals->client_code = '';
		$totals->client_business_name ='';
		$totals->gallons = '';
		$totals->sum_1k = '';
		$totals->sum_5k = '';
		$totals->sum_10k ='';
		$totals->sum_15k = '';
		$totals->sum_45k ='';
		$totals->sum_total ='';
		$totals->business_unit_name = '';
		$totals->payment_name ='';
		$totals->expiry_date ='';
		$totals->credit_note_reference_serie ='';
		$totals->credit_note_reference_number ='';
		$totals->guide ='';
		$totals->ose ='';
		$totals->low_number ='';

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:Z1');
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
			$sheet->setCellValue('B3', 'Compañía');
			$sheet->setCellValue('C3', 'Tipo');
			$sheet->setCellValue('D3', 'Serie');
			$sheet->setCellValue('E3', 'N°');
			$sheet->setCellValue('F3', 'Fecha de Emisión');		
         // $sheet->setCellValue('I3', 'Codigo');
			$sheet->setCellValue('G3', 'RUC');
			$sheet->setCellValue('H3', 'Razón Social');
			$sheet->setCellValue('I3', 'Precio');
			$sheet->setCellValue('J3', 'Valor Venta');
			$sheet->setCellValue('K3', 'IGV');
			$sheet->setCellValue('L3', 'Total');
			$sheet->setCellValue('M3', 'Percepción');
            $sheet->setCellValue('N3', 'Total percepción');
			$sheet->setCellValue('O3', 'Galones');
			$sheet->setCellValue('P3', 'Granel');
			$sheet->setCellValue('Q3', '5K');
			$sheet->setCellValue('R3', '10K');
			$sheet->setCellValue('S3', '15K');
			$sheet->setCellValue('T3', '45K');
			$sheet->setCellValue('U3', 'Total Kgs');
			$sheet->setCellValue('V3', 'Unidad de Negocio');
			$sheet->setCellValue('W3', 'Condicíón de pago');
			$sheet->setCellValue('X3', 'Fecha de Expiración');
			$sheet->setCellValue('Y3', 'Serie Nota de Credito');
			$sheet->setCellValue('Z3', 'Número de Nota de Credito');
			$sheet->setCellValue('AA3', 'Guía');
			$sheet->setCellValue('AB3', 'Envio OSE');
			$sheet->setCellValue('AC3', 'Número de baja');
			$sheet->getStyle('A3:AC3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->type_id);
				$sheet->setCellValue('D'.$row_number, $element->serie_number);
				$sheet->setCellValue('E'.$row_number, $element->voucher_number);
                $sheet->setCellValue('F'.$row_number, $element->issue_date);
				$sheet->setCellValue('G'.$row_number, $element->document_number);
				$sheet->setCellValue('H'.$row_number, $element->client_business_name);
                $sheet->setCellValue('I'.$row_number, $element->price);
				$sheet->setCellValue('J'.$row_number, $element->taxed_operation);
				$sheet->setCellValue('K'.$row_number, $element->igv);
                $sheet->setCellValue('L'.$row_number, $element->total);
                $sheet->setCellValue('M'.$row_number, $element->perception);
				$sheet->setCellValue('N'.$row_number, $element->total_perception);
                $sheet->setCellValue('O'.$row_number, $element->gallons);
                $sheet->setCellValue('P'.$row_number, $element->sum_1k);
				$sheet->setCellValue('Q'.$row_number, $element->sum_5k); 
				$sheet->setCellValue('R'.$row_number, $element->sum_10k);
				$sheet->setCellValue('S'.$row_number, $element->sum_15k);
				$sheet->setCellValue('T'.$row_number, $element->sum_45k);
				$sheet->setCellValue('U'.$row_number, $element->sum_total);
				$sheet->setCellValue('V'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('W'.$row_number, $element->payment_name);
				$sheet->setCellValue('X'.$row_number, $element->expiry_date);	
				$sheet->setCellValue('Y'.$row_number, $element->credit_note_reference_serie);	
				$sheet->setCellValue('Z'.$row_number, $element->credit_note_reference_number);
				$sheet->setCellValue('AA'.$row_number, $element->guide);	
				$sheet->setCellValue('AB'.$row_number, $element->ose);	
				$sheet->setCellValue('AC'.$row_number, $element->low_number);				
                $sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








