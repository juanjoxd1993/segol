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
use App\Recursive;
use Carbon\CarbonImmutable;
use Carbon\Carbon;
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
					->leftjoin('banks', 'employees.bank_id', '=', 'banks.id')
                    ->select('asists.id', 'asists.employ_id', 
					  'asists.ciclo_id',
					  'asists.horas_tarde', 
					  'asists.minutos_tarde', 
                      'employees.first_name',
					  'employees.document_number',
					  'employees.fecha_inicio',
					  'employees.sueldo',
					  'employees.cci',
					  'employees.cuenta',
					  'employees.bank_id',
					  'employees.asignacion_id',
					  'banks.name as bank_name',
					  'cicles.fecha_calc as inicio_cts',
					  'cicles.fecha_final as final_cts',
					  'cicles.fecha_inicio as fecha_ini')
              //      ->where('employees.company_id', $company_id)
                    ->where('asists.año', '=', $price_year)
                    ->where('asists.mes', '=', $price_mes)


		//	->groupBy('sales.sale_date','business_unit_id')
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
			    $facturation->cargo = $facturation['cargo'];
				$facturation->bank_name = $facturation['bank_name'];
			    $facturation->cuenta = $facturation['cuenta'];
			    $facturation->cci = $facturation['cci'];

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


				$total_comp= $facturation->sueldo+$facturation->familiar+$facturation->gratdiv+$facturation->prom_he+$facturation->prom_bom+$facturation->prom_com;
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
                $totals->fecha_inicio='';
                $totals->meses='';
                $totals->dias_calc='';
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
*/
		
			$sheet->setCellValue('A3', '#');
			$sheet->setCellValue('B3', 'N° Documento');
			$sheet->setCellValue('C3', 'APELLIDOS Y NOMBRES');
			$sheet->setCellValue('D3', 'CARGO U OCUPACIÓN');
			$sheet->setCellValue('E3', 'FECHA DE INGRESO');		
            $sheet->setCellValue('F3', 'MESES');
		 	$sheet->setCellValue('G3', 'DIAS');
		 	$sheet->setCellValue('H3', 'DIAS S.P');
            $sheet->setCellValue('I3', 'COM'.' '.$monthName);//COM 1
            $sheet->setCellValue('J3', 'COM'.' '.$monthName2);
            $sheet->setCellValue('K3', 'COM'.' '.$monthName3);
            $sheet->setCellValue('L3', 'COM'.' '.$monthName4);
            $sheet->setCellValue('M3', 'COM'.' '.$monthName5);
            $sheet->setCellValue('N3', 'COM'.' '.$monthName6);//COM
            $sheet->setCellValue('O3', 'HE'.' '.$monthName);//HE1
            $sheet->setCellValue('P3', 'HE'.' '.$monthName2);
            $sheet->setCellValue('Q3', 'HE'.' '.$monthName3);
            $sheet->setCellValue('R3', 'HE'.' '.$monthName4);
            $sheet->setCellValue('S3', 'HE'.' '.$monthName5);
            $sheet->setCellValue('T3', 'HE'.' '.$monthName6);//HE
            $sheet->setCellValue('U3', 'BON'.' '.$monthName);//BON1
            $sheet->setCellValue('V3', 'BON'.' '.$monthName2);
            $sheet->setCellValue('W3', 'BON'.' '.$monthName3);
            $sheet->setCellValue('X3', 'BON'.' '.$monthName4);
            $sheet->setCellValue('Y3', 'BON'.' '.$monthName5);
            $sheet->setCellValue('Z3', 'BON'.' '.$monthName6);//BON 
			$sheet->setCellValue('AA3', 'FIESTAS PATRIAS');
		 	$sheet->setCellValue('AB3', 'REM BASICA');
			$sheet->setCellValue('AC3', 'ASIG. FAM');
            $sheet->setCellValue('AD3', 'GRAT. FIESTAS PATRIAS');
			$sheet->setCellValue('AE3', 'Prom. HE');
			$sheet->setCellValue('AF3', 'Prom. Comisiones');
			$sheet->setCellValue('AG3', 'Prom. Bon Reg');
			$sheet->setCellValue('AH3', 'TOTAL COMPUTABLE');
			$sheet->setCellValue('AI3', 'IMPORTE X MES');
			$sheet->setCellValue('AJ3', 'IMPORTE X DIA');
			$sheet->setCellValue('AK3', 'TOTAL CTS ');
			$sheet->setCellValue('AL3', 'BANCO');
			$sheet->setCellValue('AM3', 'CUENTA ');
			$sheet->setCellValue('AN3', 'CCI');


         


			$sheet->getStyle('A3:AN3'.$sheet->getHighestRow())->getAlignment()->setWrapText(true)->applyFromArray([
				'font' => [
					'bold' => true,
					'size' => 8,
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
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
                $sheet->setCellValue('F'.$row_number, $element->meses);
                $sheet->setCellValue('G'.$row_number, $element->dias_calc);
				if($element->total_sp == 0){
					$sheet->setCellValue('H'.$row_number, '-');
				}else{
					$sheet->setCellValue('H'.$row_number, $element->total_sp);
				}
				if($element->total_sp == 0){
					$sheet->setCellValue('I'.$row_number, '-');
				}else{
                $sheet->setCellValue('I'.$row_number, $element->com_1);
				}
				if($element->com_2 == 0){
					$sheet->setCellValue('J'.$row_number, '-');
				}else{
				$sheet->setCellValue('J'.$row_number, $element->com_2);
				}
				if($element->com_3 == 0){
					$sheet->setCellValue('K'.$row_number, '-');
				}else{
					$sheet->setCellValue('K'.$row_number, $element->com_3);
					}
				if($element->com_4 == 0){
					$sheet->setCellValue('L'.$row_number, '-');
				}else{
					$sheet->setCellValue('L'.$row_number, $element->com_4);
					}
				if($element->com_5 == 0){
					$sheet->setCellValue('M'.$row_number, '-');
				}else{
					$sheet->setCellValue('M'.$row_number, $element->com_5);
					}
				if($element->com_6 == 0){
					$sheet->setCellValue('N'.$row_number, '-');
				}else{
					$sheet->setCellValue('N'.$row_number, $element->com_6);
					}
				if($element->he_1 == 0){
					$sheet->setCellValue('O'.$row_number, '-');
				}else{				
				$sheet->setCellValue('O'.$row_number, $element->he_1);
				}
				if($element->he_2 == 0){
					$sheet->setCellValue('P'.$row_number, '-');
				}else{
					$sheet->setCellValue('P'.$row_number, $element->he_2);
					}
				if($element->he_3 == 0){
					$sheet->setCellValue('Q'.$row_number, '-');
				}else{
					$sheet->setCellValue('Q'.$row_number, $element->he_3);
					}
				if($element->he_4 == 0){
					$sheet->setCellValue('R'.$row_number, '-');
				}else{
					$sheet->setCellValue('R'.$row_number, $element->he_4);
					}
				if($element->he_5 == 0){
					$sheet->setCellValue('S'.$row_number, '-');
				}else{
					$sheet->setCellValue('S'.$row_number, $element->he_5);
					}
				if($element->he_6 == 0){
					$sheet->setCellValue('T'.$row_number, '-');
				}else{
					$sheet->setCellValue('T'.$row_number, $element->he_6);
					}
				if($element->bon_1 == 0){
					$sheet->setCellValue('U'.$row_number, '-');
				}else{	
				$sheet->setCellValue('U'.$row_number, $element->bon_1);
				}
				if($element->bon_2 == 0){
					$sheet->setCellValue('V'.$row_number, '-');
				}else{
					$sheet->setCellValue('V'.$row_number, $element->bon_2);
					}
				if($element->bon_3 == 0){
					$sheet->setCellValue('W'.$row_number, '-');
				}else{
					$sheet->setCellValue('W'.$row_number, $element->bon_3);
					}
				if($element->bon_4 == 0){
					$sheet->setCellValue('X'.$row_number, '-');
				}else{
					$sheet->setCellValue('X'.$row_number, $element->bon_4);
					}
				if($element->bon_5 == 0){
					$sheet->setCellValue('Y'.$row_number, '-');
				}else{
					$sheet->setCellValue('Y'.$row_number, $element->bon_5);
					}
				if($element->bon_6 == 0){
					$sheet->setCellValue('Z'.$row_number, '-');
				}else{
					$sheet->setCellValue('Z'.$row_number, $element->bon_6);
				}
				if($element->grati == 0){
					$sheet->setCellValue('AA'.$row_number, '-');
				}else{
				$sheet->setCellValue('AA'.$row_number, $element->grati);
				}
				$sheet->setCellValue('AB'.$row_number, $element->sueldo); 
				if ($element->familiar == 0){
					$sheet->setCellValue('AC'.$row_number, '-');
                }else{				
					$sheet->setCellValue('AC'.$row_number, $element->familiar);
				}
				$sheet->setCellValue('AD'.$row_number, $element->gratdiv);
				$sheet->setCellValue('AE'.$row_number, $element->prom_he);
				$sheet->setCellValue('AF'.$row_number, $element->prom_com);
				$sheet->setCellValue('AG'.$row_number, $element->prom_bon);
				$sheet->setCellValue('AH'.$row_number, $element->total_comp);
				$sheet->setCellValue('AI'.$row_number, $element->import_mes);
				$sheet->setCellValue('AJ'.$row_number, $element->import_dia);
				$sheet->setCellValue('AK'.$row_number, $element->total_cts);
				$sheet->setCellValue('AL'.$row_number, $element->bank_name);
				$sheet->setCellValue('AM'.$row_number, $element->cuenta);
				$sheet->setCellValue('AN'.$row_number, $element->cci);
							
                $sheet->getStyle('AI'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('AJ'.$row_number)->getNumberFormat()->setFormatCode('0.00');
				$sheet->getStyle('AK'.$row_number)->getNumberFormat()->setFormatCode('0.00');			

		
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
		 /*	$sheet->getColumnDimension('I')->setAutoSize(true);
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
			$sheet->getColumnDimension('AD')->setAutoSize(true);*/
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

			$writer = new Xls($spreadsheet);
			return $writer->save('php://output');
		} 
		
		    else {
			return response()->json($response);
		    }
		
	}
}