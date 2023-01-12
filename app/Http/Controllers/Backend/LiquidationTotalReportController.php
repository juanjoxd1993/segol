<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Exports\LiquidationReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Liquidation;
use App\Sale;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;

class LiquidationTotalReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.liquidations_total_report')->with(compact('companies', 'current_date'));
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

		DB::enableQueryLog();

		$query = DB::table('liquidations')
		  ->leftJoin('sales', 'liquidations.sale_id', '=', 'sales.id')
		  ->leftjoin('companies', 'sales.company_id', '=', 'companies.id')
		  ->leftJoin('payments', 'sales.payment_id', '=', 'payments.id')
		  ->leftjoin('payment_methods', 'liquidations.payment_method_id', '=', 'payment_methods.id')
		  ->select(DB::raw('companies.id as company_id, companies.name as company, payments.id as payment_id, IFNULL(payments.name, "OTRO") as payment, payment_methods.id as payment_method_id, payment_methods.name as payment_method, SUM(liquidations.amount) as amount'))
		  ->where('sales.created_at', '>=', $initial_date)
		  ->where('sales.created_at', '<=', $final_date);


		if ($company_id) {
			$query = $query->where('companies.id', $company_id);
		}

		$query = $query->groupBy('sales.company_id', 'sales.payment_id', 'payment_methods.id');
		$groupedByCompany = $query->get()->groupBy('company_id');

		$response = [];

		foreach ($groupedByCompany as $company) {
			$el = new stdClass();

			$el->company = $company[0]->company;
			$el->credit = $company->sum(function($item) {
				return $item->payment_id == 2 ? $item->amount : 0;
			});

			$el->cash = $company->sum(function($item) {
				return $item->payment_id == 1 ? $item->amount : 0;
			});

			$el->deposit_transfer = $company->sum(function($item) {
				return in_array($item->payment_method_id, [2, 3]) ? $item->amount : 0;
			});

			$el->total = $company->sum(function($item) {
				return $item->amount;
			});

			$response[] = $el;
		}

		if ( $export ) {
			$initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('d/m/Y');
			$final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('d/m/Y');

			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:F1');
			$sheet->setCellValue('A1', 'REPORTE DE LIQUIDACIONES DEL '.$initial_date.' AL '.$final_date.'  '.'CONSULTADO '.CarbonImmutable::now()->format('d/m/Y H:m:s') );
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
			$sheet->setCellValue('C3', 'Crédito');
			$sheet->setCellValue('D3', 'Efectivo');
			$sheet->setCellValue('E3', 'Depósito/Transferencia');
			$sheet->setCellValue('F3', 'Total');
			$sheet->getStyle('A3:F3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company);
				$sheet->setCellValue('C'.$row_number, $element->credit);
				$sheet->setCellValue('D'.$row_number, $element->cash);
				$sheet->setCellValue('E'.$row_number, $element->deposit_transfer);
				$sheet->setCellValue('F'.$row_number, $element->total);

				$row_number++;
			}

			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} else {
			return response()->json($response);
		}
	}
}
