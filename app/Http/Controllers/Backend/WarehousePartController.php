<?php

namespace App\Http\Controllers\Backend;

use App\Client;
use App\DocumentType;
use App\Employee;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MoventClass;
use App\Provider;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use App\WarehouseType;
use Exception;
use Illuminate\Support\Facades\DB;
use PDF;

class WarehousePartController extends Controller
{
    public function index() {
		$warehouse_types = WarehouseType::select('id', 'name')->get();
		$movement_classes = MoventClass::select('id', 'name')->get();

		return view('backend.warehouse_part')->with(compact('warehouse_types', 'movement_classes'));
	}

	public function validateForm() {
		$messages = [
			'warehouse_type_id.required'	    => 'Debe seleccionar un Almacén.',
			'movement_class_id.required'	    => 'Debe seleccionar una Clase de Movimiento.',
			'warehouse_movement_id.required'    => 'El Nº de Parte es obligatorio.',
		];

		$rules = [
			'warehouse_type_id'     => 'required',
			'movement_class_id'     => 'required',
			'warehouse_movement_id' => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

    public function getWarehouseMovements() {
        $warehouse_type_id = request('warehouse_type_id');
		$movement_class_id = request('movement_class_id');

        $elements = WarehouseMovement::select('id', 'movement_number')
            ->where('warehouse_type_id', $warehouse_type_id)
            ->where('movement_class_id', $movement_class_id)
            ->orderBy('movement_number', 'desc')
            ->get();

        return $elements;
    }

    public function list() {
		$warehouse_movement_id = request('model.warehouse_movement_id');
        $flag_export = request('flag_export');
        $flag_export_guide = request('flag_export_guide');

		$warehouse_movement = WarehouseMovement::leftjoin('company_addresses', function($join) {
				$join->on('warehouse_movements.company_id', '=', 'company_addresses.company_id')
					->where('company_addresses.type', '=', 2);
			})
            ->leftjoin('employees', 'warehouse_movements.account_id', '=', 'employees.id')
			->select('warehouse_movements.id', 'warehouse_movements.company_id', 'company_addresses.address as company_address', 'company_addresses.district as company_district', 'company_addresses.province as company_province', 'company_addresses.department as company_department', 'warehouse_type_id', 'movement_class_id', 'movement_type_id', 'movement_number', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'referral_warehouse_document_type_id', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'total', 'warehouse_movements.created_at', 'employees.license as employee_license')
			->where('warehouse_movements.id', $warehouse_movement_id)
			->first();

        $creation_date = date('Y-m-d', strtotime($warehouse_movement->created_at));

		switch ( $warehouse_movement->warehouse_account_type_id ) {
			case 1:
				$document_type_id = Client::select('document_type_id')->where('id', $warehouse_movement->account_id)->withTrashed()->first()->document_type_id;
				$account_document_name = DocumentType::findOrFail($document_type_id)->name;
				break;

			case 2:
				$document_type_id = Provider::select('document_type_id')->where('id', $warehouse_movement->account_id)->withTrashed()->first()->document_type_id;
				$account_document_name = DocumentType::findOrFail($document_type_id)->name;
				break;

			case 3:
				$document_type_id = Employee::select('document_type_id')->where('id', $warehouse_movement->account_id)->first()->document_type_id;
				$account_document_name = DocumentType::findOrFail($document_type_id)->name;
				break;

			default:
				$account_document_name = '';
				break;
		}

		$warehouse_movement->account_document_name = $account_document_name;
        $warehouse_movement->company_name = $warehouse_movement->company->name;
        $warehouse_movement->warehouse_type_name = $warehouse_movement->warehouse_type->name;
        $warehouse_movement->movement_class_name = $warehouse_movement->movement_class->name;
        $warehouse_movement->movement_type_name = $warehouse_movement->movement_type->name;
        $warehouse_movement->referral_guide = $warehouse_movement->referral_guide_series . '-' . $warehouse_movement->referral_guide_number;
        $warehouse_movement->referral_document = $warehouse_movement->warehouse_document_type->short_name . '-' . $warehouse_movement->referral_serie_number . '-' . $warehouse_movement->referral_voucher_number;
        $warehouse_movement->total = number_format($warehouse_movement->total, 2, '.', ',');
        $warehouse_movement->creation_date = $creation_date;

        $elements = WarehouseMovementDetail::select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'warehouse_movement_details.total', 'warehouse_movements.id')
            ->join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
            ->where('warehouse_movement_id', $warehouse_movement->id)
            ->where('company_id', $warehouse_movement->company_id)
            ->where('warehouse_type_id', $warehouse_movement->warehouse_type_id)
            ->get();

        $elements->map(function ($item, $index) use ($warehouse_movement) {
            $item->article_code = $item->article->code;
            $item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
            $item->converted_amount = number_format($item->converted_amount, 4, '.', ',');
            if ( $warehouse_movement->movement_type_id == 1 || $warehouse_movement->movement_type_id == 2 || $warehouse_movement->movement_type_id == 15 ) {
                $item->price = number_format($item->price, 4, '.', ',');
                $item->total = number_format($item->total, 4, '.', ',');
            } else {
                $item->price = '';
                $item->total = '';
            }
        });

        if ( $flag_export ) {
            $pdf = PDF::loadView('backend.warehouse_part_pdf', compact('warehouse_movement', 'elements'));
		    return $pdf->download('parte-'.$warehouse_movement->movement_class_name.'-'.$warehouse_movement->movement_type_name.'-N'.$warehouse_movement->movement_number.'.pdf');
        } elseif ( $flag_export_guide ) {
			$packaging = WarehouseMovementDetail::join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
				->join('articles', 'articles.id', '=', 'warehouse_movement_details.article_code')
				->join('classifications', 'classifications.id', '=', 'articles.subgroup_id')
				->where('warehouse_movement_id', $warehouse_movement->id)
				->where('company_id', $warehouse_movement->company_id)
				->where('warehouse_movements.warehouse_type_id', $warehouse_movement->warehouse_type_id)
				->where('articles.subgroup_id', '>=', 55)
				->where('articles.subgroup_id', '<=', 58)
				->select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'warehouse_movement_details.total', 'articles.subgroup_id', 'classifications.name as classification_name', DB::Raw('SUM(converted_amount) as total_converted_amount'))
				->groupBy('articles.subgroup_id')
				->get();

			$packaging->map(function ($item, $index) {
				$item->total_converted_amount = number_format($item->total_converted_amount, 0, '.', ',');
			});

			$pdf = PDF::loadView('backend.pdf.referral_guide', compact('warehouse_movement', 'elements', 'packaging'));
		    return $pdf->download('guia-remision-'.$warehouse_movement->movement_class_name.'-'.$warehouse_movement->movement_type_name.'-N'.$warehouse_movement->movement_number.'.pdf');
		} else {
            return response()->json([
                'warehouse_movement'    => $warehouse_movement,
                'data'                  => $elements
            ]);
        }

    }

	public function export() {
		$warehouse_movement_id = request('warehouse_movement_id');

		$warehouse_movement = WarehouseMovement::select('id', 'company_id', 'warehouse_type_id', 'movement_class_id', 'movement_type_id', 'movement_number', 'warehouse_account_type_id', 'account_id', 'account_document_number', 'account_name', 'referral_guide_series', 'referral_guide_number', 'referral_warehouse_document_type_id', 'referral_serie_number', 'referral_voucher_number', 'scop_number', 'license_plate', 'total', 'created_at')
			->where('id', $warehouse_movement_id)
			->first();

		$creation_date = date('Y-m-d', strtotime($warehouse_movement->created_at));

		$warehouse_movement->company_name = $warehouse_movement->company->name;
		$warehouse_movement->warehouse_type_name = $warehouse_movement->warehouse_type->name;
		$warehouse_movement->movement_class_name = $warehouse_movement->movement_class->name;
		$warehouse_movement->movement_type_name = $warehouse_movement->movement_type->name;
		$warehouse_movement->referral_guide = $warehouse_movement->referral_guide_series . '-' . $warehouse_movement->referral_guide_number;
		$warehouse_movement->referral_document = $warehouse_movement->warehouse_document_type->short_name . '-' . $warehouse_movement->referral_serie_number . '-' . $warehouse_movement->referral_voucher_number;
		$warehouse_movement->total = number_format($warehouse_movement->total, 2, '.', ',');
		$warehouse_movement->creation_date = $creation_date;

		$elements = WarehouseMovementDetail::select('warehouse_movement_details.id', 'item_number', 'article_code', 'converted_amount', 'price', 'total', 'warehouse_movements.id')
			->join('warehouse_movements', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->where('warehouse_movement_id', $warehouse_movement->id)
            ->where('company_id', $warehouse_movement->company_id)
            ->where('warehouse_type_id', $warehouse_movement->warehouse_type_id)
            ->get();

		$elements->map(function ($item, $index) use ($warehouse_movement) {
			$item->article_code = $item->article->code;
			$item->article_name = $item->article->name . ' ' . $item->article->warehouse_unit->name . ' x ' . $item->article->package_warehouse;
			$item->converted_amount = number_format($item->converted_amount, 4, '.', ',');
			if ( $warehouse_movement->movement_type_id == 1 || $warehouse_movement->movement_type_id == 2 || $warehouse_movement->movement_type_id == 15 ) {
				$item->price = number_format($item->price, 4, '.', ',');
				$item->total = number_format($item->total, 4, '.', ',');
			} else {
				$item->price = '';
				$item->total = '';
			}
		});

		$pdf = PDF::loadView('backend.warehouse_part_pdf', compact('warehouse_movement', 'elements'));
		return $pdf->stream();
	}

	public function exportReferralGuide() {
		$pdf = PDF::loadView('backend.pdf.referral_guide');
		return $pdf->stream();
	}
}
