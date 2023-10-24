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


class CtsReportController extends Controller
{
    public function index() {
	//	$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		return view('backend.cts_report')->with(compact('companies', 'current_date'));
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

	                           


					/*	$elements = Asist::leftjoin('employees', 'cts_planillas.employ_id', '=', 'employees.id')
							            ->where('cts_planillas.año', '=', $price_year)
			            ->where('cts_planillas.mes', '<=', $price_mes)			            
			            ->select('cts_planillas.id as planilla_id',
                        'cts_planillas.año as año',
                        'cts_planillas.mes as mes',
                        'cts_planillas.employ_id as employ_id',
                        'cts_planillas.employ_name as employ_name',
                        'cts_planillas.cargo as cargo',
                        'cts_planillas.regimen as regimen',
                        'cts_planillas.sueldo as sueldo',
                        'cts_planillas.familiar as familiar',
                        'cts_planillas.otros as otros',
                        'cts_planillas.bruto as bruto',
                        'cts_planillas.horas_extra as horas_extra',
                        'cts_planillas.noc_25 as noc_25',
                        'cts_planillas.noc_35 as noc_35',
                        'cts_planillas.afp_id as afp_id',
                        'cts_planillas.afp_name as afp_name',
                        'cts_planillas.afp_base as afp_base',
                        'cts_planillas.afp_com as afp_com',
                        'cts_planillas.afp_prima as afp_prima',
                        'cts_planillas.quincena as quincena',
                        'cts_planillas.total_desc as total_desc',
                        'cts_planillas.neto as neto',
                        'cts_planillas.salud as salud',
                        'cts_planillas.sctr as sctr',
                        'cts_planillas.total_apor as total_apor',
                        'employees.document_number as document_number')*/


                 $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
                    ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
                    ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
                    ->select('asists.id', 'asists.employ_id', 'asists.ciclo_id','asists.horas_tarde', 'asists.minutos_tarde', 
                      'employees.first_name','employees.document_number')
                    ->where('employees.company_id', $company_id)
                    ->where('asists.año', '=', $price_año)
                    ->where('asists.mes', '=', $price_mes)


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

		
			$sheet->setCellValue('A5', '#');
			$sheet->setCellValue('B5', 'N° Documento');
			$sheet->setCellValue('C5', 'APELLIDOS Y NOMBRES');
			$sheet->setCellValue('D5', 'CARGO U OCUPACIÓN');
			$sheet->setCellValue('E5', 'REGIMEN LABORAL');
			$sheet->setCellValue('F5', 'FECHA DE INGRESO');		
            $sheet->setCellValue('G5', 'MESES');
		 	$sheet->setCellValue('H5', 'DIAS');
		 	$sheet->setCellValue('I5', 'DIAS S.P MAY-OCT');
            $sheet->setCellValue('J5', $CAB1);
            $sheet->setCellValue('K5', $CAB2);
            $sheet->setCellValue('L5', $CAB3);
            $sheet->setCellValue('M5', $CAB4);
            $sheet->setCellValue('N5', $CAB5);
            $sheet->setCellValue('O5', $CAB6);
            $sheet->setCellValue('P5', $CAB7);
            $sheet->setCellValue('Q5', $CAB8);
            $sheet->setCellValue('R5', $CAB9);
            $sheet->setCellValue('S5', $CAB10);
            $sheet->setCellValue('T5', $CAB11);
            $sheet->setCellValue('U5', $CAB12);
            $sheet->setCellValue('V5', $CAB13);
            $sheet->setCellValue('W5', $CAB14);
            $sheet->setCellValue('X5', $CAB15);
            $sheet->setCellValue('Y5', $CAB16);
            $sheet->setCellValue('Z5', $CAB17);
            $sheet->setCellValue('AA5', $CAB18);
			$sheet->setCellValue('AB5', 'OTROS');
			$sheet->setCellValue('AB5', 'FIESTAS PATRIAS');
		 	$sheet->setCellValue('AC5', 'REM BASICA');
            $sheet->setCellValue('AD5', 'GRAT. FIESTAS PATRIAS');
			$sheet->setCellValue('AE5', 'Prom. HE');
			$sheet->setCellValue('AF5', 'Pom. Comisiones');
			$sheet->setCellValue('AG5', 'Prom. Bon Reg');
			$sheet->setCellValue('AH5', 'OTROS');
			$sheet->setCellValue('AI5', 'TOTAL COMPUTABLE');
			$sheet->setCellValue('AJ5', 'IMPORTE X MES');
			$sheet->setCellValue('AK5', 'IMPORTE X DIA');
			$sheet->setCellValue('AL5', 'BANCO ');
            $sheet->setCellValue('AM5', 'CTA ');
			$sheet->setCellValue('AN5', 'CCI');


			$sheet->getStyle('A5:AN5')->applyFromArray([
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
	








