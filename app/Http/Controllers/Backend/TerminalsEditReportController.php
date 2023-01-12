<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\Company;
use App\Employee;
use App\Exports\GlpFactReportExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MoventClass;
use App\MoventType;
use App\Provider;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use phpDocumentor\Reflection\Element;
use stdClass;

class GlpFactReportController extends Controller
{
	public function index() {
		
		$companies = Company::select('id','name')->get();
		

		return view('backend.glp_fact_report')->with(compact('companies'));
	}

	public function validateForm() {
		$messages = [
			
		];

		$rules = [
			
		];

		request()->validate($rules, $messages);
		return request()->all();
	}
	
	public function getWarehouseMovements() {
		$company_id = request('company_id');
		$q = request('q');

		$guides = warehousemovement::select('id', 'scop_number as text')
		 ->when($company_id, function($query, $company_id) {
			 return $query->where('company_id', $company_id);
		 })
		 ->where('scop_number', 'like', '%'.$q.'%')
		 ->withTrashed()
		 ->get();

	 return $guides;
    }

	public function list() {
		$export = request('export');
		

		
	//	$referral_serie_number = request('model.referral_serie_number');
		$scop_number = request('model.scop_number');
		$company_id = request('model.company_id');
		
	

		$movements = WarehouseMovement::select('id', 'company_id', 'movement_class_id', 'movement_type_id', 'movement_stock_type_id', 'created_at', 'warehouse_account_type_id', 'referral_serie_number', 'referral_voucher_number',  'scop_number', 'state')
			->where('movement_class_id', 2)
            ->where('movement_type_id', 30)
           // ->where('referral_serie_number', $referral_serie_number) 
          //  ->where('referral_voucher_number', $referral_voucher_number)
			
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
          
            ->when($scop_number, function($query, $scop_number) {
				return $query->where('referral_voucher_number', $scop_number);
			})
            ->whereIn('warehouse_type_id',[8,9,10,11])
			
			
			->groupBy('warehouse_movements.id')
            ->orderBy('created_at')
			->get();
			

		$movement_details = collect([]);
		$movements->map(function($item, $index) use($movement_details, $export) {
			$item->warehouse_movement_details;
			
		
			
			$item->warehouse_movement_details->map(function($detail, $detailIndex) use($item,  $movement_details, $export) {
				$detail->article;
                $detail->warehouse_movement_id = $item->id;
				$detail->company_short_name = $item->company->short_name;
				$detail->movement_class = $item->movement_class->name;
				$detail->movement_type = $item->movement_type->name;
				$detail->movement_number = $item->movement_number;
				$detail->date = date('d-m-Y', strtotime($item->created_at));
				$detail->article_name = $detail->article->name;
				$detail->quantity = number_format($detail->converted_amount, 4, '.', ',');	
				$detail->referral_guide = $item->referral_serie_number.'-'.$item->referral_voucher_number;
				$detail->scop_number = $item->scop_number;
				if ( Auth::user()->user == 'comercial4' || Auth::user()->user == 'admin' || Auth::user()->user == 'sistemas1') {
					$detail->state = $item->state;
				} else {
					$detail->state = 1;
				}

				unset($detail->digit_amount);
			
				unset($detail->total);
				

				$movement_details->push($detail);
			});
		});

		if ( $export ) {
			$data = new GlpFactReportExport($movement_details);
			$file = Excel::download($data, 'reporte-salidas-de-mercaderia-'.time().'.xls');

			return $file;
		} else {
			return $movement_details;
		}
	}

	public function detail() {
     $id = request('id');
     $element = WarehouseMovementDetail::select('id','article_code','converted_amount')
	 ->findOrFail($id);
	 
     

    return $element;

	
	}

	
		public function update() {
            $id = request('id');
            $converted_amount = request('converted_amount');
            $article_code = request('article_code');
            $element = WarehouseMovementDetail::findOrFail($id);
			$element->converted_amount =$converted_amount;
            $element->article_code =$article_code;
            $element->save();
    
            $data = new stdClass();
            $data->type = 1;
            $data->title = '¡Ok!';
            $data->msg = 'Registro actualizado exitosamente.';
    
            return response()->json($data);
	}
}
