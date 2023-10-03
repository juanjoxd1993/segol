<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Planilla;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use stdClass;


class PlanillaTotalReportController extends Controller
{
    public function index() {
	//	$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.planilla_total_report')->with(compact('companies', 'current_date'));
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
		$price_mes = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('m');
		$price_year = CarbonImmutable::createFromDate(request('model.initial_date'))->startOfDay()->format('Y');

	                           


						$elements = planilla::leftjoin('employees', 'planilla.employee_id', '=', 'employees.id')
							            ->where('planilla.año', '=', $price_year)
			            ->where('planilla.mes', '<=', $price_mes)			            
			            ->select('planilla.id as planilla_id',
                        'planilla.año as año',
                        'planilla.mes as mes',
                        'planilla.employ_id as employ_id',
                        'planilla.employ_name as employ_name',
                        'planilla.cargo as cargo',
                        'planilla.sueldo as sueldo',
                        'planilla.familiar as familiar',
                        'planilla.otros as otros',
                        'planilla.bruto as bruto',
                        'planilla.horas_extra as horas_extra',
                        'planilla.noc_25 as noc_25',
                        'planilla.noc_35 as noc_35',
                        'planilla.afp_id as afp_id',
                        'planilla.afp_name as afp_name',
                        'planilla.afp_base as afp_base',
                        'planilla.afp_com as afp_com',
                        'planilla.afp_prima as afp_prima',
                        'planilla.quincena as quincena',
                        'planilla.total_desc as total_desc',
                        'planilla.neto as neto',
                        'planilla.salud as salud',
                        'planilla.sctr as sctr',
                        'planilla.total_apor as total_apor',
                        'employees.document_number as document_number')
			        
				
			
		//	->groupBy('sales.sale_date','business_unit_id')
            ->get();

			$response=[];

            
            $totals_sum_sueldo = 0;
			$totals_sum_familiar = 0;
		    $totals_sum_otros = 0;
            $totals_sum_bruto = 0;
		    $totals_sum_horas_extra = 0;
		    $totals_sum_noc_25 = 0;
		    $totals_sum_noc_35 = 0;
		    $totals_sum_afp_base = 0;
			$totals_sum_afp_com = 0; 
		    $totals_sum_afp_prima = 0;
			$totals_sum_quincena = 0;
			$totals_sum_total_desc = 0;
		    $totals_sum_neto = 0;
		    $totals_sum_salud = 0;
		    $totals_sum_sctr= 0;
			$totals_sum_total_apor= 0; 
			

			foreach ($elements as $facturation) {

				$facturation->document_number = $facturation['document_number'];
				$facturation->employ_name = $facturation['employ_name'];
			    $facturation->cargo = $facturation['cargo'];
                $facturation->familiar = $facturation['familiar'];
                
                if( $facturation->familiar > 0){
                    $facturation->asignacion = 'SI';   
                }
                else {
                    $facturation->asignacion = 'NO';
                }
			    $facturation->sueldo = $facturation['sueldo'];
			    $facturation->familiar = $facturation['familiar'];
                $facturation->otros = $facturation['otros'];
                $facturation->bruto = $facturation['bruto'];
                $facturation->afp_name= $facturation['afp_name'];
                $facturation->afp_base = $facturation['afp_base'];
                $facturation->afp_com = $facturation['afp_com'];
                $facturation->afp_prima = $facturation['afp_prima'];
                $facturation->total_desc = $facturation['total_desc'];
                $facturation->neto = $facturation['neto'];
                $facturation->salud = $facturation['salud'];
                $facturation->sctr = $facturation['sctr'];
                $facturation->noc_25 = $facturation['noc_25'];
                $facturation->noc_35 = $facturation['noc_35'];
                $facturation->horas_extra = $facturation['horas_extra'];
                $facturation->quincena = $facturation['quincena'];
                $facturation->total_apor = $facturation['total_apor'];




			$totals_sum_sueldo += $facturation['sueldo'];
			$totals_sum_familiar += $facturation['familiar'];
		    $totals_sum_otros += $facturation['otros'];
            $totals_sum_bruto += $facturation['bruto'];
		    $totals_sum_horas_extra += $facturation['horas_extra'];
		    $totals_sum_noc_25 += $facturation['noc_25'];
		    $totals_sum_noc_35 += $facturation ['noc_35'];
		    $totals_sum_afp_base += $facturation['afp_base'];
			$totals_sum_afp_com += $facturation ['afp_com'];
		    $totals_sum_afp_prima += $facturation['afp_prima'];
			$totals_sum_total_desc += $facturation['total_desc'];
			$totals_sum_neto += $facturation['neto'];
		    $totals_sum_salud += $facturation['salud'];
		    $totals_sum_quincena += $facturation ['quincena'];
		    $totals_sum_sctr += $facturation['sctr'];
			$totals_sum_total_apor += $facturation ['total_apor'];
			
               
			

				$response[] = $facturation;

			}


		$totals = new stdClass();
		
		$totals->document_number = 'TOTAL';
        $totals->employ_name= '';
        $totals->cargo = '';
        $totals->asignacion = '';
        $totals->sueldo =$totals_sum_sueldo ;
        $totals->familiar =$totals_sum_familiar ;
        $totals->otros =$totals_sum_otros ;
        $totals->horas_extra = $totals_sum_horas_extra ;
        $totals->noc_25 = $totals_sum_noc_25 ;
        $totals->noc_35 =$totals_sum_noc_35 ;
        $totals->bruto =$totals_sum_bruto ;
        $totals->afp_name ='' ;
        $totals->afp_base =$totals_sum_afp_base ;
        $totals->afp_com =$totals_sum_afp_com ;
        $totals->afp_prima =$totals_sum_afp_prima ;
        $totals->quincena =$totals_sum_quincena ;
        $totals->total_desc =$totals_sum_total_desc ;
        $totals->neto =$totals_sum_neto ;
        $totals->salud =$totals_sum_salud ;
        $totals->sctr =$totals_sum_sctr ;
        $totals->total_apor =$totals_sum_total_apor ;

		

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'PLANILLA DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);


	    $sheet->mergeCells('E2:M2');
		$sheet->setCellValue('E2', 'INGRESOS DEL TRABJADOR');
		$sheet->getStyle('E2')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '44546a')
			]
		]);

		$sheet->mergeCells('N2:S2');
		$sheet->setCellValue('N2', 'RETENCIONES  A  CARGO  DEL  TRABAJADOR');
		$sheet->getStyle('N2')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1E90FF')
			]
		]);

		$sheet->mergeCells('U2:W2');
		$sheet->setCellValue('U2', 'APORTACIONES DEL EMPLEADOR');
		$sheet->getStyle('U2')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '7FFF00')
			]
		]);

		


			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'N° Documento');
			$sheet->setCellValue('C3', 'APELLIDOS Y NOMBRES');
			$sheet->setCellValue('D3', 'CARGO U OCUPACIÓN');
			$sheet->setCellValue('E3', 'ASIGNACIÓN FAMILIAR');
			$sheet->setCellValue('F3', 'SUELDO BÁSICO');		
            $sheet->setCellValue('G3', 'ASIGNACIÓN FAMILIAR');
		 	$sheet->setCellValue('H3', 'HORAS EXTRA');
		 	$sheet->setCellValue('I3', 'BONO NOCT 25%');
		 	$sheet->setCellValue('J3', 'BONO NOCT 35%');
			$sheet->setCellValue('K3', 'OTROS');
			$sheet->setCellValue('L3', 'TOTAL REMUNERACIÓN BRUTA');
		 	$sheet->setCellValue('N3', 'AFP');
            $sheet->setCellValue('O3', 'APORTE OBLIGATORIO');
			$sheet->setCellValue('P3', 'COMISIÓN % SOBRE R.A.');
			$sheet->setCellValue('Q3', 'PRIMA DE SEGURO');
			$sheet->setCellValue('R3', 'QUINCENA');
			$sheet->setCellValue('S3', 'TOTAL DESCUENTO');
			$sheet->setCellValue('T3', 'REMUNERACIÓN NETA');
			$sheet->setCellValue('U3', 'SALUD');
			$sheet->setCellValue('V3', 'SCTR');
			$sheet->setCellValue('W3', 'TOTAL APORTES');
			$sheet->getStyle('A3:W3')->applyFromArray([
				'font' => [
					'bold' => true,
				],
			]);

			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->document_number);
				$sheet->setCellValue('C'.$row_number, $element->employ_name);
                $sheet->setCellValue('D'.$row_number, $element->cargo);
				$sheet->setCellValue('E'.$row_number, $element->asignacion);
				$sheet->setCellValue('F'.$row_number, $element->sueldo);
                $sheet->setCellValue('G'.$row_number, $element->familiar);
                $sheet->setCellValue('H'.$row_number, $element->horas_extra);
                $sheet->setCellValue('I'.$row_number, $element->noc_25);
                $sheet->setCellValue('J'.$row_number, $element->noc_35);
                $sheet->setCellValue('K'.$row_number, $element->otros);
				$sheet->setCellValue('L'.$row_number, $element->bruto);
				$sheet->setCellValue('N'.$row_number, $element->afp_name);
				$sheet->setCellValue('O'.$row_number, $element->afp_base);
				$sheet->setCellValue('P'.$row_number, $element->afp_com);
				$sheet->setCellValue('Q'.$row_number, $element->afp_prima);
				$sheet->setCellValue('R'.$row_number, $element->quincena);
				$sheet->setCellValue('S'.$row_number, $element->total_desc); 
				$sheet->setCellValue('T'.$row_number, $element->neto);
				$sheet->setCellValue('U'.$row_number, $element->salud);
				$sheet->setCellValue('V'.$row_number, $element->sctr);
				$sheet->setCellValue('W'.$row_number, $element->total_apor);
				
							
                $sheet->getStyle('E'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('F'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('G'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








