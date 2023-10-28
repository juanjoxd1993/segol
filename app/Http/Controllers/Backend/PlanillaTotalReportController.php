<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Client;
use App\Company;
use App\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Planilla;
use App\Asist;
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

	                           


						$elements = asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
							            ->where('asists.año', '=', $price_year)
			            ->where('asists.mes', '<=', $price_mes)			            
			            ->select('asists.id as planilla_id',
                        'asists.año as año',
                        'asists.mes as mes',
                        'asists.employ_id as employ_id',
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
				$facturation->fecha_inicio = $facturation['fecha_inicio'];
				$facturation->asistencia = $facturation['asistencia'];
				$facturation->dias_trabajados = $facturation['dias_trabajados'];
				$facturation->descanso = $facturation['descanso'];
				$facturation->feriados = $facturation['feriado'];
				$facturation->dias_mes = $facturation['dias_mes'];
				$facturation->sueldo = $facturation['sueldo'];
			    $facturation->familiar = $facturation['familiar'];
			
                
                if( $facturation->familiar > 0){
                    $facturation->asignacion = 'SI';   
                }
                else {
                    $facturation->asignacion = 'NO';
                }

				$feriado_t=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',1)
				->select('dias')
				->sum('dias');

				$facturation->feriado_t = $feriado_t;

				$natalidad=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',5)
				->select('dias')
				->sum('dias');
				$facturation->natalidad = $natalidad;

				$desc_vac=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',6)
				->select('dias')
				->sum('dias');
				$facturation->desc_vac = $desc_vac;

				$comp_vac=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',7)
				->select('dias')
				->sum('dias');

				$facturation->comp_vac = $comp_vac;

				$comp_he=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',8)
				->select('dias')
				->sum('dias');
				$facturation->comp_he = $comp_he;

				$lic_pag=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',9)
				->select('dias')
				->sum('dias');
				$facturation->lic_pag = $lic_pag;


				$paternidad=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',10)
				->select('dias')
				->sum('dias');
				$facturation->paternidad = $paternidad;
				$lic_sin=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',11)
				->select('dias')
				->sum('dias');
				$facturation->lic_sin = $lic_sin;
				$desc_t=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',2)
				->select('dias')
				->sum('dias');
				$facturation->desc_t = $desc_t;
				$descanso_med=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',3)
				->select('dias')
				->sum('dias');
				$facturation->descanso_med = $descanso_med;

				$incap_temp=benefit::leftjoin('cicles', 'benefits.cicle_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.benefit_id',4)
				->select('dias')
				->sum('dias');
				$facturation->incap_temp = $incap_temp;


				
				$tardanzas=benefit::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('minutos_tarde')
				->sum('minutos_tarde');
				$facturation->tardanzas = $tardanzas;


				$he_25=asists::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('horas_extra_25')
				->sum('horas_extra_25');
				$facturation->he_25 = $he_25;

				$he_35=asists::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('horas_extra_35')
				->sum('horas_extra_35');
				$facturation->he_35 = $he_35;
				
				$hn_25=asists::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('horas_noc_25')
				->sum('horas_noc_25');
				$facturation->hn_25 = $hn_25;

				$hn_35=asists::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('horas_noc_35')
				->sum('horas_noc_35');
				$facturation->hn_35 = $hn_35;


				$dias_lab=$facturation->asistencia + $facturation->comp_vac;
				$facturation->dias_lab = $dias_lab;
				$dias_calc=$facturation->dias_lab;
				$facturation->dias_calc = $dias_calc;
				$dias_sub=$facturation->incap_temp + $facturation->natalidad;
				$facturation->dias_sub = $dias_sub;
				$sp_faltas=$facturation->dias_mes - $facturation->dias_trabajados;
				$facturation->sp_faltas = $sp_faltas;
				$sp_total=$facturation->sp_faltas + $facturation->lic_sin;
				$facturation->sp_total = $sp_total;

				$si_total=$facturation->descanso_med + $facturation->desc_vac + $facturation->lic_pag + + $facturation->paternidad;
				$facturation->si_total=$si_total;
				$dias_nolab=$facturation->sp_total+ $facturation->si_total;
				$facturation->dias_nolab = $dias_nolab;

				$dias_mes_t=$facturation->dias_lab + $facturation->dias_sub+$facturation->dias_nolab;
				$facturation->dias_mes_t = $dias_mes_t;
				$horas_lab=($facturation->dias_lab*8);
				$facturation->horas_lab = $horas_lab;

				//-------------------------------------------

				$phe_25=($facturation->sueldo/240)*($facturation->he_25*1.25);
				$facturation->phe_25 = round($phe_25, 2);
				$phe_35=($facturation->sueldo/240)*($facturation->he_35*1.35);
				$facturation->phe_35 = round($phe_35, 2);
				$phn_35=($facturation->sueldo/240)*($facturation->hn_35*1.25);
				$facturation->phn_35 = round($phn_35, 2);
				$tot_descfer=($facturation->desc_t + $facturation->feriado_t)*($facturation->sueldo/30)*2;
				$facturation->tot_descfer = round($tot_descfer, 2);
                $pdesc_vac=$facturation->desc_vac*($facturation->sueldo/30);
				$facturation->pdesc_vac = round($pdesc_vac, 2);
                $pcomp_vac=$facturation->comp_vac*($facturation->sueldo/30);
				$facturation->pcomp_vac = round($pcomp_vac, 2);
				$plic_pag=($facturation->lic_pag*($facturation->sueldo/30));
				$facturation->plic_pag= round($plic_pag, 2);
				$pnatalidad=($facturation->natalidad*($facturation->sueldo/30));
				$facturation->pnatalidad= round($pnatalidad, 2);
				$ppaternidad=($facturation->paternidad*($facturation->sueldo/30));
				$facturation->ppaternidad= round($ppaternidad, 2);
				$pincap_temp=($facturation->incap_temp*($facturation->sueldo/30));
				$facturation->pincap_temp= round($pincap_temp, 2);
				$pdescanso_med=($facturation->descanso_med*($facturation->sueldo/30));
				$facturation->pdescanso_med= round($pdescanso_med, 2);

				$tot_rem_brut=$facturation->sueldo+$facturation->phe_25+$facturation->phe_35+
				$facturation->tot_descfer+$facturation->pcomp_vac+$facturation->pdesc_vac+
				$facturation->familiar+$facturation->plic_pag+$facturation->pnatalidad+
				$facturation->ppaternidad+$facturation->pincap_temp+$facturation->pdescanso_med+
				$facturation->phn_35;

				$facturation->ptot_rem_brut= round($ptot_rem_brut, 2);

				$ptot_rem_afec= round($ptot_rem_brut, 2)-$facturation->ptardanza-$facturation->pinasist=round($pinasist, 2);
				$facturation->ptot_rem_afec=$ptot_rem_afec;


				$facturation->afp_id= $facturation['afp_id'];			
				$facturation->afp_name= $facturation['afp_name'];			
                $facturation->afp_base = $facturation['afp_base'];
                $facturation->afp_com = $facturation['afp_com'];
                $facturation->afp_prima = $facturation['afp_prima'];

				$pafp_base=$facturation->afp_base *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp);
				$facturation->fafp_base=$pafp_base;

				$pafp_com=$facturation->afp_com *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp);
				$facturation->fafp_com=$pafp_com;

				$pafp_prima=$facturation->afp_prima *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp);
				$facturation->fafp_prima=$pafp_prima;


				$ptardanza=($facturation->sueldo/240)*($facturation->tardanzas/60);
				$facturation->ptardanza=round($ptardanza, 2);

				$pinasist=(($facturation->sueldo/30)+($facturation->sueldo/30)/30)*$facturation->sp_total;
				$facturation->pinasist=round($pinasist, 2);

				$total_desc=$facturation->fafp_base+$facturation->fafp_com+$facturation->fafp_prima+$facturation->ptardanza
				+$facturation->pinasist;

				$facturation->total_desc=$total_desc;














 













				






				




			    
                $facturation->total_desc = $facturation['total_desc'];
                $facturation->salud = $facturation['salud'];
                $facturation->sctr = $facturation['sctr'];
                $facturation->noc_25 = $facturation['noc_25'];
                $facturation->noc_35 = $facturation['noc_35'];
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
			$sheet->setCellValue('E3', 'FECHA DE INGRESO');
			$sheet->setCellValue('F3', 'FECHA DE CESE');
			$sheet->setCellValue('G3', 'ASISTENCIAS');
			$sheet->setCellValue('H3', 'DESCANSOS');
			$sheet->setCellValue('I3', 'FERIADOS');
			$sheet->setCellValue('J3', 'TRABAJO EN FERIADO');
			$sheet->setCellValue('K3', 'TRABAJO EN DESCANSO');
			$sheet->setCellValue('L3', 'COMPENSACIÓN VACACIONAL');
			$sheet->setCellValue('M3', 'TOTAL DIAS LABORADOS');
			$sheet->setCellValue('N3', 'TOTAL DIAS CÁLCULO');
			$sheet->setCellValue('O3', 'S.P. INCAP TEMPORAL');
			$sheet->setCellValue('P3', 'S.I. INCAP TEMPORAL');
			$sheet->setCellValue('Q3', 'PRE Y POST NATAL');
			$sheet->setCellValue('R3', 'TOTAL DIAS SUBSIDIADOS');
			$sheet->setCellValue('S3', 'S.P. FALTAS');
			$sheet->setCellValue('T3', 'S.P LICENCIA SIN GOCE DE HABER');
			$sheet->setCellValue('U3', 'S.P TOTAL');
			$sheet->setCellValue('V3', 'S.I DESCANSOS MEDICOS');
			$sheet->setCellValue('W3', 'DESCANSO VACACIONAL');
			$sheet->setCellValue('X3', 'LICENCIA CON GOCE DE HABER');
			$sheet->setCellValue('Y3', 'LICENCIA POR PATERNIDAD');
			$sheet->setCellValue('Z3', 'S.I TOTAL');
			$sheet->setCellValue('AA3', 'TOTAL DIAS NO LAB Y NO SUB');
			$sheet->setCellValue('AB3', 'TOTAL DIAS DEL MES');
			$sheet->setCellValue('AC3', 'TOTAL HRS LAB');
			$sheet->setCellValue('AD3', 'HR. EXTRA 25 %');
			$sheet->setCellValue('AE3', 'HR. EXTRA 35 %');
			$sheet->setCellValue('AF3', 'TARDANZAS');
			$sheet->setCellValue('AG3', 'BONO NOCT 25%');
		 	$sheet->setCellValue('AH3', 'BONO NOCT 35%');
			$sheet->setCellValue('AI3', 'SUELDO BASE');
			$sheet->setCellValue('AJ3', 'ASIG. FAMILIAR');
			$sheet->setCellValue('AK3', 'TOTAL HORAS EXTRAS AL 25 %');
			$sheet->setCellValue('AL3', 'TOTAL HORAS EXTRAS AL 35 %');
			$sheet->setCellValue('AM3', 'TRABAJO EN FERIADO O DESCANSO');
			$sheet->setCellValue('AN3', 'ALIMENTACION'); //otros
			$sheet->setCellValue('AO3', 'COMISIONES');//otros
			$sheet->setCellValue('AP3', 'MOVILIDAD');//otros
			$sheet->setCellValue('AQ3', 'COMPENSACIÓN VACACIONAL');
			$sheet->setCellValue('AR3', 'REMUNERACIÓN VACACIONAL');
			$sheet->setCellValue('AS3', 'ASIG. FAMILIAR');  //ASIGNACIONES
			$sheet->setCellValue('AT3', 'ASIG. POR CUMPLEAÑOS');
			$sheet->setCellValue('AU3', 'ASIG. POR NAC. DE HIJOS');
			$sheet->setCellValue('AV3', 'ASIG. POR FALLEC. DE FAMILIAR');
			$sheet->setCellValue('AX3', 'BONIF. POR PRODUCCIÓN, ALTURA'); //BONIFICAIONES
			$sheet->setCellValue('AY3', 'BON. EXTRAORDINARIA LEY 29351 Y 30334');
			$sheet->setCellValue('AZ3', 'BONIFICACIONES REGULARES');
			$sheet->setCellValue('BA3', 'GRAT. FIESTAS PATRIAS Y NAVIDAD LEY 29351 Y 30334'); // GRATIFICACIONES
			$sheet->setCellValue('BB3', 'GRAT. PROPORCIONAL');
			$sheet->setCellValue('BC3', 'INDEM. POR DESPIDO');// INDEMNIZACION
			$sheet->setCellValue('BD3', 'INDEM. POR RETENCIÓN DE CTS');		
            $sheet->setCellValue('BE3', 'BONO DE PRODUCTIVIDAD');//CONCEPTOS VARIOS
			$sheet->setCellValue('BF3', 'LIC. CON GOCE DE HABER');
			$sheet->setCellValue('BG3', 'SUBSIDIOS POR MATERNIDAD');
			$sheet->setCellValue('BH3', 'LIC. POR PATERNIDAD');
			$sheet->setCellValue('BI3', 'SUSP. IMPERFECTA OTROS');
			$sheet->setCellValue('BJ3', 'BONIFIC. NOCTURNA 35%');
			$sheet->setCellValue('BK3', 'TOTAL REMUNERACIÓN BRUTA');
		 	$sheet->setCellValue('BL3', 'ONP/AFP');  //APORTACIONES
            $sheet->setCellValue('BM3', 'ONP 13 %');
            $sheet->setCellValue('BN3', 'SPP-APORTE OBLIGATORIO');
			$sheet->setCellValue('BO3', 'SPP-COMISIÓN %');
			$sheet->setCellValue('BP3', 'SPP-PRIMA DE SEGURO');
			$sheet->setCellValue('BQ3', 'RENTA 5TA RETENCIONES');//por calcular
			$sheet->setCellValue('BR3', 'ADELANTOS'); // DESCUENTOS
			$sheet->setCellValue('BS3', 'DESCUENTO AUTORIZADO');
			$sheet->setCellValue('BT3', 'TARDANZA');
			$sheet->setCellValue('BU3', 'INASISTENCIAS');
			$sheet->setCellValue('BV3', 'TOTAL APORT. Y DSCTO');
			$sheet->setCellValue('BW3', 'NETO A PAGAR');
			$sheet->setCellValue('BX3', 'ESSALUD 9%');//EMPLEADOR
			$sheet->setCellValue('BY3', 'EPS');
			$sheet->setCellValue('BZ3', 'SCTR');
			$sheet->setCellValue('CA3', 'TOTAL APORTACIONES');
			$sheet->getStyle('A3:CA3')->applyFromArray([
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
				$sheet->setCellValue('E'.$row_number, $element->fecha_inicio);
				$sheet->setCellValue('E'.$row_number, $element->fecha_cese);
				$sheet->setCellValue('E'.$row_number, $element->asistencia);
				$sheet->setCellValue('F'.$row_number, $element->descanso);
				$sheet->setCellValue('G'.$row_number, $element->feriados);
				$sheet->setCellValue('G'.$row_number, $element->feriado_t);
				$sheet->setCellValue('G'.$row_number, $element->desc_t);
				$sheet->setCellValue('G'.$row_number, $element->comp_vac);
				$sheet->setCellValue('G'.$row_number, $element->dias_laborados);


				$sheet->setCellValue('G'.$row_number, $element->comp_he);
				$sheet->setCellValue('G'.$row_number, $element->comp_he);
	



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
	








