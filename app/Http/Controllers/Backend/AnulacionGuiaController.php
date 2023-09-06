<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;
use App\Article;
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
                                    ->where('state', 1)
                                    ->orderBy('movement_number', 'desc')
                                    ->get();

        $elements->map(function ($item, $index) {
            $item->creation_date = Carbon::parse($item->traslate_date)->format('d/m/Y');
        });

        return $elements;
    }

    public function anular(Request $request)
    {
        $warehouse_movement = WarehouseMovement::find($request->id);

        $warehouse_movements = WarehouseMovement::where('referral_guide_number', $warehouse_movement->referral_guide_number)
                                                ->where('referral_guide_series', $warehouse_movement->referral_guide_series)
                                                ->get();

        $warehouse_movement_details = WarehouseMovementDetail::where('warehouse_movement_id', $warehouse_movement->id)->get();

        $warehouse_account_type_id = $warehouse_movement->warehouse_account_type_id;
        $warehouse_type_id = $warehouse_movement->warehouse_type_id;

        foreach ($warehouse_movement_details as $warehouse_movement_detail) {
            $article = Article::find($warehouse_movement_detail->article_num);

            $digit_amount = intval(floatval($warehouse_movement_detail->digit_amount));

            $convertion = $article->convertion;

            $article_balon = Article::where('warehouse_type_id', $warehouse_type_id)
                                    ->where('convertion', $convertion)
                                    ->first();

            $article->stock_good += $digit_amount;
            $article->save();

            if ($warehouse_account_type_id == 1) {
                $article_balon->stock_good -= $digit_amount;
                $article_balon->save();
            } elseif ($warehouse_account_type_id == 3) {
                $article_balon->stock_return -= $digit_amount;
                $article_balon->save();
            }
        }

        foreach ($warehouse_movements as $item) {
            $item->deleted_at = date('Y-m-d');
            $item->state = 6;
            $item->save();
        }

        $warehouse_movement->deleted_at = date('Y-m-d');
        $warehouse_movement->state = 6;
        $warehouse_movement->save();

        return json_encode($warehouse_movement);
    }
}
