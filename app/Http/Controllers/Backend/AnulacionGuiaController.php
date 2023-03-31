<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use App\WarehouseType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AnulacionGuiaController extends Controller
{
    public function index()
    {

        $companies = Company::select('id', 'name')->get();
        $warehouse_types = WarehouseType::select('id', 'name')->get();

        return view('backend.anulacion_guias', compact('warehouse_types', 'companies'));
    }

    public function searchGuides(Request $request)
    {

        $elements = WarehouseMovement::select()
            ->where('company_id', $request->company_id)
            ->where('warehouse_type_id', $request->warehouse_type_id)
            ->where('stock_pend', '>', 0)
            ->where('state', 0)
            ->orderBy('movement_number', 'asc')
            ->get();

        $elements->map(function ($item, $index) {
            $item->creation_date = Carbon::parse($item->traslate_date)->format('d/m/Y');
        });

        return $elements;
    }

    public function anular(Request $request)
    {

        $warehouse_movement = WarehouseMovement::find($request->id);
        $warehouse_movement->stock_pend = 0;
        $warehouse_movement->save();


        WarehouseMovement::insertGetId([
            'company_id' => $warehouse_movement->company_id,
            'warehouse_type_id' => $warehouse_movement->warehouse_type_id,
            'movement_class_id' => $warehouse_movement->movement_class_id == 1 ? 2: 1,
            'movement_type_id' => $warehouse_movement->movement_type_id,
            'warehouse_account_type_id' => $warehouse_movement->warehouse_account_type_id,
            'total' => $warehouse_movement->total,
            'created_at' => date('Y-m-d'),
            'updated_at' => date('Y-m-d'),
        ]);
    }
}
