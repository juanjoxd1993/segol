<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\WarehouseDocumentType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class CollectionReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$max_datetime = CarbonImmutable::now()->toAtomString();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();
		$business_units = BusinessUnit::select('id', 'name')->get();

		return view('backend.collection_report')->with(compact('companies', 'max_datetime', 'warehouse_document_types', 'business_units'));
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
			'client_id.required_if'					=> 'Debe seleccionar un Cliente.',
		];

		$rules = [
			'date_type_id'					=> 'required',
			'initial_date'					=> 'required',
			'final_date'					=> 'required',
			'client_id'						=> 'required_if:client_type_id,0,1',
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
		$client_type_id = request('model.client_type_id');
		$client_id = request('model.client_id');
		$warehouse_document_type_id = request('model.warehouse_document_type_id');
		$business_unit_id = request('model.business_unit_id');

		$elements = Liquidation::leftjoin('sales', 'liquidations.sale_id', '=', 'sales.id')
			->leftjoin('companies', 'sales.company_id', '=', 'companies.id')
			->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->leftjoin('payment_methods', 'liquidations.payment_method_id', '=', 'payment_methods.id')
			->leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
			->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
			->whereNotIn('sales.if_bol', [1])
			->when($company_id, function ($query, $company_id) {
				return $query->where('sales.company_id', $company_id);
			})
			->when($date_type_id == 1, function ($query) use ($initial_date, $final_date) {
				return $query->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
							 ->where('sales.sale_date', '<=', $final_date->format('Y-m-d'));
			})
			->when($date_type_id == 2, function ($query) use ($initial_date, $final_date) {
				return $query->where('liquidations.created_at', '>=', $initial_date->startOfDay()->format('Y-m-d H:i:s'))
							 ->where('liquidations.created_at', '<=', $final_date->endOfDay()->format('Y-m-d H:i:s'));
			})
			->when($client_id, function ($query, $client_id) {
				return $query->where('clients.id', $client_id);
			})
			
			->when($warehouse_document_type_id, function ($query, $warehouse_document_type_id) {
				return $query->where('sales.warehouse_document_type_id', $warehouse_document_type_id);
			})
			->when($business_unit_id, function ($query, $business_unit_id) {
				return $query->where('clients.business_unit_id', $business_unit_id);
			})
			->select('companies.short_name as company_short_name',
			 'clients.id as client_id',
			  'clients.code as client_code', 
			  'document_types.name as document_type_name',
			  'sales.warehouse_document_type_id as warehouse_document_type_id',
			'clients.document_number', 
			'clients.business_name',
			'clients.int_name as int_name',
			'clients.bol_name as bol_name',
			'liquidations.collection',
			 DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") as liquidation_created_at'),
			'sales.sale_date', 
			'sales.expiry_date', 
			'warehouse_document_types.name as warehouse_document_type_name',
			 'liquidations.payment_method_id', 
			'sales.referral_serie_number', 
			'sales.referral_voucher_number',
			 'liquidations.amount', 
			 'sales.guide_series', 
			'sales.guide_number',
			 'liquidations.currency_id',DB::Raw('CONCAT(sales.guide_series, "-", sales.guide_number) as guide'),
			'liquidations.exchange_rate', 
			'payment_methods.name as payment_method_name',
			 'clients.route_id as route_id', 
			 'client_routes.short_name as route_name',
			 'liquidations.rem_date as remesa_date',
			 'liquidations.payment_sede as payment_sede',
			  'banks.name as bank_name',
			  DB::Raw('CONCAT(banks.short_name, "-", bank_accounts.account_number) as bank_account'), 
			  'liquidations.operation_number')
			->orderBy('company_short_name')
			->orderBy('liquidation_created_at')
			->orderBy('sale_date')
			->orderBy('expiry_date')
			->get();

		$elements->map(function ($item, $index) use (&$company_short_name, &$amount, $elements) {
			$item->origin = 'Liquidación';
			if ( $item->collection == 1 ) {
				$item->origin = 'Cobranza';
			}

			$item->sede_name = $item->int_name;

			if ( $item->warehouse_document_type_id == 7 ) {
				$item->sede_name = $item->business_name;
				$item->business_name = $item->bol_name;
			}

			$item->autogen = ' ';
			if ( $item->payment_method_id == 9 ) {
				$item->autogen = $item->operation_number;
				$item->operation_number='';
			}

			$item->pay_date = ' ';
			if ( $item->payment_method_id == 2 || $item->payment_method_id == 3 || $item->payment_method_id == 11  ) {
				$item->pay_date = $item->remesa_date;
				$item->remesa_date='';
			}

			if ( $item->referral_serie_number == 'B001' ) {
				$item->referral_serie_number = '001';
			}
			else if ( $item->referral_serie_number == 'B002' ) {
				$item->referral_serie_number = '002';
			}
			else if ( $item->referral_serie_number == 'B003' ) {
				$item->referral_serie_number = '003';
			}
			else if ( $item->referral_serie_number == 'B009' ) {
				$item->referral_serie_number = '009';
			}
			else if ( $item->referral_serie_number == 'B012' ) {
				$item->referral_serie_number = '012';
			}
			else if ( $item->referral_serie_number == 'B018' ) {
				$item->referral_serie_number = '018';
			}
			else {
				$item->referral_serie_number = $item->referral_serie_number;
			}

		
			$item->referral_voucher_number=str_pad($item->referral_voucher_number, 8, "0", STR_PAD_LEFT);

			$item->amount_soles = $item->amount;
			$item->amount_dolares = 0;
			if ( $item->currency_id == 2 ) {
				$amount = $item->amount * $item->exchange_rate;
				$item->amount_dolares = number_format($amount, 2, '.', '');
				$item->amount_soles = 0;
			}

			$item->exchange_rate = $item->exchange_rate ? $item->exchange_rate : '';
			$item->bank_account = $item->bank_account ? $item->bank_account : '';



            if ( $item->payment_method_id == 2 || $item->payment_method_id == 3 || $item->payment_method_id == 11 ) {
			$item->operation_number = $item->operation_number ? $item->operation_number : '';
		 	}

			return $item;
		});

		$response = [];
		$last_element = count($elements) - 1;
		$company_short_name = '';
		$sum_amount_soles = 0;
		$sum_amount_dolares = 0;
		$total_sum_amount_soles = 0;
		$total_sum_amount_dolares = 0;

		foreach ($elements as $index => $element) {
			if ( $index == 0 ) {
				$company_short_name = $element->company_short_name;
			}

			if ( $company_short_name !== $element->company_short_name ) {				
				$total = new stdClass();
				$total->company_short_name = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->route_name = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = '';
				$total->liquidation_created_at = 'TOTAL ' . $company_short_name;
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->warehouse_document_type_name = '';
				$total->guide_series = '';
				$total->guide_number = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->amount_soles = number_format($sum_amount_soles, 4, '.', '');
				$total->amount_dolares = number_format($sum_amount_dolares, 4, '.', '');
				$total->exchange_rate = '';
				$total->payment_method_name = '';
				$total->bank_account = '';
				$total->bank_name = '';
				$total->autogen = '';
				$total->operation_number = '';
				$total->origin = '';
				$total->sede_name= '';
				$total->guide = '';
				$total->pay_date = '';
				$total->remesa_date = '';
				$total->payment_sede = '';

				$total_sum_amount_soles += $sum_amount_soles;
				$total_sum_amount_dolares += $sum_amount_dolares;
				$sum_amount_soles = 0;
				$sum_amount_dolares = 0;

				$response[] = $total;

				$company_short_name = $element->company_short_name;
			}

			$response[] = $element;
			$sum_amount_soles += $element->amount_soles;
			$sum_amount_dolares += $element->amount_dolares;

			if ( $index == $last_element ) {
				$total = new stdClass();
				$total->company_short_name = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->route_name = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = '';
				$total->liquidation_created_at = 'TOTAL ' . $company_short_name;
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->warehouse_document_type_name = '';
				$total->guide_series = '';
				$total->guide_number = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->amount_soles = number_format($sum_amount_soles, 4, '.', '');
				$total->amount_dolares = number_format($sum_amount_dolares, 4, '.', '');
				$total->exchange_rate = '';
				$total->payment_method_name = '';
				$total->bank_name = '';
				$total->bank_account = '';
				$total->autogen = '';
				$total->operation_number = '';
				$total->origin = '';
				$total->sede_name = '';
				$total->guide = '';
				$total->pay_date = '';
				$total->remesa_date = '';
				$total->payment_sede = '';

				$total_sum_amount_soles += $sum_amount_soles;
				$total_sum_amount_dolares += $sum_amount_dolares;
				$sum_amount_soles = 0;
				$sum_amount_dolares = 0;

				$response[] = $total;

				$sumTotal = new stdClass();
				$sumTotal->company_short_name = '';
				$sumTotal->client_id = '';
				$sumTotal->client_code = '';
				$sumTotal->route_name = '';
				$sumTotal->document_type_name = '';
				$sumTotal->document_number = '';
				$sumTotal->business_name = '';
				$sumTotal->liquidation_created_at = 'TOTAL GENERAL';
				$sumTotal->sale_date = '';
				$sumTotal->expiry_date = '';
				$sumTotal->warehouse_document_type_name = '';
				$sumTotal->guide_series = '';
				$sumTotal->guide_number = '';
				$sumTotal->referral_serie_number = '';
				$sumTotal->referral_voucher_number = '';
				$sumTotal->amount_soles = number_format($total_sum_amount_soles, 4, '.', '');
				$sumTotal->amount_dolares = number_format($total_sum_amount_dolares, 4, '.', '');
				$sumTotal->exchange_rate = '';
				$sumTotal->payment_method_name = '';
				$sumTotal->bank_name = '';
				$sumTotal->bank_account = '';
				$sumTotal->autogen = '';
				$sumTotal->operation_number = '';
				$sumTotal->origin = '';
				$sumTotal->sede_name = '';
				$sumTotal->guide= '';
				$sumTotal->pay_date = '';
				$sumTotal->remesa_date = '';
				$sumTotal->payment_sede = '';

				$response[] = $sumTotal;
			}
		}

		if ( $export ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AC1');
			$sheet->setCellValue('A1', 'REPORTE DE CANCELACIONES '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y'));
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
			$sheet->setCellValue('C3', 'Ruta');
			$sheet->setCellValue('D3', 'ID Cliente');
			$sheet->setCellValue('E3', 'Cód. Cliente');
			$sheet->setCellValue('F3', 'Tipo de Doc. Id.');
			$sheet->setCellValue('G3', 'Nº Doc.');
			$sheet->setCellValue('H3', 'Razón Social');
			$sheet->setCellValue('I3', 'Punto Venta');
			$sheet->setCellValue('J3', 'Tipo Doc.');
			$sheet->setCellValue('K3', 'Nº Serie');
			$sheet->setCellValue('L3', 'Nº Doc.');
			$sheet->setCellValue('M3', 'Fecha emisión');
            $sheet->setCellValue('N3', 'Fecha venc.');
			$sheet->setCellValue('O3', 'Origen');
			$sheet->setCellValue('P3', 'Guía');
			$sheet->setCellValue('Q3', 'Monto S/');
			$sheet->setCellValue('R3', 'Monto US$');
			$sheet->setCellValue('S3', 'Tip. cambio');
			$sheet->setCellValue('T3', 'Autogenerado'); //es '' si no es remesa viene de operation number
			$sheet->setCellValue('U3', 'Entidad Financiera');
			$sheet->setCellValue('V3', 'Cuenta Bancaria Descripción');
			$sheet->setCellValue('W3', 'Nº Operación');
			$sheet->setCellValue('X3', 'Fecha de Pago'); //Fecha de voucher si hay voucher
			$sheet->setCellValue('Y3', 'Fecha de Remesa');
			$sheet->setCellValue('Z3', 'Sede Remesa');
			$sheet->setCellValue('AA3', 'Forma pago');
			$sheet->setCellValue('AB3', 'Monto Deposito Total'); // del voucher
			$sheet->setCellValue('AC3', 'Monto Cancelación Crédito'); //lo usado para liquidar
			$sheet->setCellValue('AD3', 'Origen');
			$sheet->setCellValue('AE3', 'Fecha de Cobranza'); //fecha de liquidación
			
			
			$sheet->getStyle('A3:AC3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;

				$saleDateYear = null;
				$expiryDateYear = null;
				$remesaDateYear = null;
				$payDateYear = null;
				$cobranzaDateYear = null;

			$sum_canc=Liquidation::leftjoin('sales', 'liquidations.sale_id', '=', 'sales.id')
			->where('sales.client_id', '=', $element->client_id)
			->where ('sales.warehouse_document_type_id','=',7)
			->where ('sales.guide_number','=',$element->guide_number)
			->where ('sales.guide_series','=',$element->guide_series) 
            ->select ('sales.paid')
			->sum ('sales.paid');
				
			if ($element->sale_date) {
				$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
				$saleDateYear = $saleDateObject;
			}
			if ($element->expiry_date) {
				$saleDateObject = date('d/m/Y',strtotime($element->expiry_date) );
				$expiryDateYear= $saleDateObject;
			}
			if ($element->remesa_date) {
				$saleDateObject = date('d/m/Y',strtotime($element->remesa_date) );
				$remesaDateYear = $saleDateObject;
			}
			if ($element->pay_date) {
				$saleDateObject = date('d/m/Y',strtotime($element->pay_date) );
				$payDateYear = $saleDateObject;
			}
			if ($element->pay_date) {
				$saleDateObject = date('d/m/Y',strtotime($element->pay_date) );
				$cobranzaDateYear = $saleDateObject;
			}




				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->route_name);
				$sheet->setCellValue('D'.$row_number, $element->client_id);
				$sheet->setCellValue('E'.$row_number, $element->client_code);
				$sheet->setCellValue('F'.$row_number, $element->document_type_name);
				$sheet->setCellValue('G'.$row_number, $element->document_number);
				$sheet->setCellValue('H'.$row_number, $element->business_name);
				$sheet->setCellValue('I'.$row_number, $element->sede_name);
				$sheet->setCellValue('J'.$row_number, $element->warehouse_document_type_name);
				$sheet->setCellValue('K'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('L'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('M'.$row_number, $saleDateYear);
				$sheet->setCellValue('N'.$row_number, $expiryDateYear);
				$sheet->setCellValue('O'.$row_number, $element->origin);
				$sheet->setCellValue('P'.$row_number, $element->guide);				
				$sheet->setCellValue('Q'.$row_number, $element->amount_soles);
				$sheet->setCellValue('R'.$row_number, $element->amount_dolares);
				$sheet->setCellValue('S'.$row_number, $element->exchange_rate);
				$sheet->setCellValue('T'.$row_number, $element->autogen);
				$sheet->setCellValue('U'.$row_number, $element->bank_name);
				$sheet->setCellValue('V'.$row_number, $element->bank_account);
				$sheet->setCellValue('W'.$row_number, $element->operation_number);
				$sheet->setCellValue('X'.$row_number, $payDateYear);
				$sheet->setCellValue('Y'.$row_number, $remesaDateYear);
				$sheet->setCellValue('Z'.$row_number, $element->payment_sede);
				$sheet->setCellValue('AA'.$row_number, $element->payment_method_name);
				$sheet->setCellValue('AB'.$row_number, $sum_canc);
				$sheet->setCellValue('AC'.$row_number, $element->amount_soles);
				$sheet->setCellValue('AE'.$row_number, $cobranzaDateYear);


				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('AB'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('AC'.$row_number)->getNumberFormat()->setFormatCode('0.00');

				if ( $element->company_short_name == '' ) {
					$sheet->getStyle('B'.$row_number.':AE'.$row_number)->applyFromArray([
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
			$sheet->getColumnDimension('X')->setAutoSize(true);
			$sheet->getColumnDimension('Y')->setAutoSize(true);
			$sheet->getColumnDimension('Z')->setAutoSize(true);
			$sheet->getColumnDimension('AA')->setAutoSize(true);
			$sheet->getColumnDimension('AB')->setAutoSize(true);
			$sheet->getColumnDimension('AC')->setAutoSize(true);
			$sheet->getColumnDimension('AD')->setAutoSize(true);
			$sheet->getColumnDimension('AE')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
