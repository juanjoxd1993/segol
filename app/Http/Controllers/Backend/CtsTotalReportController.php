<?php

namespace App\Http\Controllers\Backend;


use App\Services\ExcelStyleService;
use App\Employee;
use App\Client;
use App\Company;
use App\Asist;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Planilla;
use App\Cicle;
use App\Recursive;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Color;
use stdClass;


class CtsTotalReportController extends Controller
{
    public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$ciclos = Cicle::select('id', 'año', 'mes')->whereIn('mes', [5, 11])->get();

		return view('backend.cts_total_report')->with(compact('companies', 'current_date', 'ciclos'));
	}

	public function validateForm() {
		$messages = [
			'ciclo_id.required'     => 'Debe seleccionar un Ciclo.',
		];

		$rules = [
			'ciclo_id'   => 'required', 
			
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

	    $ciclo_id = request('model.ciclo_id');
    
        // Obtenemos el año y el mes del ciclo si ciclo_id está presente
        $ciclo = null;
        if ($ciclo_id) {
            $ciclo = Cicle::find($ciclo_id);
        }
		$price_mes = $ciclo->mes;
		$price_year = $ciclo->año;

	                           


                 $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
                    ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
                    ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
					->leftjoin('banks', 'employees.bank_id', '=', 'banks.id')
                    ->select('asists.id', 'asists.employ_id', 
					  'asists.ciclo_id',
					  'asists.horas_tarde', 
					  'asists.minutos_tarde', 
                      'employees.first_name',
					  'employees.last_name',
					  'employees.document_number',
					  'employees.fecha_inicio',
					  'employees.sueldo',
					  'employees.cts',
					  'employees.cci',
					  'employees.grati',
					  'employees.cuenta',
					  'employees.bank_id',
					  'employees.asignacion_id',
					  'banks.name as bank_name',
					  'areas.name as area_name',
					  'cicles.fecha_calc as inicio_cts',
					  'cicles.fecha_final as final_cts',
					  'cicles.fecha_inicio as fecha_ini')
              //      ->where('employees.company_id', $company_id)
                    ->where('asists.año', '=', $price_year)
                    ->where('asists.mes', '=', $price_mes)


			->orderBy('employees.first_name')
            ->get();

			$response=[];

           /* 
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
			$totals_sum_total_apor= 0; */
			

			foreach ($elements as $facturation) {

				$facturation->document_number = $facturation['document_number'];
				$facturation->employ_name = $facturation['first_name'];
				$facturation->total_cts = $facturation['cts'];
			    $facturation->cargo = $facturation['area_name'];
				$facturation->bank_name = $facturation['bank_name'];
			    $facturation->cuenta = $facturation['cuenta'];
			    $facturation->cci = $facturation['cci'];
				$facturation->area_name = $facturation['last_name'];
				$facturation->moneda = 'MN';




				$facturation->asignacion_id = $facturation['asignacion_id'];

				             
                if( $facturation->asignacion_id > 0){
					$facturation->familiar = 102.5; 
                }
                else {
					$facturation->familiar = 0;
                }

				$fecha_inicio = $facturation['fecha_inicio'];
				$facturation->grati = $facturation['grati'];
				$ffecha_inicio=Carbon::parse($fecha_inicio);

                
				//OBTENIENDO FECHA DE CALCULO
				$mes_calc = $facturation['inicio_cts']; //mayo- nov
				$fmes_calc=Carbon::parse($mes_calc);
				$año_calc = $facturation['final_cts'];//octubre- abr
				$faño_calc=Carbon::parse($año_calc);
				$fecha_ini = $facturation['fecha_ini'];//primer día del ciclo seleccionado
				$ffecha_ini=Carbon::parse($fecha_ini);



                if($ffecha_inicio > $fmes_calc)
				{
					$diasDiferencia = $faño_calc->diffInDays($ffecha_inicio);
				}

				else{
					$diasDiferencia = 180;
				}

				$mes_date=$ffecha_ini;

				$meses=$diasDiferencia/30;
				$meses=(int)$meses;
				$facturation->meses = $meses;

				$meses_calc=$facturation->meses*30;
				$facturation->meses_calc = $meses_calc;

				$dias_calc=$diasDiferencia-$facturation->meses_calc;
				$facturation->dias_calc = $dias_calc;


				$dias_total = $diasDiferencia+$dias_calc;
				$facturation->dias_total = $dias_total;			


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

			
				//comisiones
				$com_25=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado)
				->where('recursives.año', $año_pasado)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_25 = $com_25;
				$pcom_25=$facturation->com_25;
				$facturation->pcom_25 = round($pcom_25, 2);
				
				$com_252=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado2)
				->where('recursives.año', $año_pasado2)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_252 = $com_252;


				$pcom_252=$facturation->com_252;
				$facturation->pcom_252 = round($pcom_252, 2);
				

				$com_253=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado3)
				->where('recursives.año', $año_pasado3)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_253 = $com_253;
				$pcom_253=$facturation->com_253;
				$facturation->pcom_253 = round($pcom_253, 2);
			
				$com_254=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado4)
				->where('recursives.año', $año_pasado4)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_254 = $com_254;


				$pcom_254=$facturation->com_254;
				$facturation->pcom_254 = round($pcom_254, 2);
				
				$com_255=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado5)
				->where('recursives.año', $año_pasado5)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_255 = $com_255;
				$pcom_255=$facturation->com_255;
				$facturation->pcom_255 = round($pcom_255, 2);
			

				$com_256=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
				->where('recursives.employ_id', $facturation->employ_id)
				->where('recursives.mes', $mes_pasado6)
				->where('recursives.año', $año_pasado6)
				->where('recursives.recursive_id',6)
				->select('recursives.amount')
				->sum('recursives.amount');

				$facturation->com_256 = $com_256;
				$pcom_256=$facturation->com_256;
				$facturation->pcom_256 = round($pcom_256, 2);
				
		    $facturation->com_1=$facturation->pcom_25;
			$facturation->com_2=$facturation->pcom_252;
			$facturation->com_3=$facturation->pcom_253;
			$facturation->com_4=$facturation->pcom_254;
			$facturation->com_5=$facturation->pcom_255;
			$facturation->com_6=$facturation->pcom_256;





	

					//comisiones
					$bon_25=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado)
					->where('recursives.año', $año_pasado)
					->whereIn('recursives.recursive_id', [1,2])
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_25 = $bon_25;
					$pbon_25=$facturation->bon_25;
					$facturation->pbon_25 = round($pbon_25, 2);
					
					$bon_252=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado2)
					->where('recursives.año', $año_pasado2)
					->whereIn('recursives.recursive_id', [1,2])
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_252 = $bon_252;
	
	
					$pbon_252=$facturation->bon_252;
					$facturation->pbon_252 = round($pbon_252, 2);
					
	
					$bon_253=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado3)
					->where('recursives.año', $año_pasado3)
					->whereIn('recursives.recursive_id', [1,2])
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_253 = $bon_253;
					$pbon_253=$facturation->bon_253;
					$facturation->pbon_253 = round($pbon_253, 2);
				
					$bon_254=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado4)
					->where('recursives.año', $año_pasado4)
					->where('recursives.recursive_id',6)
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_254 = $bon_254;
	
	
					$pbon_254=$facturation->bon_254;
					$facturation->pbon_254 = round($pbon_254, 2);
					
					$bon_255=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado5)
					->where('recursives.año', $año_pasado5)
					->whereIn('recursives.recursive_id', [1,2])
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_255 = $bon_255;
					$pbon_255=$facturation->bon_255;
					$facturation->pbon_255 = round($pbon_255, 2);
				
	
					$bon_256=recursive::leftjoin('employees', 'recursives.employ_id', '=', 'employees.id')
					->where('recursives.employ_id', $facturation->employ_id)
					->where('recursives.mes', $mes_pasado6)
					->where('recursives.año', $año_pasado6)
					->whereIn('recursives.recursive_id', [1,2])
					->select('recursives.amount')
					->sum('recursives.amount');
	
					$facturation->bon_256 = $bon_256;
					$pbon_256=$facturation->bon_256;
					$facturation->pbon_256 = round($pbon_256, 2);
					
				$facturation->bon_1=$facturation->pbon_25;
				$facturation->bon_2=$facturation->pbon_252;
				$facturation->bon_3=$facturation->pbon_253;
				$facturation->bon_4=$facturation->pbon_254;
				$facturation->bon_5=$facturation->pbon_255;
				$facturation->bon_6=$facturation->pbon_256;


				//comisiones
					$don_25=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado)
					->where('asists.año', $año_pasado)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_25 = $don_25;
					$pdon_25=$facturation->don_25;
					$facturation->pdon_25 = round($pdon_25, 2);
					
					$don_252=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado2)
					->where('asists.año', $año_pasado2)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_252 = $don_252;
	
	
					$pdon_252=$facturation->don_252;
					$facturation->pdon_252 = round($pdon_252, 2);
					
	
					$don_253=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado3)
					->where('asists.año', $año_pasado3)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_253 = $don_253;
					$pdon_253=$facturation->don_253;
					$facturation->pdon_253 = round($pdon_253, 2);
				
					$don_254=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado4)
					->where('asists.año', $año_pasado4)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_254 = $don_254;
	
	
					$pdon_254=$facturation->don_254;
					$facturation->pdon_254 = round($pdon_254, 2);
					
					$don_255=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado5)
					->where('asists.año', $año_pasado5)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_255 = $don_255;
					$pdon_255=$facturation->don_255;
					$facturation->pdon_255 = round($pdon_255, 2);
				
	
					$don_256=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado6)
					->where('asists.año', $año_pasado6)
					->where('asists.domingos','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->don_256 = $don_256;
					$pdon_256=$facturation->don_256;
					$facturation->pdon_256 = round($pdon_256, 2);
					
				$facturation->don_1=$facturation->pdon_25;
				$facturation->don_2=$facturation->pdon_252;
				$facturation->don_3=$facturation->pdon_253;
				$facturation->don_4=$facturation->pdon_254;
				$facturation->don_5=$facturation->pdon_255;
				$facturation->don_6=$facturation->pdon_256;
				


				$fer_25=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado)
					->where('asists.año', $año_pasado)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_25 = $fer_25;
					$pfer_25=$facturation->fer_25;
					$facturation->pfer_25 = round($pfer_25, 2);
					
					$fer_252=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado2)
					->where('asists.año', $año_pasado2)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_252 = $fer_252;
	
	
					$pfer_252=$facturation->fer_252;
					$facturation->pfer_252 = round($pfer_252, 2);
					
	
					$fer_253=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado3)
					->where('asists.año', $año_pasado3)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_253 = $fer_253;
					$pfer_253=$facturation->fer_253;
					$facturation->pfer_253 = round($pfer_253, 2);
				
					$fer_254=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado4)
					->where('asists.año', $año_pasado4)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_254 = $fer_254;
	
	
					$pfer_254=$facturation->fer_254;
					$facturation->pfer_254 = round($pfer_254, 2);
					
					$fer_255=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado5)
					->where('asists.año', $año_pasado5)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_255 = $fer_255;
					$pfer_255=$facturation->fer_255;
					$facturation->pfer_255 = round($pfer_255, 2);
				
	
					$fer_256=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado6)
					->where('asists.año', $año_pasado6)
					->where('asists.feriados','>',0)
					->select('asists.paid_fer')
					->sum('asists.paid_fer');
	
					$facturation->fer_256 = $fer_256;
					$pfer_256=$facturation->fer_256;
					$facturation->pfer_256 = round($pfer_256, 2);
					
				$facturation->fer_1=$facturation->pfer_25;
				$facturation->fer_2=$facturation->pfer_252;
				$facturation->fer_3=$facturation->pfer_253;
				$facturation->fer_4=$facturation->pfer_254;
				$facturation->fer_5=$facturation->pfer_255;
				$facturation->fer_6=$facturation->pfer_256;
				
				
				$noc_25=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado)
					->where('asists.año', $año_pasado)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_25 = $noc_25;
					$pnoc_25=$facturation->noc_25;
					$facturation->pnoc_25 = round($pnoc_25, 2);
					
					$noc_252=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado2)
					->where('asists.año', $año_pasado2)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_252 = $noc_252;
	
	
					$pnoc_252=$facturation->noc_252;
					$facturation->pnoc_252 = round($pnoc_252, 2);
					
	
					$noc_253=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado3)
					->where('asists.año', $año_pasado3)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_253 = $noc_253;
					$pnoc_253=$facturation->noc_253;
					$facturation->pnoc_253 = round($pnoc_253, 2);
				
					$noc_254=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado4)
					->where('asists.año', $año_pasado4)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_254 = $noc_254;
	
	
					$pnoc_254=$facturation->noc_254;
					$facturation->pnoc_254 = round($pnoc_254, 2);
					
					$noc_255=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado5)
					->where('asists.año', $año_pasado5)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_255 = $noc_255;
					$pnoc_255=$facturation->noc_255;
					$facturation->pnoc_255 = round($pnoc_255, 2);
				
	
					$noc_256=asist::leftjoin('employees', 'asists.employ_id', '=', 'employees.id')
					->where('asists.employ_id', $facturation->employ_id)
					->where('asists.mes', $mes_pasado6)
					->where('asists.año', $año_pasado6)
					->where('asists.horas_noc_25','>',0)
					->select('asists.paid_dom')
					->sum('asists.paid_dom');
	
					$facturation->noc_256 = $noc_256;
					$pnoc_256=$facturation->noc_256;
					$facturation->pnoc_256 = round($pnoc_256, 2);
					
				$facturation->noc_1=$facturation->pnoc_25;
				$facturation->noc_2=$facturation->pnoc_252;
				$facturation->noc_3=$facturation->pnoc_253;
				$facturation->noc_4=$facturation->pnoc_254;
				$facturation->noc_5=$facturation->pnoc_255;
				$facturation->noc_6=$facturation->pnoc_256;
				



				


                
              

	           if($mes_pasado == 01)
		       {
				$monthName='Ene';
			   }
			   else if ($mes_pasado == 02)
		       {
				$monthName='Feb';
			   }
			   else if ($mes_pasado == 03)
		       {
				$monthName='Mar';
			   }

			   else if ($mes_pasado == 04)
		       {
				$monthName='Abr';
			   }

			   else if ($mes_pasado == 05)
		       {
				$monthName='May';
			   }
			   else if ($mes_pasado == 06)
		       {
				$monthName='Jun';
			   }
			   else if ($mes_pasado == 07)
		       {
				$monthName='Jul';
			   }
			    else if ($mes_pasado == '08')
		       {
				$monthName='Ago';
			   }
			   else if ($mes_pasado == '09')
		       {
				$monthName='Sep';
			   }
			   else if ($mes_pasado == 10)
		       {
				$monthName='Oct';
			   }
			   else if ($mes_pasado == 11)
		       {
				$monthName='Nov';
			   }
			   else if ($mes_pasado == 12)
		       {
				$monthName='Dic';
			   }


			   if($mes_pasado2== 01)
		       {
				$monthName2='Ene';
			   }
			   else if ($mes_pasado2== 02)
		       {
				$monthName2='Feb';
			   }
			   else if ($mes_pasado2== 03)
		       {
				$monthName2='Mar';
			   }

			   else if ($mes_pasado2== 04)
		       {
				$monthName2='Abr';
			   }

			   else if ($mes_pasado2== 05)
		       {
				$monthName2='May';
			   }
			   else if ($mes_pasado2== 06)
		       {
				$monthName2='Jun';
			   }
			   else if ($mes_pasado2== 07)
		       {
				$monthName2='Jul';
			   }
			   else if ($mes_pasado2== '08')
		       {
				$monthName2='Ago';
			   }
			   else if ($mes_pasado2== '09')
		       {
				$monthName2='Sep';
			   }
			   else if ($mes_pasado2== 10)
		       {
				$monthName2='Oct';
			   }
			   else if ($mes_pasado2== 11)
		       {
				$monthName2='Nov';
			   }
			   else if ($mes_pasado2== 12)
		       {
				$monthName2='Dic';
			   }


			   if($mes_pasado3== 01)
		       {
				$monthName3='Ene';
			   }
			   else if ($mes_pasado3== 02)
		       {
				$monthName3='Feb';
			   }
			   else if ($mes_pasado3== 03)
		       {
				$monthName3='Mar';
			   }

			   else if ($mes_pasado3== 04)
		       {
				$monthName3='Abr';
			   }

			   else if ($mes_pasado3== 05)
		       {
				$monthName3='May';
			   }
			   else if ($mes_pasado3== 06)
		       {
				$monthName3='Jun';
			   }
			   else if ($mes_pasado3== 07)
		       {
				$monthName3='Jul';
			   }
			   else if ($mes_pasado3== '08')
		       {
				$monthName3='Ago';
			   }
			   else if ($mes_pasado3== '09')
		       {
				$monthName3='Sep';
			   }
			   else if ($mes_pasado3== 10)
		       {
				$monthName3='Oct';
			   }
			   else if ($mes_pasado3== 11)
		       {
				$monthName3='Nov';
			   }
			   else if ($mes_pasado3== 12)
		       {
				$monthName3='Dic';
			   }


			   if($mes_pasado== 01)
		       {
				$monthName3='Ene';
			   }
			   else if ($mes_pasado== 02)
		       {
				$monthName3='Feb';
			   }
			   else if ($mes_pasado== 03)
		       {
				$monthName3='Mar';
			   }

			   else if ($mes_pasado== 04)
		       {
				$monthName3='Abr';
			   }

			   else if ($mes_pasado== 05)
		       {
				$monthName3='May';
			   }
			   else if ($mes_pasado== 06)
		       {
				$monthName3='Jun';
			   }
			   else if ($mes_pasado== 07)
		       {
				$monthName3='Jul';
			   }
			   else if ($mes_pasado== '08')
		       {
				$monthName3='Ago';
			   }
			   else if ($mes_pasado== '09')
		       {
				$monthName3='Sep';
			   }
			   else if ($mes_pasado== 10)
		       {
				$monthName3='Oct';
			   }
			   else if ($mes_pasado== 11)
		       {
				$monthName3='Nov';
			   }
			   else if ($mes_pasado== 12)
		       {
				$monthName3='Dic';
			   }


			   if($mes_pasado4== 01)
		       {
				$monthName4='Ene';
			   }
			   else if ($mes_pasado4== 02)
		       {
				$monthName4='Feb';
			   }
			   else if ($mes_pasado4== 03)
		       {
				$monthName4='Mar';
			   }

			   else if ($mes_pasado4== 04)
		       {
				$monthName4='Abr';
			   }

			   else if ($mes_pasado4== 05)
		       {
				$monthName4='May';
			   }
			   else if ($mes_pasado4== 06)
		       {
				$monthName4='Jun';
			   }
			   else if ($mes_pasado4== 07)
		       {
				$monthName4='Jul';
			   }
			   else if ($mes_pasado4 == '08')
		       {
				$monthName4='Ago';
			   }
			   else if ($mes_pasado4== '09')
		       {
				$monthName4='Sep';
			   }
			   else if ($mes_pasado4== 10)
		       {
				$monthName4='Oct';
			   }
			   else if ($mes_pasado4== 11)
		       {
				$monthName4='Nov';
			   }
			   else if ($mes_pasado4== 12)
		       {
				$monthName4='Dic';
			   }

			   if($mes_pasado5== 01)
		       {
				$monthName5='Ene';
			   }
			   else if ($mes_pasado5== 02)
		       {
				$monthName5='Feb';
			   }
			   else if ($mes_pasado5== 03)
		       {
				$monthName5='Mar';
			   }

			   else if ($mes_pasado5== 04)
		       {
				$monthName5='Abr';
			   }

			   else if ($mes_pasado5== 05)
		       {
				$monthName5='May';
			   }
			   else if ($mes_pasado5== 06)
		       {
				$monthName5='Jun';
			   }
			   else if ($mes_pasado5== 07)
		       {
				$monthName5='Jul';
			   }
			   else if ($mes_pasado5== '08')
		       {
				$monthName5='Ago';
			   }
			   else if ($mes_pasado5== '09')
		       {
				$monthName5='Sep';
			   }
			   else if ($mes_pasado5== 10)
		       {
				$monthName5='Oct';
			   }
			   else if ($mes_pasado5== 11)
		       {
				$monthName5='Nov';
			   }
			   else if ($mes_pasado5== 12)
		       {
				$monthName5='Dic';
			   }


			   if($mes_pasado6== 01)
		       {
				$monthName6='Ene';
			   }
			   else if ($mes_pasado6== 02)
		       {
				$monthName6='Feb';
			   }
			   else if ($mes_pasado6== 03)
		       {
				$monthName6='Mar';
			   }

			   else if ($mes_pasado6== 04)
		       {
				$monthName6='Abr';
			   }

			   else if ($mes_pasado6== 05)
		       {
				$monthName6='May';
			   }
			   else if ($mes_pasado6== 06)
		       {
				$monthName6='Jun';
			   }
			   else if ($mes_pasado6== 07)
		       {
				$monthName6='Jul';
			   }
			   else if ($mes_pasado6== '08')
		       {
				$monthName6='Ago';
			   }
			   else if ($mes_pasado6== '09')
		       {
				$monthName6='Sep';
			   }
			   else if ($mes_pasado6== 10)
		       {
				$monthName6='Oct';
			   }
			   else if ($mes_pasado6== 11)
		       {
				$monthName6='Nov';
			   }
			   else if ($mes_pasado6== 12)
		       {
				$monthName6='Dic';
			   }

			   else{
				$monthName  = date("F", strtotime ($mes_pasado));
                $monthName2 = date("F", strtotime ($mes_pasado2));
                $monthName3 = date("F", strtotime ($mes_pasado3));
                $monthName4 = date("F", strtotime ($mes_pasado4));
                $monthName5 = date("F", strtotime ($mes_pasado5));
                $monthName6 = date("F", strtotime ($mes_pasado6)); 
			   }
 
                
			   
	

			    $facturation->sueldo = $facturation['sueldo'];
			    $facturation->familiar = $facturation['familiar'];


				$gratdiv=$facturation->grati/6;
				$facturation->gratdiv=$gratdiv;


				$prom_he=($facturation->he_1+$facturation->he_2+$facturation->he_3+$facturation->he_4+$facturation->he_5+$facturation->he_6)/6;
				$facturation->prom_he=round($prom_he,2);


				$prom_bon=($facturation->bon_1+$facturation->bon_2+$facturation->bon_3+$facturation->bon_4+$facturation->bon_5+$facturation->bon_6)/6;
				$facturation->prom_bon=round($prom_bon,2);

				$prom_com=($facturation->com_1+$facturation->com_2+$facturation->com_3+$facturation->com_4+$facturation->com_5+$facturation->com_6)/6;
				$facturation->prom_com=round($prom_com,2);

				$prom_don=($facturation->don_1+$facturation->don_2+$facturation->don_3+$facturation->don_4+$facturation->don_5+$facturation->don_6)/6;
				$facturation->prom_don=round($prom_don,2);

				$prom_he=($facturation->he_1+$facturation->he_2+$facturation->he_3+$facturation->he_4+$facturation->he_5+$facturation->he_6)/6;
				$facturation->prom_he=round($prom_he,2);

				$prom_fer=($facturation->fer_1+$facturation->fer_2+$facturation->fer_3+$facturation->fer_4+$facturation->fer_5+$facturation->fer_6)/6;
				$facturation->prom_fer=round($prom_fer,2);

				$prom_noc=($facturation->noc_1+$facturation->noc_2+$facturation->noc_3+$facturation->noc_4+$facturation->noc_5+$facturation->noc_6)/6;
				$facturation->prom_noc=round($prom_noc,2);


				$total_comp= $facturation->sueldo+$facturation->familiar+$facturation->gratdiv+$facturation->prom_he+$facturation->prom_bom+$facturation->prom_com+$facturation->prom_don+$facturation->prom_fer+$facturation->prom_noc;
				$facturation->total_comp=round($total_comp,2);
				

				$import_mes=($facturation->total_comp/12)*$facturation->meses;
				$facturation->import_mes=round($import_mes,2);

				$import_dia=($facturation->total_comp/360)*$facturation->dias_calc;
				$facturation->import_dia=round($import_dia,2);


				$total_cts=$facturation->import_mes+$facturation->import_dia;
				$facturation->total_cts=round($total_cts,2);      





        /*
			$totals_sum_sueldo += $facturation['sueldo'];
			$totals_sum_familiar += $facturation['total_comp'];
		*/

			
               
			

				$response[] = $facturation;

			}


		$totals = new stdClass();
		
		        $totals->document_number='TOTAL';
				$totals->employ_name='';
                $totals->cargo='';
				$totals->area_name='';
				$totals->moneda='';
                $totals->fecha_inicio='';
                $totals->meses='';
                $totals->dias_calc='';
				$totals->dias_total='';
                $totals->total_sp='';
                $totals->com_1='';
				$totals->com_2='';
				$totals->com_3='';
				$totals->com_4='';
				$totals->com_5='';
				$totals->com_6='';
				$totals->he_1='';
				$totals->he_2='';
				$totals->he_3='';
				$totals->he_4='';
				$totals->he_5='';
				$totals->he_6='';
				$totals->bon_1='';
				$totals->bon_2='';
				$totals->bon_3='';
				$totals->bon_4='';
				$totals->bon_5='';
				$totals->bon_6='';
				$totals->grati='';
				$totals->sueldo=''; 
				$totals->familiar='';
				$totals->gratdiv='';
				$totals->prom_he='';
				$totals->prom_com='';
				$totals->prom_bon='';
				$totals->prom_fer='';
				$totals->prom_don='';
				$totals->prom_noc='';
				$totals->total_comp='';
				$totals->import_mes='';
				$totals->import_dia='';
				$totals->total_cts='';
				$totals->bank_name='';
				$totals->cuenta='';
				$totals->cci='';

				

		

		$response[] = $totals;


		if ( $export) {
			$spreadsheet = new Spreadsheet();
			$sheet = $spreadsheet->getActiveSheet();
			$sheet->mergeCells('A1:AA1');
			$sheet->setCellValue('A1', 'HOJA DE TRABAJO CTS DEL '.CarbonImmutable::now()->format('d/m/Y H:m:s'));
			$sheet->getStyle('A1')->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 16,
					
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
				]
			]);

/*
	    $sheet->mergeCells('E2:M2');
		$sheet->setCellValue('E2', 'INGRESOS DEL TRABJADOR');
		$sheet->getStyle('E2')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 10,
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
				'size' => 10,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '1E90FF')
			]
		]);*/

	//	$sheet->mergeCells('A3:AA3');







			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'N° Documento');
			$sheet->setCellValue('C3', 'Apellidos y Nombres');
            $sheet->setCellValue('D3', 'Centro Costo');
			$sheet->setCellValue('E3', 'Área de Trabajo');
			$sheet->setCellValue('F3', 'Fecha Ing.');		
            $sheet->setCellValue('G3', 'Moneda');		
            $sheet->setCellValue('H3', 'Meses');
		 	$sheet->setCellValue('I3', 'Dias');
            $sheet->setCellValue('J3', 'Total de días');		
		 	$sheet->setCellValue('K3', 'Faltas');
			$sheet->setCellValue('L3', 'Gratificación');
		 	$sheet->setCellValue('M3', 'Basico');
			$sheet->setCellValue('N3', 'Asignación Familiar');
            $sheet->setCellValue('O3', '1/6 de Gratificación');
			$sheet->setCellValue('P3', '1/6 de Horas Extras');
			$sheet->setCellValue('Q3', '1/6 Bono por Cumplimiento');
			$sheet->setCellValue('R3', '1/6 de Feriado');
			$sheet->setCellValue('S3', '1/6 de Dominical');
            $sheet->setCellValue('T3', '1/6 de Bono Nocturno');
			$sheet->setCellValue('U3', 'CTS Remuneración Computable');
			$sheet->setCellValue('V3', 'CTS por Meses');
			$sheet->setCellValue('W3', 'CTS por Días');
			$sheet->setCellValue('X3', 'Total Pago CTS');
			$sheet->setCellValue('Y3', 'BANCO');
			$sheet->setCellValue('Z3', 'CUENTA ');
			$sheet->setCellValue('AA3', 'CCI');



			$row_number = 4;
			foreach ($response as $index => $element) {
				$index++;		
				$sheet->setCellValueExplicit('A'.$row_number, $index, DataType::TYPE_NUMERIC);
				$sheet->setCellValue('B'.$row_number, $element->document_number);
				$sheet->setCellValue('C'.$row_number, $element->employ_name);
                $sheet->setCellValue('D'.$row_number, $element->cargo);
				$sheet->setCellValue('E'.$row_number, $element->area_name);
                $sheet->setCellValue('F'.$row_number, $element->fecha_inicio);
				$sheet->setCellValue('G'.$row_number, $element->moneda);                
				$sheet->setCellValue('H'.$row_number, $element->meses);
                $sheet->setCellValue('I'.$row_number, $element->dias_calc);
				$sheet->setCellValue('J'.$row_number, $element->dias_total);
				
				if($element->total_sp == 0){
					$sheet->setCellValue('K'.$row_number, '-');
				}else{
					$sheet->setCellValue('K'.$row_number, $element->total_sp);
				}
				
				if($element->grati == 0){
					$sheet->setCellValue('L'.$row_number, '-');
				}else{
				$sheet->setCellValue('L'.$row_number, $element->grati);
				}
				$sheet->setCellValue('M'.$row_number, $element->sueldo); 
				if ($element->familiar == 0){
					$sheet->setCellValue('N'.$row_number, '-');
                }else{				
					$sheet->setCellValue('N'.$row_number, $element->familiar);
				}
				$sheet->setCellValue('O'.$row_number, $element->gratdiv);
				$sheet->setCellValue('P'.$row_number, $element->prom_he);
				$sheet->setCellValue('Q'.$row_number, $element->prom_bon);
				$sheet->setCellValue('R'.$row_number, $element->prom_fer);
				$sheet->setCellValue('S'.$row_number, $element->prom_don);
				$sheet->setCellValue('T'.$row_number, $element->prom_noc);
				$sheet->setCellValue('U'.$row_number, $element->total_comp);
				$sheet->setCellValue('V'.$row_number, $element->import_mes);
				$sheet->setCellValue('W'.$row_number, $element->import_dia);
				$sheet->setCellValue('X'.$row_number, $element->total_cts);
				$sheet->setCellValue('Y'.$row_number, $element->bank_name);
				$sheet->setCellValue('Z'.$row_number, $element->cuenta);
				$sheet->setCellValue('AA'.$row_number, $element->cci);
							
                // $sheet->getStyle('AI'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				// $sheet->getStyle('AJ'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				// $sheet->getStyle('AK'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
		/*	$sheet->getColumnDimension('L')->setAutoSize(true);
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
			$sheet->getColumnDimension('X')->setAutoSize(true);*/
			$sheet->getColumnDimension('Y')->setAutoSize(true);
			$sheet->getColumnDimension('Z')->setAutoSize(true);
			$sheet->getColumnDimension('AA')->setAutoSize(true);
			// $sheet->getColumnDimension('AB')->setAutoSize(true);
			

			$excelStyleService = new ExcelStyleService();
			$excelStyleService::cabezeraEstilos($sheet, 'FF87CEEB');

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}