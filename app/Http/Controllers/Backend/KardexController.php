<?php

namespace App\Http\Controllers\Backend;

use App\Article;
use App\Client;
use App\Company;
use App\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Provider;
use App\WarehouseAccountType;
use App\WarehouseDocumentType;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;

class KardexController extends Controller
{
    public function index() {
        $warehouse_types = WarehouseType::select('id', 'name')->get();
		$companies = Company::select('id', 'name')->get();
        $current_date = date(DATE_ATOM, mktime(0, 0, 0));
		$warehouse_account_types = WarehouseAccountType::select('id', 'name')->get();
		$warehouse_document_types = WarehouseDocumentType::select('id', 'name')->get();

        return view('backend.kardex_report')->with(compact('warehouse_types', 'companies', 'current_date', 'warehouse_account_types', 'warehouse_document_types'));
    }

    public function getArticles() {
		$q = request('q');
		$warehouse_type_id = request('warehouse_type_id');
		
		$articles = Article::select('id', 'code', 'name', 'package_warehouse', 'warehouse_unit_id')
			->where('warehouse_type_id', $warehouse_type_id)
			->when($q, function($query, $q) {
				return $query->where('code', 'like', '%'.$q.'%')
					->orWhere('name', 'like', '%'.$q.'%');
			})
			->orderBy('code', 'asc')
			->get();

		$articles->map(function($item, $index) {
			$item->text = $item->code . ' - ' . $item->name . ' ' . $item->warehouse_unit->name . ' x ' . $item->package_warehouse;
		});

		return $articles;
	}

	public function getAccounts() {
		$company_id = request('company_id');
		$warehouse_account_type_id = request('warehouse_account_type_id');
		$q = request('q');
		
		if ( $warehouse_account_type_id == 1 ) {
			$clients = Client::select('id', 'business_name')
				->when($company_id, function($query, $company_id) {
					return $query->where('company_id', $company_id);
				})
				->where('business_name', 'like', '%'.$q.'%')
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 2 ) {			
			$clients = Provider::select('id', 'business_name')
				->where('business_name', 'like', '%'.$q.'%')
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->business_name;
				unset($item->business_name);

				return $item;
			});
		} elseif ( $warehouse_account_type_id == 3 ) {
			$clients = Employee::select('id', 'first_name', 'last_name')
				->where(function ($query) use ($q) {
					$query->where('first_name', 'like', '%'.$q.'%')
						->orWhere('last_name', 'like', '%'.$q.'%');
				})
				->withTrashed()
				->get();

			$clients->map(function($item, $index){
				$item->text = $item->first_name . ' ' . $item->last_name;
				unset($item->first_name);
				unset($item->last_name);

				return $item;
			});
		}

		return $clients;
	}

	public function validateForm() {
		$messages = [
			'warehouse_type_id.required'	=> 'Debe seleccionar un Almacén.',
			'article_id.required'			=> 'Debe seleccionar un Artículo.',
			'since_date.required'			=> 'Debe seleccionar una Fecha de inicio.',
			'to_date.required'				=> 'Debe seleccionar una Fecha de fin.',
		];

		$rules = [
			'warehouse_type_id'	=> 'required',
			'article_id'		=> 'required',
			'since_date'		=> 'required',
			'to_date'			=> 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function list() {
		$warehouse_type_id = request('model.warehouse_type_id');
		$article_id = request('model.article_id');
		$company_id = request('model.company_id');
		$since_date = date_create(request('model.since_date') . ' 00:00:00');
		$to_date = date_create(request('model.to_date') . ' 23:59:59');
		$warehouse_account_type_id = request('model.warehouse_account_type_id');
		$warehouse_account_id = request('model.warehouse_account_id');
		$referral_guide_series = request('model.referral_guide_series');
		$referral_guide_number = request('model.referral_guide_number');
		$referral_warehouse_document_type_id = request('model.referral_warehouse_document_type_id');
		$referral_serie_number = request('model.referral_serie_number');
		$referral_voucher_number = request('model.referral_voucher_number');
		$scop_number = request('model.scop_number');
		$license_plate = request('model.license_plate');

		$since_date = date_format($since_date, 'Y-m-d H:i:s');
		$to_date = date_format($to_date, 'Y-m-d H:i:s');

		$childElements = collect([]);

		$elements = WarehouseMovement::select('id', 'company_id', 'warehouse_type_id', 'movement_class_id', 'movement_type_id', 'movement_number', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'referral_warehouse_document_type_id', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'created_at')
			->when($company_id, function($query, $company_id) {
				return $query->where('company_id', $company_id);
			})
			->when($warehouse_type_id, function($query, $warehouse_type_id) {
				return $query->where('warehouse_type_id', $warehouse_type_id);
			})
			->when($warehouse_account_type_id, function($query, $warehouse_account_type_id) {
				return $query->where('warehouse_account_type_id', $warehouse_account_type_id);
			})
			->when($warehouse_account_id, function($query, $warehouse_account_id) {
				return $query->where('account_id', $warehouse_account_id);
			})
			->when($referral_guide_series, function($query, $referral_guide_series) {
				return $query->where('referral_guide_series', $referral_guide_series);
			})
			->when($referral_guide_number, function($query, $referral_guide_number) {
				return $query->where('referral_guide_number', $referral_guide_number);
			})
			->when($referral_warehouse_document_type_id, function($query, $referral_warehouse_document_type_id) {
				return $query->where('referral_warehouse_document_type_id', $referral_warehouse_document_type_id);
			})
			->when($referral_serie_number, function($query, $referral_serie_number) {
				return $query->where('referral_serie_number', $referral_serie_number);
			})
			->when($referral_voucher_number, function($query, $referral_voucher_number) {
				return $query->where('referral_voucher_number', $referral_voucher_number);
			})
			->when($scop_number, function($query, $scop_number) {
				return $query->where('scop_number', $scop_number);
			})
			->when($license_plate, function($query, $license_plate) {
				return $query->where('license_plate', $license_plate);
			})
			->where('created_at', '>=', $since_date)
			->where('created_at', '<=', $to_date)
			->get();

		$filtered = $elements->filter(function ($item, $key) use ($article_id) {
			$filtered = $item->warehouse_movement_details->filter(function ($value, $key) use ($article_id) {
				return $value->article_code == $article_id;
			});

			$item->details = $filtered;
			unset($item->warehouse_movement_details);

			return count($item->details) > 0;
		});

		$filtered->map(function ($item, $key) use ($childElements) {
			$item->details->map(function ($value, $key) use ($item, $childElements) {
				$article_id = $value->article_code;

				if ( $item->warehouse_account_type_id == 1 ) {
					$account = Client::select('business_name', 'document_number', 'document_type_id')
						->where('id', $item->account_id)
						->withTrashed()
						->first();
				} elseif ( $item->warehouse_account_type_id == 2 ) {
					$account = Provider::select('business_name', 'document_number', 'document_type_id')
						->where('id', $item->account_id)
						->withTrashed()
						->first();
				} elseif ( $item->warehouse_account_type_id == 3 ) {
					$account = Employee::select('first_name', 'last_name', 'document_type_id')
						->where('id', $item->account_id)
						->withTrashed()
						->first();
					
					$account->business_name = $account->first_name . ' ' . $account->last_name;
					$account->document_number = '';
				}

				$value->movement_class_name = $item->movement_class->name;
				$value->movement_type_name = $item->movement_type->name;
				$value->movement_number = $item->movement_number;
				$value->creation_date = date('d-m-Y', strtotime($value->created_at));
				$value->article_code = $value->article->code;
				$value->article_name = $value->article->name;
				$value->old_stock_good = number_format($value->old_stock_good, 4, '.', ',');
				$value->new_stock_good = number_format($value->new_stock_good, 4, '.', ',');
				$value->old_stock_damaged = number_format((int)$value->old_stock_repair + (int)$value->old_stock_return + (int)$value->old_stock_damaged, 4, '.', ',');
				$value->new_stock_damaged = number_format((int)$value->new_stock_repair + (int)$value->new_stock_return + (int)$value->new_stock_damaged, 4, '.', ',');
				if ( $item->movement_class_id == 1 ) {
					$value->converted_amount_good = number_format($value->converted_amount, 4, '.', ',');
					$value->converted_amount_damaged = number_format(0, 4, '.', ',');
				} elseif ( $item->movement_class_id == 2 ) {
					if ( $item->movement_type_id == 4 ) {
						$value->converted_amount_good = number_format(0, 4, '.', ',');
						$value->converted_amount_damaged = number_format(-$value->converted_amount, 4, '.', ',');
					} elseif ( $item->movement_type_id == 21 ) {
						$value->converted_amount_good = number_format(-$value->converted_amount, 4, '.', ',');
						$value->converted_amount_damaged = number_format($value->converted_amount, 4, '.', ',');
					} else {
						$value->converted_amount_good = number_format(-$value->converted_amount, 4, '.', ',');
						$value->converted_amount_damaged = number_format(0, 4, '.', ',');
					}
				}
				$value->account_document_type_name = ( isset($account) ? $account->document_type->name : '' );
				$value->account_document_number = ( isset($account) ? $account->document_number : '' );
				$value->account_name = ( isset($account) ? $account->business_name : '' );
				$value->referral_guide = $item->referral_guide_series . '-' . $item->referral_guide_number;
				$value->referral_warehouse_document_type_name = ( $item->referral_warehouse_document_type_id ? $item->warehouse_document_type->name : '' );
				$value->referral_document = $item->referral_serie_number . '-' . $item->referral_voucher_number;
				$value->scop_number = $item->scop_number;
				$value->license_plate = $item->license_plate;

				unset($value->article);

				$childElements->push($value);
			});
		});

		$article = Article::where('id', $article_id)->first();
		$article->name = $article->code . ' - ' . $article->name . ' ' . $article->warehouse_unit->name . ' x ' . $article->package_warehouse;

		return response()->json([
			'items' => $childElements,
			'article_name' => $article->name,
		]);
	}
}
