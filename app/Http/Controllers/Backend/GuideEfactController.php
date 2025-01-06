<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Clients\EfactCordClient;
use App\Company;

use App\Guide;
use App\GuidesDetail;
use App\Http\Clients\EfactClient;
use App\WarehouseMovement;
use App\WarehouseMovementDetail;
use Illuminate\Support\Facades\Log;

class GuideEfactController extends Controller
{
    private $billingClientPunto;

    public function __construct(EfactClient $billingClientPunto)
    {
        $this->billingClientPunto = $billingClientPunto;
    }

    public function index()
    {

        $companies = Company::select('id', 'name')->whereIn('id', [2])->get();
        return view('backend.guide_efact')->with(compact('companies'));
    }

    public function validate_voucher_form()
    {
        $messages = [
            'company_id.required'       => 'Debe seleccionar una Compañía.',
            'flag_ose.required'         => 'Debe seleccionar el Flag Ose.',
            'movement_type_id.required' => 'Debe seleccionar el Tipo de Movimiento.'
        ];

        $rules = [
            'company_id'        => 'required',
            'flag_ose'          => 'required',
            'movement_type_id'  => 'required'
        ];

        request()->validate($rules, $messages);
        return request()->all();
    }

    public function list()
    {

        $company_id = request('model.company_id');
        $flag_ose = request('model.flag_ose');
        $movement_type_id = request('model.movement_type_id');
        $serie = request('model.serie');

        $warehouse_movement = WarehouseMovement::join('companies', 'company_id', '=', 'companies.id')
            ->join('warehouse_types', 'warehouse_type_id', '=', 'warehouse_types.id')
            ->join('movent_classes', 'movement_class_id', '=', 'movent_classes.id')
            ->join('movent_types', 'movement_type_id', '=', 'movent_types.id')
            ->select(
                'warehouse_movements.id',
                DB::raw('CONCAT(warehouse_movements.referral_guide_series,"-",warehouse_movements.referral_guide_number) as serie_guia'),
                DB::raw('CONCAT(warehouse_movements.referral_serie_number,"-",warehouse_movements.referral_voucher_number) as serie_elect'),
                DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as issue_date'),
                'warehouse_movements.fac_date as traslate_date',
                'companies.name as compania',
                'warehouse_types.name as almacen',
                'movent_classes.name as clase_mov',
                'movent_types.name as tip_mov',
                'warehouse_movements.account_name as cliente'
            )
            ->where('warehouse_movements.company_id', $company_id)
            ->where('warehouse_movements.movement_type_id', $movement_type_id)
            ->where('warehouse_movements.electronic', 1)
            ->where('warehouse_movements.efact', $flag_ose)
            ->orderBy('warehouse_movements.id', 'desc')
            ->get();

        return $warehouse_movement;
    }

    public function get_detail()
    {
        $warehouse_movement_id = request('voucher_id');

        $warehouse_movement = WarehouseMovement::find($warehouse_movement_id, ['referral_serie_number', 'referral_voucher_number', 'account_name']);
        $warehouse_movement_detail = WarehouseMovementDetail::join('articles', 'article_code', 'articles.id')
            ->where('warehouse_movement_id', $warehouse_movement_id)
            ->select(
                'warehouse_movement_details.id as id',
                'articles.name as name',
                'warehouse_movement_details.digit_amount as quantity',
                'warehouse_movement_details.total as total'
            )
            ->get();

        return response()->json([
            'voucher'            => $warehouse_movement,
            'voucher_details'    => $warehouse_movement_detail
        ]);
    }

    public function send_voucher()
    {

        $ids = request('ids');
        $task = request('task');


        foreach ($ids as $id) {

            $guides = WarehouseMovement::leftjoin('companies', 'company_id', '=', 'companies.id')
                ->leftjoin('movent_types', 'movement_type_id', '=', 'movent_types.id')
                ->leftjoin('company_addresses', 'companies.id', '=', 'company_addresses.company_id')
                ->leftjoin('employees', 'warehouse_movements.employee_id', '=', 'employees.id')
                ->leftjoin('document_types', 'employees.document_type_id', '=', 'document_types.id')
                ->leftjoin('clients', 'warehouse_movements.account_id', '=', 'clients.id')
                ->leftjoin('document_types as client_document_types', 'clients.document_type_id', '=', 'client_document_types.id')
                ->leftjoin('client_addresses', 'client_addresses.client_id', '=', 'clients.id')
                ->leftjoin('ubigeos as client_ubigeos', 'client_ubigeos.id', '=', 'client_addresses.ubigeo_id')
                ->select(
                    'warehouse_movements.referral_serie_number as serie_number',
                    'warehouse_movements.referral_voucher_number as voucher_number',
                    DB::Raw('DATE_FORMAT(warehouse_movements.created_at, "%Y-%m-%d") as issue_date'),
                    'warehouse_movements.referral_guide_series as referral_serie_number',
                    'warehouse_movements.referral_guide_number as referral_voucher_number',
                    'companies.document_number as company_document_number',
                    'companies.name as company_name',
                    'companies.short_name as short_name',
                    'company_addresses.ubigeo as company_ubigeo',
                    'company_addresses.address as company_addresses',
                    'company_addresses.province as company_province',
                    'company_addresses.department as company_department',
                    'company_addresses.district as company_district',
                    'client_document_types.type as client_type_doc',
                    'warehouse_movements.account_document_number as client_doc_number',
                    'client_ubigeos.id as client_ubigeo_id',
                    'client_addresses.address as client_address',
                    'client_ubigeos.province as client_province',
                    'client_ubigeos.country as client_country',
                    'client_ubigeos.district as client_district',
                    'client_ubigeos.department as client_department',
                    'warehouse_movements.account_name as client_name',
                    'warehouse_movements.fac_date as guides_traslate_date',
                    'document_types.type as employe_document_type',
                    'employees.document_number as employe_document_number',
                    'employees.first_name as employe_first_name',
                    'employees.last_name as employe_last_name',
                    'employees.license as employe_license',
                    'warehouse_movements.license_plate as placa',
                    'movent_types.name as tip_mov',
                    'warehouse_movements.scop_number as scop_number',
                    'warehouse_movements.efact as efact',
                    'warehouse_movements.electronic as electronic'
                )->findOrFail($id);

            $guides_detail = WarehouseMovementDetail::join('articles', 'article_code', '=', 'articles.id')
                ->join('units', 'articles.sale_unit_id', 'units.id')
                ->where('warehouse_movement_details.warehouse_movement_id', $id)
                ->select(
                    'units.short_name as unit_short_name',
                    'warehouse_movement_details.digit_amount as quantity',
                    'articles.name as name',
                    'articles.id as article_id',
                    'warehouse_movement_details.total as total',
                )
                ->get();

            $total_text = $guides->tip_mov . ' | ' . $guides->referral_serie_number . '-' . $guides->referral_voucher_number . ' | SCOP: ' . $guides->scop_number;

            if ($task == 'xml') {

                $ruta = 'uploads/' . $guides->short_name . '/' . $guides->issue_date . '/xml/' . $guides->company_document_number . '-09-' . $guides->serie_number . '-' . $guides->voucher_number . '.xml';
                $xml_render = $this->xml_render($guides, $guides_detail, $ruta, $total_text);
                $response[] = $xml_render;

                $res = $this->billingClientPunto->sendDocumentXML(base_path('html/' . $ruta));

                if ($res != null) {
                    $this->billingClientPunto->getXmlFromTicket($res['description']);

                    $obj = WarehouseMovement::findOrFail($id);
                    $obj->efact = 1;
                    $obj->update();
                }
            }
        }
        return $response;
    }

    public function xml_render(Object $obj, $guides_detail, $ruta, $total_text)
    {

        $kg = 0;
        foreach ($guides_detail as $item) {
            $kg = $kg + ($item->total);
        }

        $textoXML = view('backend.xml.guia_remision_efact_vp', compact('obj', 'guides_detail', 'kg', 'total_text'))->render();
        $textoXML = mb_convert_encoding($textoXML, "UTF-8");
        $generate_xml = Storage::disk('public')->put($ruta, $textoXML);

        return $textoXML;
    }
}
