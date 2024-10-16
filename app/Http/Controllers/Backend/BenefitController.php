<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\BenefitType;
use App\Area;
use App\Asist;
use App\Benefit;
use App\Cicle;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class BenefitController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
        $areas = Area::select('id', 'name')->get();
        $benefit_types = BenefitType::select('id', 'name')->get();
        $ciclos = Cicle::select('id', 'año', 'mes')->get();
        
        return view('backend.benefits')->with(compact('companies', 'areas','benefit_types', 'ciclos'));
    }

    public function validateForm() {
        $messages = [
			'company_id.required'   => 'Debe seleccionar una Compañía.',
			'ciclo_id.required' => 'Debe seleccionar un Ciclo.',
		];

		$rules = [
			'company_id'    => 'required',
            'ciclo_id'      => 'required',
		];

		request()->validate($rules, $messages);
        $data = request()->all();
        $data['benefit_types'] = BenefitType::select('id', 'name')->get();
		return $data;
    }

    public function list() {
        $company_id = request('model.company_id');
        $area_id = request('model.area_id');
        $ciclo_id = request('model.ciclo_id');
        $ciclo = Cicle::find($ciclo_id);

        // Verifica si se encontró el ciclo
        $price_mes = $ciclo->mes;
        $price_year = $ciclo->año;


        $elements = Employee::join('asists', 'employees.id', '=', 'asists.employ_id')
        ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
        ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
        ->select('asists.id','asists.employ_id', 'employees.*')
        ->where('employees.company_id', $company_id)
        ->where('asists.año', '=', $price_year)
        ->where('asists.mes', '=', $price_mes)
        ->when($area_id, function ($query, $area_id) {
            return $query->where('employees.area_id', $area_id);
        })
        ->orderBy('employees.first_name')
        ->get()
        ->toArray();
    

        foreach ($elements as $key => $element) {
            $elements[$key]['benefits'] = Benefit::select('benefit_id', 'dias', 'state')->where('employ_id', $element['employ_id'])->where('ciclo_id', $ciclo_id)->get()->toArray();
        }
        
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
			'benefit_id.required'             => 'Debe seleccionar una Operación.',
			'amount.required'                   => 'El Monto es obligatorio.',
			'initial_effective_date.required'   => 'La Fecha de Vigencia inicial es obligatoria.',
			'final_effective_date.required'     => 'La Fecha de Vigencia final es obligatoria.',
		];

		$rules = [
			//'benefit_id'              => 'required',
			//'amount'                    => 'required',
			'initial_effective_date'    => 'required',
			'final_effective_date'      => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
    }

    public function store() {
        $this->validateModalForm();

        $price_ids = request('price_ids');
        $benefit_id = request('benefit_id');
        $amount = request('amount');
        $initial_effective_date = request('initial_effective_date');
        $final_effective_date = request('final_effective_date');
        $benefit_values = (array) (json_decode(request('benefit_values')));
        $ciclo_id = request('ciclo_id');

        $ids = explode(',', $price_ids);
        $today = date('Y-m-d', strtotime(CarbonImmutable::now()->startOfDay()));
        $price_mes = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('m');
        $price_año = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('Y');

        foreach ($ids as $id) {
            $employee = Employee::where('id', $id)->first();

            if ($employee) {
                $ciclo= Cicle::find($ciclo_id);

                foreach ($benefit_values[$employee->id] as $benefit_input) {
                    $newElement = new Benefit();
                    $newElement->employ_id = $id;
                    $newElement->ciclo_id = $ciclo->id;
                    $newElement->benefit_id = $benefit_input->benefit_type;
                    $newElement->dias = $benefit_input->benefit_value;
                    $newElement->initial_effective_date = date('Y-m-d', strtotime($initial_effective_date));
                    $newElement->final_effective_date = date('Y-m-d', strtotime($final_effective_date));
                    $newElement->state = 1;
                    $newElement->año = $price_año;
                    $newElement->mes = $price_mes;
                    $newElement->created_at_user = Auth::user()->user;
                    $newElement->updated_at_user = Auth::user()->user;
                    $newElement->save();
                }
            }
        }
    }

    public function close() {
        $company = request('company');

        $result = Benefit::where('ciclo_id', request('cicle'))
            ->whereHas('employ', function($query) use ($company) {
                $query->where('company_id', $company);
            })
            ->update(['state' => 2]); // cerrado

        return response()->json(true, 200);
    }
}