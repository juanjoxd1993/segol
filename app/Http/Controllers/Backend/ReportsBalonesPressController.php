<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\WarehouseMovement;
use App\Article;
use App\Sale;
use App\Client;
use App\Container;
use App\ContainerDetail;
use Carbon\CarbonImmutable;

class ReportsBalonesPressController extends Controller {
  public function index() {
		$plantas = WarehouseType::select('id', 'name')
                          ->where('type', 3)
                          ->get();

		return view('backend.reports_balones_press')->with(compact('plantas'));
	}

	public function getContainers() {
		$movement_type_id = request('movement_type_id');
		$date_init = CarbonImmutable::createFromDate(request('date_init'))->startOfDay()->format('Y-m-d');
		$date_end = CarbonImmutable::createFromDate(request('date_end'))->endOfDay()->format('Y-m-d');

		$containers = Container::where('date', '>=', $date_init)
                            ->where('date', '<=', $date_end)
                            ->where('if_devol', $movement_type_id)
                            ->select('id',
                                    'client_id',
                                    'if_devol',
                                    'warehouse_movement_id',
                                    'date')
                            ->get();

    foreach ($containers as $container) {
      $container_detail = ContainerDetail::find($container->id);
      $client = Client::find($container->client_id);
      $warehouse_movement = WarehouseMovement::find($container->warehouse_movement_id);

      $article = Article::find($container_detail->article_id);

      $warehouse_type = WarehouseType::find($warehouse_movement->warehouse_type_id);

      $container->client_name = $client->business_name;
      $container->stock = $container_detail->devol;
      $container->article_name = $article->name;
      $container->warehouse_type_name = $warehouse_type->name;
    }

		return $containers;
	}
}