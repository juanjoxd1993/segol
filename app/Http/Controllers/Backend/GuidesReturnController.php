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
use Illuminate\Support\Facades\DB;
use stdClass;

class GuidesReturnController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name')->get();


        return view('backend.guides_return')->with(compact('companies'));
    }

    public function validateForm()
    {
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

    public function getWarehouseMovements()
    {
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

    public function list()
    {

        $company_id = request('model.company_id');
        $warehouse_movement_id = request('model.warehouse_movement_id');

        $movementDetails = WarehouseMovementDetail::select('id', 'warehouse_movement_id', 'item_number', 'article_code', 'converted_amount')
            ->where('warehouse_movement_id', $warehouse_movement_id)
            ->orderBy('item_number', 'asc')
            ->get();

        $movementDetails->map(function ($item, $index) {
            $item->parent = null;
            $item->article_id = $item->article->id;
            $item->article_code = $item->article->code;
            $item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
            $item->presale_converted_amount = $item->converted_amount;
            $item->retorno = 0;
            $item->cambios = 0;
            $item->prestamo = 0;
            $item->cesion = 0;
            $item->vacios = 0;
            $item->liquidar = 0;
            $item->retorno = 0;
            $item->cambios = 0;
            $item->prestamo = 0;
            $item->cesion = 0;
            $item->vacios = 0;
            $item->liquidar = 0;
        });

        return $movementDetails;
    }


    public function detail()
    {
        $id = request('id');

        $element = WarehouseMovementDetail::select('id', 'new_stock_return')
            ->findOrFail($id);



        return $element;
    }

    public function update(Request $request)
    {
        $articles = $request->articles;

        foreach ($articles as $article) {

            if ($article['retorno'] > 0) {

                /********** Retornos Llenos - Movimiento *********/
                //Generar Movimiento de Ingreso - Producción
                $id = WarehouseMovement::insertGetId([
                    'company_id' => 1,
                    'warehouse_type_id' => 4, //Producción
                    'movement_class_id' => 1, //Ingreso
                    'movement_type_id' => 19, //Retorno de Pre-Venta
                    'warehouse_account_type_id' => 1,
                    'total' => $article['retorno'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                WarehouseMovementDetail::insert([
                    'warehouse_movement_id' => $id,
                    'item_number' => 1,
                    'article_code' => $article['article_id'],
                    'new_stock_good' => $article['retorno'],
                    'converted_amount' => $article['retorno'],
                    'total' => $article['retorno'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                Article::where('id', $article['article_id'])
                    ->update([
                        'stock_repair' => DB::raw('stock_repair + ' . $article['retorno'])
                    ]);
            }

            if ($article['cambios'] > 0) {

                /********** Cambios - Movimiento *********/

                //Generar Movimiento de Ingreso - Producción
                $id = WarehouseMovement::insertGetId([
                    'company_id' => 1,
                    'warehouse_type_id' => 4, //Producción
                    'movement_class_id' => 1, //Ingreso
                    'movement_type_id' => 32, //Retorno por Cambios
                    'warehouse_account_type_id' => 1,
                    'total' => $article['cambios'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                WarehouseMovementDetail::insert([
                    'warehouse_movement_id' => $id,
                    'item_number' => 1,
                    'article_code' => $article['article_id'],
                    'new_stock_good' => $article['cambios'],
                    'converted_amount' => $article['cambios'],
                    'total' => $article['cambios'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                Article::where('id', $article['article_id'])
                    ->update([
                        'stock_repair' => DB::raw('stock_repair - ' . ($article['cambios'] + $article['retorno'])),
                        'stock_return' => DB::raw('stock_return + ' . ($article['cambios'] + $article['retorno'])),
                    ]);
            }


            /**here && $article['parent'] */
            if ($article['prestamo'] > 0) {

                /********** Préstamos - Movimiento *********/

                //Generar Movimiento de Salida - Producción
                $id = WarehouseMovement::insertGetId([
                    'company_id' => 1,
                    'warehouse_type_id' => 4, //Producción
                    'movement_class_id' => 2, //Salida
                    'movement_type_id' => 33, //Préstamos de Balones
                    'warehouse_account_type_id' => 1,
                    'total' => $article['prestamo'],
                    'press' => 1,
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                WarehouseMovementDetail::insert([
                    'warehouse_movement_id' => $id,
                    'item_number' => 1,
                    'article_code' => $article['article_id'],
                    'new_stock_good' => $article['prestamo'],
                    'converted_amount' => $article['prestamo'],
                    'total' => $article['prestamo'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                Article::where('id', $article['article_id'])
                    ->update([
                        'stock_repair' => DB::raw('stock_repair - ' . $article['prestamo']),
                        'stock_damaged' => DB::raw('stock_damaged + ' . $article['prestamo'])
                    ]);
            }


            if ($article['cesion'] > 0) {

                /********** Cesión de uso - Movimiento *********/

                //Generar Movimiento de Salida - Producción
                $id = WarehouseMovement::insertGetId([
                    'company_id' => 1,
                    'warehouse_type_id' => 4, //Producción
                    'movement_class_id' => 2, //Salida
                    'movement_type_id' => 28, //Cesión de Uso
                    'warehouse_account_type_id' => 1,
                    'total' => $article['cesion'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                WarehouseMovementDetail::insert([
                    'warehouse_movement_id' => $id,
                    'item_number' => 1,
                    'article_code' => $article['article_id'],
                    'new_stock_good' => $article['cesion'],
                    'new_stock_return' => $article['presale_converted_amount'] - $article['cesion'],
                    'converted_amount' => $article['cesion'],
                    'total' => $article['cesion'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                Article::where('id', $article['article_id'])
                    ->update([
                        'stock_good' => DB::raw('stock_good + ' . $article['prestamo']),
                        'stock_repair' => DB::raw('stock_repair - ' . $article['prestamo']),
                        'stock_minimum' => DB::raw('stock_minimum + ' . $article['cesion']),
                    ]);
            }


            if($article['vacios']){
                Article::where('id', $article['article_id'])
                    ->update([
                        'stock_repair' => DB::raw('stock_repair - ' . $article['vacios']),
                        'stock_good' => DB::raw('stock_good + ' .$article['vacios']),
                    ]);
            }
            /**Off */

            $articleDetail = Article::where('warehouse_type_id', 4)
                ->where('code', $article['article_code'])
                ->first();

            Article::where('warehouse_type_id', 4)
                ->where('code', $article['article_code'])
                ->update([
                    'stock_good' => $articleDetail->stock_good + $article['retorno'],
                ]);

            //Actualizar new_stock_return
            if ($article['article']['group_id'] == 26) {
                WarehouseMovementDetail::where('warehouse_movement_id', $article['warehouse_movement_id'])
                    ->where('article_code', $article['article_id'])
                    ->update([
                        'new_stock_return' =>  $article['retorno'] + $article['cambios'],
                    ]);
            } else if ($article['article']['group_id'] == 7) {
                WarehouseMovementDetail::where('warehouse_movement_id', $article['warehouse_movement_id'])
                    ->where('article_code', $article['article_id'])
                    ->update([
                        'new_stock_return' =>  $article['converted_amount'] - $article['cesion'],
                    ]);
            }

            $warehouse_movement_id = $article['warehouse_movement_id'];

            //Actualizar estado
            WarehouseMovement::where('id', $warehouse_movement_id)
                ->update([
                    'state' => 1,
                ]);
        }

        $data = new stdClass();
        $data->type = 1;
        $data->title = '¡Ok!';
        $data->msg = 'Registro actualizado exitosamente.';

        return response()->json($data);
    }
}
