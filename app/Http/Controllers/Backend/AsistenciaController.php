<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PriceList;
use App\Area;
use App\Asist;
use App\AsistType;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use App\Cicle;

class AsistenciaController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
        $areas = Area::select('id', 'name')->get();
        $asistTypes = AsistType::select('id','name')->get();
        $ciclos = Cicle::select('id', 'año', 'mes')->get();
        
        return view('backend.asistencia')->with(compact('companies', 'areas', 'asistTypes', 'ciclos'));
    }

    public function validateForm() {
        $messages = [
			'company_id.required'   => 'Debe seleccionar una Compañía.',
			//'article_id.required'	=> 'Debe seleccionar un Artículo.',
		];

		$rules = [
			'company_id'    => 'required',
		//	'article_id'    => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
    }

    public function list() {
        $company_id = request('model.company_id');
        $area_id = request('model.area_id');
        $ciclo_id = request('model.ciclo_id');
        $today = date('Y-m-d', strtotime(Carbon::now()->startOfDay()));
       

        $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
            ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
            ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
            ->select('asists.id', 'asists.employ_id', 'asists.dias_mes', 'asists.laborables',
             'asists.tardanzas', 'asists.ciclo_id','asists.horas_tarde', 'asists.minutos_tarde', 
             'asists.horas_extra_25', 'asists.horas_extra_35','asists.horas_noc_25',
             'asists.horas_noc_35','employees.first_name','employees.document_number')
           // ->where('employees.company_id', $company_id)
         //   ->where('cicles.año', '=', $price_ano)
          //  ->where('cicles.mes', '=', $price_mes)
            ->where('asists.ciclo_id', $ciclo_id)

            ->when($area_id, function($query, $area_id) {
				return $query->where('employees.area_id', $area_id);
            })
            ->orderBy('area_id', 'asc')
            ->get();

        return $elements;
    }

    public function getMinEffectiveDate() {
        $today = Carbon::now()->startOfDay();
    //    $initial_effective_date = date('d-m-Y', strtotime($today . 'day'));
        $min_effective_date = date(DATE_ATOM, strtotime($today . '-3 day'));

        return response()->json([
    //        'initial_effective_date'    => $initial_effective_date,
            'min_effective_date'        => $min_effective_date,
        ]);
    }

    public function validateModalForm() {
        $messages = [
			//'operation_id.required'             => 'Debe seleccionar una Operación.',
		//	'amount.required'                   => 'El Monto es obligatorio.',
		];

		$rules = [
			//'operation_id'              => 'required',
		//	'amount'                    => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
    }

    public function store() {
        $this->validateModalForm();

        $today = date('Y-m-d', strtotime(Carbon::now()->startOfDay()));
        $price_mes = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('m');
        $price_ano = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('Y');
        $ciclo_id = request('ciclo_id');

        $data = (array)json_decode(request('asist_values'));

        foreach ($data as $employ_id => $asist_data) {
            $element = Asist::where('employ_id', $employ_id)
                ->where('año', $price_ano)
                ->where('mes', $price_mes)
                ->where('ciclo_id', $ciclo_id)
                ->first();

            if (!$element) {
                $element = new Asist();
                $element->employ_id = $employ_id;
                $element->año = $price_ano;
                $element->mes = $price_mes;
                $element->ciclo_id = $ciclo_id;
                $element->tardanzas = 0;
            }

            foreach ($asist_data as $asist) {
                $operation_id = $asist->asist_type;
                $amount = $asist->asist_value;

                if ( $operation_id == 1 ) { //FALTAS
                    $laborable = $amount;
                    $element->laborables = $laborable;
                } elseif ( $operation_id == 2 ) { //TARDANZA    
                    $element->horas_tarde = $amount / 60;
                    $element->tardanzas = $element->horas_tarde / 24;
                    $element->minutos_tarde = $amount;
                } elseif ( $operation_id == 3 ) { //HORAS EXTRA
                    $horas_extra = $amount;
                    $element->horas_extra_25 = $horas_extra;
                } elseif ( $operation_id == 4 ) {                   
                    $horas_extra_35 = $amount;
                    $element->horas_extra_35 = $horas_extra_35;
                } elseif ( $operation_id == 5 ) {
                    $horas_noc = $amount;
                    $element->horas_noc_25 = $horas_noc;
                } elseif ( $operation_id == 6 ) {
                    $horas_noc_35 = $amount;
                    $element->horas_noc_35 = $horas_noc_35;
                }
            }

            $element->save();
        }
    }
}