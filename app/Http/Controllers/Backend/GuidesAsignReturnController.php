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
use App\Container;
use App\ContainerDetail;
use Auth;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;

class GuidesAsignReturnController extends Controller
{
    
	public function index()
	{
		$companies = Company::select('id', 'name')->get();

		return view('backend.guides_asign_return')->with(compact('companies'));
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
            $containers = Container::select(
                                        'id',
                                        'client_id'
                                    )
                                    ->where('warehouse_movement', $element->id)
                                    ->where('if_devol', 1)
                                    ->where('if_asign', 0)
                                    ->get();

            if (count($containers)) {
                array_push($array_movments, $element);
            }
        };

        $array_movments = array_reverse($array_movments);

        return $array_movments;
    }

    public function viewDetail()
    {
        $id = request('id');

        $data = [];

        $containers = Container::select(
                                    'id',
                                    'client_id'
                                )
                                ->where('warehouse_movement', $id)
                                ->where('if_devol', 1)
                                ->where('if_asign', 0)
                                ->get();

        foreach ($containers as $container) {
            $detail = ContainerDetail::select(
                                            'devol',
                                            'rest_devol',
                                            'article_id'
                                        )
                                        ->where('container_id', $container->id)
                                        ->first();

            $article = Article::select(
                                    'code',
                                    'name'
                                )
                                ->where('id', $detail->article_id)
                                ->first();

            $client = Client::select('business_name')->where('id', $container->client_id)->first();

            $data[] = [
                'id' => $container->id,
                'devol' => $detail->rest_devol,
                'article_name' => $article->name,
                'client_name' => $client->business_name
            ];
        }

        return response()->json($data);
    }

    public function balonsPress()
    {
        $id = request('id');

        $container = Container::find($id);

        $container_detail = ContainerDetail::where('container_id', $container->id)->first();

        $containers = Container::where('client_id', $container->client_id)
                                ->where('pend_devol', 1)
                                ->get();

        $container_details = [];

        foreach ($containers as $container) {
            $element = ContainerDetail::where('container_id', $container->id)
                                                ->where('article_id', $container_detail->article_id)
                                                ->first();

            $container_details[] = $element;
        }

        return response()->json($container_details);
    }
}