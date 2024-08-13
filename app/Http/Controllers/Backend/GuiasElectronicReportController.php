<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Guide;
use App\Guides;
use App\GuidesDetail;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use PDF;
use QrCode;

class GuiasElectronicReportController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name')->whereIn('id', [1, 2])->get();
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
        $guide_id = request('id');

        $guides = Guide::find($guide_id, ['referral_serie_number', 'referral_voucher_number']);
        $guides_detail = GuidesDetail::join('articles', 'article_code', 'articles.id')
            ->where('guides_id', $guide_id)
            ->select(
                'guides_details.id as id',
                'articles.name as name',
                'guides_details.price as unit_price',
                'guides_details.digit_amount as quantity'
            )
            ->get();

        return response()->json([
            'voucher'            => $guides,
            'voucher_details'    => $guides_detail
        ]);
    }

    public function list()
    {
        $q = request('query');
        $search = $q['generalSearch'];

        $company_id = request('company_id');
        $movement_type_id = request('movement_type_id');

        $guides = Guide::leftjoin('companies', 'companies.id', 'guides.company_id')
            ->leftjoin('movent_types', 'movent_types.id', 'guides.movement_type_id')
            ->leftjoin('employees', 'employees.id', $movement_type_id == 11 ? 'guides.account_id' : 'guides.employee_id')
            ->select(
                'guides.id as id',
                'companies.name as company_name',
                'guides.movement_type_id as movement_type_id',
                'movent_types.name as movement_type_name',
                'guides.traslate_date as traslate_date',
                DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as fecha_emision'),
                DB::Raw('CONCAT(guides.referral_serie_number,"-",guides.referral_voucher_number) as serie'),
                DB::Raw('CONCAT(employees.first_name," ",employees.last_name) as chofer')
            )
            ->when($search, function ($query, $search) {
                return $query->where('guides.referral_serie_number', '=', $search)
                    ->orWhere('guides.referral_voucher_number', '=', $search);
            })
            ->when($movement_type_id == 12, function ($query) {
                $query->leftjoin('clients', 'clients.id', 'guides.account_id')
                    ->addSelect('clients.business_name as cliente');
            })
            ->where('guides.company_id', $company_id)
            ->where('guides.movement_type_id', $movement_type_id)
            ->orderBy('guides.id', 'desc')
            ->get();

        return $guides;
    }

    public function generarPdf()
    {

        $id = request('id');

        $obj = Guide::leftjoin('companies', 'companies.id', 'guides.company_id')
            ->leftjoin('movent_types', 'movent_types.id', 'guides.movement_type_id')
            ->leftjoin('employees', 'employees.id', 'guides.account_id')
            ->leftjoin('company_addresses', 'company_addresses.company_id', 'companies.id')
            ->leftjoin('vehicles', 'guides.vehicle_id', '=', 'vehicles.id')
            ->leftjoin('ubigeos', 'ubigeos.id', '=', 'guides.ubigeo_id')
            ->select(
                'guides.id as id',
                'guides.company_id as company_id',
                'companies.name as company_name',
                'companies.document_number as company_document_number',
                'company_addresses.address as company_address',
                'company_addresses.district as company_district',
                'company_addresses.province as company_province',
                'company_addresses.department as company_department',
                'guides.movement_type_id as movement_type_id',
                'movent_types.name as movement_type_name',
                DB::Raw('CONCAT(guides.referral_serie_number,"-",guides.referral_voucher_number) as reference'),
                DB::Raw('CONCAT(guides.referral_guide_series,"-",guides.referral_guide_number) as electronic'),
                DB::Raw('CONCAT(employees.first_name," ",employees.last_name) as chofer'),
                DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as issue_date'),
                DB::Raw('DATE_FORMAT(guides.traslate_date, "%Y-%m-%d") as traslate_date'),
                'employees.document_number as chofer_document_number',
                'employees.license as chofer_license',
                'vehicles.plate as vehicle_placa',
                'ubigeos.district as ubigeo_district',
                'ubigeos.province as ubigeo_province',
                'ubigeos.department as ubigeo_department',
                'guides.employee_id as employee_id'
            )
            ->findOrFail($id);

        if ($obj->employee_id != null) {
            $obj = Guide::leftjoin('companies', 'companies.id', 'guides.company_id')
                ->leftjoin('movent_types', 'movent_types.id', 'guides.movement_type_id')
                ->leftjoin('employees', 'employees.id', 'guides.employee_id')
                ->leftjoin('company_addresses', 'company_addresses.company_id', 'companies.id')
                ->leftjoin('vehicles', 'guides.vehicle_id', '=', 'vehicles.id')
                ->leftjoin('ubigeos', 'ubigeos.id', '=', 'guides.ubigeo_id')
                ->select(
                    'guides.id as id',
                    'guides.company_id as company_id',
                    'companies.name as company_name',
                    'companies.document_number as company_document_number',
                    'company_addresses.address as company_address',
                    'company_addresses.district as company_district',
                    'company_addresses.province as company_province',
                    'company_addresses.department as company_department',
                    'guides.movement_type_id as movement_type_id',
                    'movent_types.name as movement_type_name',
                    DB::Raw('CONCAT(guides.referral_serie_number,"-",guides.referral_voucher_number) as reference'),
                    DB::Raw('CONCAT(guides.referral_guide_series,"-",guides.referral_guide_number) as electronic'),
                    DB::Raw('CONCAT(employees.first_name," ",employees.last_name) as chofer'),
                    DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as issue_date'),
                    DB::Raw('DATE_FORMAT(guides.traslate_date, "%Y-%m-%d") as traslate_date'),
                    'employees.document_number as chofer_document_number',
                    'employees.license as chofer_license',
                    'vehicles.plate as vehicle_placa',
                    'ubigeos.district as ubigeo_district',
                    'ubigeos.province as ubigeo_province',
                    'ubigeos.department as ubigeo_department',
                    'guides.employee_id as employee_id'
                )
                ->findOrFail($id);
        }


        $details = GuidesDetail::join('articles', 'articles.id', 'guides_details.article_code')
            ->select(
                'articles.name as article_name',
                'articles.convertion as article_convertion',
                'guides_details.digit_amount as quantity'
            )
            ->where('guides_details.guides_id', $id)
            ->get();

        $kg = 0;
        foreach ($details as $item) {
            $kg = $kg + ($item->article_convertion * $item->quantity);
        }

        $document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate('| ' . $obj->company_document_number . ' | ' . $obj->electronic));

        $pdf = PDF::loadView('backend.pdf_guia_elect', compact('obj', 'details', 'kg', 'document_qrcode'));
        return $pdf->download('SD.pdf');
    }
}
