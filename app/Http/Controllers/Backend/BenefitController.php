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
        
        return view('backend.benefits')->with(compact('companies', 'areas','benefit_types'));
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


        $elements = Employee::select( 
        'document_number ',
        'first_name ',
        'company_id',
        'area_id')
            ->where('company_id', $company_id)
            ->when($area_id, function($query, $area_id) {
				return $query->where('employees.area_id', $area_id);
            })
          //->orderBy('employ_id', 'asc')
            ->get();

        
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
			'benefit_id'              => 'required',
			'amount'                    => 'required',
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

        $ids = explode(',', $price_ids);
        $today = date('Y-m-d', strtotime(CarbonImmutable::now()->startOfDay()));
        $price_mes = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('m');
        $price_año = CarbonImmutable::createFromDate(request($today))->startOfDay()->format('Y');


        foreach ($ids as $id) {
            $element = Employee::where('id', $id)
           // ->where('año', '=', $price_año)
           // ->where('mes', '=', $price_mes)
            ->first();

            if ( $element ) {
            
                $elements = Employee::where('id', $element->id)
              //  ->where('año', '=', $price_año)
              //  ->where('mes', '=', $price_mes)
                ->get();
                
                $ciclo= Cicle::select('id','año','mes')
                ->where('año', '=', $price_año)
                ->where('mes', '=', $price_mes)
                ->select('id')
                ->sum('id');
           

             

                $newElement = new Benefit();
                $newElement->employ_id = $element->id;
                $newElement->ciclo_id = $ciclo;
                $newElement->benefit_id = $benefit_id;
                $newElement->dias = $amount;
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