<?php

namespace App\Http\Controllers\Backend;

use App\Clients\EfactClient;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Auth;
use App\Company;
use App\Guide;
use App\GuidesDetail;

class GuideEfactController extends Controller
{
    private $env = 'production';
    private $billingClientPunto;

    public function __construct(EfactClient $billingClientPunto)
    {
        $this->billingClientPunto = $billingClientPunto;
    }

    public function index()
    {

        $companies = Company::select('id', 'name')->whereIn('id', [1, 2])->get();
        $user_name = Auth::user()->user;
        return view('backend.guide_efact')->with(compact('companies', 'user_name'));
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

        $guides = Guide::join('companies', 'company_id', '=', 'companies.id')
            ->join('warehouse_types', 'warehouse_type_id', '=', 'warehouse_types.id')
            ->join('movent_classes', 'movement_class_id', '=', 'movent_classes.id')
            ->join('movent_types', 'movement_type_id', '=', 'movent_types.id')
            ->select(
                'guides.id',
                DB::raw('CONCAT(guides.referral_guide_series,"-",guides.referral_guide_number) as serie_elect'),
                DB::raw('CONCAT(guides.referral_serie_number,"-",guides.referral_voucher_number) as serie_guia'),
                DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as issue_date'),
                'guides.traslate_date as traslate_date',
                'companies.name as compania',
                'warehouse_types.name as almacen',
                'movent_classes.name as clase_mov',
                'movent_types.name as tip_mov',
                'guides.account_name as cliente'
            )
            ->where('guides.company_id', $company_id)
            ->where('guides.movement_type_id', $movement_type_id)
            ->where('guides.state', $flag_ose)
            ->orderBy('guides.id', 'desc')
            ->get();

        return $guides;
    }

    public function get_detail()
    {
        $guide_id = request('voucher_id');

        $guides = Guide::find($guide_id, ['referral_guide_series', 'referral_guide_number']);
        $guides_detail = GuidesDetail::join('articles', 'article_code', 'articles.id')
            ->where('guides_id', $guide_id)
            ->select(
                'guides_details.id as id',
                'articles.name as name',
                'guides_details.price as unit_price',
                'guides_details.digit_amount as quantity',
                'guides_details.igv as igv',
                'guides_details.total as total'
            )
            ->get();

        return response()->json([
            'voucher'            => $guides,
            'voucher_details'    => $guides_detail
        ]);
    }

    public function send_voucher()
    {
        //$this->billingClient->getAccessToken();

        $company_id = request('company_id');
        $ids = request('ids');
        $task = request('task');
        $flag_ose = request('flag_ose');

        $company = Company::where('id', $company_id)
            ->select('document_number', 'name', 'short_name', 'bizlinks_user', 'bizlinks_password', 'bizlinks_user_test', 'bizlinks_password_test', 'certificate_pem')
            ->first();


        foreach ($ids as $id) {

            $guides = Guide::leftjoin('companies', 'company_id', '=', 'companies.id')
                ->leftjoin('warehouse_types', 'warehouse_type_id', '=', 'warehouse_types.id')
                ->leftjoin('movent_classes', 'movement_class_id', '=', 'movent_classes.id')
                ->leftjoin('movent_types', 'movement_type_id', '=', 'movent_types.id')
                ->leftjoin('company_addresses', 'companies.id', '=', 'company_addresses.company_id')
                ->leftjoin('vehicles', 'guides.vehicle_id', '=', 'vehicles.id')
                ->leftjoin('warehouse_account_types', 'guides.warehouse_account_type_id', 'warehouse_account_types.id')
                ->leftjoin('employees', 'guides.account_id', '=', 'employees.id')
                ->leftjoin('client_routes', 'route_id', '=', 'client_routes.id')
                ->leftjoin('ubigeos', 'ubigeo_id', 'ubigeos.id')
                ->leftjoin('document_types', 'employees.document_type_id', 'document_types.id')
                ->select(
                    'document_types.type as document_type',
                    'guides.referral_serie_number as referral_serie_number',
                    'guides.referral_voucher_number as referral_voucher_number',
                    'client_routes.id as client_id',
                    'warehouse_account_types.id as persona_id',
                    'guides.id',
                    'guides.company_id as company_id',
                    'guides.referral_guide_series as serie_number',
                    'guides.referral_guide_number as voucher_number',
                    'companies.short_name as short_name',
                    'companies.document_number as company_document_number',
                    'company_addresses.ubigeo as company_ubigeo',
                    'company_addresses.department as company_department',
                    'company_addresses.province as company_province',
                    'company_addresses.district as company_district',
                    'company_addresses.address as company_addresses',
                    'companies.name as company_name',
                    'warehouse_types.name as almacen',
                    'movent_classes.name as clase_mov',
                    'movent_types.name as tip_mov',
                    'guides.account_name as cliente',
                    'vehicles.plate as placa',
                    'employees.document_number as employe_document_number',
                    'employees.first_name as employe_first_name',
                    'employees.last_name as employe_last_name',
                    'employees.license as employe_license',
                    DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as issue_date'),
                    'guides.traslate_date as guides_traslate_date',
                    'ubigeos.ubigeo as ubigeo_ubigeo',
                    'ubigeos.district as ubigeo_district',
                    'ubigeos.province as ubigeo_province',
                    'ubigeos.department as ubigeo_department',
                    'ubigeos.country as ubigeo_country',
                    'guides.employee_id as employee_id',
                    'guides.scop_number as scop_number'
                )->findOrFail($id);

            if ($guides->employee_id != null) {
                $guides = Guide::leftjoin('companies', 'company_id', '=', 'companies.id')
                    ->leftjoin('warehouse_types', 'warehouse_type_id', '=', 'warehouse_types.id')
                    ->leftjoin('movent_classes', 'movement_class_id', '=', 'movent_classes.id')
                    ->leftjoin('movent_types', 'movement_type_id', '=', 'movent_types.id')
                    ->leftjoin('company_addresses', 'companies.id', '=', 'company_addresses.company_id')
                    ->leftjoin('vehicles', 'guides.vehicle_id', '=', 'vehicles.id')
                    ->leftjoin('warehouse_account_types', 'guides.warehouse_account_type_id', 'warehouse_account_types.id')
                    ->leftjoin('clients', 'guides.account_id', '=', 'clients.id')
                    ->leftjoin('employees', 'guides.employee_id', '=', 'employees.id')
                    ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
                    ->leftjoin('ubigeos', 'ubigeo_id', 'ubigeos.id')
                    ->leftjoin('document_types', 'employees.document_type_id', 'document_types.id')
                    ->select(
                        'document_types.type as document_type',
                        'guides.referral_serie_number as referral_serie_number',
                        'guides.referral_voucher_number as referral_voucher_number',
                        'client_routes.id as client_id',
                        'warehouse_account_types.id as persona_id',
                        'guides.id',
                        'guides.company_id as company_id',
                        'guides.referral_guide_series as serie_number',
                        'guides.referral_guide_number as voucher_number',
                        'companies.short_name as short_name',
                        'companies.document_number as company_document_number',
                        'company_addresses.ubigeo as company_ubigeo',
                        'company_addresses.department as company_department',
                        'company_addresses.province as company_province',
                        'company_addresses.district as company_district',
                        'company_addresses.address as company_addresses',
                        'companies.name as company_name',
                        'warehouse_types.name as almacen',
                        'movent_classes.name as clase_mov',
                        'movent_types.name as tip_mov',
                        'guides.account_name as cliente',
                        'vehicles.plate as placa',
                        'employees.document_number as employe_document_number',
                        'employees.first_name as employe_first_name',
                        'employees.last_name as employe_last_name',
                        'employees.license as employe_license',
                        DB::Raw('DATE_FORMAT(guides.created_at, "%Y-%m-%d") as issue_date'),
                        'guides.traslate_date as guides_traslate_date',
                        'ubigeos.ubigeo as ubigeo_ubigeo',
                        'ubigeos.district as ubigeo_district',
                        'ubigeos.province as ubigeo_province',
                        'ubigeos.department as ubigeo_department',
                        'ubigeos.country as ubigeo_country',
                        'guides.employee_id as employee_id',
                        'guides.scop_number as scop_number'
                    )->findOrFail($id);
            }

            $guides_detail = GuidesDetail::join('articles', 'article_code', 'articles.id')
                ->join('units', 'articles.sale_unit_id', 'units.id')
                ->where('guides_id',  $guides->id)
                ->select(
                    'guides_details.id as id',
                    'articles.id as article_id',
                    'articles.name as name',
                    'articles.convertion as convertion',
                    'guides_details.price as unit_price',
                    'guides_details.digit_amount as quantity',
                    'guides_details.igv as igv',
                    'guides_details.total as total',
                    'units.short_name as unit_short_name'
                )
                ->get();

            $total_text = $guides->tip_mov . ' | ' . $guides->referral_serie_number . '-' . $guides->referral_voucher_number . ' | SCOP: ' . $guides->scop_number;

            if ($task == 'xml') {
                /*
                $ruta = 'uploads/' . $guides->short_name . '/' . $guides->issue_date . '/xml/' .
                    $guides->company_document_number . '-09-' . $guides->serie_number . '-' . $guides->voucher_number . '.xml';
                    */

                $ruta = 'uploads/' . $guides->short_name . '/' . $guides->issue_date . '/xml/' .
                    $guides->company_document_number . '-09-' . $guides->serie_number . '-' . $guides->voucher_number . '.xml';

                $xml_render = $this->xml_render($guides, $guides_detail, $ruta, $total_text);
                $response[] = $xml_render;

                $res = $this->billingClientPunto->sendDocumentXML(base_path('html/'.$ruta));

                if ($res !== null) {
                    $responseXml = $this->billingClientPunto->getXmlFromTicket($res['description']);
                    $guides->state = 1;
                    $guides->update();
                }
            }
        }
        return $response;
    }

    public function xml_render(Object $obj, $guides_detail, $ruta, $total_text)
    {

        $kg = 0;
        foreach ($guides_detail as $item) {
            $kg = $kg + ($item->convertion * $item->quantity);
        }

        $textoXML = view('backend.xml.guia_remision_efact', compact('obj', 'guides_detail', 'kg', 'total_text'))->render();
        $textoXML = mb_convert_encoding($textoXML, "UTF-8");
        $generate_xml = Storage::disk('public')->put($ruta, $textoXML);
        return $textoXML;
    }
}
