<?php

namespace App\Http\Controllers\Backend;

use App\Employee;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PriceList;
use App\Area;
use App\Asist;
use App\Recursive;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;

class RecursiveRegisterController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
        $areas = Area::select('id', 'name')->get();
        
        return view('backend.recursive_register')->with(compact('companies', 'areas'));
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
            ->leftjoin('cicles', 'asists.ciclo_id', '=', 'cicles.id')
            ->leftjoin('areas', 'employees.area_id', '=', 'areas.id')
            ->select('asists.id', 'asists.employ_id', 'asists.dias_mes', 'asists.laborables',
             'asists.tardanzas', 'asists.ciclo_id','employees.first_name','employees.document_number',
             'cicles.año','cicles.mes')
            ->where('employees.company_id', $company_id)
            ->where('asists.año', '=', $price_año)
            ->where('asists.mes', '=', $price_mes)

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
        $amount2 = request('amount2');
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

                
                $elements = Asist::where('employ_id', $element->employ_id)
                    ->where('año', '=', $price_año)
                    ->where('mes', '=', $price_mes)
                    ->get();
                            

				$newElement = new Recursive();
                $newElement->employ_id = $element->employ_id;
                $newElement->recursive_id = $operation_id;
                $newElement->ciclo_id = $element->ciclo_id;
				$newElement->dias = $amount;
                $newElement->año = $element->año;
                $newElement->mes = $element->mes;
                $newElement->amount = number_format($amount2, 4, '.', '');
                $newElement->state = 1;
                $newElement->created_at_user = Auth::user()->user;
                $newElement->updated_at_user = Auth::user()->user;
                $newElement->save();

            }
        }
    }
}