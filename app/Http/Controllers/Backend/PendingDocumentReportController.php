<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Sale;
use App\Manager;
use App\ClientRoute;
use App\WarehouseDocumentType;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use Carbon\Carbon;
use stdClass;

class PendingDocumentReportController extends Controller
{
  public function index() {
		$companies = Company::select('id', 'name')->get();
		$max_datetime = CarbonImmutable::now()->toAtomString();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();
		$business_units = BusinessUnit::select('id', 'name')->get();
		$client_routes= ClientRoute::select('id','name')->get();

		return view('backend.pending_document_report')->with(compact('companies', 'max_datetime', 'warehouse_document_types', 'business_units','client_routes'));
	}

	public function getClients() {
		$company_id = request('company_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->where('company_id', $company_id)
			->where('business_name', 'like', '%'.$q.'%')
			->get();

		return $clients;
	}

	public function validateForm() {
		$messages = [
			'date_type_id.required'					=> 'Debe seleccionar un Tipo de Fecha.',
			'initial_date.required'					=> 'Debe seleccionar una Fecha inicial.',
			'final_date.required'					=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			'date_type_id'					=> 'required',
			'initial_date'					=> 'required',
			'final_date'					=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$export = request('export');

		$company_id = request('model.company_id');
		$date_type_id = request('model.date_type_id');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'));
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'));
		$client_id = request('model.client_id');
		$warehouse_document_type_id = request('model.warehouse_document_type_id');
		$business_unit_id = request('model.business_unit_id');
		$route_id = request('model.route_id');

		$elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
										->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
										->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
										->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
										->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
										->leftjoin('payments', 'clients.payment_id', '=', 'payments.id')
										->leftjoin('currencies', 'sales.currency_id', '=', 'currencies.id')
										->leftjoin('managers', 'clients.manager_id', '=', 'managers.id')
										->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
										->when($company_id, function ($query, $company_id) {
											return $query->where('sales.company_id', $company_id);
										})
										->when($route_id, function ($query, $route_id) {
											return $query->where('clients.route_id', $route_id);
										})
										->when($date_type_id == 1, function ($query) use ($initial_date, $final_date) {
											return $query->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
														->where('sales.sale_date', '<=', $final_date->format('Y-m-d'));
										})
										->when($date_type_id == 2, function ($query) use ($initial_date, $final_date) {
											return $query->where('sales.expiry_date', '>=', $initial_date->startOfDay()->format('Y-m-d'))
														->where('sales.expiry_date', '<=', $final_date->endOfDay()->format('Y-m-d'));
										})
										->when($client_id, function ($query, $client_id) {
											return $query->where('clients.id', $client_id);
										})
										->when($warehouse_document_type_id, function ($query, $warehouse_document_type_id) {
											return $query->where('sales.warehouse_document_type_id', $warehouse_document_type_id);
										})
										->when($warehouse_document_type_id == null, function ($query, $warehouse_document_type_id) {
											return $query->where('sales.warehouse_document_type_id', '!=', 27);
										})
										->when($business_unit_id, function ($query, $business_unit_id) {
											return $query->where('clients.business_unit_id', $business_unit_id);
										})
										->where('sales.balance', '!=', 0)
										->whereNotIn('sales.client_id', [1031,427,13326,14072,13783,14269,14274,14294,14328,14329,14258])
										->whereNotIn('sales.warehouse_document_type_id', [1, 2, 3, 4,6,9,10,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
										->select('companies.short_name as company_short_name', 'sales.warehouse_document_type_id', 'warehouse_document_types.name as warehouse_document_type_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_date', 'sales.expiry_date', 'clients.id as client_id', DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'),'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name','clients.bol_name','clients.int_name', 'payments.name as payment_name', 'currencies.symbol as currency_symbol', 'sales.total_perception', 'sales.balance', 'sales.paid','sales.account_name','business_units.name as business_unit_name','managers.name as manager')
										->orderBy('clients.business_name')
										->orderBy('company_short_name')
										->orderBy('warehouse_document_type_name')
										->orderBy('referral_serie_number')
										->orderBy('referral_voucher_number')
										->orderBy('sale_date')
										->orderBy('expiry_date')
										->get();

		$response = [];
		$last_element = count($elements) - 1;
		$company_short_name = '';
		$total_company_total = 0;
		$total_company_balance = 0;
		$total_company_paid = 0;
		$grand_total_total = 0;
		$grand_total_balance = 0;
		$grand_total_paid = 0;

		foreach ($elements as $index => $element) {
			if ( $index == 0 ) {
				$company_short_name = $element->company_short_name;
				
			}


			

			

			if ( $company_short_name !== $element->company_short_name ) {
				$total = new stdClass();
				$total->company_short_name = '';
				$total->warehouse_document_type_id = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->client_route_id = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->int_name = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = 'TOTAL ' . $company_short_name;
				$total->payment_name = '';
				$total->currency_symbol = '';
				$total->manager = '';
				$total->total_perception = number_format($total_company_total, 2, '.', '');
				$total->balance = number_format($total_company_balance, 2, '.', '');
				$total->paid = number_format($total_company_paid, 2, '.', '');
				$total->business_unit_name = '';
				$total->days = '';
				

				$grand_total_total += $total_company_total;
				$grand_total_balance += $total_company_balance;
				$grand_total_paid += $total_company_paid;
				$total_company_total = 0;
				$total_company_balance = 0;
				$total_company_paid = 0;
				

				$response[] = $total;

				$company_short_name = $element->company_short_name;
				
			}

			$response[] = $element;
			$total_company_total += $element->total_perception;
			$total_company_balance += $element->balance;
			$total_company_paid += $element->paid;

			if ( $index == $last_element ) {
				$total = new stdClass();
				$total->company_short_name = '';
				$total->warehouse_document_type_id = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->client_route_id = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->int_name = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = 'TOTAL ' . $company_short_name;
				$total->payment_name = '';
				$total->currency_symbol = '';
				$total->manager = '';
				$total->days = '';
				$total->total_perception = number_format($total_company_total, 2, '.', '');
				$total->balance = number_format($total_company_balance, 2, '.', '');
				$total->paid = number_format($total_company_paid, 2, '.', '');
				$total->business_unit_name = '';
				
			//	$total->account_name = '';
				

				$grand_total_total += $total_company_total;
				$grand_total_balance += $total_company_balance;
				$grand_total_paid += $total_company_paid;
				$total_company_total = 0;
				$total_company_balance = 0;
				$total_company_paid = 0;

				
			
				$response[] = $total;

				$sumTotal = new stdClass();
				$sumTotal->company_short_name = '';
				$sumTotal->warehouse_document_type_name = '';
				$sumTotal->warehouse_document_type_id= '';
				$sumTotal->referral_serie_number = '';
				$sumTotal->referral_voucher_number = '';
				$sumTotal->sale_date = '';
				$sumTotal->expiry_date = '';
				$sumTotal->client_route_id = '';
				$sumTotal->client_id = '';
				$sumTotal->client_code = '';
				$sumTotal->int_name = '';
				$sumTotal->document_type_name = '';
				$sumTotal->document_number = '';
				$sumTotal->business_name = 'TOTAL GENERAL';
				$sumTotal->payment_name = '';
				$sumTotal->currency_symbol = '';
				$sumTotal->manager = '';
				$sumTotal->days = '';
				$sumTotal->total_perception = number_format($grand_total_total, 2, '.', '');
				$sumTotal->balance = number_format($grand_total_balance, 2, '.', '');
				$sumTotal->paid = number_format($grand_total_paid, 2, '.', '');
				$sumTotal->business_unit_name = '';
				

				$response[] = $sumTotal;
			}
		}

		if ( $export ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:W1');
			$sheet->setCellValue('A1', 'DOCUMENTOS PENDIENTES '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y').'  '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Tipo Doc.');
			$sheet->setCellValue('D3', 'Nº Serie');
			$sheet->setCellValue('E3', 'Nº Doc.');
			$sheet->setCellValue('F3', 'Fecha emisión');
			$sheet->setCellValue('G3', 'Fecha venc.');
			$sheet->setCellValue('H3', 'Ruta');
			$sheet->setCellValue('I3', 'ID Cliente');
			$sheet->setCellValue('J3', 'Cód. Cliente');
			$sheet->setCellValue('K3', 'Doc. Cliente');
			$sheet->setCellValue('L3', 'Nº Doc.');
			$sheet->setCellValue('M3', 'Razón Social');
			$sheet->setCellValue('N3', 'Punto de Venta');
			$sheet->setCellValue('O3', 'Tipo Venta');
			$sheet->setCellValue('P3', 'Moneda');
			$sheet->setCellValue('Q3', 'Total');
			$sheet->setCellValue('R3', 'Pago a cuenta');
			$sheet->setCellValue('S3', 'Saldo');
			$sheet->setCellValue('T3', 'Saldo Acumulado');
			$sheet->setCellValue('U3', 'Unidad Negocio');
			$sheet->setCellValue('V3', 'Vendedor');
			$sheet->setCellValue('W3', 'Supervisor');
			$sheet->setCellValue('X3', 'Dias de Morosidad');

			$sheet->getStyle('A3:X3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$acumulate = 0;
			$before_element_client_id = 0;

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;

				$element_client_id = $element->client_id;

				if ($before_element_client_id == $element_client_id) {
					$acumulate += $element->balance;
				} else {
					$before_element_client_id = $element_client_id;
					$acumulate = $element->balance;
				}

			
			    if ( $element->warehouse_document_type_id == 7 ) {
				$element->int_name = $element->bol_name;
			    }


				$date1 = Carbon::now();
				$date2 = Carbon::parse($element->expiry_date);

            	$diff = $date1->diffInDays($date2);
		
				if($date1 > $date2){

				$days= $diff;
				} else{
				$days= 0;
		    	}



				$saleDateYear = null;
				$expiryDateYear = null;
				
				if ($element->sale_date) {
					$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
					$saleDateYear = $saleDateObject;
				}
				if ($element->expiry_date) {
					$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
					$expiryDateYear= $saleDateObject;
				}

				if ( $element->referral_serie_number == 'B001' ) {
					$element->referral_serie_number = '001';
				}
				else if ( $element->referral_serie_number == 'B002' ) {
					$element->referral_serie_number = '003';
				}
				else if ( $element->referral_serie_number == 'B003' ) {
					$element->referral_serie_number = '003';
				}
				else if ( $element->referral_serie_number == 'B009' ) {
					$element->referral_serie_number = '009';
				}
				else if ( $element->referral_serie_number == 'B012' ) {
					$element->referral_serie_number = '012';
				}
				else if ( $element->referral_serie_number == 'B018' ) {
					$element->referral_serie_number = '018';
				}
				else {
					$element->referral_serie_number = $element->referral_serie_number;
				}



				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->warehouse_document_type_name);
				$sheet->setCellValue('D'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('E'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('F'.$row_number, $saleDateYear);
				$sheet->setCellValue('G'.$row_number, $expiryDateYear);
				$sheet->setCellValue('H'.$row_number, $element->client_route_id);
				$sheet->setCellValue('I'.$row_number, $element->client_id);
				$sheet->setCellValue('J'.$row_number, $element->client_code);
				$sheet->setCellValue('K'.$row_number, $element->document_type_name);
				$sheet->setCellValue('L'.$row_number, $element->document_number);
				$sheet->setCellValue('M'.$row_number, $element->business_name);
				$sheet->setCellValue('N'.$row_number, $element->int_name);
				$sheet->setCellValue('O'.$row_number, $element->payment_name);
				$sheet->setCellValue('P'.$row_number, $element->currency_symbol);
				$sheet->setCellValue('Q'.$row_number, $element->total_perception);
				$sheet->setCellValue('R'.$row_number, $element->paid);
				$sheet->setCellValue('S'.$row_number, $element->balance);
				$sheet->setCellValue('T'.$row_number, $acumulate);
				$sheet->setCellValue('U'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('V'.$row_number, $element->manager);
				$sheet->setCellValue('W'.$row_number, $element->manager);
				$sheet->setCellValue('X'.$row_number, $days);
				

				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('T'.$row_number)->getNumberFormat()->setFormatCode('0.00');

				if ( $element->company_short_name == '' ) {
					$sheet->getStyle('B'.$row_number.':W'.$row_number)->applyFromArray([
						'font' => [
							'bold' => true,
						],
						'alignment' => [
							'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
						]
					]);
				}

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
			


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
