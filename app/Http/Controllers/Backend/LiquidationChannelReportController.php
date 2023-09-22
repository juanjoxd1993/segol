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
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpParser\Node\Stmt\Else_;
use stdClass;


class LiquidationChannelReportController extends Controller
{
    public function index() {
		$business_units = BusinessUnit::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.liquidations_channel_report')->with(compact('business_units', 'current_date'));
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

		// $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d');
		$initial_date = CarbonImmutable::createFromDate('23-09-2023')->startOfDay()->format('Y-m-d');
		// $final_date = CarbonImmutable::createFromDate(request('model.final_date'))->endOfDay()->format('Y-m-d');
		$final_date = CarbonImmutable::createFromDate('23-09-2023')->endOfDay()->format('Y-m-d');
		$business_unit_id = request('model.business_unit_id');
		$client_id = request('model.client_id');
		
		$elements = SaleDetail::leftjoin('sales', 'sale_details.sale_id', '=', 'sales.id')
					    ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')    
                        ->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
						->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')                                          
                        ->leftjoin('companies', 'sales.company_id', '=', 'companies.id')		
						->leftjoin('articles', 'sale_details.article_id', '=', 'articles.id')
		                ->leftjoin('client_channels', 'clients.channel_id', '=', 'client_channels.id')
			            ->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			            ->leftjoin('client_sectors', 'clients.sector_id', '=', 'client_sectors.id')
			            ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
						->leftjoin('managers', 'clients.manager_id', '=', 'managers.id')
						->leftjoin('client_addresses', function($join) {
							$join->on('clients.id', '=', 'client_addresses.client_id')
								->where('client_addresses.address_type_id', 1);})
						->leftjoin('ubigeos', 'client_addresses.ubigeo_id', '=', 'ubigeos.id')
			            ->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			            ->leftjoin('warehouse_movements', 'sales.warehouse_movement_id', '=', 'warehouse_movements.id')
			            ->leftjoin('movent_types', 'warehouse_movements.movement_type_id', '=', 'movent_types.id')
			            ->where('sales.sale_date', '>=', $initial_date)
			            ->where('sales.sale_date', '<=', $final_date)
						->whereNotIn('sales.warehouse_document_type_id', [2,3,8,9,10,11,14,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30])
						->whereNotIn('sales.if_bol', [1])
						->select('sale_details.id', 
			'companies.short_name as company_short_name',
			'sale_date',
			'business_units.name as business_unit_name', 
			'client_channels.name as client_channel_name', 
			'client_zones.name as client_zone_name',
			'client_sectors.name as client_sector_name', 
			'client_routes.short_name as client_route_id',
			'warehouse_document_types.short_name as warehouse_document_type_short_name', 
			'sales.referral_serie_number', 
			'sales.guide_series', 
			'sales.referral_voucher_number',
			'sales.expiry_date as expiry_date',
			'sales.balance as balance',
			'articles.name as article_name',
			'sale_details.quantity',
			DB::Raw('(SELECT SUM(sale_details.quantity * (SELECT articles.convertion FROM articles WHERE articles.id = sale_details.article_id ))/1000 ) AS sum_total'),
			'sale_details.referential_convertion',
			'sale_details.price_igv as price',
			'sale_details.total', 
			'clients.id as client_id',
			'clients.code as client_code', 
			'clients.business_name as client_business_name', 
			'clients.document_number as document_number', 
			'clients.int_name as int_name',
			'clients.credit_limit_days as credit_limit_days',
			'document_types.name as client_document_name', 
			'warehouse_movements.movement_number as warehouse_movement_movement_number', 
			'movent_types.name as movement_type_name', 
			DB::Raw('CONCAT(sales.guide_series, "-", sales.guide_number) as guide'),'sales.license_plate as plate','ubigeos.district as district', 'ubigeos.province as province', 'ubigeos.department as department',
			'clients.seller_id as seller_id',
			'managers.name as manager',
			'clients.grupo as grupo',
			'clients.estado as estado')

			->when($business_unit_id, function($query, $business_unit_id) {
				return $query->where('clients.business_unit_id', $business_unit_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('sales.client_id', $client_id);
			})
			->groupBy('sale_details.id')
			->orderBy('clients.business_unit_id')
			->orderBy('sale_date')
			->orderBy('business_unit_name')
			->orderBy('warehouse_document_type_short_name')
			->orderBy('sales.referral_serie_number')
			->orderBy('sales.referral_voucher_number')
			->get();
			$response=[];

			foreach ($elements as $saledetail) {

				$saledetail->company_short_name = $saledetail['company_short_name'];
				$saledetail->sale_date = $saledetail ['sale_date'];
				$saledetail->business_unit_name = $saledetail['business_unit_name'];
				$saledetail->client_channel_name = $saledetail['client_channel_name'];
				$saledetail->client_zone_name =$saledetail ['client_zone_name'];
				$saledetail->client_sector_name = $saledetail['client_sector_name'];
				$saledetail->client_route_id = $saledetail['client_route_id'];

				$saledetail->warehouse_document_type_short_name = $saledetail['warehouse_document_type_short_name'];
				$saledetail->guide_series = $saledetail['guide_series'];
				// if ($saledetail->warehouse_document_type_short_name == 'FE')
				// {
				// $saledetail->referral_serie_number = 'F'.$saledetail['referral_serie_number'];
				// }
				// elseif($saledetail->warehouse_document_type_short_name == 'BE'){
				// $saledetail->referral_serie_number = 'B'.$saledetail['referral_serie_number'];
				// }
				// else{
				// 	$saledetail->referral_serie_number = $saledetail['referral_serie_number'];
				// }
				$saledetail->referral_serie_number = $saledetail['referral_serie_number'];

				$saledetail->referral_voucher_number = $saledetail['referral_voucher_number'];

				$saledetail->article_name =$saledetail ['article_name'];
				$saledetail->sum_total = $saledetail['sum_total'];
				$saledetail->price = $saledetail['price'];
				$saledetail->total = $saledetail['total'];
				$saledetail->client_id = $saledetail['client_id'];
				$saledetail->client_code = $saledetail['client_code'];
				$saledetail->client_business_name =$saledetail ['client_business_name'];
				$saledetail->warehouse_movement_movement_number = $saledetail['warehouse_movement_movement_number'];
				$saledetail->movement_type_name = $saledetail['movement_type_name'];
				$saledetail->guide = $saledetail['guide'];
				$saledetail->plate = $saledetail['plate'];
				$saledetail->district = $saledetail['district'];
				$saledetail->province = $saledetail['province'];
				$saledetail->department = $saledetail['department'];
				$saledetail->seller_id = $saledetail['seller_id'];
				$saledetail->manager = $saledetail['manager'];
				$saledetail->grupo = $saledetail['grupo'];
				$saledetail->estado = $saledetail['estado'];
				$saledetail->client_document_name = $saledetail['client_document_name'];
				$saledetail->document_number = $saledetail['document_number'];
				$saledetail->int_name = $saledetail['int_name'];
				$saledetail->credit_limit_days = $saledetail['credit_limit_days'];
				$saledetail->expiry_date = $saledetail['expiry_date'];
				$saledetail->sale_value = $saledetail->total/1.18;
				$saledetail->quantity = $saledetail['quantity'];
				$saledetail->igv =  $saledetail->total-$saledetail->sale_value;
				$saledetail->referential_convertion = $saledetail['referential_convertion'];
				$saledetail->kgs = $saledetail->sum_total*1000;
				if ($saledetail->business_unit_name == 'Granel' || $saledetail->business_unit_name == 'Grifo')
				{
					$saledetail->glns = $saledetail->kgs/2.018;
				}
				else{
					$saledetail->glns = '';
				}

				$saledetail->balance = $saledetail['balance'];

				if ($saledetail->balance > 0)
				{
					$saledetail->condition = 'Credito'.' '. $saledetail->credit_limit_days.' '.'días';
				}
				else{
					$saledetail->condition = 'Contado';
				}
				
				$response[] = $saledetail;

			}

		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->sale_date = '';
		$totals->business_unit_name = '';
		$totals->client_channel_name = '';
		$totals->client_zone_name = '';
		$totals->client_sector_name = '';
		$totals->client_route_id = '';
		$totals->warehouse_document_type_short_name = '';
		$totals->guide_series = '';
		$totals->referral_serie_number = '';
		$totals->referral_voucher_number = '';
		$totals->article_name = '';
		$totals->sum_total = '';
		$totals->price = '';
		$totals->total = '';
		$totals->client_id = '';
		$totals->client_code = '';
		$totals->client_business_name = '';
		$totals->warehouse_movement_movement_number = '';
		$totals->movement_type_name = '';
		$totals->guide = '';
		$totals->plate = '';
		$totals->district = '';
		$totals->province = '';
		$totals->department = '';
		$totals->seller_id = '';
		$totals->manager = '';
		$totals->grupo = '';
		$totals->estado = '';
		$totals->client_document_name = '';
		$totals->document_number = '';
		$totals->int_name = '';
		$totals->expiry_date = '';
		$totals->sale_value = '';
		$totals->igv = '';
		$totals->quantity = '';
		$totals->referential_convertion = '';
		$totals->kgs = '';
		$totals->glns = '';
		$totals->condition = '';

		$response[] = $totals;

		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AK1');
			$sheet->setCellValue('A1', 'REPORTE DE VENTAS '.' '.'DEL'.' '.$initial_date.' AL '.$final_date.'  '.'DESCARGADO EL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Chofer Vendedor');
			$sheet->setCellValue('D3', 'Supervisor');
			$sheet->setCellValue('E3', 'Ruta');
			$sheet->setCellValue('F3', 'Unidad de Negocio');
			$sheet->setCellValue('G3', 'Canal'); 
			$sheet->setCellValue('H3', 'Sector');
			$sheet->setCellValue('I3', 'Guía');
			$sheet->setCellValue('J3', 'Tipo Doc.');
			$sheet->setCellValue('K3', 'N° Serie');
			$sheet->setCellValue('L3', 'N° Documento');
			$sheet->setCellValue('M3', 'Fecha de Despacho');
			$sheet->setCellValue('N3', 'Fecha de Emisión');
			$sheet->setCellValue('O3', 'ID Cliente');
			$sheet->setCellValue('P3', 'Tipo de Doc. Id.');
			$sheet->setCellValue('Q3', 'N° Doc.');
			$sheet->setCellValue('R3', 'Razón Social');
			$sheet->setCellValue('S3', 'Punto de Venta');
			$sheet->setCellValue('T3', 'Condición de Pago');
			$sheet->setCellValue('U3', 'Fecha de Vencimiento');
			$sheet->setCellValue('V3', 'N° SCOP');
			$sheet->setCellValue('W3', 'Zona');
			$sheet->setCellValue('X3', 'Distrito');
			$sheet->setCellValue('Y3', 'Provincia');
			$sheet->setCellValue('Z3', 'Departamento');
			$sheet->setCellValue('AA3', 'Placa Tracto');
			$sheet->setCellValue('AB3', 'Placa Cisterna');
			$sheet->setCellValue('AC3', 'Articulo');
			$sheet->setCellValue('AD3', 'TM');
			$sheet->setCellValue('AE3', 'Cantidad Facturada');
			$sheet->setCellValue('AF3', 'Cantidad KG.');
			$sheet->setCellValue('AG3', 'Cantidad GL.');
			$sheet->setCellValue('AH3', 'Precio');
			$sheet->setCellValue('AI3', 'SubTotal');
			$sheet->setCellValue('AJ3', 'IGV');
			$sheet->setCellValue('AK3', 'Total');

			$sheet->getStyle('A3:AK3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				
				$saleDateYear = null;
			//	$saleDateMonth = null;
			//	$saleDateDay = null;

				if ($element->sale_date) {
					$saleDateObject = date('d/m/Y',strtotime($element->sale_date) );
					$saleDateYear = $saleDateObject;
			//		$saleDateMonth = str_pad($saleDateObject->month, 2, '0', STR_PAD_LEFT);
			//		$saleDateDay = str_pad($saleDateObject->day, 2, '0', STR_PAD_LEFT);
				}

				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->seller_id);
				$sheet->setCellValue('D'.$row_number, $element->manager);	
				$sheet->setCellValue('E'.$row_number, $element->client_route_id);
				$sheet->setCellValue('F'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('G'.$row_number, $element->client_channel_name);
				$sheet->setCellValue('H'.$row_number, $element->client_sector_name);
				$sheet->setCellValue('I'.$row_number, $element->guide);

				$sheet->setCellValue('J'.$row_number, $element->warehouse_document_type_short_name);
				// $sheet->setCellValue('K'.$row_number, $element->guide_series);
				$sheet->setCellValue('K'.$row_number, $element->referral_serie_number);
				// $sheet->setCellValue('L'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('L'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('M'.$row_number, $saleDateYear);
				$sheet->setCellValue('N'.$row_number, $saleDateYear);
				$sheet->setCellValue('O'.$row_number, $element->client_id);
				$sheet->setCellValue('P'.$row_number, $element->client_document_name);
				$sheet->setCellValue('Q'.$row_number, $element->document_number);
				$sheet->setCellValue('R'.$row_number, $element->client_business_name);
				$sheet->setCellValue('S'.$row_number, $element->int_name);	
				$sheet->setCellValue('T'.$row_number, $element->condition);
				$sheet->setCellValue('U'.$row_number, $element->expiry_date);

				$sheet->setCellValue('W'.$row_number, $element->client_zone_name);
				$sheet->setCellValue('X'.$row_number, $element->district);
				$sheet->setCellValue('Y'.$row_number, $element->province);
				$sheet->setCellValue('Z'.$row_number, $element->department);	
				$sheet->setCellValue('AA'.$row_number, $element->plate);

				$sheet->setCellValue('AC'.$row_number, $element->article_name);
				$sheet->setCellValue('AD'.$row_number, $element->sum_total);
				$sheet->setCellValue('AE'.$row_number, $element->quantity);
				// $sheet->setCellValue('AF'.$row_number, $element->kgs);
				$sheet->setCellValue('AF'.$row_number, (floatval($element->quantity) * floatval($element->referential_convertion)));
				$sheet->setCellValue('AG'.$row_number, $element->glns);
				$sheet->setCellValue('AH'.$row_number, $element->price);
				$sheet->setCellValue('AI'.$row_number, $element->sale_value); 
				$sheet->setCellValue('AJ'.$row_number, $element->igv); 
				$sheet->setCellValue('AK'.$row_number, $element->total);

				// $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('P'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

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

			$writer = new Xls($spreadsheet);
			// echo json_encode($response);
			return $writer->save('php://output');
		} 
		else {
			return response()->json($response);
		}
		
	}
}
	








