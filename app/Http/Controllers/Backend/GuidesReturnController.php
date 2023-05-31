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

use App\Client;
use App\ClientLiquidations;
use App\GuidesState;
use App\WarehouseTypeInUser;
use PDF;
use App\ControlSerie;
use Exception;

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

		$guide_state = GuidesState::select('id')
						->where('name', 'Validada')
						->first();

        $elements = WarehouseMovement::select(
            'id',
            'referral_guide_series',
            'referral_guide_number',
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
            ->where('state', $guide_state->id)
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

        $company_id = request('model.company_id');
        $warehouse_movement_id = request('model.warehouse_movement_id');

        $movementDetails = WarehouseMovementDetail::select(
            'id',
            'warehouse_movement_id',
            'item_number',
            'article_code',
            'digit_amount',
            'converted_amount'
            )
            ->where('warehouse_movement_id', $warehouse_movement_id)
            ->orderBy('item_number', 'asc')
            ->get();

        $movementDetails->map(function ($item, $index) {

            $parse_converted_amount = intval(floatval($item->converted_amount));
            $parse_digit_amount = intval(floatval($item->digit_amount));

            $item->parent = null;
            $item->article_id = $item->article->id;
            $item->article_code = $item->article->code;
            $item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
            $item->presale = $parse_digit_amount;
            $item->presale_converted_amount = $parse_converted_amount;
            $item->retorno = 0;
            $item->retorno_press = 0;
            $item->cambios = 0;
            $item->prestamo = 0;
            $item->cesion = 0;
            $item->vacios = 0;
            $item->group_id = $item->article->group_id;

            if ($item->article->group_id == 26) {
                $item->liquidar = $parse_digit_amount;
            } else {
                $item->liquidar = 0;
            };
            
            $item->retorno = 0;
            $item->cambios = 0;
            $item->prestamo = 0;
            $item->cesion = 0;
            $item->vacios = 0;
        });

        $elements = array();

        foreach ($movementDetails as $val) {
            array_push($elements, $val);
        };

        $elements = array_filter($elements, function($val) {
            return $val->group_id != 7;
        });

        return $elements;
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
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
			->where('user_id', $user_id)
			->first();

        $articles = $request->articles;
        $clients = $request->clients;
        $warehouse_movement_id = $request->warehouse_movement_id;
		$warehouse_type_id = $warehouse_type_user->warehouse_type_id;

		$guide_state = GuidesState::select('id')
            ->where('name', 'Por Liquidar')
            ->first();

        foreach ($articles as $article) {
            $warehouse_movement_detail_id = $article['id'];

            $warehouse_movement_detail = WarehouseMovementDetail::find($warehouse_movement_detail_id);

            $articleGeneral = Article::where('warehouse_type_id', 5)
                ->where('id', $article['article_id'])
                ->first();

            $articleWT = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('code', $articleGeneral->code)
                ->first();

            $articleBalon = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('convertion', $articleWT->convertion)
                ->where('group_id', 7)
                ->first();

            $articleEnvasado = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('code', 2)
                ->first();

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
                // actualiza el stock_return
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_good' => DB::raw('stock_good + ' . $article['retorno'])
                //     ]);

                $articleWT->stock_good += $article['retorno'];
                $articleBalon->stock_return -= $article['retorno'];
            }

            if ($article['retorno_press'] > 0) {

                /********** Retornos Llenos - Movimiento *********/
                //Generar Movimiento de Ingreso - Producción
                $id = WarehouseMovement::insertGetId([
                    'company_id' => 1,
                    'warehouse_type_id' => 4, //Producción
                    'movement_class_id' => 1, //Ingreso
                    'movement_type_id' => 19, //Retorno de Pre-Venta
                    'warehouse_account_type_id' => 1,
                    'total' => $article['retorno_press'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                WarehouseMovementDetail::insert([
                    'warehouse_movement_id' => $id,
                    'item_number' => 1,
                    'article_code' => $articleBalon->id,
                    'new_stock_good' => $article['retorno_press'],
                    'converted_amount' => $article['retorno_press'],
                    'total' => $article['retorno'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                // descuenta al stock_repair
                $articleBalon->stock_repair -= $article['retorno_press'];
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_repair' => DB::raw('stock_repair - ' . $article['retorno_press'])
                //     ]);
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
                    'article_code' => $articleBalon->id,
                    'new_stock_good' => $article['cambios'],
                    'converted_amount' => $article['cambios'],
                    'total' => $article['cambios'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock damaged por el Movimiento
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_damaged' => DB::raw('stock_damaged + ' . $article['cambios']),
                //     ]);
                $articleBalon->stock_damaged += $article['cambios'];
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
                    'article_code' => $articleBalon->id,
                    'new_stock_good' => $article['prestamo'],
                    'converted_amount' => $article['prestamo'],
                    'total' => $article['prestamo'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                // solo debe actualizar el stock_repair aumentandolo
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_repair' => DB::raw('stock_repair + ' . $article['prestamo']),
                //     ]);
                $articleBalon->stock_repair += $article['prestamo'];
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
                    'article_code' => $articleBalon->id,
                    'new_stock_good' => $article['cesion'],
                    'new_stock_return' => $article['presale_converted_amount'] - $article['cesion'],
                    'converted_amount' => $article['cesion'],
                    'total' => $article['cesion'],
                    'created_at' => date('Y-m-d'),
                    'updated_at' => date('Y-m-d'),
                ]);

                //Actualizar Stock por el Movimiento
                // Solo deberia mover el stock_good descontandole y tambien el stock_minimum
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_good' => DB::raw('stock_good - ' . $article['cesion']),
                //         'stock_minimum' => DB::raw('stock_minimum + ' . $article['cesion']),
                //     ]);

                $warehouse_movement_detail->old_stock_cesion = $warehouse_movement_detail->new_stock_cesion;
                $warehouse_movement_detail->new_stock_cesion = $article['cesion'];

                $articleBalon->stock_return -= $article['cesion'];
                $articleBalon->stock_minimum += $article['cesion'];
            }

            if($article['vacios']){
                // Solo debe actualizar el stock _good
                // Article::where('id', $article['article_id'])
                //     ->update([
                //         'stock_good' => DB::raw('stock_good + ' .$article['vacios']),
                //     ]);
                $articleBalon->stock_good += $article['vacios'];
            }

            $articleWT->save();
            $articleBalon->save();
            $warehouse_movement_detail->save();
            /**Off */

            $articleDetail = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('code', $article['article_code'])
                ->first();

            //Actualizar new_stock_return
            if ($article['article']['group_id'] == 26) {
                WarehouseMovementDetail::where('warehouse_movement_id', $article['warehouse_movement_id'])
                    ->where('article_code', $article['article_id'])
                    ->update([
                        'new_stock_return' =>  $article['retorno'] + $article['cambios'],
                    ]);
            } else if ($article['article']['group_id'] == 7) {
                // el new_stock_return deberia solo registrar los blones en buen estado y no los prestamos ni los mal estado
                // WarehouseMovementDetail::where('warehouse_movement_id', $article['warehouse_movement_id'])
                //     ->where('article_code', $article['article_id'])
                //     ->update([
                //         'new_stock_return' =>  $article['converted_amount'] - $article['cesion'],
                //     ]);
                WarehouseMovementDetail::where('warehouse_movement_id', $article['warehouse_movement_id'])
                    ->where('article_code', $article['article_id'])
                    ->update([
                        'new_stock_return' =>  $article['converted_amount'] - $article['cesion'] - $article['prestamo'] - $article['cambios'],
                    ]);
            }

            $warehouse_movement_id = $article['warehouse_movement_id'];

            //Actualizar estado
            WarehouseMovement::where('id', $warehouse_movement_id)
                ->update([
                    'state' => $guide_state->id,
                ]);

        }

        foreach ($clients as $client) {
            $client_liquidation = new ClientLiquidations;
            $client_liquidation->warehouse_movement_id = $request->warehouse_movement_id;
            $client_liquidation->client_id = $client ['client_id'];
            $client_liquidation->article_id = $client['article_id'];
            $client_liquidation->quantity = $client['liquidation'];
            $client_liquidation->save();
        };

        $data = new stdClass();
        $data->type = 1;
        $data->title = '¡Ok!';
        $data->msg = 'Registro actualizado exitosamente.';

        return $this->generatePdf($warehouse_movement_id, $articles);
    }

    public function getClients()
    {
        $company_id = request('company_id');
        $client_id = request('client_id');
        $q = request('q');
        if ( isset($client_id) ) {
            $elements = Client::select('id', 'code', 'business_name', 'payment_id', 'perception_percentage_id', 'credit_limit')
                ->where('id', $client_id)
                ->first();
            $elements->text = $elements->business_name;
            unset($elements->business_name);
        } else {
            $elements = Client::select('id', 'code', 'business_name', 'document_type_id', 'payment_id', 'perception_percentage_id', 'credit_limit')
                ->where('company_id', $company_id)
                ->where('business_name', 'like', '%'.$q.'%') ->orWhere('id', 'like', '%'.$q.'%')
                ->orderBy('business_name', 'asc')
                ->with(['perception_percentage' => function ($query) {
                    $query->select('id', 'value');
                }])
                ->get();
            $elements->map(function($item, $index) {
                $item->text = $item->id . ' - ' .$item->business_name;
                unset($item->business_name);
                unset($item->code);
                return $item;
            });
        }

        return $elements;
    }

    public function getBalon()
    {
        $article = request('article');

        $article_id = $article['article_id'];
        $article_cesion = $article['cesion'];

        $articleGeneral = Article::find($article_id);

        $articleBalon = Article::where('warehouse_type_id', 5)
            ->where('convertion', $articleGeneral->convertion)
            ->where('group_id', 7)
            ->first();

        $articleBalon->parent = null;
        $articleBalon->article_id = $articleBalon->id;
        $articleBalon->article_code = $articleBalon->code;
        $articleBalon->article_name = $articleBalon->name . ' ' . $articleBalon->warehouse_unit->name . ' x ' . $articleBalon->package_warehouse;
        $articleBalon->presale = 0;
        $articleBalon->presale_converted_amount = 0;
        $articleBalon->retorno = 0;
        $articleBalon->retorno_press = 0;
        $articleBalon->cambios = 0;
        $articleBalon->prestamo = 0;
        $articleBalon->cesion = $article_cesion;
        $articleBalon->vacios = 0;
        $articleBalon->liquidar = $article_cesion;
        $articleBalon->group_id = $articleBalon->group_id;

        return $articleBalon;
    }

    public function generatePdf($warehouse_movement_id, $elements)
	{
		$user_id = Auth::user()->id;

		$warehouse_type_user = WarehouseTypeInUser::select('warehouse_type_id')
			->where('user_id', $user_id)
			->first();

        $warehouse_type_id = $warehouse_type_user->warehouse_type_id;

        $warehouse_movement = WarehouseMovement::find($warehouse_movement_id);

        $current_date = CarbonImmutable::now()->startOfDay()->format('d/m/Y');
        $current_time = CarbonImmutable::now()->format('h:i A');

        $number_page = 1;

        $control_serie = ControlSerie::select('num_serie', 'correlative')
                                    ->where('num_serie', $warehouse_movement->referral_guide_series)
                                    ->first();

        $control_serie_number = $control_serie->num_serie;
        $control_serie_correlative = $control_serie->correlative;

        $name_full_data = $warehouse_movement->account_name;

        $state = 'ACTIVO';

        $number_placa = $warehouse_movement->license_plate;

        $guide_remision = 'GR/' . $warehouse_movement->referral_guide_series . '-' . $warehouse_movement->referral_guide_number;

        $turno = 'MAÑANA';

        $articles = array();

        $total_quantity = 0;
        $total_return = 0;
        $total_vacios = 0;
        $total_damaged = 0;
        $total_transito = 0;
        $total_consignacion = 0;
        $total_devolucion = 0;
        $total_observador = 0;
        $total_deposito = 0;
        $total = 0;

        foreach ($elements as $element) {
            $articleGeneral = Article::where('warehouse_type_id', 5)
                ->where('id', $element['article_id'])
                ->first();

            $articleWT = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('code', $articleGeneral->code)
                ->first();

            $articleBalon = Article::where('warehouse_type_id', $warehouse_type_id)
                ->where('convertion', $articleWT->convertion)
                ->where('group_id', 7)
                ->first();

            $article = new stdClass();

            $article->name = $articleBalon->name;
            $article->quantity = $element['presale'];
            $article->return = $element['retorno'];
            $article->vacios = $element['presale'] - $element['retorno'] - $element['cambios'];
            $article->damaged = $element['cambios'];
            $article->transito = 0;
            $article->consignacion = 0;
            $article->devolucion = 0;
            $article->observador = 0;
            $article->deposito = 0;
            $article->total = $article->quantity + $article->return + $article->vacios + $article->damaged + $article->transito + $article->consignacion + $article->devolucion + $article->observador + $article->deposito;

            array_push($articles, $article);

            $total_quantity += $article->quantity;
            $total_return += $article->return;
            $total_vacios += $article->vacios;
            $total_damaged += $article->damaged;
            $total_transito += $article->transito;
            $total_consignacion += $article->consignacion;
            $total_devolucion += $article->devolucion;
            $total_observador += $article->observador;
            $total_deposito += $article->deposito;
            $total += $article->total;
        };

		try{
			$pdf = PDF::loadView('backend.pdf.return_guide',
                                compact('current_date',
                                        'current_time',
                                        'number_page',
                                        'control_serie_number',
                                        'control_serie_correlative',
                                        'name_full_data',
                                        'state',
                                        'number_placa',
                                        'guide_remision',
                                        'turno',
                                        'articles',
                                        'total_quantity',
                                        'total_return',
                                        'total_vacios',
                                        'total_damaged',
                                        'total_transito',
                                        'total_consignacion',
                                        'total_devolucion',
                                        'total_observador',
                                        'total_deposito',
                                        'total'));

			return $pdf->download('guia-de-retorno-N°-' . $warehouse_movement->referral_guide_series . '-' . $warehouse_movement->referral_guide_number . '.pdf');
		}catch(Exception $e){
			return '';
		}
	}
}
