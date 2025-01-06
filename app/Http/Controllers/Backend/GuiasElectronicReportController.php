<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Http\Controllers\Controller;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Illuminate\Support\Facades\DB;
use PDF;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class GuiasElectronicReportController extends Controller
{
	public function index()
	{
		$companies = Company::select('id', 'name')->whereIn('id', [2])->get();
		return view('backend.guias_electronic')->with(compact('companies'));
	}

	public function validateForm()
	{
		$messages = [
			'company_id.required'                           => 'Debe seleccionar una Compañía.',
			'movement_type_id.required'                     => 'Debe seleccionar Tipo de Movimiento.',
		];

		$rules = [
			'company_id'                                    => 'required',
			'movement_type_id'                              => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

	public function detail()
	{
		$warehouse_movement_id = request('id');

		$warehouse_movement = WarehouseMovement::find($warehouse_movement_id, ['referral_guide_series', 'referral_guide_number','account_name']);
		$warehouse_movement_detail = WarehouseMovementDetail::join('articles', 'warehouse_movement_details.article_code', 'articles.id')
			->where('warehouse_movement_id', $warehouse_movement_id)
			->select(
				'warehouse_movement_details.id as id',
				'articles.name as name',
				'warehouse_movement_details.digit_amount as quantity'
			)
			->get();

		return response()->json([
			'voucher'            => $warehouse_movement,
			'voucher_details'    => $warehouse_movement_detail
		]);
	}

	public function list()
	{
		$q = request('query');
		$search = $q['generalSearch'];

		$company_id = request('company_id');
		$movement_type_id = request('movement_type_id');

		if ($movement_type_id = 12) {
			$warehouse_movement = WarehouseMovement::leftjoin('companies', 'companies.id', 'warehouse_movements.company_id')
				->leftjoin('movent_types', 'movent_types.id', 'warehouse_movements.movement_type_id')
				->leftjoin('employees', 'employees.id', 'warehouse_movements.employee_id')
				->leftjoin('clients', 'clients.id', 'warehouse_movements.account_id')
				->select(
					'warehouse_movements.id as id',
					'companies.name as company_name',
					'warehouse_movements.movement_type_id as movement_type_id',
					'movent_types.name as movement_type_name',
					DB::Raw('CASE WHEN warehouse_movements.electronic = 1 THEN "Electrónico" ELSE "Físico" END as tipo_guia'),
					DB::Raw('CONCAT(warehouse_movements.referral_serie_number,"-",warehouse_movements.referral_voucher_number) AS electronic'),
					DB::Raw('CONCAT(warehouse_movements.referral_guide_series,"-",warehouse_movements.referral_guide_number) AS fisico'),
					DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as issue_date'),
					DB::Raw('DATE_FORMAT(warehouse_movements.fac_date, "%Y-%m-%d") as traslate_date'),
					'clients.business_name as cliente',
					'employees.first_name as chofer'
				)
				->when($search, function ($query, $search) {
					return $query->where('warehouse_movements.referral_serie_number', '=', /*'%'.*/ $search/*.'%'*/)
						->orWhere('warehouse_movements.referral_voucher_number', '=', /*'%'.*/ $search/*.'%'*/);
				})
				->where('warehouse_movements.company_id', $company_id)
				->where('warehouse_movements.movement_type_id', $movement_type_id)
				->where('warehouse_movements.warehouse_type_id', 75)
				->orderBy('warehouse_movements.id', 'desc')
				->get();
		}

		return $warehouse_movement;
	}

	public function generarPdf()
	{

		$id = request('id');

		$obj = WarehouseMovement::leftjoin('companies', 'companies.id', '=', 'warehouse_movements.company_id')
			->leftjoin('company_addresses', 'company_addresses.company_id', '=', 'companies.id')
			->leftjoin('movent_types', 'movent_types.id', '=', 'warehouse_movements.movement_type_id')
			->leftjoin('clients', 'clients.id', '=', 'warehouse_movements.account_id')
			->leftjoin('document_types', 'document_types.id', '=', 'clients.document_type_id')
			->leftjoin('client_addresses', 'client_addresses.client_id', '=', 'clients.id')
			->leftjoin('employees', 'employees.id', '=', 'warehouse_movements.employee_id')
			->leftjoin('warehouse_movement_details', 'warehouse_movement_details.warehouse_movement_id', '=', 'warehouse_movements.id')
			->leftjoin('articles', 'articles.id', '=', 'warehouse_movement_details.article_code')
			->select(
				'companies.id as company_id',
				'companies.name as company_name',
				'company_addresses.address as company_address',
				'company_addresses.district as company_district',
				'company_addresses.province as company_province',
				'company_addresses.department as company_department',
				'companies.document_number as company_document_number',
				DB::Raw('CONCAT(warehouse_movements.referral_serie_number,"-",warehouse_movements.referral_voucher_number) as electronic'),
				'movent_types.name as movement_type_name',
				DB::Raw('CONCAT(warehouse_movements.referral_guide_series,"-",warehouse_movements.referral_guide_number) as reference'),
				'clients.business_name as cliente',
				'document_types.name as tipo_doc_client',
				'clients.document_number as client_document_number',
				DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as issue_date'),
				DB::Raw('DATE_FORMAT(warehouse_movements.fac_date, "%Y-%m-%d") as traslate_date'),
				'client_addresses.address as client_address',
				'employees.first_name as chofer',
				'employees.document_number as chofer_document_number',
				'employees.license as chofer_license',
				'warehouse_movements.license_plate as vehicle_placa',
				DB::Raw('CASE WHEN warehouse_movements.electronic = 1 THEN "Electrónica" ELSE "Física" END as nom_guia'),
				DB::Raw('SUM(warehouse_movement_details.converted_amount) as kg')
			)
			->where('warehouse_movements.id', $id)
			->firstOrFail();

		$details = WarehouseMovementDetail::leftjoin('articles', 'articles.id', '=', 'warehouse_movement_details.article_code')
			->leftjoin('units', 'units.id', '=', 'articles.warehouse_unit_id')
			->select(
				'articles.name as article_name',
				'units.short_name as article_unit',
				'warehouse_movement_details.digit_amount as quantity',
			)
			->where('warehouse_movement_id', $id)
			->get();

		$document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate(
			'| ' . $obj->company_document_number . ' | ' . $obj->reference . ' | ' . $obj->electronic.
			' | ' . $obj->issue_date.' | ' . $obj->traslate_date.' | ' . $obj->cliente.' | ' . $obj->chofer
		));

		$pdf = PDF::loadView('backend.pdf_guia_elect', compact('obj', 'details', 'document_qrcode'));
		return $pdf->download('SD.pdf');
	}
}
