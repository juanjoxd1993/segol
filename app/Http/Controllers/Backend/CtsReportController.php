<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Client;
use App\Company;
use App\Asist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Planilla;
use App\Cicle;
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
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$ciclos = Cicle::select('id', 'año', 'mes')->get();

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

	                           


                 $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
                    ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
                    ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
                    ->select('asists.id', 'asists.employ_id', 'asists.ciclo_id','asists.horas_tarde', 'asists.minutos_tarde', 
                      'employees.first_name','employees.document_number')
              //      ->where('employees.company_id', $company_id)
                    ->where('asists.año', '=', $price_year)
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
				$facturation->fecha_inicio = $facturation['fecha_inicio'];
                
				//OBTENIENDO FECHA DE CALCULO
				$facturation->mes_calc = $facturation['inicio_cts']; //mayo- nov
				$facturation->año_calc = $facturation['final_cts'];//octubre- abr
				$facturation->fecha_ini = $facturation['fecha_ini'];//primer día del ciclo seleccionado



                if($facturation->fecha_inicio>$facturation->mes_calc)
				{
					$diasDiferencia = $facturation->año_calc->diffInDays($facturation->fecha_inicio);
				}

				else{
					$diasDiferencia = 180;
				}

				$mes_date=$facturation->fecha_ini;

				$meses=$diasDiferencia/30;
				$meses=round($meses,1);
				$facturation->meses = $meses;

				$meses_calc=$facturation->meses*30;
				$facturation->meses_calc = $meses_calc;

				$dias_calc=$diasDiferencia-$facturation->meses_calc;
				$facturation->dias_calc = $dias_calc;


				$price_mes = date('Y-m-01', strtotime('-1 month', strtotime($mes_date)));
            //  $inicioMesPasado = date("Y-m-d", $price_mes);
                $tiempoMesPasado = strtotime( "last day of previous month",strtotime($mes_date));
                $fechaMesPasado = date("Y-m-d", $tiempoMesPasado);
				$mes_pasado=date("m", $tiempoMesPasado);
				$año_pasado=date("Y", $tiempoMesPasado);

                $price_mes2 = date('Y-m-01', strtotime('-2 month', strtotime($mes_date)));
                $price_mes3 = date('Y-m-01', strtotime('-3 month', strtotime($mes_date)));
                $price_mes4 = date('Y-m-01', strtotime('-4 month', strtotime($mes_date)));
                $price_mes5 = date('Y-m-01', strtotime('-5 month', strtotime($mes_date)));
                $price_mes6 = date('Y-m-01', strtotime('-6 month', strtotime($mes_date)));

                $tiempoMesPasado2 = strtotime( "last day of previous month",strtotime($price_mes));
                $fechaMesPasado2 = date("Y-m-d", $tiempoMesPasado2);
				$mes_pasado2=date("m", $tiempoMesPasado2);
				$año_pasado2=date("Y", $tiempoMesPasado2);
                $tiempoMesPasado3 = strtotime( "last day of previous month",strtotime($price_mes2));
                $fechaMesPasado3 = date("Y-m-d", $tiempoMesPasado3);
				$mes_pasado3=date("m", $tiempoMesPasado3);
				$año_pasado3=date("Y", $tiempoMesPasado3);
                $tiempoMesPasado4 = strtotime( "last day of previous month",strtotime($price_mes3));
                $fechaMesPasado4 = date("Y-m-d", $tiempoMesPasado4);
				$mes_pasado4=date("m", $tiempoMesPasado4);
				$año_pasado4=date("Y", $tiempoMesPasado4);
                $tiempoMesPasado5 = strtotime( "last day of previous month",strtotime($price_mes4));
                $fechaMesPasado5 = date("Y-m-d", $tiempoMesPasado5);
				$mes_pasado5=date("m", $tiempoMesPasado5);
				$año_pasado5=date("Y", $tiempoMesPasado5);
                $tiempoMesPasado6 = strtotime( "last day of previous month",strtotime($price_mes5));
                $fechaMesPasado6 = date("Y-m-d", $tiempoMesPasado6);
				$mes_pasado6=date("m", $tiempoMesPasado6);
				$año_pasado6=date("Y", $tiempoMesPasado6);
				
				//horas extras
				$he_25=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado)
				->where('asists.año', $año_pasado)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_25 = $he_25;

				$he_35=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado)
				->where('asists.año', $año_pasado)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_35 = $he_35;

				$phe_25=($facturation->sueldo/240)*($facturation->he_25*1.25);
				$facturation->phe_25 = round($phe_25, 2);
				$phe_35=($facturation->sueldo/240)*($facturation->he_35*1.35);
				$facturation->phe_35 = round($phe_35, 2);

				$he_252=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado2)
				->where('asists.año', $año_pasado2)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_252 = $he_252;

				$he_352=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado2)
				->where('asists.año', $año_pasado2)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_352 = $he_352;

				$phe_252=($facturation->sueldo/240)*($facturation->he_252*1.25);
				$facturation->phe_252 = round($phe_252, 2);
				$phe_352=($facturation->sueldo/240)*($facturation->he_352*1.35);
				$facturation->phe_352 = round($phe_352, 2);

				$he_253=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado3)
				->where('asists.año', $año_pasado3)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_253 = $he_253;

				$he_353=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado3)
				->where('asists.año', $año_pasado3)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_353 = $he_353;

				$phe_253=($facturation->sueldo/240)*($facturation->he_253*1.25);
				$facturation->phe_253 = round($phe_253, 2);
				$phe_353=($facturation->sueldo/240)*($facturation->he_353*1.35);
				$facturation->phe_353 = round($phe_353, 2);


				$he_254=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado4)
				->where('asists.año', $año_pasado4)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_254 = $he_254;

				$he_354=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado4)
				->where('asists.año', $año_pasado4)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_354 = $he_354;

				$phe_254=($facturation->sueldo/240)*($facturation->he_254*1.25);
				$facturation->phe_254 = round($phe_254, 2);
				$phe_354=($facturation->sueldo/240)*($facturation->he_354*1.35);
				$facturation->phe_354 = round($phe_354, 2);


				$he_255=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado5)
				->where('asists.año', $año_pasado5)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_255 = $he_255;

				$he_355=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado5)
				->where('asists.año', $año_pasado5)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_355 = $he_355;

				$phe_255=($facturation->sueldo/240)*($facturation->he_255*1.25);
				$facturation->phe_255 = round($phe_255, 2);
				$phe_355=($facturation->sueldo/240)*($facturation->he_355*1.35);
				$facturation->phe_355 = round($phe_355, 2);

				$he_256=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado6)
				->where('asists.año', $año_pasado6)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');

				$facturation->he_256 = $he_256;

				$he_356=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->where('asists.mes', $mes_pasado6)
				->where('asists.año', $año_pasado6)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_356 = $he_356;

				$phe_256=($facturation->sueldo/240)*($facturation->he_256*1.25);
				$facturation->phe_256 = round($phe_256, 2);
				$phe_356=($facturation->sueldo/240)*($facturation->he_356*1.35);
				$facturation->phe_356 = round($phe_356, 2);


		    $facturation->he_1=$facturation->phe_25+$facturation->phe_35;
			$facturation->he_2=$facturation->phe_252+$facturation->phe_352;
			$facturation->he_3=$facturation->phe_253+$facturation->phe_353;
			$facturation->he_4=$facturation->phe_254+$facturation->phe_354;
			$facturation->he_5=$facturation->phe_255+$facturation->phe_355;
			$facturation->he_6=$facturation->phe_256+$facturation->phe_356;










				$com_1=0;
				$bon_1=0;


                
            

                $monthName  = date("F", strtotime ($fechaMesPasado));
                $monthName2 = date("F", strtotime ($fechaMesPasado2));
                $monthName3 = date("F", strtotime ($fechaMesPasado3));
                $monthName4 = date("F", strtotime ($fechaMesPasado4));
                $monthName5 = date("F", strtotime ($fechaMesPasado5));
                $monthName6 = date("F", strtotime ($fechaMesPasado6)); 

				                
                if( $facturation->familiar > 0){
                    $facturation->asignacion = 'SI';   
                }
                else {
                    $facturation->asignacion = 'NO';
                }
	

			    $facturation->sueldo = $facturation['sueldo'];
			    $facturation->familiar = $facturation['familiar'];
               




			$totals_sum_sueldo += $facturation['sueldo'];
			$totals_sum_familiar += $facturation['familiar'];
		

			
               
			

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
		 	$sheet->setCellValue('I5', 'DIAS S.P');
            $sheet->setCellValue('J5', 'COM'. $monthName);//COM 1
            $sheet->setCellValue('K5', 'COM'. $monthName2);
            $sheet->setCellValue('L5', 'COM'. $monthName3);
            $sheet->setCellValue('M5', 'COM'. $monthName4);
            $sheet->setCellValue('N5', 'COM'. $monthName5);
            $sheet->setCellValue('O5', 'COM'. $monthName6);//COM
            $sheet->setCellValue('P5', 'HE');//HE1
            $sheet->setCellValue('Q5', 'HE');
            $sheet->setCellValue('R5', 'HE');
            $sheet->setCellValue('S5', 'HE');
            $sheet->setCellValue('T5', 'HE');
            $sheet->setCellValue('U5', 'HE');//HE
            $sheet->setCellValue('V5', 'BON');//BON1
            $sheet->setCellValue('W5', 'BON');
            $sheet->setCellValue('X5', 'BON');
            $sheet->setCellValue('Y5', 'BON');
            $sheet->setCellValue('Z5', 'BON');
            $sheet->setCellValue('AA5', 'BON');//BON 
			$sheet->setCellValue('AB5', 'FIESTAS PATRIAS');
		 	$sheet->setCellValue('AC5', 'REM BASICA');
            $sheet->setCellValue('AD5', 'GRAT. FIESTAS PATRIAS');
			$sheet->setCellValue('AE5', 'Prom. HE');
			$sheet->setCellValue('AF5', 'Prom. Comisiones');
			$sheet->setCellValue('AG5', 'Prom. Bon Reg');
			$sheet->setCellValue('AH5', 'TOTAL COMPUTABLE');
			$sheet->setCellValue('AI5', 'IMPORTE X MES');
			$sheet->setCellValue('AJ5', 'IMPORTE X DIA');
			$sheet->setCellValue('AK5', 'BANCO ');
            $sheet->setCellValue('AL5', 'CTA ');
			$sheet->setCellValue('AM5', 'CCI');


			$sheet->getStyle('A5:AM5')->applyFromArray([
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
                $sheet->setCellValue('E'.$row_number, $element->fecha_ingreso);
                $sheet->setCellValue('F'.$row_number, $element->meses);
                $sheet->setCellValue('G'.$row_number, $element->dias);
                $sheet->setCellValue('H'.$row_number, $element->total_sp);
                $sheet->setCellValue('I'.$row_number, $element->com_1);
				$sheet->setCellValue('J'.$row_number, $element->com_2);
				$sheet->setCellValue('K'.$row_number, $element->com_3);
				$sheet->setCellValue('M'.$row_number, $element->com_4);
				$sheet->setCellValue('N'.$row_number, $element->com_5);
				$sheet->setCellValue('O'.$row_number, $element->com_6);
				$sheet->setCellValue('P'.$row_number, $element->he_1);
				$sheet->setCellValue('Q'.$row_number, $element->he_2);
				$sheet->setCellValue('R'.$row_number, $element->he_3);
				$sheet->setCellValue('S'.$row_number, $element->he_4);
				$sheet->setCellValue('T'.$row_number, $element->he_5);
				$sheet->setCellValue('U'.$row_number, $element->he_6);
				$sheet->setCellValue('V'.$row_number, $element->bon_1);
				$sheet->setCellValue('W'.$row_number, $element->bon_2);
				$sheet->setCellValue('X'.$row_number, $element->bon_3);
				$sheet->setCellValue('Y'.$row_number, $element->bon_4);
				$sheet->setCellValue('Z'.$row_number, $element->bon_5);
				$sheet->setCellValue('AA'.$row_number, $element->bon_6);
				$sheet->setCellValue('AB'.$row_number, $element->grati);
				$sheet->setCellValue('AC'.$row_number, $element->sueldo); 
				$sheet->setCellValue('AD'.$row_number, $element->familiar);
				$sheet->setCellValue('AE'.$row_number, $element->gratdiv);
				$sheet->setCellValue('AF'.$row_number, $element->prom_he);
				$sheet->setCellValue('AG'.$row_number, $element->prom_com);
				$sheet->setCellValue('AH'.$row_number, $element->prom_bon);
				$sheet->setCellValue('AI'.$row_number, $element->total_comp);
				$sheet->setCellValue('AF'.$row_number, $element->import_mes);
				$sheet->setCellValue('AG'.$row_number, $element->import_dia);
				$sheet->setCellValue('AH'.$row_number, $element->total_cts);





				
							
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
	








