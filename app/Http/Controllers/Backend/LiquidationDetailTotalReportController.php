<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SaleDetail;
use App\Sale;
use App\BusinessUnit;
use App\Liquidation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class LiquidationDetailTotalReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date =CarbonImmutable::now()->toAtomString();
		return view('backend.liquidations_detail_total_report')->with(compact('companies', 'current_date'));
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
		
					$elements = Sale::leftjoin('sale_details', 'sales.id', '=', 'sale_details.sale_id')
					    ->leftjoin('liquidations', 'sales.id', '=', 'liquidations.sale_id')    
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
			            ->where('sales.created_at', '>=', $initial_date)
			            ->where('sales.created_at', '<=', $final_date)
						->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29])
						->whereNotIn('sales.client_id', [1031, 427, 13326, 13775,14258,14072])
			->select('companies.short_name as company_short_name' ,'sales.sale_date',DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") as liquidation_date'),'clients.business_unit_id as business_unit_id','clients.channel_id as channel_id',
			'business_units.name as business_unit_name', 'client_channels.name as client_channel_name'
			/*DB::Raw('(SELECT SUM(sale_details.kg)) AS sum_total')*/)

			->when($company_id, function($query, $company_id) {
				return $query->where('clients.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('sales.client_id', $client_id);
			})


		//	->groupBy('business_unit_id')
            ->groupBy('channel_id')
            ->groupBy('liquidation_date')
			->groupBy('sales.sale_date')


            ->orderBy('liquidation_date')
            
            
			
			->get();
			$response=[];
			$totals_total = 0;
		    $totals_sum_total = 0;
            $totals_deposit = 0;
            $totals_efective = 0;
            $totals_pre_balance = 0;
		//	$totals_cancelacion = 0;


			foreach ($elements as $saledetail) {

                $saledetail->business_unit_name = $saledetail['business_unit_name'];
				$saledetail->company_short_name = $saledetail['company_short_name'];
				$saledetail->liquidation_date = $saledetail['liquidation_date'];	
                $saledetail->sale_date = $saledetail['sale_date'];			
				$saledetail->client_channel_name = $saledetail['client_channel_name'];
				$saledetail->channel_id = $saledetail['channel_id'];
				$saledetail->business_unit_id = $saledetail['business_unit_id'];
                
				$sum_total = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d")'), '=', $saledetail['liquidation_date'])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])
				->where('sales.sale_date', '=', $saledetail['sale_date'])
				->select('sale_details.kg')
				->sum('sale_details.kg');

				$sum_soles = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])				
			    ->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $saledetail['liquidation_date'])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])
				->where('sales.sale_date', '=', $saledetail['sale_date'])
				->select('sale_details.total')
				->sum('sale_details.total_perception');

				$sum_deposits = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
			    ->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $saledetail['liquidation_date'])
				->where('sales.sale_date', '=', $saledetail['sale_date'])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])
				->select('sales.deposit')
				->sum('sales.deposit');

				$sum_efective = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
			    ->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $saledetail['liquidation_date'])
				->where('sales.sale_date', '=', $saledetail['sale_date'])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])
				->select('sales.efective')
				->sum('sales.efective');

				$sum_pre_balance = Sale::leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->whereIn('sales.warehouse_document_type_id',[13,5,7,4])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				->where('sales.sale_date', '=', $saledetail['sale_date'])
			    ->where(DB::Raw('DATE_FORMAT(sales.created_at, "%Y-%m-%d") '), '=', $saledetail['liquidation_date'])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])
				->select('sales.pre_balance')
				->sum('sales.pre_balance');

			/*	$sum_canc =Liquidation::leftjoin('sales','liquidations.sale_id','=','sales.id')
				->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
				->where(DB::Raw('DATE_FORMAT(liquidations.created_at, "%Y-%m-%d") '), '=', $saledetail['liquidation_date'])
			//	->where('sales.sale_date', '=', $saledetail['sale_date'])
				->whereNotIn('sales.client_id', [1031, 427, 13326, 13775, 14072,14258])
				->where('clients.business_unit_id', '=', $saledetail['business_unit_id'])
				->where('clients.channel_id', '=', $saledetail['channel_id'])			 
				->where('liquidations.collection', 1 )
				->select('liquidations.amount')
				->sum('liquidations.amount');*/





			//	$saledetail->sum_total = $saledetail['sum_total']/1000;
				$saledetail->sum_total = ($sum_total/1000);
				$saledetail->deposit = $sum_deposits;
                $saledetail->efective = $sum_efective;
                $saledetail->pre_balance = $sum_pre_balance;			    
				$saledetail->total = $sum_soles;
			//	$saledetail->cancelacion = $sum_canc;
            //    $saledetail->cancelacion =  $saledetail['sum_canc'];


				$totals_total += $saledetail['total'];
			    $totals_sum_total += $saledetail['sum_total'];
                $totals_deposit += $saledetail['deposit'];
			    $totals_efective += $saledetail['efective'];
                $totals_pre_balance += $saledetail['pre_balance'];
			//	$totals_cancelacion += $saledetail['cancelacion'];


				$response[] = $saledetail;

			}


		$totals = new stdClass();
		$totals->business_unit_name = 'TOTAL';
        $totals->company_short_name = '';
		$totals->sale_date = '';
		$totals->liquidation_date = '';
		$totals->client_channel_name = '';
        $totals->sum_total = $totals_sum_total;
        $totals->total = $totals_total;
        $totals->deposit= $totals_deposit;
        $totals->efective = $totals_efective;
        $totals->pre_balance = $totals_pre_balance;
	//	$totals->cancelacion = $totals_cancelacion;
		
		
		

		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:L1');
			$sheet->setCellValue('A1', 'REPORTE DE LIQUIDACIONES DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Compañía');			
            $sheet->setCellValue('D3', 'Fecha de Liquidación');
			$sheet->setCellValue('E3', 'Fecha de Emisión');
            $sheet->setCellValue('F3', 'Canal de Venta'); 
            $sheet->setCellValue('G3', 'TM');
			$sheet->setCellValue('H3', 'Total');
            $sheet->setCellValue('I3', 'Depositos');
            $sheet->setCellValue('J3', 'Efectivo');
            $sheet->setCellValue('K3', 'Credito');
	//		$sheet->setCellValue('L3', 'Cancelación');


			$sheet->getStyle('A3:L3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_unit_name); 
				$sheet->setCellValue('C'.$row_number, $element->company_short_name);
				$sheet->setCellValue('D'.$row_number, $element->liquidation_date);
                $sheet->setCellValue('E'.$row_number, $element->sale_date);
                $sheet->setCellValue('F'.$row_number, $element->client_channel_name);
                $sheet->setCellValue('G'.$row_number, $element->sum_total);
				$sheet->setCellValue('H'.$row_number, $element->total);
                $sheet->setCellValue('I'.$row_number, $element->deposit);
                $sheet->setCellValue('J'.$row_number, $element->efective);
                $sheet->setCellValue('K'.$row_number, $element->pre_balance);
	//			$sheet->setCellValue('L'.$row_number, $element->cancelacion);

				
                $sheet->getStyle('H'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('I'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');
	//			$sheet->getStyle('L'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
	//		$sheet->getColumnDimension('L')->setAutoSize(true);


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








