<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use strtotime;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class CertificadoReportController extends Controller
{
    public function index() {
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.certificado_report')->with(compact('current_date'));
	}

	public function validateForm() {
		$messages = [
			'initial_date.required'	=> 'Debe seleccionar una Fecha inicial.',
		];

		$rules = [
			'initial_date'	=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getClients() {
		$q = request('q');

		$clients = Employee::select('id', 'first_name as text')
			->where('first_name', 'like', '%'.$q.'%')
			->withTrashed()
			->get();

		return $clients;
	}

	public function list() {

		$export = request('export');

	    $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y-m-d H:i:s');
		$client_id = request('model.client_id');
		
					$elements = Employee::leftjoin('document_types', 'employees.document_type_id', '=', 'document_types.id')
			        ->where('employees.id', $client_id)
			        ->select('employees.id',
                    'employees.first_name',
                    'employees.document_type_id',
                    'employees.last_name',
                    'employees.fecha_inicio',
                    'employees.fecha_cese',
                    'document_types.name')
			
			    ->get();
			    $response=[];

			

			foreach ($elements as $saledetail) {

				$saledetail->client_id = $saledetail['client_id'];
				$saledetail->client_code = $saledetail['client_code'];
				$saledetail->client_business_name =$saledetail ['client_business_name'];
				
				$response[] = $saledetail;

			}


		

		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AB1');
			$sheet->setCellValue('A1', 'REPORTE DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
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
		//	$sheet->setCellValue('C3', 'Año de Despacho');
		//	$sheet->setCellValue('D3', 'Mes de Despacho');
			$sheet->setCellValue('C3', 'Fecha de Despacho');
			$sheet->setCellValue('D3', 'Unidad de Negocio');
            $sheet->setCellValue('E3', 'Canal'); 
            $sheet->setCellValue('F3', 'Sector');
            $sheet->setCellValue('G3', 'Ruta');
			$sheet->setCellValue('H3', 'Tipo');
			$sheet->setCellValue('I3', '# Serie');
			$sheet->setCellValue('J3', '# Documento');
			$sheet->setCellValue('K3', 'ID');
			$sheet->setCellValue('L3', 'Razón Social');
			$sheet->setCellValue('M3', 'Articulo');
			$sheet->setCellValue('N3', 'TM');
            $sheet->setCellValue('O3', 'Precio');
            $sheet->setCellValue('P3', 'Total');
			$sheet->setCellValue('Q3', '# de Parte');
			$sheet->setCellValue('R3', 'Tipo Movimiento');
			$sheet->setCellValue('S3', 'Guía');
			$sheet->setCellValue('T3', 'Placa');
			$sheet->setCellValue('U3', 'Zona');
			$sheet->setCellValue('V3', 'Distrito');
			$sheet->setCellValue('W3', 'Provincia');
			$sheet->setCellValue('X3', 'Departamento');
			$sheet->setCellValue('Y3', 'Chofer Vendedor');
			$sheet->setCellValue('Z3', 'Supervisor');
			$sheet->setCellValue('AA3', 'Grupo');
			$sheet->setCellValue('AB3', 'Estado');
			
			$sheet->getStyle('A3:AB3')->applyFromArray([
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
			//	$sheet->setCellValue('C'.$row_number, $saleDateYear);
			//	$sheet->setCellValue('D'.$row_number, $saleDateMonth);
				$sheet->setCellValue('C'.$row_number, $saleDateYear);
				$sheet->setCellValue('D'.$row_number, $element->business_unit_name);
                $sheet->setCellValue('E'.$row_number, $element->client_channel_name);
                $sheet->setCellValue('F'.$row_number, $element->client_sector_name);
                $sheet->setCellValue('G'.$row_number, $element->client_route_id);
				$sheet->setCellValue('H'.$row_number, $element->warehouse_document_type_short_name);
				$sheet->setCellValue('I'.$row_number, $element->referral_serie_number);
				$sheet->setCellValue('J'.$row_number, $element->referral_voucher_number);
				$sheet->setCellValue('K'.$row_number, $element->client_id);
			//	$sheet->setCellValue('L'.$row_number, $element->client_code);
				$sheet->setCellValue('L'.$row_number, $element->client_business_name);
                $sheet->setCellValue('M'.$row_number, $element->article_name);
                $sheet->setCellValue('N'.$row_number, $element->sum_total);
				$sheet->setCellValue('O'.$row_number, $element->price); 
				$sheet->setCellValue('P'.$row_number, $element->total);
				$sheet->setCellValue('Q'.$row_number, $element->warehouse_movement_movement_number);
				$sheet->setCellValue('R'.$row_number, $element->movement_type_name);
				$sheet->setCellValue('S'.$row_number, $element->guide);
				$sheet->setCellValue('T'.$row_number, $element->plate);
				$sheet->setCellValue('U'.$row_number, $element->client_zone_name);				
				$sheet->setCellValue('V'.$row_number, $element->district);
				$sheet->setCellValue('W'.$row_number, $element->province);
				$sheet->setCellValue('X'.$row_number, $element->department);
				$sheet->setCellValue('Y'.$row_number, $element->seller_id);
				$sheet->setCellValue('Z'.$row_number, $element->manager);	
				$sheet->setCellValue('AA'.$row_number, $element->grupo);
				$sheet->setCellValue('AB'.$row_number, $element->estado);				
             //   $sheet->getStyle('N'.$row_number)->getNumberFormat()->setFormatCode('0.00');
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
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








