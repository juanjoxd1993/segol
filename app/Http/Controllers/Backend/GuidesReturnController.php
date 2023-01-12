<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\Company;
use Illuminate\Http\Request;
use App\WarehouseDocumentType;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Auth;
use DB;
use stdClass;

class GuidesReturnController extends Controller
{
    public function index() {
        $companies = Company::select('id', 'name')->get();
		
       
		return view('backend.guides_return')->with(compact('companies'));
    }

    public function validateForm() {
		$messages = [
			'company_id.required'               => 'Debe seleccionar una Compañía.',
			'warehouse_movement_id.required'    => 'El Nº de Parte es obligatorio.',
		];

		$rules = [
			'company_id'            => 'required',
			'warehouse_movement_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getWarehouseMovements() {
        $company_id = request('company_id');

        $elements = WarehouseMovement::select('id', 'referral_guide_series', 'referral_guide_number', 'license_plate', 'created_at')
            ->where('company_id', $company_id)
            ->where('warehouse_type_id', 5)

            ->where(function ($query) {
                $query->where('action_type_id', 3)
                    ->orWhere('action_type_id', 4)
                    ->orWhere('action_type_id', 6)
                    ->orWhere('action_type_id', 7)
                    ->orWhere('action_type_id', 8);
            })
			->where('state', 0)
            ->orderBy('movement_number', 'asc')
            ->get();

        $elements->map(function ($item, $index) {
            $item->creation_date = date('d-m-Y', strtotime($item->created_at));
        });

        return $elements;
    }

    public function list() {



        $company_id = request('model.company_id');
        $warehouse_movement_id = request('model.warehouse_movement_id');

            $movementDetails = WarehouseMovementDetail::select('id', 'warehouse_movement_id', 'item_number', 'article_code', 'converted_amount')
            ->where('warehouse_movement_id', $warehouse_movement_id)
            ->orderBy('item_number', 'asc')
            ->get();

        $movementDetails->map(function ($item, $index) {
            $item->warehouse_movement_detail_id = $item->id;
            $item->article_id = $item->article->article_id;
            $item->article_code = $item->article->code;
			$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
            $item->presale_converted_amount = $item->converted_amount;
            



        });
        
        return $movementDetails;
    }

    
    public function detail() {
		$id = request('id');

		$element = WarehouseMovementDetail::select('id', 'new_stock_return')
			->findOrFail($id);

		
	
		return $element;
	}

	public function update() {
		$id = request('id');
		$new_stock_return = request('new_stock_return');
		
		
		$element = WarehouseMovementDetail::findOrFail($id);
		$element->new_stock_return = $new_stock_return;
		$element->save();

		$data = new stdClass();
		$data->type = 1;
		$data->title = '¡Ok!';
		$data->msg = 'Registro actualizado exitosamente.';

		return response()->json($data);
	}




}
