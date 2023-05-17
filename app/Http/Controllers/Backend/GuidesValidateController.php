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
use Auth;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class GuidesValidateController extends Controller
{
    
	public function index()
	{
		$companies = Company::select('id', 'name')->get();

		return view('backend.guides_validate')->with(compact(
            'companies'
        ));
	}

	public function validateForm() {
        $messages = [
            'company_id.required'               => 'Debe seleccionar una Compañía.',
        ];

        $rules = [
            'company_id'            => 'required',
        ];

        request()->validate($rules, $messages);
        return request()->all();
	}

    public function getWarehouseMovements()
    {
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
            ->where('warehouse_type_id', 5)
            ->where(function ($query) {
                $query->where('action_type_id', 3)
                    ->orWhere('action_type_id', 4)
                    ->orWhere('action_type_id', 6)
                    ->orWhere('action_type_id', 7)
                    ->orWhere('action_type_id', 8);
            })
            ->where('state', 1)
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

        $elements = array_filter($elements, function($val) {
            return $val->group_id != 7;
        });

        return [
            'articles' => $elements,
            'account_type_id' => $account_type_id
        ];
    }

    public function removeArticle()
    {
        $warehouse_movement_id = request('model.warehouse_movement_id');
        $id = request('article.id');
        $actualDate = date("Y-m-d");

        $movement = WarehouseMovement::select('warehouse_account_type_id')
            ->where('id', $warehouse_movement_id)
            ->first();

        $account_type_id = $movement->warehouse_account_type_id;

        $movementDetail = WarehouseMovementDetail::select(
            'id',
            'warehouse_movement_id',
            'item_number',
            'article_code',
            'article_num',
            'digit_amount',
            'deleted_at'
            )
            ->where('id', $id)
            ->first();

        $stock_movement_detail = $movementDetail->digit_amount;

        $movementDetail->deleted_at = $actualDate;
        $movementDetail->save();

        $article = Article::where('warehouse_type_id', 4)
            ->where('id', $movementDetail->article_num)
            ->first();

        $articleBalon = Article::where('warehouse_type_id', 4)
            ->where('convertion', $article->convertion)
            ->where('name', 'like', '%BALON%')
            ->first();

        $article->stock_good += $stock_movement_detail;
        $article->save();

        if ($account_type_id == 3) {
            $articleBalon->stock_return -= $stock_movement_detail;
            $articleBalon->save();
        }

        $data = [
            'type' => 1,
            'title' => '¡Ok!',
            'msg' => 'Articulo eliminado exitosamente.'
        ];

        return response()->json($data);
    }

    public function updateArticles()
    {
        $warehouse_movement_id = request('model.warehouse_movement_id');
        $articles = request('articles');

        $movement = WarehouseMovement::select('id','warehouse_account_type_id')
            ->where('id', $warehouse_movement_id)
            ->first();

        $account_type_id = $movement->warehouse_account_type_id;

        $item_number = 0;

        foreach ($articles as $item) {
            $item_number += 1; 
            $id = $item['id'];
            $presale = $item['presale'];
            $prestamo = $item['prestamo'];
            $cesion = $item['cesion'];

            $movementDetail = WarehouseMovementDetail::find($id);

            if ($id != 0) {
                $stock_movement_detail = $movementDetail->digit_amount;

                $article = Article::where('warehouse_type_id', 4)
                    ->where('id', $movementDetail->article_num)
                    ->first();

                $articleBalon = Article::where('warehouse_type_id', 4)
                    ->where('convertion', $article->convertion)
                    ->where('name', 'like', '%BALON%')
                    ->first();

                $articleEnvasado = Article::find(4791);
    
                if ($presale != $stock_movement_detail) {
    
                    $difference = $stock_movement_detail - $presale;
    
                    $movementDetail->digit_amount = $presale;
                    $movementDetail->converted_amount = $presale * $article->convertion;
    
                    if ($difference < 0) {
                        $differenceParse = $difference * -1;
    
                        if ($article->stock_good != 0) {                        
                            $article->stock_good -= $differenceParse;
                            $article->save();
                        } else {
                            $articleEnvasado->stock_good -= $article->convertion * $differenceParse;
                            $articleEnvasado->save();
                        }
        
                        if ($account_type_id == 3) {
                            $articleBalon->stock_good -= $differenceParse;
                            $articleBalon->stock_return += $differenceParse;
                        }
                    } elseif ($difference > 0) {
                        $article->stock_good += $difference;
                        $article->save();
        
                        if ($account_type_id == 3) {
                            $articleBalon->stock_return -= $difference;
                            $articleBalon->save();
                        }
                    }
                }

                if ($prestamo) {
                    $articleBalon->stock_repair += $prestamo;
    
                    $movementDetail->old_stock_repair = $movementDetail->new_stock_repair;
                    $movementDetail->new_stock_repair = $prestamo;
    
                    //Generar Movimiento de Salida - Producción
                    $id_movement = WarehouseMovement::insertGetId([
                        'company_id' => 1,
                        'warehouse_type_id' => 4, //Producción
                        'movement_class_id' => 2, //Salida
                        'movement_type_id' => 33, //Préstamos de Balones
                        'warehouse_account_type_id' => $account_type_id,
                        'total' => $prestamo,
                        'press' => 1,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => date('Y-m-d'),
                    ]);
    
                    WarehouseMovementDetail::insert([
                        'warehouse_movement_id' => $id_movement,
                        'item_number' => 1,
                        'article_code' => $movementDetail->article_code,
                        'new_stock_good' => $prestamo,
                        'converted_amount' => $prestamo,
                        'total' => $prestamo,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => date('Y-m-d'),
                    ]);
                }

                if ($cesion) {
                    $articleBalon->stock_minimum += $cesion;

                    $movementDetail->old_stock_cesion = $movementDetail->new_stock_cesion;
                    $movementDetail->new_stock_cesion = $cesion;

                    //Generar Movimiento de Salida - Producción
                    $id_movement = WarehouseMovement::insertGetId([
                        'company_id' => 1,
                        'warehouse_type_id' => 4, //Producción
                        'movement_class_id' => 2, //Salida
                        'movement_type_id' => 28, //Cesión de Uso
                        'warehouse_account_type_id' => $account_type_id,
                        'total' => $cesion,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => date('Y-m-d'),
                    ]);

                    WarehouseMovementDetail::insert([
                        'warehouse_movement_id' => $id_movement,
                        'item_number' => 1,
                        'article_code' => $movementDetail->article_code,
                        'new_stock_good' => $cesion,
                        'new_stock_return' => $presale - $cesion,
                        'converted_amount' => $cesion,
                        'total' => $cesion,
                        'created_at' => date('Y-m-d'),
                        'updated_at' => date('Y-m-d'),
                    ]);
                }

                $articleBalon->save();
                $movementDetail->save();
            } else {

                $articleGeneral = Article::where('warehouse_type_id', 5)
                    ->where('id', $item['article_id'])
                    ->first();

                $article = Article::where('warehouse_type_id', 4)
                    ->where('code', $articleGeneral->code)
                    ->first();
    
                $articleBalon = Article::where('warehouse_type_id', 4)
                    ->where('convertion', $article->convertion)
                    ->where('name', 'like', '%BALON%')
                    ->first();
    
                $articleEnvasado = Article::find(4791);

                if ($presale) {
                    $movementDetail = new WarehouseMovementDetail();
                    $movementDetail->warehouse_movement_id = $movement->id;
                    $movementDetail->item_number = $item_number;
                    $movementDetail->article_code = $item['article_id'];
                    $movementDetail->article_num = $article->id;
                    $movementDetail->digit_amount = $presale;
                    $movementDetail->converted_amount = $presale * $article->convertion;
                    $movementDetail->old_stock_good = $article->stock_good;
                    $movementDetail->new_stock_good = $article->stock_good;
                    $movementDetail->price = 0;
                    $movementDetail->sale_value = 0;
                    $movementDetail->exonerated_value = 0;
                    $movementDetail->inaccurate_value = 0;
                    $movementDetail->igv = 0;
                    $movementDetail->total = 0;
                    $movementDetail->igv_perception = 0;
                    $movementDetail->igv_percentage = 0;
                    $movementDetail->igv_perception_percentage = 0;
                    $movementDetail->created_at_user = Auth::user()->user;
                    $movementDetail->updated_at_user = Auth::user()->user;
    
                    if ($prestamo) {
                        $articleBalon->stock_repair += $prestamo;
        
                        $movementDetail->old_stock_repair = $movementDetail->new_stock_repair;
                        $movementDetail->new_stock_repair = $prestamo;
        
                        //Generar Movimiento de Salida - Producción
                        $id_movement = WarehouseMovement::insertGetId([
                            'company_id' => 1,
                            'warehouse_type_id' => 4, //Producción
                            'movement_class_id' => 2, //Salida
                            'movement_type_id' => 33, //Préstamos de Balones
                            'warehouse_account_type_id' => $account_type_id,
                            'total' => $prestamo,
                            'press' => 1,
                            'created_at' => date('Y-m-d'),
                            'updated_at' => date('Y-m-d'),
                        ]);
        
                        WarehouseMovementDetail::insert([
                            'warehouse_movement_id' => $id_movement,
                            'item_number' => 1,
                            'article_code' => $movementDetail->article_code,
                            'new_stock_good' => $prestamo,
                            'converted_amount' => $prestamo,
                            'total' => $prestamo,
                            'created_at' => date('Y-m-d'),
                            'updated_at' => date('Y-m-d'),
                        ]);
                    }
    
                    if ($cesion) {
                        $articleBalon->stock_minimum += $cesion;
        
                        $movementDetail->old_stock_cesion = $movementDetail->new_stock_cesion;
                        $movementDetail->new_stock_cesion = $cesion;
        
                        //Generar Movimiento de Salida - Producción
                        $id_movement = WarehouseMovement::insertGetId([
                            'company_id' => 1,
                            'warehouse_type_id' => 4, //Producción
                            'movement_class_id' => 2, //Salida
                            'movement_type_id' => 28, //Cesión de Uso
                            'warehouse_account_type_id' => $account_type_id,
                            'total' => $cesion,
                            'created_at' => date('Y-m-d'),
                            'updated_at' => date('Y-m-d'),
                        ]);
        
                        WarehouseMovementDetail::insert([
                            'warehouse_movement_id' => $id_movement,
                            'item_number' => 1,
                            'article_code' => $movementDetail->article_code,
                            'new_stock_good' => $cesion,
                            'new_stock_return' => $presale - $cesion,
                            'converted_amount' => $cesion,
                            'total' => $cesion,
                            'created_at' => date('Y-m-d'),
                            'updated_at' => date('Y-m-d'),
                        ]);
                    }
    
                    $articleBalon->save();
                    $movementDetail->save();
                }
            }
        }

        $data = [
            'type' => 1,
            'title' => '¡Ok!',
            'msg' => 'Articulos editados exitosamente.'
        ];

        return response()->json($data);
    }

    public function getArticles()
	{
		$company_id = request('company_id');
		$q = request('q');

        $clients = Article::leftjoin('operation_types', 'operation_types.id', '=', 'articles.operation_type_id')
            ->where('warehouse_type_id', 5)
            ->where('articles.name', 'like', '%' . $q . '%')
			->select(
				'articles.id',
				'code',
				'articles.name',
				'package_sale',
				'sale_unit_id',
				'operation_type_id',
				'factor',
				'operation_types.name as operation_type_name',
				'business_type',
				'convertion',
				'group_id'
				)
            ->get();

        $clients->map(function ($item, $index) {
            $item->text = $item->name . ' Unidad x 1';
            unset($item->name);

            return $item;
        });

		return $clients;
	}

    public function validateGuides()
    {
        $ids = request('ids');

        foreach ($ids as $id) {
            $movement = WarehouseMovement::find($id);

            $account_type_id = $movement->warehouse_account_type_id;

            if ($account_type_id == 1) {
                $guide_state = GuidesState::select('id')
                                ->where('name', 'Por Liquidar')
                                ->first();

                $movement->state = $guide_state->id;
                $movement->save();
            } else {
                $guide_state = GuidesState::select('id')
                                ->where('name', 'Validada')
                                ->first();

                $movement->state = $guide_state->id;
                $movement->save();
            }
            
        }

        $data = [
            'type' => 1,
            'title' => '¡Ok!',
            'msg' => 'Guias validadas exitosamente.'
        ];

        return response()->json($data);
    }
}