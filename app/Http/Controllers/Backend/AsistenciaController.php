<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PriceList;
use App\Area;
use App\Asist;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class AsistenciaController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
        $areas = Area::select('id', 'name')->get();
        
        return view('backend.asistencia')->with(compact('companies', 'areas'));
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
        $today = date('Y-m-d', strtotime(Carbon::now()->startOfDay()));
        $price_mes = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('m');
        $price_año = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('Y');

        $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
            ->leftjoin('cicles', 'asist.ciclo_id', '=', 'cicles.id')
            ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
            ->select('asists.id', 'employ_id', 'dias_mes', 'laborables', 'tardanzas', 'horas_tarde', 'minutos_tarde', 'horas_extra_25', 'horas_extra_35','horas_noc_25','horas_noc_35','first_name','document_number')
            ->where('company_id', $company_id)
            ->where('cicles.año', '=', $price_año)
            ->where('cicles.mes', '=', $price_mes)

            ->when($area_id, function($query, $area_id) {
				return $query->where('Employees.area_id', $area_id);
            })
            ->orderBy('area_id', 'asc')
            ->get();

        $elements->map(function ($item, $key) {
            $item->article_code = $item->article->code;
            $item->article_name = $item->article->name . ' ' . $item->article->sale_unit->name . ' x ' . $item->article->package_sale;
            $item->initial_effective_date = date('d/m/Y', strtotime($item->initial_effective_date));
            $item->final_effective_date = date('d/m/Y', strtotime($item->final_effective_date));
            $item->price_igv = number_format($item->price_igv, 4, '.', ',');
        });

        return $elements;
    }

    public function getMinEffectiveDate() {
        $today = Carbon::now()->startOfDay();
        $initial_effective_date = date('d-m-Y', strtotime($today . 'day'));
        $min_effective_date = date(DATE_ATOM, strtotime($today . '-3 day'));

        return response()->json([
            'initial_effective_date'    => $initial_effective_date,
            'min_effective_date'        => $min_effective_date,
        ]);
    }

    public function validateModalForm() {
        $messages = [
			'operation_id.required'             => 'Debe seleccionar una Operación.',
		//	'amount.required'                   => 'El Monto es obligatorio.',
		];

		$rules = [
			'operation_id'              => 'required',
		//	'amount'                    => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
    }

    public function store() {
        $this->validateModalForm();

        $price_ids = request('price_ids');
        $operation_id = request('operation_id');
        $amount = request('amount');
        $today = date('Y-m-d', strtotime(Carbon::now()->startOfDay()));
        $price_mes = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('m');
        $price_año = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('Y');

        $ids = explode(',', $price_ids);

        foreach ($ids as $id) {
            $element = Asist::where('id', $id)
            ->where('año', '=', $price_año)
            ->where('mes', '=', $price_mes)
            ->first();

            if ( $element ) {

                $element->updated_at_user = Auth::user()->user;
                $element->save();

                $elements = Asist::where('employ_id', $element->employ_id)
                    ->where('año', '=', $price_año)
                    ->where('mes', '=', $price_mes)
                    ->get();
                
                //FALTAS
                if ( $operation_id == 1 ) {
                    $laborable = $element->laborables - 1;
                    $element->laborables = $laborable;
                    $element->save();

                //TARDANZA

                } elseif ( $operation_id == 2 ) {
                    $tardanza = $element->tardanzas + 1;
                    $horas_tarde = $element->horas_tarde + $amount;

                    $element->tardanzas = $tardanza;
                    $element->horas_tarde = $horas_tarde;
                    $element->save();

                //HORAS EXTRA

                } elseif ( $operation_id == 3 ) {
                   
                    $horas_extra = $element->horas_extra_25 + $amount;

                    $element->horas_extra_25 = $horas_extra;
                    $element->save();
                }

                elseif ( $operation_id == 4 ) {
                   
                $horas_extra_35 = $element->horas_extra_35 + $amount;

                $element->horas_extra_35 = $horas_extra_35;
                $element->save();
                }
                
                elseif ( $operation_id == 5 ) {
                   
                 $horas_noc = $element->horas_noc_25 + $amount;

                $element->horas_noc_25 = $horas_noc;
                $element->save();
                }

                elseif ( $operation_id == 6 ) {
                   
                $horas_noc_35 = $element->horas_noc_35 + $amount;

                $element->horas_noc_35 = $horas_noc_35;
                $element->save();
                }

            }
        }
    }
}