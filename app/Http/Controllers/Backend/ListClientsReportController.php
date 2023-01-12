<?php

namespace App\Http\Controllers\Backend;

use App\BusinessUnit;
use App\Client;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ListClientsReportController extends Controller
{
    public function index() {
		$business_units = BusinessUnit::select('id', 'name')->get();
		$companies = Company::select('id', 'name')->get();

		return view('backend.list_clients_report')->with(compact('business_units', 'companies'));
	}

	public function validateForm() {
		$messages = [
			'business_unit_id.required'	=> 'Debe seleccionar una Fecha inicial.',
		];

		$rules = [
			'business_unit_id'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$export_excel = request('export_excel');

		$business_unit_id = request('model.business_unit_id');
		$company_id = request('model.company_id');

		$elements = Client::leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
			->leftjoin('companies', 'clients.company_id', '=', 'companies.id')
			->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
			->leftjoin('client_addresses', function($join) {
				$join->on('clients.id', '=', 'client_addresses.client_id')
					->where('client_addresses.address_type_id', 1);
			})
			->leftjoin('ubigeos', 'client_addresses.ubigeo_id', '=', 'ubigeos.id')
			->leftjoin('payments', 'clients.payment_id', '=', 'payments.id')
			->leftjoin('client_channels', 'clients.channel_id', '=', 'client_channels.id')
			->leftjoin('client_zones', 'clients.zone_id', '=', 'client_zones.id')
			->leftjoin('client_sectors', 'clients.sector_id', '=', 'client_sectors.id')
			->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
			->leftjoin('managers', 'clients.manager_id', '=', 'managers.id')
			->where('clients.business_unit_id', $business_unit_id)
			->when($company_id, function ($query, $company_id) {
				return $query->where('clients.company_id', $company_id);
			})
			->select('business_units.name as business_unit_name', 'companies.short_name as company_short_name', 'clients.id as client_id', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number', 'clients.business_name', 'client_addresses.address as client_address',  
			'ubigeos.district as ubigeo', 'ubigeos.department as department', 'ubigeos.province as province',
			'payments.name as payment_name', 'clients.credit_limit_days', 'clients.credit_limit', 
			'client_channels.name as client_channel_name', 'client_zones.name as client_zone_name', 
			'client_sectors.name as client_sector_name','client_routes.short_name as client_route_id', 
			'client_routes.name as client_route_name','clients.phone_number_1 as client_phone',
			'clients.phone_number_2 as client_phone2','clients.phone_number_3 as client_phone3','clients.email as client_email',
			'client_addresses.gps_x as client_gps_x','client_addresses.gps_y as client_gps_y','clients.contact_name_1 as client_contact_name',
			'clients.business_type as business_type','clients.dgh as dgh','clients.police as police','clients.grupo as grupo','clients.estado as estado','managers.name as manager_name','clients.state as state')
			->groupBy('clients.id')
			->orderBy('business_unit_name')
			->orderBy('company_short_name')
			->orderBy('business_name')
			->get();

		if ( $export_excel ) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AH1');
			$sheet->setCellValue('A1', 'REPORTE RELACIÓN DE CLIENTES ACTIVOS AL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
			$sheet->setCellValue('B3', 'Unidad Negocio');
			$sheet->setCellValue('C3', 'Compañía');
			$sheet->setCellValue('D3', 'ID Cliente');
			$sheet->setCellValue('E3', 'Cód. Cliente');
			$sheet->setCellValue('F3', 'Doc. Cliente');
			$sheet->setCellValue('G3', 'Nº Doc.');
			$sheet->setCellValue('H3', 'Razón Social');
			$sheet->setCellValue('I3', 'Dirección');
			$sheet->setCellValue('J3', 'Distrito');
			$sheet->setCellValue('K3', 'Provincia');
			$sheet->setCellValue('L3', 'Departamento');
			$sheet->setCellValue('M3', 'Cond. venta');
			$sheet->setCellValue('N3', 'Días');
			$sheet->setCellValue('O3', 'Límite crédito');
			$sheet->setCellValue('P3', 'Canal venta');
			$sheet->setCellValue('Q3', 'Zona venta');
			$sheet->setCellValue('R3', 'Sector económico');
			$sheet->setCellValue('S3', 'ID Ruta');
			$sheet->setCellValue('T3', 'Ruta');
			$sheet->setCellValue('U3', 'Celular 1');
			$sheet->setCellValue('V3', 'Celular 2');
			$sheet->setCellValue('W3', 'Teléfono');
			$sheet->setCellValue('X3', 'correo');			
			$sheet->setCellValue('Y3', 'gps_x');
			$sheet->setCellValue('Z3', 'gps_y');
			$sheet->setCellValue('AA3', 'Nombre de Contacto');
			$sheet->setCellValue('AB3', 'Tipo de Negocio');
			$sheet->setCellValue('AC3', 'DGH');
			$sheet->setCellValue('AD3', 'Poliza');
			$sheet->setCellValue('AE3', 'Grupo');
			$sheet->setCellValue('AF3', 'Estado');
			$sheet->setCellValue('AG3', 'Supervisor');
			$sheet->setCellValue('AH3', 'Activo');
			$sheet->getStyle('A3:AH3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($elements as $index => $element) {
				$index++;
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->business_unit_name);
				$sheet->setCellValue('C'.$row_number, $element->company_short_name);
				$sheet->setCellValue('D'.$row_number, $element->client_id);
				$sheet->setCellValue('E'.$row_number, $element->client_code);
				$sheet->setCellValue('F'.$row_number, $element->document_type_name);
				$sheet->setCellValue('G'.$row_number, $element->document_number);
				$sheet->setCellValue('H'.$row_number, $element->business_name);
				$sheet->setCellValue('I'.$row_number, $element->client_address);
				$sheet->setCellValue('J'.$row_number, $element->ubigeo);
				$sheet->setCellValue('K'.$row_number, $element->province);
				$sheet->setCellValue('L'.$row_number, $element->department);
				$sheet->setCellValue('M'.$row_number, $element->payment_name);
				$sheet->setCellValue('N'.$row_number, $element->credit_limit_days);
				$sheet->setCellValue('O'.$row_number, $element->credit_limit);
				$sheet->setCellValue('P'.$row_number, $element->client_channel_name);
				$sheet->setCellValue('Q'.$row_number, $element->client_zone_name);
				$sheet->setCellValue('R'.$row_number, $element->client_sector_name);
				$sheet->setCellValue('S'.$row_number, $element->client_route_id);
				$sheet->setCellValue('T'.$row_number, $element->client_route_name);
				$sheet->setCellValue('U'.$row_number, $element->client_phone);
				$sheet->setCellValue('V'.$row_number, $element->client_phone2);
				$sheet->setCellValue('W'.$row_number, $element->client_phone3);
				$sheet->setCellValue('X'.$row_number, $element->client_email);
				$sheet->setCellValue('Y'.$row_number, $element->client_gps_x);
				$sheet->setCellValue('Z'.$row_number, $element->client_gps_y);
                $sheet->setCellValue('AA'.$row_number, $element->client_contact_name);
				$sheet->setCellValue('AB'.$row_number, $element->business_type);
				$sheet->setCellValue('AC'.$row_number, $element->dgh);
				$sheet->setCellValue('AD'.$row_number, $element->police);
				$sheet->setCellValue('AE'.$row_number, $element->grupo);
				$sheet->setCellValue('AF'.$row_number, $element->estado);
				$sheet->setCellValue('AG'.$row_number, $element->manager_name);
				$sheet->setCellValue('AH'.$row_number, $element->state);
				$sheet->getStyle('O'.$row_number)->getNumberFormat()->setFormatCode('0.00');

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
			$sheet->getColumnDimension('AF')->setAutoSize(true);
			$sheet->getColumnDimension('AG')->setAutoSize(true);
			$sheet->getColumnDimension('AH')->setAutoSize(true);

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		}
		
		return response()->json($elements);
	}
}
