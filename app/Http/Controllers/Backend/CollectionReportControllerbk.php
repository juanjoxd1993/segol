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
			'client_type_id.required_with'			=> 'Debe seleccionar un Tipo de Cliente.',
			'client_id.required_if'					=> 'Debe seleccionar un Cliente.',
		];

		$rules = [
			'date_type_id'					=> 'required',
			'initial_date'					=> 'required',
			'final_date'					=> 'required',
			'client_type_id'				=> 'required_with:client_id',
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
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->leftjoin('payment_methods', 'liquidations.payment_method_id', '=', 'payment_methods.id')
			->leftjoin('bank_accounts', 'liquidations.bank_account_id', '=', 'bank_accounts.id')
			->leftjoin('banks', 'bank_accounts.bank_id', '=', 'banks.id')
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
			->when($client_type_id == 1, function ($query) use ($client_id) {
				return $query->where('clients.id', $client_id);
			})
			->when($client_type_id == 2, function ($query) use ($client_id) {
				return $query->where('clients.link_client_id', $client_id);
			})
			->when($warehouse_document_type_id, function ($query, $warehouse_document_type_id) {
				return $query->where('sales.warehouse_document_type_id', $warehouse_document_type_id);
			})
			->when($business_unit_id, function ($query, $business_unit_id) {
				return $query->where('clients.business_unit_id', $business_unit_id);
			})
			->select('companies.short_name as company_short_name', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'liquidations.collection', DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") as liquidation_created_at'), 'sales.sale_date', 'sales.expiry_date', 'warehouse_document_types.name as warehouse_document_type_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'liquidations.amount', 'liquidations.currency_id', 'liquidations.exchange_rate', 'payment_methods.name as payment_method_name', DB::Raw('CONCAT(banks.short_name, "-", bank_accounts.account_number) as bank_account'), 'liquidations.operation_number')
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
			
			$item->amount_soles = $item->amount;
			$item->amount_dolares = 0;
			if ( $item->currency_id == 2 ) {
				$amount = $item->amount * $item->exchange_rate;
				$item->amount_dolares = number_format($amount, 2, '.', '');
				$item->amount_soles = 0;
			}

			$item->exchange_rate = $item->exchange_rate ? $item->exchange_rate : '';
			$item->bank_account = $item->bank_account ? $item->bank_account : '';
			$item->operation_number = $item->operation_number ? $item->operation_number : '';

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
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = '';
				$total->liquidation_created_at = 'TOTAL ' . $company_short_name;
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->amount_soles = number_format($sum_amount_soles, 4, '.', '');
				$total->amount_dolares = number_format($sum_amount_dolares, 4, '.', '');
				$total->exchange_rate = '';
				$total->payment_method_name = '';
				$total->bank_account = '';
				$total->operation_number = '';
				$total->origin = '';

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
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = '';
				$total->liquidation_created_at = 'TOTAL ' . $company_short_name;
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->amount_soles = number_format($sum_amount_soles, 4, '.', '');
				$total->amount_dolares = number_format($sum_amount_dolares, 4, '.', '');
				$total->exchange_rate = '';
				$total->payment_method_name = '';
				$total->bank_account = '';
				$total->operation_number = '';
				$total->origin = '';

				$total_sum_amount_soles += $sum_amount_soles;
				$total_sum_amount_dolares += $sum_amount_dolares;
				$sum_amount_soles = 0;
				$sum_amount_dolares = 0;

				$response[] = $total;

				$sumTotal = new stdClass();
				$sumTotal->company_short_name = '';
				$sumTotal->client_id = '';
				$sumTotal->client_code = '';
				$sumTotal->document_type_name = '';
				$sumTotal->document_number = '';
				$sumTotal->business_name = '';
				$sumTotal->liquidation_created_at = 'TOTAL GENERAL';
				$sumTotal->sale_date = '';
				$sumTotal->expiry_date = '';
				$sumTotal->warehouse_document_type_name = '';
				$sumTotal->referral_serie_number = '';
				$sumTotal->referral_voucher_number = '';
				$sumTotal->amount_soles = number_format($total_sum_amount_soles, 4, '.', '');
				$sumTotal->amount_dolares = number_format($total_sum_amount_dolares, 4, '.', '');
				$sumTotal->exchange_rate = '';
				$sumTotal->payment_method_name = '';
				$sumTotal->bank_account = '';
				$sumTotal->operation_number = '';
				$sumTotal->origin = '';

				$response[] = $sumTotal;
			}
		}

		if ( $export ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', 'RELACIÓN DE COBRANZAS '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y'));
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
			$sheet->setCellValue('C3', 'ID Cliente');
			$sheet->setCellValue('D3', 'Cód. Cliente');
			$sheet->setCellValue('E3', 'Doc. Cliente');
			$sheet->setCellValue('F3', 'Nº Doc.');
			$sheet->setCellValue('G3', 'Razón Social');
			$sheet->setCellValue('H3', 'Origen');
			$sheet->setCellValue('I3', 'Fecha cobranza');
			$sheet->setCellValue('J3', 'Fecha emisión');
			$sheet->setCellValue('K3', 'Fecha venc.');
			$sheet->setCellValue('L3', 'Tipo Doc.');
			$sheet->setCellValue('M3', 'Nº Serie');
			$sheet->setCellValue('N3', 'Nº Doc.');
			$sheet->setCellValue('O3', 'Monto S/');
			$sheet->setCellValue('P3', 'Monto US$');
			$sheet->setCellValue('Q3', 'Tip. cambio');
			$sheet->setCellValue('R3', 'Forma pago');
			$sheet->setCellValue('S3', 'Banco');
			$sheet->setCellValue('T3', 'Nº Operación');
			$sheet->getStyle('A3:T3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->client_id);
				$sheet->setCellValue('D'.$row_number, $element->client_code);
				$sheet->setCellValue('E'.$row_number, $element->document_type_name);
				$sheet->setCellValue('F'.$row_number, $element->document_number);
				$sheet->setCellValue('G'.$row_number, $element->business_name);
				$sheet->setCellValue('H'.$row_number, $element->origin);
				$sheet->setCellValue('I'.$row_number, $element->liquidation_created_at);
				$sheet->setCellValue('J'.$row_number, $element->sale_date);
				$sheet->setCellValue('K'.$row_number, $element->expiry_date);
				$sheet->setCellValue('L'.$row_number, $element->warehouse_document_type_name);
				$sheet->setCellValue('M'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('N'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('O'.$row_number, $element->amount_soles);
				$sheet->setCellValue('P'.$row_number, $element->amount_dolares);
				$sheet->setCellValue('Q'.$row_number, $element->exchange_rate);
				$sheet->setCellValue('R'.$row_number, $element->payment_method_name);
				$sheet->setCellValue('S'.$row_number, $element->bank_account);
				$sheet->setCellValue('T'.$row_number, $element->operation_number);

				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.0000');

				if ( $element->company_short_name == '' ) {
					$sheet->getStyle('B'.$row_number.':T'.$row_number)->applyFromArray([
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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
