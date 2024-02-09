<?php

namespace App\Http\Controllers\Backend;

use App\Budget;
use App\Classification;
use App\Client;
use App\ClientChannel;
use App\ClientRoute;
use App\ClientSector;
use App\ClientZone;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\PriceHistory;
use App\Sale;
use App\SaleDetail;
use App\SaleOption;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use stdClass;

class SalesReportController extends Controller
{
	public function index() {
		$companies = Company::select('id', 'name')->get();
		$current_date = Carbon::now();
		$max_date = date(DATE_ATOM, mktime(0, 0, 0));
		$sale_options = SaleOption::select('id', 'name')
															->where('id', '!=', 1)
															->get();

			return view('backend.sales_report')->with(compact('companies', 'current_date', 'max_date', 'sale_options'));
	}

	public function validateForm() {
		$messages = [
			'to_date.required'          => 'Debe seleccionar una Fecha Limite.',
			'sale_option_id.required'   => 'Debe seleccionar una Opción.',
			'price.required'			=> 'Debe ingresar un precio.',
			'price.numeric'				=> 'El precio debe ser mayor a 0.',
			'price.min'					=> 'El precio debe ser mayor a 0.',
			'price.not_in'				=> 'El precio debe ser mayor a 0.',
		];

		$rules = [
			'to_date'       	=> 'required',
            'sale_option_id'	=> 'required',
            'price'				=> 'required|numeric|min:0|not_in:0',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function getCurrentPrice() {
		$day = CarbonImmutable::createFromDate(request('to_date'));
		$current_month = $day->format('m');
		$current_year = $day->format('Y');

		$price = PriceHistory::where('year', $current_year)
			->where('month', $current_month)
			->select('price')
			->first();
		
		$response = $price ? $price->price : 0;

		return $response;
	}

	public function list() {
		$sale_option_id = request('model.sale_option_id');
		$day = CarbonImmutable::createFromDate(request('model.to_date'));
		$price = request('model.price');
		
		// $sale_option_id = 1;
		// $day = CarbonImmutable::createFromDate('2020-03-01');
		
		$days_removed = 0;
		$to_date = $day->format('Y-m-d');
		$first_day = $day->firstOfMonth()->format('Y-m-d');
		$last_day = $day->lastOfMonth()->format('d');
		$current_day = $day->format('j');
		$current_month = $day->format('m');
		$current_year = $day->format('Y');
		$previous_month = $day->firstOfMonth()->subMonth();
		$previous_year = $day->firstOfMonth()->subYear()->format('Y');

		switch ($sale_option_id) {
			case 2:
				$sale_option = 'client_route_id';
				$sale_table = 'client_routes';
				$sale_column = 'route_id';
				$select_table = 'clients';
				break;
			case 3:
				$sale_option = 'client_channel_id';
				$sale_table = 'client_channels';
				$sale_column = 'channel_id';
				$select_table = 'clients';
				break;
			case 4:
				$sale_option = 'family_id';
				$sale_table = 'classifications';
				$sale_column = 'family_id';
				$select_table = 'articles';
				break;
			case 5:
				$sale_option = 'subgroup_id';
				$sale_table = 'classifications';
				$sale_column = 'subgroup_id';
				$select_table = 'articles';
				break;
		}

		$elements = Budget::rightjoin('client_routes', 'client_routes.id', '=', 'budgets.client_route_id')
											->leftjoin('client_channels','client_channels.id','=','budgets.client_channel_id')
											->where('year', $current_year)
											->where('month', $current_month)
											->where('sale_option_id', $sale_option_id)
											->select('year',
															'month',
															'days',
															'client_route_id',
															'client_routes.name as client_route_name',
															$sale_option, $sale_table.'.name as sale_option_name',
															'metric_tons',
															'total',
															'percentage')
											->get();

		$previous_month_price = PriceHistory::where('year', $current_year)
																				->where('month', $previous_month->format('m'))
																				->select('price')
																				->first();
		
		$previous_month_price = $previous_month_price ? $previous_month_price->price : 0;
		
		$previous_year_price = PriceHistory::where('year', $previous_year)
			->where('month', $current_month)
			->select('price')
			->first();
		
		$previous_year_price = $previous_year_price ? $previous_year_price->price : 0;

		$sale_report = collect();

		if ( count($elements) > 0 ) {
			$group_ids = array_values(array_unique($elements->pluck('client_route_id')->all()));
			$days = array_values(array_unique($elements->pluck('days')->all()));
			$parent_elements = $elements->unique('client_route_id')->values()->all();

			foreach ($parent_elements as $parent_element) {
				$child_elements = $elements->filter(function ($value, $key) use ($parent_element) {
					return $value->client_route_id == $parent_element->client_route_id;
				});

				$families = collect();

				foreach ($child_elements as $child_element) {
					DB::enableQueryLog();

					$current_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
						->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
						->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
						->where(function($query) {
							$query->where('sales.company_id', '<>', 1)
								->orWhere(function($query) {
									$query->where('sales.company_id', 1)
										->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
								});
						})
						->where('sales.sale_date', '>=', $first_day)
						->where('sales.sale_date', '<=', $to_date)
						->where('clients.route_id', $child_element->client_route_id)
						->where($select_table.'.'.$sale_column, $child_element->$sale_option)
						->select('clients.route_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
						->groupBy('clients.route_id', $select_table.'.'.$sale_column)
						->orderBy('clients.route_id', 'asc')
						->orderBy($select_table.'.'.$sale_column, 'asc')
						->first();

					// return DB::getQueryLog();

					$previous_month_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
																						->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
																						->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
																						->where(function($query) {
																							$query->where('sales.company_id', '<>', 1)
																								->orWhere(function($query) {
																									$query->where('sales.company_id', 1)
																										->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
																								});
																						})
																						->whereYear('sales.sale_date', $previous_month->format('Y'))
																						->whereMonth('sales.sale_date', $previous_month->format('m'))
																						->where('clients.route_id', $child_element->route_id)
																						->where($select_table.'.'.$sale_column, $child_element->$sale_option)
																						->select('clients.route_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
																						->groupBy('clients.route_id', $select_table.'.'.$sale_column)
																						->orderBy('clients.route_id', 'asc')
																						->orderBy($select_table.'.'.$sale_column, 'asc')
																						->first();

					$previous_year_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
																					->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
																					->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
																					->where(function($query) {
																						$query->where('sales.company_id', '<>', 1)
																							->orWhere(function($query) {
																								$query->where('sales.company_id', 1)
																									->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
																							});
																					})
																					->whereYear('sales.sale_date', $previous_year)
																					->whereMonth('sales.sale_date', $current_month)
																					->where('clients.route_id', $child_element->client_route_id)
																					->where($select_table.'.'.$sale_column, $child_element->$sale_option)
																					->select('clients.route_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
																					->groupBy('clients.route_id', $select_table.'.'.$sale_column)
																					->orderBy('clients.route_id', 'asc')
																					->orderBy($select_table.'.'.$sale_column, 'asc')
																					->first();

					$obj = new stdClass();
					$obj->family_name = $child_element->sale_option_name;
					$obj->current_advance = ( isset($current_query) ? $current_query->sum_metric_tons : 0 );
					$obj->current_projection = ( $obj->current_advance > 0 ? ( $obj->current_advance / $current_day ) * $last_day : 0 );
					$obj->previous_month = ( isset($previous_month_query) ? $previous_month_query->sum_metric_tons : 0 );
					$obj->previous_year = ( isset($previous_year_query) ? $previous_year_query->sum_metric_tons : 0 );
					$obj->budget = $child_element->metric_tons;
					$obj->v_previous_month = ( $obj->previous_month > 0 ? number_format( ( ($obj->current_projection - $obj->previous_month) / $obj->previous_month ) * 100, 2, '.', '') : 0 );
					$obj->v_previous_year = ( $obj->previous_year > 0 ? number_format( ( ($obj->current_projection - $obj->previous_year) / $obj->previous_year ) * 100, 2, '.', '') : 0 );
					$obj->v_budget = ( $obj->budget > 0 ? number_format( ( ($obj->current_projection - $obj->budget) / $obj->budget ) * 100, 2, '.', '') : 0 );
					$obj->money_current_advance = ( isset($current_query) ? $current_query->sum_total : 0 );
					$obj->money_current_projection = ( $obj->money_current_advance > 0 ? ( $obj->money_current_advance / $current_day ) * $last_day : 0 );
					$obj->money_previous_month = ( isset($previous_month_query) ? $previous_month_query->sum_total : 0 );
					$obj->money_previous_year = ( isset($previous_year_query) ? $previous_year_query->sum_total : 0 );
					$obj->money_budget = $child_element->total;
					$obj->money_v_previous_month = ( $obj->money_previous_month > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_previous_month) / $obj->money_previous_month ) * 100, 2, '.', '') : 0 );
					$obj->money_v_previous_year = ( $obj->money_previous_year > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_previous_year) / $obj->money_previous_year ) * 100, 2, '.', '') : 0 );
					$obj->money_v_budget = ( $obj->money_budget > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_budget) / $obj->money_budget ) * 100, 2, '.', '') : 0 );
					$obj->price = number_format($price, 2, '.', '');
					$obj->mbt_current_advance = $obj->money_current_advance > 0 ? number_format($obj->money_current_advance - ($obj->current_advance * $price), 2, '.', '') : 0;
					$obj->mbt_current_projection = $obj->money_current_projection > 0 ? number_format($obj->money_current_projection - ($obj->current_projection * $price), 2, '.', '') : 0;
					$obj->mbt_previous_month = $obj->money_previous_month > 0 ? number_format($obj->money_previous_month - ($obj->previous_month * $previous_month_price), 2, '.', '') : 0;
					$obj->mbt_previous_year = $obj->money_previous_year > 0 ? number_format($obj->money_previous_year - ($obj->previous_year * $previous_year_price), 2, '.', '') : 0;
					$obj->mbt_budget = $obj->money_budget > 0 ? number_format($obj->money_budget - ($obj->budget * $price), 2, '.', '') : 0;
					$obj->mbt_v_previous_month = $obj->mbt_previous_month > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_previous_month) / $obj->mbt_previous_month ) * 100, 2, '.', '') : 0;
					$obj->mbt_v_previous_year = $obj->mbt_previous_year > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_previous_year) / $obj->mbt_previous_year ) * 100, 2, '.', '') : 0;
					$obj->mbt_v_budget = $obj->mbt_budget > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_budget) / $obj->mbt_budget ) * 100, 2, '.', '') : 0;

					$families->push($obj);
				}

				$group_obj = new stdClass();
				$group_obj->item_id = $parent_element->client_route_id;
				$group_obj->item_name = $parent_element->client_route_name;
				$group_obj->current_advance = $families->sum('current_advance');
				$group_obj->current_projection = $families->sum('current_projection');
				$group_obj->previous_month = $families->sum('previous_month');
				$group_obj->previous_year = $families->sum('previous_year');
				$group_obj->budget = $families->sum('budget');
				$group_obj->v_previous_month = ( $group_obj->previous_month > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->previous_month) / $group_obj->previous_month ) * 100, 2, '.', '') : 0 );
				$group_obj->v_previous_year = ( $group_obj->previous_year > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->previous_year) / $group_obj->previous_year ) * 100, 2, '.', '') : 0 );
				$group_obj->v_budget = ( $group_obj->budget > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->budget) / $group_obj->budget ) * 100, 2, '.', '') : 0 );
				$group_obj->money_current_advance = $families->sum('money_current_advance');
				$group_obj->money_current_projection = $families->sum('money_current_projection');
				$group_obj->money_previous_month = $families->sum('money_previous_month');
				$group_obj->money_previous_year = $families->sum('money_previous_year');
				$group_obj->money_budget = $families->sum('money_budget');
				$group_obj->money_v_previous_month = ( $group_obj->money_previous_month > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_previous_month) / $group_obj->money_previous_month ) * 100, 2, '.', '') : 0 );
				$group_obj->money_v_previous_year = ( $group_obj->money_previous_year > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_previous_year) / $group_obj->money_previous_year ) * 100, 2, '.', '') : 0 );
				$group_obj->money_v_budget = ( $group_obj->money_budget > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_budget) / $group_obj->money_budget ) * 100, 2, '.', '') : 0 );
				$group_obj->price = number_format($price, 2, '.', '');
				$group_obj->mbt_current_advance = $group_obj->money_current_advance > 0 ? number_format($group_obj->money_current_advance - ($group_obj->current_advance * $price), 2, '.', '') : 0;
				$group_obj->mbt_current_projection = $group_obj->money_current_projection > 0 ? number_format($group_obj->money_current_projection - ($group_obj->current_projection * $price), 2, '.', '') : 0;
				$group_obj->mbt_previous_month = $group_obj->money_previous_month > 0 ? number_format($group_obj->money_previous_month - ($group_obj->previous_month * $previous_month_price), 2, '.', '') : 0;
				$group_obj->mbt_previous_year = $group_obj->money_previous_year > 0 ? number_format($group_obj->money_previous_year - ($group_obj->previous_year * $previous_year_price), 2, '.', '') : 0;
				$group_obj->mbt_budget = $group_obj->money_budget > 0 ? number_format($group_obj->money_budget - ($group_obj->budget * $price), 2, '.', '') : 0;
				$group_obj->mbt_v_previous_month = $group_obj->mbt_previous_month > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_previous_month) / $group_obj->mbt_previous_month ) * 100, 2, '.', '') : 0;
				$group_obj->mbt_v_previous_year = $group_obj->mbt_previous_year > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_previous_year) / $group_obj->mbt_previous_year ) * 100, 2, '.', '') : 0;
				$group_obj->mbt_v_budget = $group_obj->mbt_budget > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_budget) / $group_obj->mbt_budget ) * 100, 2, '.', '') : 0;
				$group_obj->families = $families;

				$sale_report->push($group_obj);
			}

			$total_obj = new stdClass();
			$total_obj->item_name = 'TOTAL';
			$total_obj->current_advance = $sale_report->sum('current_advance');
			$total_obj->current_projection = $sale_report->sum('current_projection');
			$total_obj->previous_month = $sale_report->sum('previous_month');
			$total_obj->previous_year = $sale_report->sum('previous_year');
			$total_obj->budget = $sale_report->sum('budget');
			$total_obj->v_previous_month = '-';
			$total_obj->v_previous_year = '-';
			$total_obj->v_budget = '-';
			$total_obj->money_current_advance = $sale_report->sum('money_current_advance');
			$total_obj->money_current_advance = $sale_report->sum('money_current_advance');
			$total_obj->money_current_projection = $sale_report->sum('money_current_projection');
			$total_obj->money_previous_month = $sale_report->sum('money_previous_month');
			$total_obj->money_previous_year = $sale_report->sum('money_previous_year');
			$total_obj->money_budget = $sale_report->sum('money_budget');
			$total_obj->money_v_previous_month = '-';
			$total_obj->money_v_previous_year = '-';
			$total_obj->money_v_budget = '-';
			$total_obj->price = number_format($price, 2, '.', '');
			$total_obj->mbt_current_advance = $sale_report->sum('mbt_current_advance');
			$total_obj->mbt_current_advance = $sale_report->sum('mbt_current_advance');
			$total_obj->mbt_current_projection = $sale_report->sum('mbt_current_projection');
			$total_obj->mbt_previous_month = $sale_report->sum('mbt_previous_month');
			$total_obj->mbt_previous_year = $sale_report->sum('mbt_previous_year');
			$total_obj->mbt_budget = $sale_report->sum('mbt_budget');
			$total_obj->mbt_v_previous_month = '-';
			$total_obj->mbt_v_previous_year = '-';
			$total_obj->mbt_v_budget = '-';

			$sale_report->push($total_obj);
		}

		return $sale_report;
	}

	public function detail() {
		$sale_option_id = request('model.sale_option_id');
		$day = CarbonImmutable::createFromDate(request('model.to_date'));
		$business_unit_id = request('model.id');
		$first_day = $day->firstOfMonth()->format('Y-m-d');
		$last_day = $day->lastOfMonth()->format('Y-m-d');

		switch ($sale_option_id) {
			case 2:
				$sale_option = 'route_id';
				$sale_table = 'client_routes';
				$sale_column = 'route_id';
				$select_table = 'clients';
				break;
				
			case 3:
			    $sale_option = 'channel_id';
				$sale_table = 'client_channels';
				$sale_column = 'channel_id';
				$select_table = 'clients';
				break;

		//	case 4:
		//		$sale_option = 'zone_id';
		//		$sale_table = 'client_zones';
		//		$sale_column = 'zone_id';
		//		$select_table = 'clients';
		//		break;
			case 4:
				$sale_option = 'family_id';
				$sale_table = 'classifications';
				$sale_column = 'family_id';
				$select_table = 'articles';
				break;
			case 5:
				$sale_option = 'subgroup_id';
				$sale_table = 'classifications';
				$sale_column = 'subgroup_id';
				$select_table = 'articles';
				break;
		}

		$details = SaleDetail::join('sales', 'sale_details.sale_id', '=', 'sales.id')
			->join('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
			->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
			->where('sales.sale_date', '>=', $first_day)
			->where('sales.sale_date', '<=', $last_day)
			->where('clients.business_unit_id', $business_unit_id)
			// ->where('clients.'.$sale_option, $sale_option_id)
			// ->select('clients.channel_id as client_channel_id', DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
			->select('clients.business_name', 'warehouse_document_types.name as warehouse_document_type_name', 'sales.referral_serie_number', 'sales.referral_voucher_number', 'sales.sale_date', 'sales.total')
			->groupBy('clients.business_name', 'ASC')
			->orderBy('sales.sale_date', 'ASC')
			->get();

		return $details;
	}

	public function export() {
		// $sale_option_id = request('model.sale_option_id');
		$price = request('price');
		$sale_options = SaleOption::where('id', '>=', 2)
			->where('id', '<=', 6)
			->select('id', 'name')
			->get();

		$day = CarbonImmutable::createFromDate(request('model.to_date'));
		
		$to_date = $day->format('Y-m-d');
		$first_day = $day->firstOfMonth()->format('Y-m-d');
		$last_day = $day->lastOfMonth()->format('d');
		$current_day = $day->format('j');
		$current_month = $day->format('m');
		$current_year = $day->format('Y');
		$previous_month = $day->firstOfMonth()->subMonth();
		$previous_year = $day->firstOfMonth()->subYear()->format('Y');

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		$sheet->mergeCells('A2:Z2');
		$sheet->setCellValue('A2', 'INFORME DIARIO DE VENTAS');
		$sheet->getStyle('A2')->applyFromArray([
			'font' => [
				'bold' => true,
				'size' => 16,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
		]);
		$sheet->mergeCells('B3:I3');
		$sheet->setCellValue('B3', 'TONELADAS MÉTRICAS');
		$sheet->getStyle('B3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '44546a')
			]
		]);
		$sheet->mergeCells('J3:Q3');
		$sheet->setCellValue('J3', 'VENTA S/');
		$sheet->getStyle('J3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '009933')
			]
		]);
		$sheet->mergeCells('R3:Z3');
		$sheet->setCellValue('R3', 'MBT');
		$sheet->getStyle('R3')->applyFromArray([
			'font' => [
				'color' => array('rgb' => 'FFFFFF'),
				'bold' => true,
				'size' => 12,
			],
			'alignment' => [
				'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
			],
			'fill' => [
				'fillType' => Fill::FILL_SOLID,
				'startColor' => array('rgb' => '0000ff')
			]
		]);

		$row_number = 4;

		foreach ($sale_options as $so) {
			$sale_option_id = $so->id;

			switch ($sale_option_id) {
				case 2:
					$sale_option = 'client_route_id';
					$sale_table = 'client_routes';
					$sale_column = 'route_id';
					$select_table = 'clients';
					break;

					
				case 3:
					$sale_option = 'client_channel_id';
					$sale_table = 'client_channels';
					$sale_column = 'channel_id';
					$select_table = 'clients';
					break;
				
			//	case 4:
			//		$sale_option = 'client_zone_id';
			//		$sale_table = 'client_zones';
			//		$sale_column = 'zone_id';
			//		$select_table = 'clients';
			//		break;
				case 4:
					$sale_option = 'family_id';
					$sale_table = 'classifications';
					$sale_column = 'family_id';
					$select_table = 'articles';
					break;
				case 5:
					$sale_option = 'subgroup_id';
					$sale_table = 'classifications';
					$sale_column = 'subgroup_id';
					$select_table = 'articles';
					break;
			}
			$row_number++;

			// $sheet->mergeCells('A'.$row_number.':Q'.$row_number);
			// $sheet->setCellValue('A'.$row_number, $so->name);
			// $row_number++;
			
			$sheet->setCellValue('A'.$row_number, $so->name);
			$sheet->setCellValue('B'.$row_number, 'AVANCE');
			$sheet->setCellValue('C'.$row_number, 'PROYEC');
			$sheet->setCellValue('D'.$row_number, 'M.A.');
			$sheet->setCellValue('E'.$row_number, 'A.A.');
			$sheet->setCellValue('F'.$row_number, 'PPTO');
			$sheet->setCellValue('G'.$row_number, 'V% M.A.');
			$sheet->setCellValue('H'.$row_number, 'V% A.A.');
			$sheet->setCellValue('I'.$row_number, 'V% PPTO');
			$sheet->setCellValue('J'.$row_number, 'AVANCE');
			$sheet->setCellValue('K'.$row_number, 'PROYEC.');
			$sheet->setCellValue('L'.$row_number, 'M.A.');
			$sheet->setCellValue('M'.$row_number, 'A.A.');
			$sheet->setCellValue('N'.$row_number, 'PPTO');
			$sheet->setCellValue('O'.$row_number, 'V% M.A.');
			$sheet->setCellValue('P'.$row_number, 'V% A.A.');
			$sheet->setCellValue('Q'.$row_number, 'V% PPTO');
			$sheet->setCellValue('R'.$row_number, 'PRECIO');
			$sheet->setCellValue('S'.$row_number, 'AVANCE');
			$sheet->setCellValue('T'.$row_number, 'PROYEC.');
			$sheet->setCellValue('U'.$row_number, 'M.A.');
			$sheet->setCellValue('V'.$row_number, 'A.A.');
			$sheet->setCellValue('W'.$row_number, 'PPTO');
			$sheet->setCellValue('X'.$row_number, 'V% M.A.');
			$sheet->setCellValue('Y'.$row_number, 'V% A.A.');
			$sheet->setCellValue('Z'.$row_number, 'V% PPTO');
			$row_number++;

			$elements = Budget::rightjoin('business_units', 'business_units.id', '=', 'budgets.business_unit_id')
				->rightjoin($sale_table, $sale_table.'.id', '=', 'budgets.'.$sale_option)
				->where('year', $current_year)
				->where('month', $current_month)
				->where('sale_option_id', $sale_option_id)
				->select('year', 'month', 'days', 'business_unit_id', 'business_units.name as business_unit_name', $sale_option, $sale_table.'.name as sale_option_name', 'metric_tons', 'total', 'percentage')
				->groupBy('business_unit_id', $sale_option)
				->get();

			$previous_month_price = PriceHistory::where('year', $current_year)
				->where('month', $previous_month->format('m'))
				->select('price')
				->first();
			
			$previous_month_price = $previous_month_price ? $previous_month_price->price : 0;
			
			$previous_year_price = PriceHistory::where('year', $previous_year)
				->where('month', $current_month)
				->select('price')
				->first();
			
			$previous_year_price = $previous_year_price ? $previous_year_price->price : 0;

			$sale_report = collect();

			if ( count($elements) > 0 ) {
				$group_ids = array_values(array_unique($elements->pluck('business_unit_id')->all()));
				$days = array_values(array_unique($elements->pluck('days')->all()));
				$parent_elements = $elements->unique('business_unit_id')->values()->all();

				// if ( $current_day > $days[0] ) {
				// 	$current_day = $days[0];
				// }

				foreach ($parent_elements as $parent_element) {
					$child_elements = $elements->filter(function ($value, $key) use ($parent_element) {
						return $value->business_unit_id == $parent_element->business_unit_id;
					});

					$families = collect();

					foreach ($child_elements as $child_element) {
						$current_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
							->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
							->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
							->where(function($query) {
								$query->where('sales.company_id', '<>', 1)
									->orWhere(function($query) {
										$query->where('sales.company_id', 1)
											->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
									});
							})
							->where('sales.sale_date', '>=', $first_day)
							->where('sales.sale_date', '<=', $to_date)
							->where('clients.business_unit_id', $child_element->business_unit_id)
							->where($select_table.'.'.$sale_column, $child_element->$sale_option)
							->select('clients.business_unit_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
							->groupBy('clients.business_unit_id', $select_table.'.'.$sale_column)
							->orderBy('clients.business_unit_id', 'asc')
							->orderBy($select_table.'.'.$sale_column, 'asc')
							->first();

						$previous_month_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
							->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
							->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
							->where(function($query) {
								$query->where('sales.company_id', '<>', 1)
									->orWhere(function($query) {
										$query->where('sales.company_id', 1)
											->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
									});
							})
							->whereYear('sales.sale_date', $previous_month->format('Y'))
							->whereMonth('sales.sale_date', $previous_month->format('m'))
							->where('clients.business_unit_id', $child_element->business_unit_id)
							->where($select_table.'.'.$sale_column, $child_element->$sale_option)
							->select('clients.business_unit_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
							->groupBy('clients.business_unit_id', $select_table.'.'.$sale_column)
							->orderBy('clients.business_unit_id', 'asc')
							->orderBy($select_table.'.'.$sale_column, 'asc')
							->first();

						$previous_year_query = SaleDetail::leftjoin('sales', 'sales.id', '=', 'sale_details.sale_id')
							->leftjoin('clients', 'clients.id', '=', 'sales.client_id')
							->leftjoin('articles', 'articles.id', '=', 'sale_details.article_id')
							->where(function($query) {
								$query->where('sales.company_id', '<>', 1)
									->orWhere(function($query) {
										$query->where('sales.company_id', 1)
											->whereNotIn('sales.client_id', [1031, 427, 13326, 13775]);
									});
							})
							->whereYear('sales.sale_date', $previous_year)
							->whereMonth('sales.sale_date', $current_month)
							->where('clients.business_unit_id', $child_element->business_unit_id)
							->where($select_table.'.'.$sale_column, $child_element->$sale_option)
							->select('clients.business_unit_id', $select_table.'.'.$sale_column, DB::Raw('SUM((quantity * convertion) / 1000) AS sum_metric_tons, SUM(sale_details.total) AS sum_total'))
							->groupBy('clients.business_unit_id', $select_table.'.'.$sale_column)
							->orderBy('clients.business_unit_id', 'asc')
							->orderBy($select_table.'.'.$sale_column, 'asc')
							->first();

						$obj = new stdClass();
						$obj->family_name = $child_element->sale_option_name;
						$obj->current_advance = ( isset($current_query) ? $current_query->sum_metric_tons : 0 );
						// $obj->current_projection = ( $obj->current_advance > 0 ? ( $obj->current_advance / $current_day ) * $days[0] : 0 );
						$obj->current_projection = ( $obj->current_advance > 0 ? ( $obj->current_advance / $current_day ) * $last_day : 0 );
						$obj->previous_month = ( isset($previous_month_query) ? $previous_month_query->sum_metric_tons : 0 );
						$obj->previous_year = ( isset($previous_year_query) ? $previous_year_query->sum_metric_tons : 0 );
						$obj->budget = $child_element->metric_tons;
						$obj->v_previous_month = ( $obj->previous_month > 0 ? number_format( ( ($obj->current_projection - $obj->previous_month) / $obj->previous_month ) * 100, 2, '.', '') : 0 );
						$obj->v_previous_year = ( $obj->previous_year > 0 ? number_format( ( ($obj->current_projection - $obj->previous_year) / $obj->previous_year ) * 100, 2, '.', '') : 0 );
						$obj->v_budget = ( $obj->budget > 0 ? number_format( ( ($obj->current_projection - $obj->budget) / $obj->budget ) * 100, 2, '.', '') : 0 );
						$obj->money_current_advance = ( isset($current_query) ? $current_query->sum_total : 0 );
						// $obj->money_current_projection = ( $obj->money_current_advance > 0 ? ( $obj->money_current_advance / $current_day ) * $days[0] : 0 );
						$obj->money_current_projection = ( $obj->money_current_advance > 0 ? ( $obj->money_current_advance / $current_day ) * $last_day : 0 );
						$obj->money_previous_month = ( isset($previous_month_query) ? $previous_month_query->sum_total : 0 );
						$obj->money_previous_year = ( isset($previous_year_query) ? $previous_year_query->sum_total : 0 );
						$obj->money_budget = $child_element->total;
						$obj->money_v_previous_month = ( $obj->money_previous_month > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_previous_month) / $obj->money_previous_month ) * 100, 2, '.', '') : 0 );
						$obj->money_v_previous_year = ( $obj->money_previous_year > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_previous_year) / $obj->money_previous_year ) * 100, 2, '.', '') : 0 );
						$obj->money_v_budget = ( $obj->money_budget > 0 ? number_format( ( ($obj->money_current_projection - $obj->money_budget) / $obj->money_budget ) * 100, 2, '.', '') : 0 );
						$obj->price = number_format($price, 2, '.', '');
						$obj->mbt_current_advance = $obj->money_current_advance > 0 ? number_format($obj->money_current_advance - ($obj->current_advance * $price), 2, '.', '') : 0;
						$obj->mbt_current_projection = $obj->money_current_projection > 0 ? number_format($obj->money_current_projection - ($obj->current_projection * $price), 2, '.', '') : 0;
						$obj->mbt_previous_month = $obj->money_previous_month > 0 ? number_format($obj->money_previous_month - ($obj->previous_month * $previous_month_price), 2, '.', '') : 0;
						$obj->mbt_previous_year = $obj->money_previous_year > 0 ? number_format($obj->money_previous_year - ($obj->previous_year * $previous_year_price), 2, '.', '') : 0;
						$obj->mbt_budget = $obj->money_budget > 0 ? number_format($obj->money_budget - ($obj->budget * $price), 2, '.', '') : 0;
						$obj->mbt_v_previous_month = $obj->mbt_previous_month > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_previous_month) / $obj->mbt_previous_month ) * 100, 2, '.', '') : 0;
						$obj->mbt_v_previous_year = $obj->mbt_previous_year > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_previous_year) / $obj->mbt_previous_year ) * 100, 2, '.', '') : 0;
						$obj->mbt_v_budget = $obj->mbt_budget > 0 ? number_format( ( ($obj->mbt_current_projection - $obj->mbt_budget) / $obj->mbt_budget ) * 100, 2, '.', '') : 0;

						$families->push($obj);
					}

					$group_obj = new stdClass();
					$group_obj->item_id = $parent_element->business_unit_id;
					$group_obj->item_name = $parent_element->business_unit_name;
					$group_obj->current_advance = $families->sum('current_advance');
					$group_obj->current_projection = $families->sum('current_projection');
					$group_obj->previous_month = $families->sum('previous_month');
					$group_obj->previous_year = $families->sum('previous_year');
					$group_obj->budget = $families->sum('budget');
					$group_obj->v_previous_month = ( $group_obj->previous_month > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->previous_month) / $group_obj->previous_month ) * 100, 2, '.', '') : 0 );
					$group_obj->v_previous_year = ( $group_obj->previous_year > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->previous_year) / $group_obj->previous_year ) * 100, 2, '.', '') : 0 );
					$group_obj->v_budget = ( $group_obj->budget > 0 ? number_format( ( ($group_obj->current_projection - $group_obj->budget) / $group_obj->budget ) * 100, 2, '.', '') : 0 );
					$group_obj->money_current_advance = $families->sum('money_current_advance');
					$group_obj->money_current_projection = $families->sum('money_current_projection');
					$group_obj->money_previous_month = $families->sum('money_previous_month');
					$group_obj->money_previous_year = $families->sum('money_previous_year');
					$group_obj->money_budget = $families->sum('money_budget');
					$group_obj->money_v_previous_month = ( $group_obj->money_previous_month > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_previous_month) / $group_obj->money_previous_month ) * 100, 2, '.', '') : 0 );
					$group_obj->money_v_previous_year = ( $group_obj->money_previous_year > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_previous_year) / $group_obj->money_previous_year ) * 100, 2, '.', '') : 0 );
					$group_obj->money_v_budget = ( $group_obj->money_budget > 0 ? number_format( ( ($group_obj->money_current_projection - $group_obj->money_budget) / $group_obj->money_budget ) * 100, 2, '.', '') : 0 );
					$group_obj->price = number_format($price, 2, '.', '');
					$group_obj->mbt_current_advance = $group_obj->money_current_advance > 0 ? number_format($group_obj->money_current_advance - ($group_obj->current_advance * $price), 2, '.', '') : 0;
					$group_obj->mbt_current_projection = $group_obj->money_current_projection > 0 ? number_format($group_obj->money_current_projection - ($group_obj->current_projection * $price), 2, '.', '') : 0;
					$group_obj->mbt_previous_month = $group_obj->money_previous_month > 0 ? number_format($group_obj->money_previous_month - ($group_obj->previous_month * $previous_month_price), 2, '.', '') : 0;
					$group_obj->mbt_previous_year = $group_obj->money_previous_year > 0 ? number_format($group_obj->money_previous_year - ($group_obj->previous_year * $previous_year_price), 2, '.', '') : 0;
					$group_obj->mbt_budget = $group_obj->money_budget > 0 ? number_format($group_obj->money_budget - ($group_obj->budget * $price), 2, '.', '') : 0;
					$group_obj->mbt_v_previous_month = $group_obj->mbt_previous_month > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_previous_month) / $group_obj->mbt_previous_month ) * 100, 2, '.', '') : 0;
					$group_obj->mbt_v_previous_year = $group_obj->mbt_previous_year > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_previous_year) / $group_obj->mbt_previous_year ) * 100, 2, '.', '') : 0;
					$group_obj->mbt_v_budget = $group_obj->mbt_budget > 0 ? number_format( ( ($group_obj->mbt_current_projection - $group_obj->mbt_budget) / $group_obj->mbt_budget ) * 100, 2, '.', '') : 0;
					$group_obj->families = $families;

					$sale_report->push($group_obj);

					$sheet->setCellValue('A'.$row_number, $group_obj->item_name);
					$sheet->setCellValue('B'.$row_number, $group_obj->current_advance);
					$sheet->setCellValue('C'.$row_number, $group_obj->current_projection);
					$sheet->setCellValue('D'.$row_number, $group_obj->previous_month);
					$sheet->setCellValue('E'.$row_number, $group_obj->previous_year);
					$sheet->setCellValue('F'.$row_number, $group_obj->budget);
					$sheet->setCellValue('G'.$row_number, $group_obj->v_previous_month);
					$sheet->setCellValue('H'.$row_number, $group_obj->v_previous_year);
					$sheet->setCellValue('I'.$row_number, $group_obj->v_budget);
					$sheet->setCellValue('J'.$row_number, $group_obj->money_current_advance);
					$sheet->setCellValue('K'.$row_number, $group_obj->money_current_projection);
					$sheet->setCellValue('L'.$row_number, $group_obj->money_previous_month);
					$sheet->setCellValue('M'.$row_number, $group_obj->money_previous_year);
					$sheet->setCellValue('N'.$row_number, $group_obj->money_budget);
					$sheet->setCellValue('O'.$row_number, $group_obj->money_v_previous_month);
					$sheet->setCellValue('P'.$row_number, $group_obj->money_v_previous_year);
					$sheet->setCellValue('Q'.$row_number, $group_obj->money_v_budget);
					$sheet->setCellValue('R'.$row_number, $group_obj->price);
					$sheet->setCellValue('S'.$row_number, $group_obj->mbt_current_advance);
					$sheet->setCellValue('T'.$row_number, $group_obj->mbt_current_projection);
					$sheet->setCellValue('U'.$row_number, $group_obj->mbt_previous_month);
					$sheet->setCellValue('V'.$row_number, $group_obj->mbt_previous_year);
					$sheet->setCellValue('W'.$row_number, $group_obj->mbt_budget);
					$sheet->setCellValue('X'.$row_number, $group_obj->mbt_v_previous_month);
					$sheet->setCellValue('Y'.$row_number, $group_obj->mbt_v_previous_year);
					$sheet->setCellValue('Z'.$row_number, $group_obj->mbt_v_budget);
					$row_number++;

					foreach ($families as $family) {
						$sheet->setCellValue('A'.$row_number, $family->family_name);
						$sheet->setCellValue('B'.$row_number, $family->current_advance);
						$sheet->setCellValue('C'.$row_number, $family->current_projection);
						$sheet->setCellValue('D'.$row_number, $family->previous_month);
						$sheet->setCellValue('E'.$row_number, $family->previous_year);
						$sheet->setCellValue('F'.$row_number, $family->budget);
						$sheet->setCellValue('G'.$row_number, $family->v_previous_month);
						$sheet->setCellValue('H'.$row_number, $family->v_previous_year);
						$sheet->setCellValue('I'.$row_number, $family->v_budget);
						$sheet->setCellValue('J'.$row_number, $family->money_current_advance);
						$sheet->setCellValue('K'.$row_number, $family->money_current_projection);
						$sheet->setCellValue('L'.$row_number, $family->money_previous_month);
						$sheet->setCellValue('M'.$row_number, $family->money_previous_year);
						$sheet->setCellValue('N'.$row_number, $family->money_budget);
						$sheet->setCellValue('O'.$row_number, $family->money_v_previous_month);
						$sheet->setCellValue('P'.$row_number, $family->money_v_previous_year);
						$sheet->setCellValue('Q'.$row_number, $family->money_v_budget);
						$sheet->setCellValue('R'.$row_number, $family->price);
						$sheet->setCellValue('S'.$row_number, $family->mbt_current_advance);
						$sheet->setCellValue('T'.$row_number, $family->mbt_current_projection);
						$sheet->setCellValue('U'.$row_number, $family->mbt_previous_month);
						$sheet->setCellValue('V'.$row_number, $family->mbt_previous_year);
						$sheet->setCellValue('W'.$row_number, $family->mbt_budget);
						$sheet->setCellValue('X'.$row_number, $family->mbt_v_previous_month);
						$sheet->setCellValue('Y'.$row_number, $family->mbt_v_previous_year);
						$sheet->setCellValue('Z'.$row_number, $family->mbt_v_budget);
						$row_number++;
					}
					// $row_number++;
				}

				$total_obj = new stdClass();
				$total_obj->item_name = 'TOTAL';
				$total_obj->current_advance = $sale_report->sum('current_advance');
				$total_obj->current_projection = $sale_report->sum('current_projection');
				$total_obj->previous_month = $sale_report->sum('previous_month');
				$total_obj->previous_year = $sale_report->sum('previous_year');
				$total_obj->budget = $sale_report->sum('budget');
				$total_obj->v_previous_month = '-';
				$total_obj->v_previous_year = '-';
				$total_obj->v_budget = '-';
				$total_obj->money_current_advance = $sale_report->sum('money_current_advance');
				$total_obj->money_current_advance = $sale_report->sum('money_current_advance');
				$total_obj->money_current_projection = $sale_report->sum('money_current_projection');
				$total_obj->money_previous_month = $sale_report->sum('money_previous_month');
				$total_obj->money_previous_year = $sale_report->sum('money_previous_year');
				$total_obj->money_budget = $sale_report->sum('money_budget');
				$total_obj->money_v_previous_month = '-';
				$total_obj->money_v_previous_year = '-';
				$total_obj->money_v_budget = '-';
				$total_obj->price = number_format($price, 2, '.', '');
				$total_obj->mbt_current_advance = $sale_report->sum('mbt_current_advance');
				$total_obj->mbt_current_advance = $sale_report->sum('mbt_current_advance');
				$total_obj->mbt_current_projection = $sale_report->sum('mbt_current_projection');
				$total_obj->mbt_previous_month = $sale_report->sum('mbt_previous_month');
				$total_obj->mbt_previous_year = $sale_report->sum('mbt_previous_year');
				$total_obj->mbt_budget = $sale_report->sum('mbt_budget');
				$total_obj->mbt_v_previous_month = '-';
				$total_obj->mbt_v_previous_year = '-';
				$total_obj->mbt_v_budget = '-';

				$sale_report->push($total_obj);
			}
		
			$sheet->getColumnDimension('A')->setAutoSize(true);
			$sheet->getColumnDimension('B')->setAutoSize(true);
			$sheet->getColumnDimension('C')->setAutoSize(true);
			$sheet->getColumnDimension('D')->setAutoSize(true);
			$sheet->getColumnDimension('E')->setAutoSize(true);
			$sheet->getColumnDimension('F')->setAutoSize(true);
			$sheet->getColumnDimension('G')->setAutoSize(true);
			$sheet->getColumnDimension('H')->setAutoSize(true);
			$sheet->getColumnDimension('I')->setAutoSize(true);
			$sheet->getColumnDimension('J')->setAutoSize(true);
			$sheet->getColumnDimension('K')->setAutoSize(true);
			$sheet->getColumnDimension('L')->setAutoSize(true);
			$sheet->getColumnDimension('M')->setAutoSize(true);
			$sheet->getColumnDimension('N')->setAutoSize(true);
			$sheet->getColumnDimension('O')->setAutoSize(true);
			$sheet->getColumnDimension('P')->setAutoSize(true);
			$sheet->getColumnDimension('Q')->setAutoSize(true);
			$sheet->getColumnDimension('R')->setAutoSize(true);
			$sheet->getColumnDimension('S')->setAutoSize(true);
			$sheet->getColumnDimension('T')->setAutoSize(true);
			$sheet->getColumnDimension('U')->setAutoSize(true);
			$sheet->getColumnDimension('V')->setAutoSize(true);
			$sheet->getColumnDimension('W')->setAutoSize(true);
			$sheet->getColumnDimension('X')->setAutoSize(true);
			$sheet->getColumnDimension('Y')->setAutoSize(true);
			$sheet->getColumnDimension('Z')->setAutoSize(true);
		}

		$writer = new Xlsx($spreadsheet);
		return $writer->save('php://output');
	}

	public function updateClientId() {
		$sales = Sale::select('id', 'company_id', 'client_code')
			->whereNull('client_id')
			->get();
		$sales->each(function ($item, $key) {
			$client = Client::select('id', 'company_id', 'code')
						->where('code', $item->client_code)
						->where('company_id', $item->company_id)
						->first();
			
			if ( $client ) {
				$item->client_id = $client->id;
				$item->save();
			}
		});
	}
}
