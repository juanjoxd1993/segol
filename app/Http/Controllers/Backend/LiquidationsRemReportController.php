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


class LiquidationsRemReportController extends Controller
{
  public function index() {

		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.liquidations_rem_report')->with(compact('companies', 'current_date'));
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
		$business_unit_id = request('model.business_unit_id');
		$client_id = request('model.client_id');
		
		$elements = liquidations::leftjoin('sales', 'liquidations.sales_id', '=', 'sales.id')
									->where('sales.sale_date', '>=', $initial_date)
									->where('sales.sale_date', '<=', $final_date)
									->select('liquidations.id','liquidations.state', 'sales.sale_date as sale_date', DB::Raw('MIN(liquidations.operation_number) as initial_voucher'), DB::Raw('MAX(liquidations.operation_number) as final_voucher'), DB::Raw('SUM(liquidations.amount) as sum_total'))
									->where('liquidations.state',0)
                                    ->groupBy('sale_date')
									->get();

		$response=[];

		foreach ($elements as $liquidation) {
			$liquidation->sale_date = $liquidation['sale_date'];
			$liquidation->initial_voucher = $liquidation['initial_voucher'];
			$liquidation->final_voucher = $liquidation['final_voucher'];
			$liquidation->sum_total = $liquidation['sum_total'];
            $liquidation->state = $liquidation['state'];
			

			$response[] = $liquidation;
		}

		
		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:G1');
			$sheet->setCellValue('A1', 'REPORTE DE REMESAS DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

			$sheet->setCellValue('A3', 'Fecha de Cierre');
			$sheet->setCellValue('B3', 'N° de Recibo Inicial');
			$sheet->setCellValue('D3', 'N° de Recibo Final');
			$sheet->setCellValue('E3', 'Recaudación'); 
			$sheet->setCellValue('F3', 'Estado');
			
			$sheet->getStyle('A3:F3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;

			foreach ($response as $index => $element) {

                $state = "";

				switch ($element->state) {
					case 0:
						$state = "Por Remesar";
						break;
					case 1:
						$state = "Remesado";
						break;
					
				}

				$index++;
				$sheet->setCellValue('A'.$row_number, $element->sale_date);
				$sheet->setCellValue('C'.$row_number, $element->initial_voucher);
				$sheet->setCellValue('D'.$row_number, $element->final_voucher);
				$sheet->setCellValue('E'.$row_number, $element->sum_total);
				$sheet->setCellValue('F'.$row_number, $element->state);	
				$sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				
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

	public function validateModal() {
		$messages = [
			'operation_number.required' => 'Debe ingresar el nro de Operación.',
			'detalle.required' => 'Debe Ingresar los números de recibo Inicial',
            'final.required' => 'Debe Ingresar los números de recibo Final',
			'amount.required' => 'Debe ingresar un monto valido.',
			'date.required'   => 'Debe ingresar una fecha valida.',
		];

		$rules = [
			'amount' => 'required',
			'date'   => 'required',
			'detalle' => 'required',
            'final' => 'required',
			'operation_number' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}



	public function updateVoucher() {
	$this->validateModal();

	$user_id = Auth::user()->id;
    $amount = request('amount');
    $date = request('date');
	$detalle = request('detalle');
    $final = request('final');
	$operation_number = request('operation_number');

    $remesa = new Remesa();
    $remesa->amount = $amount;
    $remesa->date = $date;
    $remesa->user_id = $user_id;
	$remesa->detalle = $detalle;
    $remesa->final = $final;
	$remesa->operation_number = $operation_number;
    $remesa->save();

    return response()->json($remesa, 200);

	}


}