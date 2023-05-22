<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\Article;

class ReportsEnvasesGeneralController extends Controller {
    public function index() {
		return view('backend.reports_envases_general');
	}

	public function getStockArticles() {
		$types_array = request('warehouse_types_array');

		$warehouse_type_ids = WarehouseType::select('id')
			->whereIn('type', $types_array)
			->get();

		$array_warehouse_type_ids = [];

		foreach ($warehouse_type_ids as $warehouse_type_id) {
			array_push($array_warehouse_type_ids, $warehouse_type_id->id);
		}

		$cisternas = WarehouseType::select('id')
			->whereIn('type', [3, 4])
			->get();

		$cisternas_array = [];

		foreach ($cisternas as $cisterna) {
			array_push($cisternas_array, $cisterna->id);
		}

		$proovedores = WarehouseType::select('id')
			->whereIn('type', [2])
			->get();

		$proovedores_array = [];

		foreach ($proovedores as $proovedor) {
			array_push($proovedores_array, $proovedor->id);
		}

		$plantas = WarehouseType::select('id')
			->whereIn('type', [5])
			->get();

		$plantas_array = [];

		foreach ($plantas as $planta) {
			array_push($plantas_array, $planta->id);
		}

		$all = array_merge($cisternas_array, $proovedores_array, $plantas_array);

		$articles = Article::select(
			'id',
			'warehouse_type_id',
			'stock_good',
			'stock_repair',
			'stock_return',
			'stock_damaged',
			'stock_minimum',
			'group_id',
			'name',
			'code')
			->whereIn('warehouse_type_id', $warehouse_type_ids)
			->where('group_id', 7)
			->get();

		$stocks_articles = [];

		$balon5 = [
			'name' => 'BALON 5KG GLP',
			'code' => 11,
			'stock_good' => 0,
			'stock_repair' => 0,
			'stock_return' => 0,
			'stock_damaged' => 0,
			'stock_minimum' => 0,
			'stock' => 0
		];
		$balon10 = [
			'name' => 'BALON 10KG GLP',
			'code' => 12,
			'stock_good' => 0,
			'stock_repair' => 0,
			'stock_return' => 0,
			'stock_damaged' => 0,
			'stock_minimum' => 0,
			'stock' => 0
		];
		$balon15 = [
			'name' => 'BALON 15KG GLP',
			'code' => 13,
			'stock_good' => 0,
			'stock_repair' => 0,
			'stock_return' => 0,
			'stock_damaged' => 0,
			'stock_minimum' => 0,
			'stock' => 0
		];
		$balon45 = [
			'name' => 'BALON 45KG GLP',
			'code' => 14,
			'stock_good' => 0,
			'stock_repair' => 0,
			'stock_return' => 0,
			'stock_damaged' => 0,
			'stock_minimum' => 0,
			'stock' => 0
		];

		foreach ($articles as $article) {
			$stock = $article->stock_good + $article->stock_repair + $article->stock_return + $article->stock_damaged + $article->stock_minimum;
			$warehouse_type_id = $article->warehouse_type_id;
			$code = $article->code;

			if ($code == 11) {
				$balon5['stock_good'] += $article->stock_good;
				$balon5['stock_repair'] += $article->stock_repair;
				$balon5['stock_return'] += $article->stock_return;
				$balon5['stock_damaged'] += $article->stock_damaged;
				$balon5['stock_minimum'] += $article->stock_minimum;
				$balon5['stock'] += $stock;
			} elseif ($code == 12) {
				$balon10['stock_good'] += $article->stock_good;
				$balon10['stock_repair'] += $article->stock_repair;
				$balon10['stock_return'] += $article->stock_return;
				$balon10['stock_damaged'] += $article->stock_damaged;
				$balon10['stock_minimum'] += $article->stock_minimum;
				$balon10['stock'] += $stock;
			} elseif ($code == 13) {
				$balon15['stock_good'] += $article->stock_good;
				$balon15['stock_repair'] += $article->stock_repair;
				$balon15['stock_return'] += $article->stock_return;
				$balon15['stock_damaged'] += $article->stock_damaged;
				$balon15['stock_minimum'] += $article->stock_minimum;
				$balon15['stock'] += $stock;
			}  elseif ($code == 14) {
				$balon45['stock_good'] += $article->stock_good;
				$balon45['stock_repair'] += $article->stock_repair;
				$balon45['stock_return'] += $article->stock_return;
				$balon45['stock_damaged'] += $article->stock_damaged;
				$balon45['stock_minimum'] += $article->stock_minimum;
				$balon45['stock'] += $stock;
			}
		}

		array_push($stocks_articles, $balon5);
		array_push($stocks_articles, $balon10);
		array_push($stocks_articles, $balon15);
		array_push($stocks_articles, $balon45);

		return $stocks_articles;
	}
}