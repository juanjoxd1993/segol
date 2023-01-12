<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class CheckingAccountReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$max_datetime = CarbonImmutable::now()->toAtomString();
		$business_units = BusinessUnit::select('id', 'name')->get();

		return view('backend.checking_account_report')->with(compact('companies', 'max_datetime', 'business_units'));
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
		// $initial_date = CarbonImmutable::createFromDate('2020-12-01');
		// $final_date = CarbonImmutable::createFromDate('2020-12-01');
		$client_id = request('model.client_id');
		$business_unit_id = request('model.business_unit_id');

		$clients = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
			->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
			->when($company_id, function ($query, $company_id) {
				return $query->where('sales.company_id', $company_id);
			})
			->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
			->where('sales.sale_date', '<=', $final_date->format('Y-m-d'))
			->when($client_id, function ($query, $client_id) {
				return $query->where('clients.id', $client_id);
			})
			->where(function ($query) {
				$query->where(function ($subquery) {
					$subquery->where('sales.warehouse_document_type_id', '<>', 27)
						->where('sales.warehouse_document_type_id', '<>', 28);
					})
					->orWhere(function ($subquery) {
						$subquery->where('sales.warehouse_document_type_id', 27)
							->orWhere('sales.warehouse_document_type_id', 28)
							->where('sales.paid', '<>', 0);
					});
			})
			->select('companies.short_name as company_short_name', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'business_units.name as business_unit_name')
			->groupBy('companies.id', 'clients.id')
			->orderBy('company_short_name')
			->orderBy('clients.business_name')
			->get();

		$clients->map(function ($item, $index) use ($initial_date, $final_date) {
			/** Calcular Saldo anterior */
			$previous_balance_soles = Sale::leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
				->where('sales.client_id', $item->client_id)
				->where('sales.sale_date', '<', $initial_date->format('Y-m-d'))
				->where('liquidations.currency_id', 1)
				->select(DB::Raw('SUM(sales.total_perception) - SUM(liquidations.amount) as previous_balance_soles'))
				->first();

			$previous_balance_dolares = Liquidation::leftjoin('sales', 'liquidations.sale_id', '=', 'sales.id')
				->where('sales.client_id', $item->client_id)
				->where('sales.sale_date', '<', $initial_date->format('Y-m-d'))
				->where('liquidations.currency_id', 2)
				->select(DB::Raw('SUM(liquidations.amount * liquidations.exchange_rate) as previous_balance_dolares'))
				->first();

			$previous_balance = number_format($previous_balance_soles->previous_balance_soles + $previous_balance_dolares->previous_balance_dolares, 2, '.', '');
			$item->previous_balance = $previous_balance;

			/** Calcular Cargos */
			$charges = Sale::where('client_id', $item->client_id)
				->where('sale_date', '>=', $initial_date->format('Y-m-d'))
				->where('sale_date', '<=', $final_date->format('Y-m-d'))
				->where('total_perception', '>', 0)
				->select(DB::Raw('SUM(total_perception) as charges'))
				->first();
			$item->charges = number_format($charges->charges, 2, '.', '');

			/** Calcular Abonos */
			$deposits_soles = Sale::leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')
				->where('sales.client_id', $item->client_id)
				->where('sale_date', '>=', $initial_date->format('Y-m-d'))
				->where('sale_date', '<=', $final_date->format('Y-m-d'))
				->where('sales.total_perception', '<', 0)
				->select(DB::Raw('CASE WHEN COUNT(sales.total_perception) > 0 THEN SUM(sales.total_perception) ELSE 0 END + CASE WHEN liquidations.amount > 0 THEN SUM(liquidations.amount) ELSE 0 END as deposits_soles'))
				->first();

			$deposits_dolares = Liquidation::leftjoin('sales', 'liquidations.sale_id', '=', 'sales.id')
				->where('sales.client_id', $item->client_id)
				->where('sale_date', '>=', $initial_date->format('Y-m-d'))
				->where('sale_date', '<=', $final_date->format('Y-m-d'))
				->where('liquidations.currency_id', 2)
				->select(DB::Raw('SUM(liquidations.amount * liquidations.exchange_rate) as deposits_dolares'))
				->first();

			$item->deposits = number_format(abs($deposits_soles->deposits_soles) + $deposits_dolares->deposits_dolares, 2, '.', '');

			/** Calcular Saldo */
			$item->balance = number_format($item->previous_balance + $item->charges - $item->deposits, 2, '.', '');

			$item->warehouse_document_type_name = '';
			$item->referral_serie_number = '';
			$item->referral_voucher_number = '';
			$item->sale_date = '';
			$item->expiry_date = '';
			$item->liquidation_created_at = '';
			$item->exchange_rate = '';
			$item->amount_dolares = '';

			return $item;
		});

		$response = [];
		$last_element = count($clients) - 1;
		$company_short_name = '';
		$total_company_previous_balance = 0;
		$total_company_charges = 0;
		$total_company_deposits = 0;
		$total_company_balance = 0;
		$grand_total_previous_balance = 0;
		$grand_total_charges = 0;
		$grand_total_deposits = 0;
		$grand_total_balance = 0;

		foreach ($clients as $client_index => $client) {
			if ( $client_index == 0 ) {
				$company_short_name = $client->company_short_name;
			}

			if ( $company_short_name !== $client->company_short_name ) {
				$total_company = new stdClass();
				$total_company->company_short_name = '';
				$total_company->client_id = '';
				$total_company->client_code = '';
				$total_company->document_type_name = '';
				$total_company->document_number = '';
				$total_company->business_name = '';
				$total_company->warehouse_document_type_name = 'TOTAL ' . $company_short_name;
				$total_company->referral_serie_number = '';
				$total_company->referral_voucher_number = '';
				$total_company->sale_date = '';
				$total_company->expiry_date = '';
				$total_company->liquidation_created_at = '';
				$total_company->previous_balance = $total_company_previous_balance;
				$total_company->charges = $total_company_charges;
				$total_company->deposits = $total_company_deposits;
				$total_company->balance = $total_company_balance;
				$total_company->exchange_rate = '';
				$total_company->amount_dolares = '';
				$total_company->business_unit_name = '';
				$response[] = $total_company;

				$grand_total_previous_balance += $total_company_previous_balance;
				$grand_total_charges += $total_company_charges;
				$grand_total_deposits += $total_company_deposits;
				$grand_total_balance += $total_company_balance;
				$total_company_previous_balance = 0;
				$total_company_charges = 0;
				$total_company_deposits = 0;
				$total_company_balance = 0;

				$company_short_name = $client->company_short_name;
			}

			$response[] = $client;
			$total_company_previous_balance += $client->previous_balance;
			$total_company_charges += $client->charges;
			$total_company_deposits += $client->deposits;
			$total_company_balance += $client->balance;

			$sales = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
				->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
				->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
				->when($company_id, function ($query, $company_id) {
					return $query->where('sales.company_id', $company_id);
				})
				->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
				->where('sales.sale_date', '<=', $final_date->format('Y-m-d'))
				->where('clients.id', $client->client_id)
				->when($business_unit_id, function ($query, $business_unit_id) {
					return $query->where('clients.business_unit_id', $business_unit_id);
				})
				->select('sales.id', 'companies.short_name as company_short_name', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'warehouse_document_types.name as warehouse_document_type_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_date', 'sales.expiry_date', 'sales.total_perception as charges', 'business_units.name as business_unit_name')
				->orderBy('company_short_name')
				->orderBy('clients.business_name')
				->orderBy('sale_date')
				->orderBy('warehouse_document_type_name')
				->orderBy('referral_serie_number')
				->orderBy('referral_voucher_number')
				->get();

			$sales->map(function($item, $sale_index) {
				$item->liquidation_created_at = '';
				$item->previous_balance = '';
				$item->deposits = '';
				$item->balance = '';
				$item->exchange_rate = '';
				$item->amount_dolares = '';

				return $item;
			});

			foreach ($sales as $sale) {
				$response[] = $sale;

				$liquidations = Liquidation::where('sale_id', $sale->id)
					->select(DB::Raw('DATE_FORMAT(created_at, "%Y-%m-%d") as liquidation_created_at'), 'exchange_rate', DB::Raw('CASE WHEN currency_id = 2 THEN amount * exchange_rate ELSE amount END as deposits'), DB::Raw('CASE WHEN currency_id = 2 THEN amount ELSE null END as amount_dolares'))
					->orderBy('liquidation_created_at')
					->get();

				$liquidations->map(function ($item, $liquidation_index) use ($sale) {
					$item->company_short_name = $sale->company_short_name;
					$item->client_id = $sale->client_id;
					$item->client_code = $sale->client_code;
					$item->document_type_name = $sale->document_type_name;
					$item->document_number = $sale->document_number;
					$item->business_name = $sale->business_name;
					$item->warehouse_document_type_name = $sale->warehouse_document_type_name;
					$item->referral_serie_number = $sale->referral_serie_number;
					$item->referral_voucher_number = $sale->referral_voucher_number;
					$item->sale_date = $sale->sale_date;
					$item->expiry_date = $sale->expiry_date;
					$item->previous_balance = '';
					$item->charges = '';
					$item->balance = '';
					$item->business_unit_name = $sale->business_unit_name;

					return $item;
				});

				foreach ($liquidations as $liquidation) {
					$response[] = $liquidation;
				}
			}

			if ( $client_index == $last_element ) {
				$total_company = new stdClass();
				$total_company->company_short_name = '';
				$total_company->client_id = '';
				$total_company->client_code = '';
				$total_company->document_type_name = '';
				$total_company->document_number = '';
				$total_company->business_name = '';
				$total_company->warehouse_document_type_name = 'TOTAL ' . $company_short_name;
				$total_company->referral_serie_number = '';
				$total_company->referral_voucher_number = '';
				$total_company->sale_date = '';
				$total_company->expiry_date = '';
				$total_company->liquidation_created_at = '';
				$total_company->previous_balance = $total_company_previous_balance;
				$total_company->charges = $total_company_charges;
				$total_company->deposits = $total_company_deposits;
				$total_company->balance = $total_company_balance;
				$total_company->exchange_rate = '';
				$total_company->amount_dolares = '';
				$total_company->business_unit_name = '';
				$response[] = $total_company;

				$grand_total_previous_balance += $total_company_previous_balance;
				$grand_total_charges += $total_company_charges;
				$grand_total_deposits += $total_company_deposits;
				$grand_total_balance += $total_company_balance;
				$total_company_previous_balance = 0;
				$total_company_charges = 0;
				$total_company_deposits = 0;
				$total_company_balance = 0;

				$company_short_name = $client->company_short_name;
			}
		}

		$grand_total = new stdClass();
		$grand_total->company_short_name = '';
		$grand_total->client_id = '';
		$grand_total->client_code = '';
		$grand_total->document_type_name = '';
		$grand_total->document_number = '';
		$grand_total->business_name = '';
		$grand_total->warehouse_document_type_name = 'TOTAL GENERAL';
		$grand_total->referral_serie_number = '';
		$grand_total->referral_voucher_number = '';
		$grand_total->sale_date = '';
		$grand_total->expiry_date = '';
		$grand_total->liquidation_created_at = '';
		$grand_total->previous_balance = $grand_total_previous_balance;
		$grand_total->charges = $grand_total_charges;
		$grand_total->deposits = $grand_total_deposits;
		$grand_total->balance = $grand_total_balance;
		$grand_total->exchange_rate = '';
		$grand_total->amount_dolares = '';
		$grand_total->business_unit_name = '';
		$response[] = $grand_total;

		if ( $export ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', 'REPORTE CUENTA CORRIENTE CLIENTES '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y'));
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
			$sheet->setCellValue('H3', 'Tipo Doc.');
			$sheet->setCellValue('I3', 'Nº Serie');
			$sheet->setCellValue('J3', 'Nº Doc.');
			$sheet->setCellValue('K3', 'Fecha emisión');
			$sheet->setCellValue('L3', 'Fecha vencimiento');
			$sheet->setCellValue('M3', 'Fecha cobranza');
			$sheet->setCellValue('N3', 'Saldo anterior');
			$sheet->setCellValue('O3', 'Cargos');
			$sheet->setCellValue('P3', 'Abonos');
			$sheet->setCellValue('Q3', 'Saldo');
			$sheet->setCellValue('R3', 'Tipo cambio');
			$sheet->setCellValue('S3', 'Monto US$');
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
				$sheet->setCellValue('C'.$row_number, $element->client_id);
				$sheet->setCellValue('D'.$row_number, $element->client_code);
				$sheet->setCellValue('E'.$row_number, $element->document_type_name);
				$sheet->setCellValue('F'.$row_number, $element->document_number);
				$sheet->setCellValue('G'.$row_number, $element->business_name);
				$sheet->setCellValue('H'.$row_number, $element->warehouse_document_type_name);
				$sheet->setCellValue('I'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('J'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('K'.$row_number, $element->sale_date);
				$sheet->setCellValue('L'.$row_number, $element->expiry_date);
				$sheet->setCellValue('M'.$row_number, $element->liquidation_created_at);
				$sheet->setCellValue('N'.$row_number, $element->previous_balance);
				$sheet->setCellValue('O'.$row_number, $element->charges);
				$sheet->setCellValue('P'.$row_number, $element->deposits);
				$sheet->setCellValue('Q'.$row_number, $element->balance);
				$sheet->setCellValue('R'.$row_number, $element->exchange_rate);
				$sheet->setCellValue('S'.$row_number, $element->amount_dolares);
				$sheet->setCellValue('T'.$row_number, $element->business_unit_name);

				$sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('Q'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('R'.$row_number)->getNumberFormat()->setFormatCode('0.0000');
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
