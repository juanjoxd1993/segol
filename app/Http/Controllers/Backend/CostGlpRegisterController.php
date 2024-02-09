<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\MoventClass;
use App\MoventType;
use App\WarehouseDocumentType;
use App\WarehouseType;
use App\Article;
use App\GlpCost;
use App\Currency;
use App\WarehouseAccountType;
use App\Client;
use App\Employee;
use App\MovementStockType;
use App\Provider;
use App\Rate;
use App\Unit;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Auth;
use Carbon\CarbonImmutable;
use PDF;

class CostGlpRegisterController extends Controller
{
    public function index() {
		
		$date = CarbonImmutable::now()->startOfDay();
		$current_date = $date->startOfDay()->toAtomString();
		
		
		return view('backend.cost_glp_register')->with(compact( 'current_date'));
	}

	
	public function validateForm() {
		$messages = [
			
		
			'since_date.required'		=> 'Debe seleccionar una Fecha.',
            'price_mes.required'		=> 'Debe seleccionar un mes.',

		];

		$rules = [
		

			'price_mes'	 => 'required',
			'since_date' => 'required',
	
		];

		request()->validate($rules, $messages);
		return request()->all();
	}


	public function store() {
        

        $since_date = request('since_date');
        $price_mes = request('price_mes');

        $movements = WarehouseMovement::select('id', 'warehouse_type_id', 'movement_type_id', 'referral_serie_number', 'referral_voucher_number', 'created_at')
			->where('price_mes', $price_mes)
            ->where('movement_type_id', 30)
            ->where('created_at', '<=', $since_date)
			->get();

                    $total = WarehouseMovement::where('referral_voucher_number', $movements['referral_voucher_number'])
                    ->where('movement_class_id', 1)
                    ->select('soles')
                    ->sum('soles');

                    $quantity = WarehouseMovementDetail::where('warehouse_movement_id', $movements['id'])
                    ->select('converted_amount')
                    ->sum('converted_amount');   

		$glp = new GlpCost();
				
		$glp->soles += $total;;
		$glp->kgs += $quantity;
		$glp->fecha = date('Y-m-d', strtotime($since_date));
        $glp->cost= $glp->kgs/$glp->kgs;
		
		$glp->save();
    		
    }
}