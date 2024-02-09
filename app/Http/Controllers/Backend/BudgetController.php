<?php

namespace App\Http\Controllers\Backend;

use App\Budget;
use App\BusinessUnit;
use App\Classification;
use App\ClientChannel;

use App\ClientZone;
use App\ClientRoute;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Sale;
use App\SaleDetail;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BudgetController extends Controller
{
    public function index() {
		$business_units = BusinessUnit::select('id', 'name')->get();

		return view('backend.budgets')->with(compact('business_units'));
	}

	public function validateForm() {
		$messages = [
			'business_unit_id.required'	=> 'Debe seleccionar una Unidad de Negocio.',
			'year_month.required'		=> 'Debe seleccionar un Periodo.',
			'days.required'				=> 'Los Días son obligatorios.',
		];

		$rules = [
			'business_unit_id'	=> 'required',
			'year_month'		=> 'required',
			'days'				=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$this->validateForm();
		
		$business_unit_id = request('business_unit_id');
		$year_month = CarbonImmutable::createFromDate(request('year_month'));
		$days = request('days');

		// $business_unit_id = 2;
		// $year_month = CarbonImmutable::createFromDate('2020-02');

		$year = $year_month->format('Y');
		$month = $year_month->format('m');

		if ( $business_unit_id == 1 || $business_unit_id == 3 ) {
			$elements = ClientRoute::select('id', 'name')
				->get();
		} elseif ( $business_unit_id == 2 ) {
			$elements = ClientChannel::select('id', 'name')
				->get();
		}

		// $elements = Budget::join('business_units', 'business_units.id', '=', 'budgets.business_unit_id')
		// 	->leftjoin('classifications as groups', 'groups.id', '=', 'budgets.group_id')
		// 	->leftjoin('classifications as subgroups', 'subgroups.id', '=', 'budgets.subgroup_id')
		// 	->leftjoin('regions', 'regions.id', '=', 'budgets.region_id')
		// 	->where('business_unit_id', $business_unit_id)
		// 	->where('year', $year)
		// 	->where('month', $month)
		// 	->select('budgets.id', 'year', 'month', 'days', 'business_unit_id', 'business_units.name as business_unit_name', 'client_channel_id', 'client_sector_id', 'client_zone_id', 'group_id', 'groups.name as group_name', 'subgroup_id', 'subgroups.name as subgroup_name', 'region_id', 'regions.name as region_name', 'metric_tons', 'total', 'percentage')
		// 	->when($business_unit_id == 1 || $business_unit_id == 3, function($query) {
		// 		return $query->leftjoin('client_zones', 'client_zones.id', '=', 'budgets.client_zone_id')
		// 			->addSelect('client_zones.name as client_zone_name')
		// 			->orderBy('client_zone_id', 'ASC');
		// 	})
		// 	->when($business_unit_id == 2, function($query) {
		// 		return $query->leftjoin('client_sectors', 'client_sectors.id', '=', 'budgets.client_sector_id')
		// 			->addSelect('client_sectors.name as client_sector_name')
		// 			->orderBy('client_sector_id', 'ASC');
		// 	})
		// 	->get();

		return response()->json([
			'elements'			=> $elements,
			'business_unit_id'	=> $business_unit_id,
			'year'				=> $year,
			'month'				=> $month,
			'days'				=> $days,
		]);
	}

	public function store() {
		$business_unit_id = request('business_unit_id');
		$year = request('year');
		$month = request('month');
		$days = request('days');
		$ids = request('ids');
		$metric_tons = request('metric_tons');
		$totals = request('totals');
		$percentages = request('percentages');
		$elements_total_mt = request('elements_total_mt');
		$elements_total = request('elements_total');
		$elements_total_percentage = request('elements_total_percentage');

		$same_month = Budget::where('year', $year)
			->where('month', $month)
			->where('business_unit_id', $business_unit_id)
			->get();

		if ( $same_month ) {
			foreach ($same_month as $sm) {
				$sm->delete();
			}
		}

		// $business_unit_id = 1;
		// $year = '2020';
		// $month = '03';

		foreach ($ids as $index => $id) {
			$budget = new Budget();
			$budget->year = $year;
			$budget->month = $month;
			$budget->days = $days;
			$budget->business_unit_id = $business_unit_id;
			if ( $business_unit_id == 1 || $business_unit_id == 3 ) {
				$budget->client_route_id = $id;
			} elseif ( $business_unit_id == 2 ) {
				$budget->client_channel_id = $id;
			}
			$budget->metric_tons = $metric_tons[$index];
			$budget->total = $totals[$index];
			$budget->percentage = $percentages[$index];
			$budget->save();
		}

		$totalBudget = new Budget();
		$totalBudget->year = $year;
		$totalBudget->month = $month;
		$totalBudget->days = $days;
		$totalBudget->business_unit_id = $business_unit_id;
		$totalBudget->metric_tons = $elements_total_mt;
		$totalBudget->total = $elements_total;
		$totalBudget->percentage = $elements_total_percentage;
		$totalBudget->sale_option_id = 1;
		$totalBudget->save();

		$date = CarbonImmutable::createFromDate($year.'-'.$month);
		$previous_month = $date->subMonth(1);
		$first_day = $previous_month->firstOfMonth()->format('Y-m-d');
		$last_day = $previous_month->lastOfMonth()->format('Y-m-d');

		// $budget_channels = Budget::where('year', $previous_month->format('Y'))
		// 	->where('month', $previous_month->format('m'))
		// 	->where('business_unit_id', $business_unit_id)
		// 	->where('client_channel_id', '!=', null)
		// 	->select('client_channel_id', 'percentage')
		// 	->get();

		$budget_routes = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
			->join('articles', 'articles.id', '=', 'sale_details.article_id')
			->where('sales.sale_date', '>=', $first_day)
			->where('sales.sale_date', '<=', $last_day)
			->where('clients.business_unit_id', $business_unit_id)
			->select('clients.route_id as client_route_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
			->groupBy('clients.route_id')
			->orderBy('clients.route_id', 'asc')
			->get();

		$routes_total_metric_tons = $budget_routes->sum('sum_metric_tons');
		$routes = ClientRoute::select('id', 'name')->get();

		foreach ($routes as $route) {
			$element = new Budget();
			$element->year = $year;
			$element->month = $month;
			$element->days = $days;
			$element->business_unit_id = $business_unit_id;
			$element->client_route_id = $route->id;

			$filtered = $budget_routes->first(function ($item, $key) use ($route) {
				return $item->client_route_id === $route->id;
			});

			if ( $filtered ) {
				$percentage = ( $filtered->sum_metric_tons * 100 ) / $routes_total_metric_tons;
				$element->metric_tons = ( $totalBudget->metric_tons * $percentage ) / 100;
				$element->total = ( $totalBudget->total * $percentage ) / 100;
				$element->percentage = $percentage;
			} else {
				$element->metric_tons = 0;
				$element->total = 0;
				$element->percentage = 0;
			}

			$element->sale_option_id = 2;
			$element->save();
		}

		if ( $business_unit_id == 1 || $business_unit_id == 3 ) {
			// $budget_sectors = Budget::where('year', $previous_month->format('Y'))
			// 	->where('month', $previous_month->format('m'))
			// 	->where('business_unit_id', $business_unit_id)
			// 	->where('client_sector_id', '!=', null)
			// 	->select('client_sector_id', 'percentage')
			// 	->get();

			$budget_routes = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
				->join('articles', 'articles.id', '=', 'sale_details.article_id')
				->where('sales.sale_date', '>=', $first_day)
				->where('sales.sale_date', '<=', $last_day)
				->where('clients.business_unit_id', $business_unit_id)
				->select('clients.route_id as client_route_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
				->groupBy('clients.route_id')
				->orderBy('clients.route_id', 'asc')
				->get();

			$routes_total_metric_tons = $budget_routes->sum('sum_metric_tons');
			$routes = ClientRoute::select('id', 'name')->get();

			foreach ($routes as $route) {
				$element = new Budget();
				$element->year = $year;
				$element->month = $month;
				$element->days = $days;
				$element->business_unit_id = $business_unit_id;
				$element->client_route_id = $route->id;

				$filtered = $budget_routes->first(function ($item, $key) use ($route) {
					return $item->client_sector_id === $route->id;
				});

				if ( $filtered ) {
					$percentage = ( $filtered->sum_metric_tons * 100 ) / $routes_total_metric_tons;
					$element->metric_tons = ( $totalBudget->metric_tons * $percentage ) / 100;
					$element->total = ( $totalBudget->total * $percentage ) / 100;
					$element->percentage = $percentage;
				} else {
					$element->metric_tons = 0;
					$element->total = 0;
					$element->percentage = 0;
				}

				$element->sale_option_id = 2;
				$element->save();
			}
		} elseif ( $business_unit_id == 2 ) {
			// $budget_zones = Budget::where('year', $previous_month->format('Y'))
			// 	->where('month', $previous_month->format('m'))
			// 	->where('business_unit_id', $business_unit_id)
			// 	->where('client_zone_id', '!=', null)
			// 	->select('client_zone_id', 'percentage')
			// 	->get();
			
			$budget_channels = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
				->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
				->join('articles', 'articles.id', '=', 'sale_details.article_id')
				->where('sales.sale_date', '>=', $first_day)
				->where('sales.sale_date', '<=', $last_day)
				->where('clients.business_unit_id', $business_unit_id)
				->select('clients.channel_id as client_channel_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
				->groupBy('clients.channel_id')
				->orderBy('clients.channel_id', 'asc')
				->get();

			$channel_total_metric_tons = $budget_channels->sum('sum_metric_tons');
			$channels = ClientChannel::select('id', 'name')->get();

			foreach ($channels as $channel) {
				$element = new Budget();
				$element->year = $year;
				$element->month = $month;
				$element->days = $days;
				$element->business_unit_id = $business_unit_id;
				$element->client_channel_id = $channel->id;

				$filtered = $budget_channels->first(function ($item, $key) use ($channel) {
					return $item->client_channel_id === $channel->id;
				});

				if ( $filtered ) {
					$percentage = ( $filtered->sum_metric_tons * 100 ) / $channel_total_metric_tons;
					$element->metric_tons = ( $totalBudget->metric_tons * $percentage ) / 100;
					$element->total = ( $totalBudget->total * $percentage ) / 100;
					$element->percentage = $percentage;
				} else {
					$element->metric_tons = 0;
					$element->total = 0;
					$element->percentage = 0;
				}

				$element->sale_option_id = 3;
				$element->save();
			}
		}

		// $budget_families = Budget::where('year', $previous_month->format('Y'))
		// 	->where('month', $previous_month->format('m'))
		// 	->where('business_unit_id', $business_unit_id)
		// 	->where('family_id', '!=', null)
		// 	->select('family_id', 'percentage')
		// 	->get();

		$budget_families = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
			->join('articles', 'articles.id', '=', 'sale_details.article_id')
			->where('sales.sale_date', '>=', $first_day)
			->where('sales.sale_date', '<=', $last_day)
			->where('clients.business_unit_id', $business_unit_id)
			->select('articles.family_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
			->groupBy('articles.family_id')
			->orderBy('articles.family_id', 'asc')
			->get();

		$families_total_metric_tons = $budget_families->sum('sum_metric_tons');
		$families = Classification::where('id', '>=', 1)
			->where('id', '<=', 6)
			->orWhere('id', 239)
			->select('id', 'name')
			->get();

		foreach ($families as $family) {
			$element = new Budget();
			$element->year = $year;
			$element->month = $month;
			$element->days = $days;
			$element->business_unit_id = $business_unit_id;
			$element->family_id = $family->id;

			$filtered = $budget_families->first(function ($item, $key) use ($family) {
				return $item->family_id === $family->id;
			});

			if ( $filtered ) {
				$percentage = ( $filtered->sum_metric_tons * 100 ) / $families_total_metric_tons;
				$element->metric_tons = ( $totalBudget->metric_tons * $percentage ) / 100;
				$element->total = ( $totalBudget->total * $percentage ) / 100;
				$element->percentage = $percentage;
			} else {
				$element->metric_tons = 0;
				$element->total = 0;
				$element->percentage = 0;
			}

			$element->sale_option_id = 4;
			$element->save();
		}

		// $budget_subgroups = Budget::where('year', $previous_month->format('Y'))
		// 	->where('month', $previous_month->format('m'))
		// 	->where('business_unit_id', $business_unit_id)
		// 	->where('subgroup_id', '!=', null)
		// 	->select('subgroup_id', 'percentage')
		// 	->get();

		$budget_subgroups = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
			->join('articles', 'articles.id', '=', 'sale_details.article_id')
			->where('sales.sale_date', '>=', $first_day)
			->where('sales.sale_date', '<=', $last_day)
			->where('clients.business_unit_id', $business_unit_id)
			->select('articles.subgroup_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
			->groupBy('articles.subgroup_id')
			->orderBy('articles.subgroup_id', 'asc')
			->get();

		$subgroups_total_metric_tons = $budget_subgroups->sum('sum_metric_tons');
		$subgroups = Classification::where('id', '>=', 54)
			->where('id', '<=', 58)
			->select('id', 'name')
			->get();

		foreach ($subgroups as $subgroup) {
			$element = new Budget();
			$element->year = $year;
			$element->month = $month;
			$element->days = $days;
			$element->business_unit_id = $business_unit_id;
			$element->subgroup_id = $subgroup->id;
			
			$filtered = $budget_subgroups->first(function ($item, $key) use ($subgroup) {
				return $item->subgroup_id === $subgroup->id;
			});

			if ( $filtered ) {
				$percentage = ( $filtered->sum_metric_tons * 100 ) / $subgroups_total_metric_tons;
				$element->metric_tons = ( $totalBudget->metric_tons * $percentage ) / 100;
				$element->total = ( $totalBudget->total * $percentage ) / 100;
				$element->percentage = $percentage;
			} else {
				$element->metric_tons = 0;
				$element->total = 0;
				$element->percentage = 0;
			}

			$element->sale_option_id = 5;
			$element->save();
		}

		return response()->json([
			'type'	=> 1,
			'title'	=> '¡Ok!',
			'msg'	=> 'Registro creado correctamente.'
		]);
	}
}
