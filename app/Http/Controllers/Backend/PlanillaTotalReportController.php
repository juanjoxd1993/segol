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
use App\Benefit;
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
						->leftjoin('tasas', 'employees.afp_id', '=', 'tasas.id')
						->leftjoin('sctasas', 'employees.salud_id', '=', 'sctasas.id')
						->leftjoin('saludtasas', 'employees.sctr_id', '=', 'saludtasas.id')
						->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')					
						->where('asists.año', '=', $price_year)
			            ->where('asists.mes', '<=', $price_mes)			            
			            ->select('asists.id as planilla_id',
                        'asists.año as año',
                        'asists.mes as mes',
                        'asists.employ_id as employ_id',
						'employees.salud_id as salud_id',
						'employees.sctr_id as sctr_id',
						'saludtasas.salud_porc as salud_porc',
						'sctasas.sctr_porc as sctr_porc',
						'tasas.id as afp_id',
						'tasas.name as afp_name',
						'tasas.afp_base as afp_base',
						'tasas.afp_com as afp_com',
						'tasas.afp_prima as afp_prima',
						'cicles.id as ciclo_id',
						'cicles.dias as dias_mes',
						'cicles.laborables as dias_lab',
						'cicles.feriados as feriados',
						'cicles.domingos as descanso',
						'employees.first_name as employ_name',
						'employees.last_name as cargo',
						'employees.fecha_inicio as fecha_inicio',
						'employees.fecha_cese as fecha_cese',
						'asists.laborables as asistencia',
						'employees.sueldo as sueldo',
						'employees.asignacion_familiar as familiar',					
                        'employees.document_number as document_number')
			        
				
			
		//	->groupBy('sales.sale_date','business_unit_id')
            ->get();

			$response=[];

            
            $totals_sum_sueldo = 0;
			$totals_sum_familiar = 0;
            $totals_sum_ptot_rem_afec= 0;
		    $totals_sum_fafp_base = 0;
			$totals_sum_fafp_com = 0; 
		    $totals_sum_fafp_prima = 0;
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
				$facturation->descanso = $facturation['descanso'];
				$facturation->feriados = $facturation['feriado'];
				$facturation->dias_mes = $facturation['dias_mes'];
				$facturation->dias_lab = $facturation['dias_lab'];
				$facturation->sueldo = $facturation['sueldo'];
			    $facturation->familiar = $facturation['familiar'];
			
                
                if( $facturation->familiar > 0){
                    $facturation->asignacion = 'SI';   
                }
                else {
                    $facturation->asignacion = 'NO';
                }

				$feriado_t=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',1)
				->select('benefits.dias')
				->sum('benefits.dias');

				$facturation->feriado_t = $feriado_t;

				$natalidad=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',5)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->natalidad = $natalidad;

				$desc_vac=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',6)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->desc_vac = $desc_vac;

				$comp_vac=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',7)
				->select('benefits.dias')
				->sum('benefits.dias');

				$facturation->comp_vac = $comp_vac;

				$comp_he=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',8)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->comp_he = $comp_he;

				$lic_pag=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',9)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->lic_pag = $lic_pag;


				$paternidad=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',10)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->paternidad = $paternidad;
				$lic_sin=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',11)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->lic_sin = $lic_sin;
				$desc_t=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',2)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->desc_t = $desc_t;
				$descanso_med=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',3)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->descanso_med = $descanso_med;

				$incap_temp=Benefit::leftjoin('cicles', 'benefits.ciclo_id', '=', 'cicles.id')
				->leftjoin('employees', 'benefits.employ_id', '=', 'employees.id')
				->where('benefits.employ_id', $facturation->employ_id)
				->where('benefits.Benefit_id',4)
				->select('benefits.dias')
				->sum('benefits.dias');
				$facturation->incap_temp = $incap_temp;


				
				$tardanzas=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('asists.minutos_tarde')
				->sum('asists.minutos_tarde');
				$facturation->tardanzas = $tardanzas;


				$he_25=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('asists.horas_extra_25')
				->sum('asists.horas_extra_25');
				$facturation->he_25 = $he_25;

				$he_35=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('asists.horas_extra_35')
				->sum('asists.horas_extra_35');
				$facturation->he_35 = $he_35;
				
				$hn_25=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('asists.horas_noc_25')
				->sum('asists.horas_noc_25');
				$facturation->hn_25 = $hn_25;

				$hn_35=Asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
				->where('asists.employ_id', $facturation->employ_id)
				->select('asists.horas_noc_35')
				->sum('asists.horas_noc_35');
				$facturation->hn_35 = $hn_35;


				$dias_lab=$facturation->asistencia + $facturation->comp_vac;
				$facturation->dias_lab = $dias_lab;


				$dias_sub=$facturation->incap_temp + $facturation->natalidad;
				$facturation->dias_sub = $dias_sub;
				$sp_faltas=$facturation->dias_lab - $facturation->asistencia;
				$facturation->sp_faltas = $sp_faltas;
				$sp_total=$facturation->sp_faltas + $facturation->lic_sin;
				$facturation->sp_total = $sp_total;

				$si_total=$facturation->descanso_med + $facturation->desc_vac + $facturation->lic_pag + $facturation->paternidad;
				$facturation->si_total=$si_total;
				$dias_nolab=$facturation->sp_total+ $facturation->si_total;
				$facturation->dias_nolab = $dias_nolab;           

				$dias_calc=$facturation->dias_lab+$facturation->dias_sub+$facturation->dias_nolab;
                
				if($dias_calc == 31)
				{
					$dias_cal=30-$facturation->si_total-$facturation->dias_sub;
				}
				else
				{
					$dias_cal=$facturation->dias_lab-$facturation->dias_nolab;
				}

				$facturation->dias_calc = $dias_cal;

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


				$pinasist=(($facturation->sueldo/30)+($facturation->sueldo/30)/30)*$facturation->sp_total;
				$facturation->pinasist=round($pinasist, 2);

				$facturation->tot_rem_brut= round($tot_rem_brut, 2);

				$ptot_rem_afec= round($tot_rem_brut, 2)-$facturation->ptardanza-$facturation->pinasist;
				$facturation->ptot_rem_afec=$ptot_rem_afec;


				$facturation->afp_id= $facturation['afp_id'];			
				$facturation->afp_name= $facturation['afp_name'];			
                $facturation->afp_base = $facturation['afp_base'];
                $facturation->afp_com = $facturation['afp_com'];
                $facturation->afp_prima = $facturation['afp_prima'];

				$pafp_base=($facturation->afp_base *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp))/100;
				$facturation->fafp_base=round($pafp_base,2);

				$pafp_com=($facturation->afp_com *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp))/100;
				$facturation->fafp_com=round($pafp_com,2);

				$pafp_prima=($facturation->afp_prima *($facturation->ptot_rem_afec+$facturation->ppaternidad+$facturation->pincap_temp))/100;
				$facturation->fafp_prima=round($pafp_prima,2);


				$ptardanza=($facturation->sueldo/240)*($facturation->tardanzas/60);
				$facturation->ptardanza=round($ptardanza, 2);

				

				$total_desc=$facturation->fafp_base+$facturation->fafp_com+$facturation->fafp_prima+$facturation->ptardanza
				+$facturation->pinasist;

				$facturation->total_desc=$total_desc;


				$neto=$facturation->tot_rem_brut -$facturation->total_desc;
				$facturation->neto=round($neto, 2);
				$facturation->salud_id = $facturation['salud_id'];
                $facturation->sctr_id = $facturation['sctr_id'];
				$facturation->salud_porc = $facturation['salud_porc'];
                $facturation->sctr_porc = $facturation['sctr_porc'];
                $facturation->salud = round((($facturation->ptot_rem_afec*$facturation->salud_porc)/100), 2);
                $facturation->sctr = round((($facturation->ptot_rem_afec*$facturation->sctr_porc)/100), 2);
                $facturation->total_apor =$facturation->salud+$facturation->sctr;


			$totals_sum_sueldo += $facturation['sueldo'];
			$totals_sum_familiar += $facturation['familiar'];
            $totals_sum_ptot_rem_afec += $facturation['ptot_rem_afec'];
		    $totals_sum_fafp_base += $facturation['fafp_base'];
			$totals_sum_fafp_com += $facturation ['fafp_com'];
		    $totals_sum_fafp_prima += $facturation['fafp_prima'];
			$totals_sum_total_desc += $facturation['total_desc'];
			$totals_sum_neto += $facturation['neto'];
		    $totals_sum_salud += $facturation['salud'];
		    $totals_sum_sctr += $facturation['sctr'];
			$totals_sum_total_apor += $facturation ['total_apor'];
			
				$response[] = $facturation;

			}


				$totals = new stdClass();
				$totals->document_number = 'TOTAL';
				$totals->employ_name = '';
                $totals->cargo = '';
				$totals->fecha_inicio = '';
				$totals->fecha_cese = '';
				$totals->asistencia = '';
				$totals->descanso = '';
				$totals->feriados = '';
				$totals->feriado_t = '';
				$totals->desc_t = '';
				$totals->comp_vac = '';
				$totals->dias_lab = ''; 
				$totals->dias_calc = '';
				$totals->incap_temp = '';
				$totals->natalidad = '';
				$totals->dias_sub = '';
				$totals->sp_faltas = '';
				$totals->lic_sin = '';
				$totals->sp_total = '';	
				$totals->descanso_med = '';
				$totals->desc_vac = '';
				$totals->lic_pag = '';
				$totals->paternidad = '';
				$totals->si_total = '';
				$totals->dias_no_lab = '';
				$totals->dias_mes = '';
				$totals->horas_lab = '';
				$totals->he_25 = '';
				$totals->he_35 = '';
				$totals->tardanzas = '';
				$totals->hn_35 = '';
				$totals->sueldo = $totals_sum_sueldo;
				$totals->familiar = $totals_sum_familiar;
				$totals->phe_25 = '';
				$totals->phe_35 = '';
				$totals->p_desc_fer = '';
				//por asignar modulo
				/*
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';*/

				$totals->pcomp_vac = '';
				$totals->pdesc_vac = '';
				$totals->familiar = '';
				// por aisgnar modulo
				/*
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';
				$totals->al = '';*/


				$totals->plic_pag = '';
				$totals->pnatalidad = '';
				$totals->pincap_temp = '';
				$totals->pdescanso_med = '';
				$totals->ppaternidad = '';
				$totals->phn_35 = '';
				$totals->tot_rem_brut =  $totals_sum_ptot_rem_afec;
				$totals->ptot_rem_afec = '';
				$totals->afp_name = '';
				$totals->fafp_base =$totals_sum_fafp_base;
       			$totals->fafp_com =$totals_sum_fafp_com;
       			$totals->fafp_prima =$totals_sum_fafp_prima;
				//retenciones de quinta
				/*
				$totals->quincena = '';
				$totals->quincena = '';
				$totals->quincena = '';*/

				$totals->ptardanza = '';
				$totals->pinasist = '';
				$totals->total_desc =$totals_sum_total_desc;
				$totals->neto =$totals_sum_neto;
				$totals->salud =$totals_sum_salud;
				$totals->sctr =$totals_sum_sctr ;
				$totals->total_apor =$totals_sum_total_apor;
				

		

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:BY1');
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
			$sheet->setCellValue('O3', 'S.I. INCAP TEMPORAL');
			$sheet->setCellValue('P3', 'PRE Y POST NATAL');
			$sheet->setCellValue('Q3', 'TOTAL DIAS SUBSIDIADOS');
			$sheet->setCellValue('R3', 'S.P. FALTAS');
			$sheet->setCellValue('S3', 'S.P LICENCIA SIN GOCE DE HABER');
			$sheet->setCellValue('T3', 'S.P TOTAL');
			$sheet->setCellValue('U3', 'S.I DESCANSOS MEDICOS');
			$sheet->setCellValue('V3', 'DESCANSO VACACIONAL');
			$sheet->setCellValue('W3', 'LICENCIA CON GOCE DE HABER');
			$sheet->setCellValue('X3', 'LICENCIA POR PATERNIDAD');
			$sheet->setCellValue('Y3', 'S.I TOTAL');
			$sheet->setCellValue('Z3', 'TOTAL DIAS NO LAB Y NO SUB');
			$sheet->setCellValue('AA3', 'TOTAL DIAS DEL MES');
			$sheet->setCellValue('AB3', 'TOTAL HRS LAB');
			$sheet->setCellValue('AC3', 'HR. EXTRA 25 %');
			$sheet->setCellValue('AD3', 'HR. EXTRA 35 %');
			$sheet->setCellValue('AE3', 'TARDANZAS');
		 	$sheet->setCellValue('AF3', 'BONO NOCT 35%');
			$sheet->setCellValue('AG3', 'SUELDO BASE');
			$sheet->setCellValue('AH3', 'ASIG. FAMILIAR');
			$sheet->setCellValue('AI3', 'TOTAL HORAS EXTRAS AL 25 %');
			$sheet->setCellValue('AJ3', 'TOTAL HORAS EXTRAS AL 35 %');
			$sheet->setCellValue('AK3', 'TRABAJO EN FERIADO O DESCANSO');
			$sheet->setCellValue('AL3', 'ALIMENTACION'); //otros
			$sheet->setCellValue('AM3', 'COMISIONES');//otros
			$sheet->setCellValue('AN3', 'MOVILIDAD');//otros
			$sheet->setCellValue('AO3', 'COMPENSACIÓN VACACIONAL');
			$sheet->setCellValue('AP3', 'REMUNERACIÓN VACACIONAL');
			$sheet->setCellValue('AQ3', 'ASIG. FAMILIAR');  //ASIGNACIONES
			$sheet->setCellValue('AR3', 'ASIG. POR CUMPLEAÑOS');
			$sheet->setCellValue('AS3', 'ASIG. POR NAC. DE HIJOS');
			$sheet->setCellValue('AT3', 'ASIG. POR FALLEC. DE FAMILIAR');
			$sheet->setCellValue('AU3', 'BONIF. POR PRODUCCIÓN, ALTURA'); //BONIFICAIONES
			$sheet->setCellValue('AV3', 'BON. EXTRAORDINARIA LEY 29351 Y 30334');
			$sheet->setCellValue('AW3', 'BONIFICACIONES REGULARES');
			$sheet->setCellValue('AX3', 'GRAT. FIESTAS PATRIAS Y NAVIDAD LEY 29351 Y 30334'); // GRATIFICACIONES
			$sheet->setCellValue('AY3', 'GRAT. PROPORCIONAL');
			$sheet->setCellValue('AZ3', 'INDEM. POR DESPIDO');// INDEMNIZACION
			$sheet->setCellValue('BA3', 'INDEM. POR RETENCIÓN DE CTS');		
            $sheet->setCellValue('BB3', 'BONO DE PRODUCTIVIDAD');//CONCEPTOS VARIOS
			$sheet->setCellValue('BC3', 'LIC. CON GOCE DE HABER');
			$sheet->setCellValue('BD3', 'SUBSIDIOS POR MATERNIDAD');
			$sheet->setCellValue('BE3', 'SUBSIDIOS DE INCAP. POT ENFERMEDAD');
			$sheet->setCellValue('BF3', 'DESCANSO MEDICO');
			$sheet->setCellValue('BG3', 'LIC. POR PATERNIDAD');
			$sheet->setCellValue('BH3', 'BONIFIC. NOCTURNA 35%');
			$sheet->setCellValue('BI3', 'TOTAL REMUNERACIÓN BRUTA');
			$sheet->setCellValue('BJ3', 'INGRESOS AFECTOS');
		 	$sheet->setCellValue('BK3', 'ONP/AFP');  //APORTACION
            $sheet->setCellValue('BL3', 'ONP 13 / SPP-APORTE OBLIGATORIO');
			$sheet->setCellValue('BM3', 'SPP-COMISIÓN %');
			$sheet->setCellValue('BN3', 'SPP-PRIMA DE SEGURO');
			$sheet->setCellValue('BO3', 'RENTA 5TA RETENCIONES');//por calcular
			$sheet->setCellValue('BP3', 'ADELANTOS'); // DESCUENTOS
			$sheet->setCellValue('BQ3', 'DESCUENTO AUTORIZADO');
			$sheet->setCellValue('BR3', 'TARDANZA');
			$sheet->setCellValue('BS3', 'INASISTENCIAS');
			$sheet->setCellValue('BT3', 'TOTAL APORT. Y DSCTO');
			$sheet->setCellValue('BU3', 'NETO A PAGAR');
			$sheet->setCellValue('BV3', 'ESSALUD 9%');//EMPLEADOR
			$sheet->setCellValue('BW3', 'EPS');
			$sheet->setCellValue('BX3', 'SCTR');
			$sheet->setCellValue('BY3', 'TOTAL APORTACIONES');
			$sheet->getStyle('A3:BY3')->applyFromArray([
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
				$sheet->setCellValue('F'.$row_number, $element->fecha_cese);
				$sheet->setCellValue('G'.$row_number, $element->asistencia);
				$sheet->setCellValue('H'.$row_number, $element->descanso);
				$sheet->setCellValue('I'.$row_number, $element->feriados);
				$sheet->setCellValue('J'.$row_number, $element->feriado_t);
				$sheet->setCellValue('K'.$row_number, $element->desc_t);
				$sheet->setCellValue('L'.$row_number, $element->comp_vac);
				$sheet->setCellValue('M'.$row_number, $element->dias_lab); 
				$sheet->setCellValue('N'.$row_number, $element->dias_calc);
				$sheet->setCellValue('O'.$row_number, $element->incap_temp);
				$sheet->setCellValue('P'.$row_number, $element->natalidad);
				$sheet->setCellValue('Q'.$row_number, $element->dias_sub);
				$sheet->setCellValue('R'.$row_number, $element->sp_faltas);
				$sheet->setCellValue('S'.$row_number, $element->lic_sin);
				$sheet->setCellValue('T'.$row_number, $element->sp_total);	
				$sheet->setCellValue('U'.$row_number, $element->descanso_med);
				$sheet->setCellValue('V'.$row_number, $element->desc_vac);
				$sheet->setCellValue('W'.$row_number, $element->lic_pag);
				$sheet->setCellValue('X'.$row_number, $element->paternidad);
				$sheet->setCellValue('Y'.$row_number, $element->si_total);
				$sheet->setCellValue('Z'.$row_number, $element->dias_no_lab);
				$sheet->setCellValue('AA'.$row_number, $element->dias_mes);
				$sheet->setCellValue('AB'.$row_number, $element->horas_lab);
				$sheet->setCellValue('AC'.$row_number, $element->he_25);
				$sheet->setCellValue('AD'.$row_number, $element->he_35);
				$sheet->setCellValue('AE'.$row_number, $element->tardanzas);
				$sheet->setCellValue('AF'.$row_number, $element->hn_35);
				$sheet->setCellValue('AG'.$row_number, $element->sueldo);
				$sheet->setCellValue('AH'.$row_number, $element->familiar);
				$sheet->setCellValue('AI'.$row_number, $element->phe_25);
				$sheet->setCellValue('AJ'.$row_number, $element->phe_35);
				$sheet->setCellValue('AK'.$row_number, $element->p_desc_fer);
				//por asignar modulo
				/*
				$sheet->setCellValue('AL'.$row_number, $element->al);
				$sheet->setCellValue('AM'.$row_number, $element->al);
				$sheet->setCellValue('AN'.$row_number, $element->al);*/

				$sheet->setCellValue('AO'.$row_number, $element->pcomp_vac);
				$sheet->setCellValue('AP'.$row_number, $element->pdesc_vac);
				$sheet->setCellValue('AQ'.$row_number, $element->familiar);
				// por aisgnar modulo
				/*
				$sheet->setCellValue('AR'.$row_number, $element->al);
				$sheet->setCellValue('AS'.$row_number, $element->al);
				$sheet->setCellValue('AT'.$row_number, $element->al);
				$sheet->setCellValue('AU'.$row_number, $element->al);
				$sheet->setCellValue('AV'.$row_number, $element->al);
				$sheet->setCellValue('AW'.$row_number, $element->al);
				$sheet->setCellValue('AX'.$row_number, $element->al);
				$sheet->setCellValue('AY'.$row_number, $element->al);
				$sheet->setCellValue('AZ'.$row_number, $element->al);
				$sheet->setCellValue('BA'.$row_number, $element->al);
				$sheet->setCellValue('BB'.$row_number, $element->al);*/

              /*  if($element->plic_pag == 0){
					$sheet = $spreadsheet->getActiveSheet()->getColumnDimension('BC')->setVisible(false);
				}
				else{*/
					$sheet->setCellValue('BC'.$row_number, $element->plic_pag);
		//			}
				$sheet->setCellValue('BD'.$row_number, $element->pnatalidad);
				$sheet->setCellValue('BE'.$row_number, $element->pincap_temp);
				$sheet->setCellValue('BF'.$row_number, $element->pdescanso_med);

				$sheet->setCellValue('BG'.$row_number, $element->ppaternidad);
				$sheet->setCellValue('BH'.$row_number, $element->phn_35);

			/*	if($element->tot_rem_brut == 0){
					$sheet = $spreadsheet->getActiveSheet()->getColumnDimension('BI')->setVisible(false);
				}
				else{*/
				$sheet->setCellValue('BI'.$row_number, $element->tot_rem_brut);
		//		}

				$sheet->setCellValue('BJ'.$row_number, $element->ptot_rem_afec);
				$sheet->setCellValue('BK'.$row_number, $element->afp_name);
				$sheet->setCellValue('BL'.$row_number, $element->fafp_base);
				$sheet->setCellValue('BM'.$row_number, $element->fafp_com);
				$sheet->setCellValue('BN'.$row_number, $element->fafp_prima);
				//retenciones de quinta
				/*
				$sheet->setCellValue('BO'.$row_number, $element->quincena);
				$sheet->setCellValue('BP'.$row_number, $element->quincena);
				$sheet->setCellValue('BQ'.$row_number, $element->quincena);*/

				$sheet->setCellValue('BR'.$row_number, $element->ptardanza);
				$sheet->setCellValue('BS'.$row_number, $element->pinasist);
				$sheet->setCellValue('BT'.$row_number, $element->total_desc); 
				$sheet->setCellValue('BU'.$row_number, $element->neto);
				$sheet->setCellValue('BV'.$row_number, $element->salud);
				$sheet->setCellValue('BX'.$row_number, $element->sctr);
				$sheet->setCellValue('BY'.$row_number, $element->total_apor);
				
							
                $sheet->getStyle('BV'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('BX'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('BY'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
			$sheet->getColumnDimension('AI')->setAutoSize(true);
			$sheet->getColumnDimension('AJ')->setAutoSize(true);
			$sheet->getColumnDimension('AK')->setAutoSize(true);
			$sheet->getColumnDimension('AL')->setAutoSize(true);
			$sheet->getColumnDimension('AM')->setAutoSize(true);
			$sheet->getColumnDimension('AN')->setAutoSize(true);
			$sheet->getColumnDimension('AO')->setAutoSize(true);
			$sheet->getColumnDimension('AP')->setAutoSize(true);
			$sheet->getColumnDimension('AQ')->setAutoSize(true);
			$sheet->getColumnDimension('AR')->setAutoSize(true);
			$sheet->getColumnDimension('AS')->setAutoSize(true);
			$sheet->getColumnDimension('AT')->setAutoSize(true);
			$sheet->getColumnDimension('AU')->setAutoSize(true);
			$sheet->getColumnDimension('AV')->setAutoSize(true);
			$sheet->getColumnDimension('AW')->setAutoSize(true);
			$sheet->getColumnDimension('AX')->setAutoSize(true);
			$sheet->getColumnDimension('AY')->setAutoSize(true);
			$sheet->getColumnDimension('AZ')->setAutoSize(true);
			$sheet->getColumnDimension('BA')->setAutoSize(true);
			$sheet->getColumnDimension('BB')->setAutoSize(true);
			$sheet->getColumnDimension('BC')->setAutoSize(true);
			 


			

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}
	








