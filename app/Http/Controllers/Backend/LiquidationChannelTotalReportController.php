<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SaleDetail;
use App\Sale;
use App\BusinessUnit;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class LiquidationChannelTotalReportController extends Controller
{
    public function index() {
		$business_units = BusinessUnit::select('id', 'name')->get();
		$current_date =CarbonImmutable::now()->toAtomString();
		return view('backend.liquidations_channel_total_report')->with(compact('business_units', 'current_date'));
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
		$business_unit_id = request('business_unit_id');
		$q = request('q');

		$clients = Client::select('id', 'business_name as text')
			->when($business_unit_id, function($query, $business_unit_id) {
				return $query->where('business_unit_id', $business_unit_id);
			})
			->where('business_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

	public function list() {

		$export = request('export');

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'));
		$final_date = CarbonImmutable::createFromDate(request('model.final_date'));
		$business_unit_id = request('model.business_unit_id');
		$client_id = request('model.client_id');
		
					$elements = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')    
                        ->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')                     
                        ->leftjoin('companies', 'sales.company_id', '=', 'companies.id')		
						->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
		                ->leftjoin('client_channels', 'clients.channel_id', '=', 'client_channels.id')
			            ->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			            ->leftjoin('client_sectors', 'clients.sector_id', '=', 'client_sectors.id')
			            ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			            ->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			            ->leftjoin('warehouse_movements', 'sales.warehouse_movement_id', '=', 'warehouse_movements.id')
			            ->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
			            ->where('sales.sale_date', '>=', $initial_date)
			            ->where('sales.sale_date', '<=', $final_date)
						->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
						->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072, 14258])
			->select('companies.short_name as company_short_name', 'business_units.name as business_unit_name', 'client_channels.name as client_channel_name',DB::Raw('(SELECT SUM(sale_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = sale_details.article_id ))/1000 ) AS sum_total'),DB::Raw('(SELECT SUM(sale_details.total)) AS total'))

			->when($business_unit_id, function($query, $business_unit_id) {
				return $query->where('clients.business_unit_id', $business_unit_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('sales.client_id', $client_id);
			})
			->groupBy('client_channels.id')
			->orderBy('business_unit_id')
			
			->get();
			$response=[];
			$totals_total = 0;
		    $totals_sum_total = 0;


			foreach ($elements as $saledetail) {

				$currency = Currency::where('id', 1)
                ->select('symbol')
                ->first();

				$totals_total += $saledetail['total'];
			    $totals_sum_total += $saledetail['sum_total'];


         
				$saledetail->company_short_name = $saledetail['company_short_name'];
				$saledetail->business_unit_name = $saledetail['business_unit_name'];
				$saledetail->client_channel_name = $saledetail['client_channel_name'];
				$saledetail->sum_total = $saledetail['sum_total'];
				$saledetail->symbol = $currency->symbol;
				$saledetail->total = $saledetail['total'];
				
				$response[] = $saledetail;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->sale_date = '';
		$totals->business_unit_name = '';
		$totals->client_channel_name = '';
		$totals->symbol = 's./';
		$totals->sum_total = $totals_sum_total;
		$totals->total = $totals_total;
		

		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:U1');
			$sheet->setCellValue('A1', 'REPORTE DE VENTA CANALES DEL '.$initial_date->format('d/m/Y').' AL '.$final_date->format('d/m/Y').'  '.'CONSULTADO EL'.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Unidad de Negocio');
            $sheet->setCellValue('D3', 'Canal'); 
            $sheet->setCellValue('E3', 'TM');
			$sheet->setCellValue('F3', 'Venta Soles');

			$sheet->getStyle('A3:F3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->business_unit_name);
                $sheet->setCellValue('D'.$row_number, $element->client_channel_name);
                $sheet->setCellValue('E'.$row_number, $element->sum_total);
				$sheet->setCellValue('F'.$row_number, $element->total);
				
                $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('s./ 0.00');
				$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('s./ 0.00');			

		
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
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








