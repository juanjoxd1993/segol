<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Sale;
use App\WarehouseDocumentType;
use Carbon\CarbonImmutable;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class UncollectedDocumentReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$max_datetime = CarbonImmutable::now()->toAtomString();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();
		$business_units = BusinessUnit::select('id', 'name')->get();

		return view('backend.uncollected_document_report')->with(compact('companies', 'max_datetime', 'warehouse_document_types', 'business_units'));
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
			'initial_date.required'					=> 'Debe seleccionar una Fecha inicial.',
			'final_date.required'					=> 'Debe seleccionar una Fecha final.',
		];

		$rules = [
			'initial_date'					=> 'required',
			'final_date'					=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$export = request('export');

		$company_id = request('model.company_id');
		$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'));
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'));
		$client_id = request('model.client_id');
		$warehouse_document_type_id = request('model.warehouse_document_type_id');
		$business_unit_id = request('model.business_unit_id');

		$elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
			->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('payments', 'clients.payment_id', '=', 'payments.id')
			->leftjoin('currencies', 'sales.currency_id', '=', 'currencies.id')
			->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
			->when($company_id, function ($query, $company_id) {
				return $query->where('sales.company_id', $company_id);
			})
			->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
			->where('sales.sale_date', '<=', $final_date->format('Y-m-d'))
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
			->select('companies.short_name as company_short_name', 'warehouse_document_types.name as warehouse_document_type_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_date', 'sales.expiry_date', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'payments.name as payment_name', 'currencies.symbol as currency_symbol', 'sales.sale_value', 'sales.exonerated_value', 'sales.inaccurate_value', 'sales.igv', 'sales.total', 'business_units.name as business_unit_name')
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
		$total_company_sale_value = 0;
		$total_company_exonerated_value = 0;
		$total_company_inaccurate_value = 0;
		$total_company_igv = 0;
		$total_company_total = 0;
		$grand_total_sale_value = 0;
		$grand_total_exonerated_value = 0;
		$grand_total_inaccurate_value = 0;
		$grand_total_igv = 0;
		$grand_total_total = 0;

		foreach ($elements as $index => $element) {
			if ( $index == 0 ) {
				$company_short_name = $element->company_short_name;
			}

			if ( $company_short_name !== $element->company_short_name ) {
				$total = new stdClass();
				$total->company_short_name = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = 'TOTAL ' . $company_short_name;
				$total->payment_name = '';
				$total->currency_symbol = '';
				$total->sale_value = number_format($total_company_sale_value, 4, '.', '');
				$total->exonerated_value = number_format($total_company_exonerated_value, 4, '.', '');
				$total->inaccurate_value = number_format($total_company_inaccurate_value, 4, '.', '');
				$total->igv = number_format($total_company_igv, 2, '.', '');
				$total->total = number_format($total_company_total, 2, '.', '');
				$total->business_unit_name = '';

				$grand_total_sale_value += $total_company_sale_value;
				$grand_total_exonerated_value += $total_company_exonerated_value;
				$grand_total_inaccurate_value += $total_company_inaccurate_value;
				$grand_total_igv += $total_company_igv;
				$grand_total_total += $total_company_total;
				$total_company_sale_value = 0;
				$total_company_exonerated_value = 0;
				$total_company_inaccurate_value = 0;
				$total_company_igv = 0;
				$total_company_total = 0;

				$response[] = $total;

				$company_short_name = $element->company_short_name;
			}

			$response[] = $element;
			$total_company_sale_value += $element->sale_value;
			$total_company_exonerated_value += $element->exonerated_value;
			$total_company_inaccurate_value += $element->inaccurate_value;
			$total_company_igv += $element->igv;
			$total_company_total += $element->total;

			if ( $index == $last_element ) {
				$total = new stdClass();
				$total->company_short_name = '';
				$total->warehouse_document_type_name = '';
				$total->referral_serie_number = '';
				$total->referral_voucher_number = '';
				$total->sale_date = '';
				$total->expiry_date = '';
				$total->client_id = '';
				$total->client_code = '';
				$total->document_type_name = '';
				$total->document_number = '';
				$total->business_name = 'TOTAL ' . $company_short_name;
				$total->payment_name = '';
				$total->currency_symbol = '';
				$total->sale_value = number_format($total_company_sale_value, 4, '.', '');
				$total->exonerated_value = number_format($total_company_exonerated_value, 4, '.', '');
				$total->inaccurate_value = number_format($total_company_inaccurate_value, 4, '.', '');
				$total->igv = number_format($total_company_igv, 2, '.', '');
				$total->total = number_format($total_company_total, 2, '.', '');
				$total->business_unit_name = '';

				$grand_total_sale_value += $total_company_sale_value;
				$grand_total_exonerated_value += $total_company_exonerated_value;
				$grand_total_inaccurate_value += $total_company_inaccurate_value;
				$grand_total_igv += $total_company_igv;
				$grand_total_total += $total_company_total;
				$total_company_sale_value = 0;
				$total_company_exonerated_value = 0;
				$total_company_inaccurate_value = 0;
				$total_company_igv = 0;
				$total_company_total = 0;

				$response[] = $total;

				$sumTotal = new stdClass();
				$sumTotal->company_short_name = '';
				$sumTotal->warehouse_document_type_name = '';
				$sumTotal->referral_serie_number = '';
				$sumTotal->referral_voucher_number = '';
				$sumTotal->sale_date = '';
				$sumTotal->expiry_date = '';
				$sumTotal->client_id = '';
				$sumTotal->client_code = '';
				$sumTotal->document_type_name = '';
				$sumTotal->document_number = '';
				$sumTotal->business_name = 'TOTAL GENERAL';
				$sumTotal->payment_name = '';
				$sumTotal->currency_symbol = '';
				$sumTotal->sale_value = number_format($grand_total_sale_value, 4, '.', '');
				$sumTotal->exonerated_value = number_format($grand_total_exonerated_value, 4, '.', '');
				$sumTotal->inaccurate_value = number_format($grand_total_inaccurate_value, 4, '.', '');
				$sumTotal->igv = number_format($grand_total_igv, 2, '.', '');
				$sumTotal->total = number_format($grand_total_total, 2, '.', '');
				$sumTotal->business_unit_name = '';

				$response[] = $sumTotal;
			}
		}

		if ( $export ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', 'RELACIÓN DE DOCUMENTOS EMITIDOS '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y'));
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
			$sheet->setCellValue('H3', 'ID Cliente');
			$sheet->setCellValue('I3', 'Cód. Cliente');
			$sheet->setCellValue('J3', 'Doc. Cliente');
			$sheet->setCellValue('K3', 'Nº Doc.');
			$sheet->setCellValue('L3', 'Razón Social');
			$sheet->setCellValue('M3', 'Tipo Venta');
			$sheet->setCellValue('N3', 'Moneda');
			$sheet->setCellValue('O3', 'Valor Afecto');
			$sheet->setCellValue('P3', 'valor Inafecto');
			$sheet->setCellValue('Q3', 'Valor Exonerado');
			$sheet->setCellValue('R3', 'IGV');
			$sheet->setCellValue('S3', 'Total');
			$sheet->setCellValue('T3', 'Undidad Negocio');
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
				$sheet->setCellValue('C'.$row_number, $element->warehouse_document_type_name);
				$sheet->setCellValue('D'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('E'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('F'.$row_number, $element->sale_date);
				$sheet->setCellValue('G'.$row_number, $element->expiry_date);
				$sheet->setCellValue('H'.$row_number, $element->client_id);
				$sheet->setCellValue('I'.$row_number, $element->client_code);
				$sheet->setCellValue('J'.$row_number, $element->document_type_name);
				$sheet->setCellValue('K'.$row_number, $element->document_number);
				$sheet->setCellValue('L'.$row_number, $element->business_name);
				$sheet->setCellValue('M'.$row_number, $element->payment_name);
				$sheet->setCellValue('N'.$row_number, $element->currency_symbol);
				$sheet->setCellValue('O'.$row_number, $element->sale_value);
				$sheet->setCellValue('P'.$row_number, $element->inaccurate_value);
				$sheet->setCellValue('Q'.$row_number, $element->exonerated_value);
				$sheet->setCellValue('R'.$row_number, $element->igv);
				$sheet->setCellValue('S'.$row_number, $element->total);
				$sheet->setCellValue('T'.$row_number, $element->business_unit_name);

				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
				$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('S'.$row_number)->getNumberFormat()->setFormatCode('0.00');

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
