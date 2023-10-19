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

        $elements = Asist::join('employees', 'asists.employ_id', '=', 'employees.id')
            ->leftjoin('cicles', 'asist.ciclo_id', '=', 'cicles.id')
            ->select('asists.id', 'employ_id', 'dias_mes', 'laborables', 'tardanzas', 'initial_effective_date', 'final_effective_date', 'price_lists.state', 'business_name','business_unit_id')
            ->where('company_id', $company_id)
            ->where('final_effective_date', '>=', $today)
            ->when($area_id, function($query, $area_id) {
				return $query->where('Employees.area_id', $area_id);
            })
            ->orderBy('business_unit_id', 'asc')
            ->orderBy('initial_effective_date', 'asc')
            ->orderBy('final_effective_date', 'asc')
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
			'amount.required'                   => 'El Monto es obligatorio.',
			'initial_effective_date.required'   => 'La Fecha de Vigencia inicial es obligatoria.',
			'final_effective_date.required'     => 'La Fecha de Vigencia final es obligatoria.',
		];

		$rules = [
			'operation_id'              => 'required',
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
        $operation_id = request('operation_id');
        $amount = request('amount');
        $initial_effective_date = request('initial_effective_date');
        $final_effective_date = request('final_effective_date');

        $ids = explode(',', $price_ids);
        $today = date('Y-m-d', strtotime(CarbonImmutable::now()->startOfDay()));
		$yesterday = date('Y-m-d', strtotime($initial_effective_date . '-1 day'));
        $last_yesterday = date('Y-m-d', strtotime($initial_effective_date . '-2 day'));

        foreach ($ids as $id) {
            $element = PriceList::where('id', $id)
                ->where('initial_effective_date', '<=', $today)
                ->where('final_effective_date', '>=', $today)
                ->first();

            if ( $element ) {
            //    $element->initial_effective_date= $last_yesterday;
                $element->final_effective_date = $yesterday; //aplicamos variable que inhabilita el precio anterior activo
                $element->updated_at_user = Auth::user()->user;
                $element->save();

                $elements = PriceList::where('client_id', $element->client_id)
                    ->where('warehouse_type_id', $element->warehouse_type_id)
                    ->where('article_id', $element->article_id)
                    ->where('initial_effective_date', '>', $yesterday)
                    ->get();
                
                $elements->map(function($item, $key) {
                    $item->state = 0;
                    $item->save();
                    $item->delete();
                });

                $igv = Rate::select('value')
                    ->where('description', 'IGV')
                    ->where('state', 1)
                    ->first();

                $igv->operator = ($igv->value / 100) + 1;

                if ( $operation_id == 1 ) {
                    $price = $amount;
                    $price_igv = $amount;
                } elseif ( $operation_id == 2 ) {
                    $price = $element->price_igv + $amount;
                    $price_igv = $element->price_igv + $amount;
                } elseif ( $operation_id == 3 ) {
                    $price = $element->price_igv - $amount;
                    $price_igv = $element->price_igv - $amount;
                }

                if ( $element->article->igv == 1 ) {
                    $price = $price / $igv->operator;
                }

                $newElement = new PriceList();
                $newElement->client_id = $element->client_id;
                $newElement->warehouse_type_id = $element->warehouse_type_id;
                $newElement->article_id = $element->article_id;
                $newElement->price = number_format($price, 4, '.', '');
                $newElement->price_igv = number_format($price_igv, 4, '.', '');
                $newElement->initial_effective_date = date('Y-m-d', strtotime($initial_effective_date));
                $newElement->final_effective_date = date('Y-m-d', strtotime($final_effective_date));
                $newElement->state = 1;
                $newElement->created_at_user = Auth::user()->user;
                $newElement->updated_at_user = Auth::user()->user;
                $newElement->save();
            }
        }
    }
}