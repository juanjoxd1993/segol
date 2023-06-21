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
use App\ClientRoute;
use App\GuidesSerie;
use App\Vehicle;
use App\GuidesState;
use App\WarehouseTypeInUser;
use Auth;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class GuidesValidatePressController extends Controller
{
    
	public function index()
	{
		$companies = Company::select('id', 'name')->get();

		return view('backend.guides_validate_press')->with(compact(
      'companies'
    ));
	}

	public function validateForm()
  {
    $messages = [
        'company_id.required' => 'Debe seleccionar una Compañía.',
    ];

    $rules = [
        'company_id'          => 'required',
    ];

    request()->validate($rules, $messages);
    return request()->all();
	}

  public function getWarehouseMovements()
  {
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
                                              ->where('user_id', $user_id)
                                              ->first();

		$warehouse_type_id = $warehouse_type_user->warehouse_type_id;

      $company_id = request('company_id');

      $elements = WarehouseMovement::select(
          'id',
          'referral_guide_series',
          'referral_guide_number',
          'license_plate',
          'account_name',
          'license_plate',
          'created_at')
          ->where('company_id', $company_id)
          ->where('warehouse_type_id', $warehouse_type_id)
          ->where(function ($query) {
              $query->where('action_type_id', 3)
                  ->orWhere('action_type_id', 4)
                  ->orWhere('action_type_id', 6)
                  ->orWhere('action_type_id', 7)
                  ->orWhere('action_type_id', 8);
          })
          ->where('state', 5)
          ->orderBy('movement_number', 'asc')
          ->get();

      $elements->map(function ($item, $index) {
          $item->creation_date = date('d-m-Y', strtotime($item->created_at));
      });

      $array_movments = array();

      foreach ($elements as $element) {
          array_push($array_movments, $element);
      };

      $array_movments = array_reverse($array_movments);

      return $array_movments;
  }

  public function list()
  {

      $company_id = request('company_id');
      $warehouse_movement_id = request('warehouse_movement_id');

      $movement = WarehouseMovement::select('warehouse_account_type_id')
          ->where('id', $warehouse_movement_id)
          ->first();

      $account_type_id = $movement->warehouse_account_type_id;

      $movementDetails = WarehouseMovementDetail::select(
          'id',
          'warehouse_movement_id',
          'item_number',
          'article_code',
          'digit_amount',
          'converted_amount',
          'new_stock_repair',
          'new_stock_cesion'
          )
          ->where('warehouse_movement_id', $warehouse_movement_id)
          ->orderBy('item_number', 'asc')
          ->get();

      $movementDetails->map(function ($item, $index) {

          $parse_converted_amount = intval(floatval($item->converted_amount));
          $parse_digit_amount = intval(floatval($item->digit_amount));
          $prestamo = $item->new_stock_repair ? intval(floatval($item->new_stock_repair)) : 0;
          $cesion = $item->new_stock_cesion ? intval(floatval($item->new_stock_cesion)) : 0;

          $item->parent = null;
          $item->article_id = $item->article->id;
          $item->article_code = $item->article->code;
          $item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
          $item->presale = $parse_digit_amount;
          $item->presale_converted_amount = $parse_converted_amount;
          $item->converted_amount = $parse_converted_amount;
          $item->retorno = 0;
          $item->retorno_press = 0;
          $item->cambios = 0;
          $item->prestamo = $prestamo;
          $item->cesion = $cesion;
          $item->vacios = 0;
          $item->group_id = $item->article->group_id;

          if ($item->article->group_id == 26) {
              $item->liquidar = $parse_converted_amount;
          } else {
              $item->liquidar = 0;
          };

          $item->retorno = 0;
          $item->cambios = 0;
          $item->vacios = 0;
      });

      $elements = array();

      foreach ($movementDetails as $val) {
          array_push($elements, $val);
      };

      // $elements = array_filter($elements, function($val) {
      //     return $val->group_id != 7;
      // });

      return [
          'articles' => $elements,
          'account_type_id' => $account_type_id
      ];
  }

  public function validateGuides()
  {
      $ids = request('ids');

      foreach ($ids as $id) {
          $movement = WarehouseMovement::find($id);

          $account_type_id = $movement->warehouse_account_type_id;

          $guide_state = GuidesState::select('id')
                          ->where('name', 'Por Liquidar')
                          ->first();

          $movement->state = $guide_state->id;
          $movement->save();
          
      }

      $data = [
          'type' => 1,
          'title' => '¡Ok!',
          'msg' => 'Guias validadas exitosamente.'
      ];

      return response()->json($data);
  }
}