<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\WarehouseType;
use App\Article;

class ReportsGlpGlobalController extends Controller {
    public function index() {
		return view('backend.reports_glp_global');
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

		$articles = Article::select('id', 'warehouse_type_id', 'stock_good', 'group_id', 'name', 'code')
			->whereIn('warehouse_type_id', $warehouse_type_ids)
			->whereIn('group_id', [26, 27])
			->get();

		$stocks_articles = [];

		$envasado = [
			'description' => 'Transito',
			'name' => 'GLP KG ENVASADO',
			'group_id' => 26,
			'stock_transito' => 0,
			'stock_envasado' => 0,
			'stock_comprado' => 0,
			'stock_producido' => 0,
			'stock' => 0
		];
		$granel = [
			'description' => 'Transito',
			'name' => 'GLP KG GRANEL',
			'group_id' => 27,
			'stock_transito' => 0,
			'stock_envasado' => 0,
			'stock_comprado' => 0,
			'stock_producido' => 0,
			'stock' => 0
		];

		foreach ($articles as $article) {
			$group_id = $article->group_id;
			$stock = $article->stock_good;
			$warehouse_type_id = $article->warehouse_type_id;
			$code = $article->code;

			if (in_array($warehouse_type_id, $cisternas_array)) {
				if ($group_id == 26) {
					$envasado['stock'] += $stock;
					$envasado['stock_transito'] += $stock;
				} elseif ($group_id == 27) {
					$granel['stock'] += $stock;
					$granel['stock_transito'] += $stock;
				};
			} elseif (in_array($warehouse_type_id, $proovedores_array)) {
				if ($group_id == 26) {
					$envasado['stock'] += $stock;
					$envasado['stock_comprado'] += $stock;
				} elseif ($group_id == 27) {
					$granel['stock'] += $stock;
					$granel['stock_comprado'] += $stock;
				};
			} elseif (in_array($warehouse_type_id, $plantas_array)) {
				if ($group_id == 26) {
					$envasado['stock'] += $stock;
					if ($code) {
						$envasado['stock_envasado'] += $stock;
					} else {
						$envasado['stock_producido'] += $stock;
					}
				} elseif ($group_id == 27) {
					$granel['stock'] += $stock;
				};
			}
		}

		array_push($stocks_articles, $envasado);
		array_push($stocks_articles, $granel);

		return $stocks_articles;
	}
}