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


class FacturationBoletasReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.facturation_boletas_report')->with(compact('companies', 'current_date'));
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
		
					$elements = VoucherDetail::leftjoin('vouchers', 'voucher_details.voucher_id', '=', 'vouchers.id')
                        ->leftjoin('companies', 'vouchers.company_id', '=', 'companies.id')
						->leftjoin('clients', 'vouchers.client_id', '=', 'clients.id')
						->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
					    ->leftjoin('client_channels', 'clients.channel_id', '=', 'client_channels.id')
			            ->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			            ->leftjoin('client_sectors', 'clients.sector_id', '=', 'client_sectors.id')
			            ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
						->leftjoin('client_addresses', function($join) {
							$join->on('clients.id', '=', 'client_addresses.client_id')
								->where('client_addresses.address_type_id', 1);})
						->leftjoin('ubigeos', 'client_addresses.ubigeo_id', '=', 'ubigeos.id')
			            ->where('vouchers.issue_date', '>=', $initial_date)
			            ->where('vouchers.issue_date', '<=', $final_date)
			->select('voucher_details.id', 'companies.short_name as company_short_name', 'issue_date', 'business_units.name as business_unit_name', 'client_channels.name as client_channel_name', 'client_zones.name as client_zone_name', 'client_sectors.name as client_sector_name',
			 DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'),'vouchers.serie_number', 'vouchers.voucher_number','voucher_details.name as name',
			 'quantity as sum_total','voucher_details.original_price as price','voucher_details.total', 'clients.bol_number as client_code', 
			 'clients.bol_name as client_business_name', DB::Raw('CONCAT(vouchers.referral_guide_series, "-", vouchers.referral_guide_number) as guide'),
			 'ubigeos.district as district', 'ubigeos.province as province', 'ubigeos.department as department', 'vouchers.ose as state')
            ->where('vouchers.voucher_type_id',2)
			->when($company_id, function($query, $company_id) {
				return $query->where('vouchers.company_id', $company_id);
			})
			->when($client_id, function($query, $client_id) {
				return $query->where('vouchers.client_id', $client_id);
			})
			->groupBy('voucher_details.id')
			->orderBy('vouchers.company_id')
			->orderBy('issue_date')
			->orderBy('business_unit_name')
			->orderBy('vouchers.serie_number')
			->orderBy('vouchers.voucher_number')
			->get();
			$response=[];


			foreach ($elements as $voucherdetail) {


         
				$voucherdetail->company_short_name = $voucherdetail['company_short_name'];
				$voucherdetail->issue_date = $voucherdetail['issue_date'];
				$voucherdetail->business_unit_name = $voucherdetail['business_unit_name'];
				$voucherdetail->client_channel_name = $voucherdetail['client_channel_name'];
				$voucherdetail->client_zone_name =$voucherdetail ['client_zone_name'];
				$voucherdetail->client_sector_name = $voucherdetail['client_sector_name'];
				$voucherdetail->client_route_id = $voucherdetail['client_route_id'];
				$voucherdetail->serie_number = $voucherdetail['serie_number'];
				$voucherdetail->voucher_number = $voucherdetail['voucher_number'];
				$voucherdetail->state = $voucherdetail['state'];
				$voucherdetail->name =$voucherdetail ['name'];
				$voucherdetail->sum_total = $voucherdetail['sum_total'];
				$voucherdetail->price = $voucherdetail['price'];
				$voucherdetail->total = $voucherdetail['total'];
				$voucherdetail->client_code = $voucherdetail['client_code'];
				$voucherdetail->client_business_name =$voucherdetail ['client_business_name'];
				$voucherdetail->guide = $voucherdetail['guide'];
				$voucherdetail->district = $voucherdetail['district'];
				$voucherdetail->province = $voucherdetail['province'];
				$voucherdetail->department = $voucherdetail['department'];

				$response[] = $voucherdetail;

			}


		$totals = new stdClass();
		$totals->company_short_name = 'TOTAL';
		$totals->issue_date = '';
		$totals->business_unit_name = '';
		$totals->client_channel_name = '';
		$totals->client_zone_name = '';
		$totals->client_sector_name = '';
		$totals->client_route_id = '';
		$totals->serie_number = '';
		$totals->voucher_number = '';
		$totals->state = '';
		$totals->name = '';
		$totals->sum_total = '';
		$totals->price = '';
		$totals->total = '';
		$totals->client_code = '';
		$totals->client_business_name = '';
		$totals->guide = '';
		$totals->district = '';
		$totals->province = '';
		$totals->department = '';

		$response[] = $totals;






		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:T1');
			$sheet->setCellValue('A1', 'REPORTE DE FACTURACIONES DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('C3', 'Fecha de Emisión');
			$sheet->setCellValue('D3', 'Unidad de Negocio');
   //         $sheet->setCellValue('E3', 'Canal'); 
   //         $sheet->setCellValue('F3', 'Zona');
   //         $sheet->setCellValue('G3', 'Sector');
   //         $sheet->setCellValue('H3', 'Ruta');
			$sheet->setCellValue('E3', '# Serie');
			$sheet->setCellValue('F3', '# Documento');
			$sheet->setCellValue('G3', 'Estado');
			$sheet->setCellValue('H3', 'Articulo');
            $sheet->setCellValue('I3', 'Cantidad');
            $sheet->setCellValue('J3', 'Precio');
			$sheet->setCellValue('K3', 'Total');
			$sheet->setCellValue('L3', 'N° de Documento');
			$sheet->setCellValue('M3', 'Razón Social');
	//		$sheet->setCellValue('R3', 'Guía');
	//		$sheet->setCellValue('S3', 'Distrito');
	//		$sheet->setCellValue('T3', 'Provincia');
	//		$sheet->setCellValue('U3', 'Departamento');
			$sheet->getStyle('A3:M3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->company_short_name);
				$sheet->setCellValue('C'.$row_number, $element->issue_date);
				$sheet->setCellValue('D'.$row_number, $element->business_unit_name);
        //      $sheet->setCellValue('E'.$row_number, $element->client_channel_name);
        //      $sheet->setCellValue('F'.$row_number, $element->client_zone_name);
        //      $sheet->setCellValue('G'.$row_number, $element->client_sector_name);
        //      $sheet->setCellValue('H'.$row_number, $element->client_route_id);
				$sheet->setCellValue('E'.$row_number, $element->serie_number);
				$sheet->setCellValue('F'.$row_number, $element->voucher_number);
				$sheet->setCellValue('G'.$row_number, $element->state);
                $sheet->setCellValue('H'.$row_number, $element->name);
                $sheet->setCellValue('I'.$row_number, $element->sum_total);
				$sheet->setCellValue('J'.$row_number, $element->price); 
				$sheet->setCellValue('K'.$row_number, $element->total);
				$sheet->setCellValue('L'.$row_number, $element->client_code);
				$sheet->setCellValue('M'.$row_number, $element->client_business_name);
		//		$sheet->setCellValue('R'.$row_number, $element->guide);
		//		$sheet->setCellValue('S'.$row_number, $element->district);
		//		$sheet->setCellValue('T'.$row_number, $element->province);
		//		$sheet->setCellValue('U'.$row_number, $element->department);				
                $sheet->getStyle('J'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('K'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
	//		$sheet->getColumnDimension('N')->setAutoSize(true);
	//		$sheet->getColumnDimension('O')->setAutoSize(true);
	//		$sheet->getColumnDimension('P')->setAutoSize(true);
	//		$sheet->getColumnDimension('Q')->setAutoSize(true);
	//		$sheet->getColumnDimension('R')->setAutoSize(true);
	//		$sheet->getColumnDimension('S')->setAutoSize(true);
	//		$sheet->getColumnDimension('T')->setAutoSize(true);
	//		$sheet->getColumnDimension('U')->setAutoSize(true);


			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








