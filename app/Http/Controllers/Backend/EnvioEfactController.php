<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Auth;
use PDF;
use App\ClientAddress;
use App\Client;
use App\Clients\EfactClient;
use App\Clients\EfactCordClient;
use App\Company;
use App\CompanyAddress;
use App\Currency;
use App\CreditNoteReason;
use App\DocumentType;
use App\Liquidation;
use App\Payment;
use App\Unit;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use App\Mail\VoucherMail;
use App\Mail\VoucherMailOficial;
use App\Sale;
use App\SaleDetail;
use App\WarehouseDocumentType;
use Carbon\CarbonImmutable;
use Luecano\NumeroALetras\NumeroALetras;
use NumeroALetras\NumeroALetras as NumeroALetrasNumeroALetras;
// use NumeroALetras\NumeroALetras as NumeroALetrasNumeroALetras;
use QrCode;
use SimpleXMLElement;
use SoapClient;
use SoapFault;
use SoapHeader;
use SoapVar;
use stdClass;

class EnvioEfactController extends Controller
{
    private $billingClientPunto;

    public function __construct(EfactClient $billingClientPunto)
    {
        $this->billingClientPunto = $billingClientPunto;
    }

    public function sendOse()
    {

        $companies = Company::select('id', 'name')->get();
        $voucher_types = VoucherType::select('id', 'name')->whereIn('id', [1, 2])->get();
        $user_name = Auth::user()->user;
        return view('backend.efact_voucher_send_ose')->with(compact('companies', 'voucher_types', 'user_name'));
    }

    public function validate_voucher_form()
    {
        $messages = [
            'company_id.required'       => 'Debe seleccionar una Compañía.',
            'voucher_type.required'     => 'Debe seleccionar un Tipo de Documento.',
            'date_of_issue.required_if' => 'Debe seleccionar una Fecha de Emisión.',
            'serie.required_if'         => 'El Número de Serie es obligatorio.',
        ];

        $rules = [
            'company_id'        => 'required',
            'voucher_type'      => 'required',
            'date_of_issue'     => 'required_if:voucher_type,2',
            'serie'             => 'required_if:voucher_type,2',
        ];

        request()->validate($rules, $messages);
        return request()->all();
    }

    public function list()
    {
        // Obtener datos del formulario
        $company_id = request('model.company_id');
        $voucher_type_id = request('model.voucher_type');
        $date_of_issue = request('model.date_of_issue') ? CarbonImmutable::createFromFormat('d-m-Y', request('model.date_of_issue'))->format('Y-m-d') : request('model.date_of_issue');
        $serie = request('model.serie');
        $initial_number = request('model.initial_number');
        $final_number = request('model.final_number');
        $order_serie = request('model.order_serie');
        $order_number = request('model.order_number');
        $flag_ose = request('model.flag_ose');

        $voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type', 'type']);

        if ($voucher_type->id == 3 || $voucher_type->id == 4 || $voucher_type->id == 13) {
            $serie_number = $voucher_type->serie_type . sprintf('%02d', $serie);
        } else {
            $serie_number = $voucher_type->serie_type . sprintf('%03d', $serie);
        }

        if ($voucher_type_id == 14) {
            $vouchers = Voucher::join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
                ->leftjoin('payments', 'payments.id', '=', 'vouchers.payment_id')
                ->leftjoin('clients', 'clients.id', '=', 'vouchers.client_id')
                ->leftjoin('document_types', 'document_types.id', '=', 'clients.document_type_id')
                ->leftjoin('currencies', 'currencies.id', '=', 'currency_id')
                ->where('vouchers.company_id', $company_id)
                ->where('voucher_type_id', $voucher_type_id)
                ->when($serie, function ($query) use ($serie_number) {
                    return $query->where('serie_number', '=', $serie_number);
                })
                ->when($initial_number, function ($query) use ($initial_number) {
                    return $query->where('voucher_number', '>=', $initial_number);
                })
                ->when($final_number, function ($query) use ($final_number) {
                    return $query->where('voucher_number', '<=', $final_number);
                })
                ->when($voucher_type->reference == 2, function ($query) use ($order_serie, $order_number, $date_of_issue) {
                    return $query->when($order_serie, function ($query) use ($order_serie) {
                        return $query->where('order_series', $order_serie);
                    })
                        ->when($order_number, function ($query) use ($order_number) {
                            return $query->where('order_number', $order_number);
                        })
                        ->where('issue_date', $date_of_issue);
                })
                ->where('ose', $flag_ose)
                ->select('vouchers.id', 'voucher_types.type', 'credit_note_reference_serie as serie_number', 'credit_note_reference_number as voucher_number', DB::Raw('DATE_FORMAT(issue_date, "%d-%m-%Y") as issue_date_formated'), 'payments.name as payment_name', 'order_series', 'order_number', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number as client_document_number', 'client_name', 'currencies.symbol as currency_symbol', 'total', 'igv_perception', 'summary_number', 'low_number')
                ->orderBy('credit_note_reference_serie', 'ASC')
                ->orderBy('credit_note_reference_number', 'ASC')
                ->get();
        } else {
            $vouchers = Voucher::join('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
                ->leftjoin('payments', 'payments.id', '=', 'vouchers.payment_id')
                ->leftjoin('clients', 'clients.id', '=', 'vouchers.client_id')
                ->leftjoin('document_types', 'document_types.id', '=', 'clients.document_type_id')
                ->leftjoin('currencies', 'currencies.id', '=', 'currency_id')
                ->where('vouchers.company_id', $company_id)
                ->where('voucher_type_id', $voucher_type_id)
                ->when($serie, function ($query) use ($serie_number) {
                    return $query->where('serie_number', '=', $serie_number);
                })
                ->when($initial_number, function ($query) use ($initial_number) {
                    return $query->where('voucher_number', '>=', $initial_number);
                })
                ->when($final_number, function ($query) use ($final_number) {
                    return $query->where('voucher_number', '<=', $final_number);
                })
                ->when($voucher_type->reference == 2, function ($query) use ($order_serie, $order_number, $date_of_issue) {
                    return $query->when($order_serie, function ($query) use ($order_serie) {
                        return $query->where('order_series', $order_serie);
                    })
                        ->when($order_number, function ($query) use ($order_number) {
                            return $query->where('order_number', $order_number);
                        })
                        ->where('issue_date', $date_of_issue);
                })
                ->where('ose', $flag_ose)
                ->select(
                    'vouchers.id',
                    'vouchers.ose',
                    'voucher_types.type',
                    'serie_number',
                    'voucher_number',
                    DB::Raw('DATE_FORMAT(issue_date, "%d-%m-%Y") as issue_date_formated'),
                    'payments.name as payment_name',
                    'order_series',
                    'order_number',
                    'clients.code as client_code',
                    'document_types.name as document_type_name',
                    'clients.document_number as client_document_number',
                    'client_name',
                    'currencies.symbol as currency_symbol',
                    'total',
                    'igv_perception',
                    'summary_number',
                    'low_number'
                )
                ->orderBy('serie_number', 'ASC')
                ->orderBy('voucher_number', 'ASC')
                ->get();
        }

        return $vouchers;
    }

    public function get_vouchers_for_table()
    {
        // Obtener datos del formulario
        $company_id = request('model.company_id');
        $voucher_id = request('model.voucher_type');
        $date_of_issue = date_create(request('model.date_of_issue'));
        $date_of_issue = date_format($date_of_issue, 'Y-m-d');
        $serie = request('model.serie');
        $initial_number = request('model.initial_number');
        $final_number = request('model.final_number');
        $order_serie = request('model.order_serie');
        $order_number = request('model.order_number');
        $flagOse = request('model.flag_ose');

        $company = Company::where('id', $company_id)
            ->select('database_name')
            ->first();

        $voucher = VoucherType::where('id', $voucher_id)
            ->select('name', 'serie_type', 'type', 'reference')
            ->first();

        $query = DB::connection($company->database_name)
            ->table('FacturacionMarket')
            ->where('TipoDocumento', $voucher->reference)
            ->where('Estado', 1)
            ->when($serie, function ($query, $serie) {
                return $query->where('NumSerie', $serie);
            })
            ->when($initial_number, function ($query, $initial_number) {
                return $query->where('NumeroDocumento', '>=', $initial_number);
            })
            ->when($final_number, function ($query, $final_number) {
                return $query->where('NumeroDocumento', '<=', $final_number);
            })
            ->where('flagOse', $flagOse);
        if ($voucher->reference == 2) {
            $query->where('FechaEmision', $date_of_issue)
                ->when($order_serie, function ($query, $order_serie) {
                    return $query->where('AnioPedido', $order_serie);
                })
                ->when($order_number, function ($query, $order_number) {
                    return $query->where('NumeroPedido', $order_number);
                })
                ->select('TipoDocumento', 'NumSerie', 'NumeroDocumento', 'CodCliente', 'FechaEmision', 'ValorNeto', 'IGV', 'ImporteTotal', 'TipoMoneda', 'CodFormaPago', 'PorcentajeIgv', 'NombreCliente', 'FechaVencimiento', 'AnioPedido', 'NumeroPedido');
        } else {
            $query->where('FechaEmision', '>=', '2019-09-13')
                ->select('TipoDocumento', 'NumSerie', 'NumeroDocumento', 'CodCliente', 'FechaEmision', 'ValorNeto', 'IGV', 'ImporteTotal', 'TipoMoneda', 'CodFormaPago', 'RucCliente', 'PorcentajeIgv', 'NombreCliente', 'FechaVencimiento', 'porcPercepcion', 'impPercepcion', 'AnioPedido', 'NumeroPedido', 'NumTarjeta', 'Comentario');
        }
        $query->orderBy('NumSerie', 'ASC')
            ->orderBy('NumeroDocumento', 'ASC');
        $query_response = $query->get();

        // Crear datos para la paginación de KT Datatable
        $meta = new \stdClass();
        $meta->page = 1;
        $meta->pages = 1;
        $meta->perpage = -1;
        $meta->total = $query->count();
        $meta->field = 'RecordID';
        for ($i = 1; $i <= $query->count(); $i++) {
            $meta->rowIds[] = $i;
        }

        // Crear objetos de los elementos del query
        $objs = [];

        foreach ($query_response as $key => $obj) {
            $voucher_type = $voucher->type;

            $payment = Payment::where('reference', $obj->CodFormaPago)
                ->select('id', 'name', 'reference')
                ->first();

            $currency = Currency::where('reference', $obj->TipoMoneda)
                ->select('id', 'symbol')
                ->first();

            if ($voucher->type == '07') {
                $reference_reason = CreditNoteReason::where('type', substr($obj->Comentario, 0, 2))
                    ->select('name', 'type')
                    ->first();
            }

            $client_query = DB::connection($company->database_name);
            if ($voucher->reference == 2) {
                $client = $client_query->select("select CodCliente, Nombre, RazonSocial, Direccion, EMail, RucCliente, DNI from MaestroCliente where CodCliente = (SELECT CASE WHEN (SELECT DNI FROM MaestroCliente WHERE CodCliente = " . $obj->CodCliente . ") = '' OR (SELECT DNI FROM MaestroCliente WHERE CodCliente = " . $obj->CodCliente . ") = NULL THEN (SELECT CodCliente FROM MaestroCliente WHERE CodCliente = 4237) ELSE " . $obj->CodCliente . " END)");
            } else {
                $client = $client_query->table('MaestroCliente')
                    ->where('CodCliente', $obj->CodCliente)
                    ->select('CodCliente', 'Nombre', 'RazonSocial', 'Direccion', 'EMail', 'RucCliente', 'DNI')
                    ->get();
            }

            $aux = new \stdClass();

            $aux->RecordID = $key + 1;
            $aux->company_id = $company_id;

            $aux->client_address = (trim($client[0]->Direccion) ? $client[0]->Direccion : 'Lima - Lima - Perú');
            $aux->client_code = $client[0]->CodCliente;
            if ($voucher->type == '03') {
                $aux->client_document_name = 'DNI';
                if (trim($client[0]->DNI) == '') {
                    $aux->client_document_number = '12345678';
                } else {
                    $aux->client_document_number = trim($client[0]->DNI);
                }
                $aux->client_name = trim($client[0]->Nombre);
            } else {
                $aux->client_document_name = 'RUC';
                $aux->client_document_number = trim($client[0]->RucCliente);
                $aux->client_name = trim($client[0]->RazonSocial);
            }
            $aux->client_email = $client[0]->EMail;


            $aux->document_currency_id = $currency->id;
            $aux->document_currency_symbol = $currency->symbol;
            $aux->document_date_of_issue = date('d-m-Y', strtotime($obj->FechaEmision));
            $aux->document_expiration_date = ($obj->FechaVencimiento ? date('d-m-Y', strtotime($obj->FechaVencimiento)) : '');
            $aux->document_hour_of_issue = date('h:i:s', strtotime($obj->FechaEmision));
            $aux->document_igv = number_format(abs($obj->IGV), 2, '.', '');
            $aux->document_igv_percentage = number_format($obj->PorcentajeIgv, 2, '.', '') * 100;
            if ($voucher->type == '07') {
                $aux->document_serie = $voucher->serie_type . sprintf('%02d', $obj->NumSerie);
            } else {
                $aux->document_serie = $voucher->serie_type . sprintf('%03d', $obj->NumSerie);
            }
            $aux->document_serie_number = $obj->NumSerie;
            $aux->document_subtotal = number_format(abs($obj->ValorNeto), 2, '.', '');
            $aux->document_order_serie = $obj->AnioPedido;
            $aux->document_order_number = $obj->NumeroPedido;
            $aux->document_payment_id = $payment->id;
            $aux->document_payment_name = $payment->name;
            if ($voucher->reference == 3 && $obj->impPercepcion > 0 && $company_id == 1) {
                if ($payment->reference != 1 && trim($client[0]->RucCliente) == '20536075195') {
                    $aux->document_perception = '';
                } else {
                    $aux->document_perception = number_format(abs($obj->impPercepcion), 2, '.', '');
                }

                if ($obj->porcPercepcion == 0) {
                    $aux->document_perception_percentage = number_format(((abs($obj->impPercepcion) * 100) / $obj->ImporteTotal) / 100, 3, '.', '');
                } else {
                    $aux->document_perception_percentage = number_format($obj->porcPercepcion, 3, '.', '');
                }
            } else {
                $aux->document_perception = '';
                $aux->document_perception_percentage = '';
            }

            if ($voucher->type == '07') {
                $document_references = explode('-', $obj->NumTarjeta);

                $aux->document_reference_number = $document_references[1];
                $aux->document_reference_reason_name = $reference_reason->name;
                $aux->document_reference_reason_type = $reference_reason->type;
                $aux->document_reference_serie = 'F' . sprintf('%03d', $document_references[0]);
            } else {
                $aux->document_reference_number = '';
                $aux->document_reference_reason_name = '';
                $aux->document_reference_reason_type = '';
                $aux->document_reference_serie = '';
            }
            $aux->document_total = number_format(abs($obj->ImporteTotal), 2);
            $aux->document_voucher_type = $voucher->type;
            $aux->document_voucher_number = $obj->NumeroDocumento;
            $aux->document_voucher_reference = $obj->TipoDocumento;

            $objs[$key] = $aux;
        }

        return response()->json([
            'meta' => $meta,
            'data' => $objs,
        ]);
    }

    public function get_voucher_detail()
    {
        $voucher_id = request('voucher_id');

        $voucher = Voucher::find($voucher_id, ['serie_number', 'voucher_number']);
        $voucher_details = VoucherDetail::where('voucher_id', $voucher_id)
            ->select('id', 'name', 'unit_price', 'quantity', 'igv', 'total')
            ->get();

        return response()->json([
            'voucher'            => $voucher,
            'voucher_details'    => $voucher_details
        ]);
    }

    public function send_voucher()
    {

        $company_id = request('company_id');
        $ids = request('ids');
        $task = request('task');
        $flag_ose = request('flag_ose');

        $response = [];

        $vouchers = Voucher::leftjoin('credit_note_reasons', 'credit_note_reasons.id', '=', 'vouchers.credit_note_reason_id')
            ->leftjoin('currencies', 'currencies.id', '=', 'vouchers.currency_id')
            ->leftjoin('payments', 'payments.id', '=', 'vouchers.payment_id')
            ->leftjoin('company_addresses', function ($join) {
                $join->on('company_addresses.company_id', '=', 'vouchers.company_id')
                    ->where('company_addresses.type', '=', 1);
            })
            ->leftjoin('companies', 'companies.id', '=', 'vouchers.company_id')
            ->leftjoin('clients', 'clients.id', '=', 'vouchers.client_id')
            ->leftjoin('document_types', 'document_types.id', '=', 'clients.document_type_id')
            ->leftjoin('warehouse_document_types', 'warehouse_document_types.id', '=', 'vouchers.warehouse_document_type_id')
            ->leftjoin('client_addresses', function ($join) {
                $join->on('client_addresses.client_id', '=', 'clients.id')
                    ->where('client_addresses.address_type_id', '=', 1);
            })
            ->leftjoin('ubigeos', 'ubigeos.id', 'client_addresses.id')
            ->leftjoin('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
            ->leftjoin('rates', 'rates.id', '=', 'clients.perception_percentage_id')
            ->whereIn('vouchers.id', $ids)
            ->select(
                'vouchers.id',
                'rates.type as rate_type',
                'rates.value as rate_value',
                'ubigeos.province as client_province',
                'ubigeos.district as client_district',
                'ubigeos.department as client_department',
                'ubigeos.ubigeo as client_ubigeo',
                'warehouse_document_types.name as warehouse_document_types_name',
                'warehouse_document_types.efact_serie as efact_serie',
                'serie_number',
                'voucher_number',
                'referral_guide_series',
                'referral_guide_number',
                'vouchers.voucher_type_id',
                'voucher_types.type as voucher_type_type',
                'voucher_types.name as voucher_type_name',
                'credit_note_reasons.id as credit_note_reason_id',
                'credit_note_reasons.type as credit_note_reason_type',
                'credit_note_reasons.name as credit_note_reason_name',
                'credit_note_reference_serie',
                'credit_note_reference_number',
                'low_number',
                'issue_date',
                DB::Raw('DATE_FORMAT(issue_date, "%Y-%m-%d") as issue_year'),
                'issue_hour',
                'expiry_date',
                'currencies.name as currency_name',
                'currencies.short_name as currency_short_name',
                'currencies.symbol as currency_symbol',
                'payments.id as payment_id',
                'payments.name as payment_name',
                'taxed_operation',
                'unaffected_operation',
                'exonerated_operation',
                'vouchers.igv',
                'total',
                'igv_perception',
                'total_perception',
                'igv_percentage',
                'igv_perception_percentage',
                'ose',
                'company_addresses.address as company_address',
                'company_addresses.district as company_district',
                'company_addresses.province as company_province',
                'company_addresses.department as company_department',
                'company_addresses.ubigeo as company_ubigeo',
                'vouchers.company_id as company_id',
                'companies.document_number as company_document_number',
                'companies.name as company_name',
                'companies.short_name as company_short_name',
                'clients.code as client_code',
                'clients.business_name as client_name',
                'clients.document_number as client_document_number',
                'clients.email as client_email',
                'clients.manager_mail as manager_mail',
                'clients.credit_limit_days as client_credit_limit_days',
                'document_types.type as client_document_type',
                'document_types.name as client_document_name',
                'client_addresses.address as client_address'
            )
            ->with(['voucher_details' => static function ($query) {
                $query->leftjoin('units', 'units.id', '=', 'voucher_details.unit_id')
                    ->leftjoin('articles', function ($join) {
                        $join->on('voucher_details.name', '=', 'articles.name')
                            ->where('articles.warehouse_type_id', 5);
                    })
                    ->select(
                        'voucher_id',
                        'voucher_details.id',
                        'unit_id',
                        'units.name as unit_name',
                        'units.short_name as unit_short_name',
                        'voucher_details.name',
                        'quantity',
                        'unit_price',
                        'sale_value',
                        'exonerated_value',
                        'inaccurate_value',
                        'voucher_details.igv',
                        DB::Raw('ROUND(total - voucher_details.igv, 2) as subtotal'),
                        'total'
                    )
                    ->when('articles.igv = 1', function ($query) {
                        return $query->leftjoin('rates', function ($join) {
                            $join->on('rates.description', '=', DB::Raw("'IGV'"))
                                ->where('state', 1);
                        })
                            ->addSelect(DB::Raw('rates.value as igv_percentage'));
                    });
            }])
            ->where('vouchers.ose', $flag_ose)
            ->get();

        foreach ($vouchers as $voucher) {
            $voucher->ose = 1;
            $voucher->update();
        }

        $vouchers->map(function ($item, $key) use ($task, &$response, &$routes, &$mail_info, &$low_date, &$low_number) {
            $formatter = new NumeroALetras();
            $item->total_text = $formatter->toInvoice($item->total, 2,  $item->currency_name);
            $item->payment_due_date = CarbonImmutable::createFromDate(request($item->issue_date))->addDays($item->client_credit_limite_days)->format('Y-m-d');

            if ($item->voucher_type_id == 3 && $item->efact_serie == '03') {
                $item->serie_number = 'BA90';
            }

            // Datos de Rutas
            $nombre_xml = $item->company_document_number . '-' . $item->voucher_type_type . '-' . $item->serie_number . '-' . $item->voucher_number;
            $nombre_ruta = 'uploads/' . $item->company_short_name . '/' . $item->issue_year;
            $item->nombre_xml = $nombre_xml;
            $item->nombre_ruta = $nombre_ruta;
            $item->nombre_ruta_xml = $nombre_ruta . '/xml/' . $nombre_xml . '.xml';
            $item->nombre_ruta_pdf = $nombre_ruta . '/pdf/' . $nombre_xml . '.pdf';

            $item->low_date = $low_date;
            $item->low_number = $low_number;

            if ($task == 'xml') {
                $xml_render = $this->xml_render($item);
                $response[] = $xml_render;

                $res = $this->billingClientPunto->sendDocumentXML(base_path('html/' . $item->nombre_ruta_xml));

                if ($res !== null) {

                    $responseXml = $this->billingClientPunto->getXmlFromTicket($res['description']);

                    /*
                    if ($item->client_email != null) {
                        Mail::to($item->client_email)->queue(new VoucherMailOficial($item));
                    }
                    if ($item->manager_mail != null) {
                        Mail::to($item->manager_mail)->queue(new VoucherMailOficial($item));
                    }
                        */
                    //Mail::to('juan.olivas@puntodedistribucion.com')->queue(new VoucherMailOficial($item));
                    //Mail::to('desarrollopdd@puntodedistribucion.com')->queue(new VoucherMailOficial($item));
                }
            }
        });

        return $response;
    }

    public function xml_render(object $obj)
    {

        if ($obj->voucher_type_type == '01') {
            $textoXML = view('backend.xml.factura_gravada_ofi', compact('obj'))->render();
        } else if ($obj->voucher_type_type == '03') {
            $textoXML = view('backend.xml.boleta_efact', compact('obj'))->render();
        } else if ($obj->voucher_type_type == '07') {
            $textoXML = view('backend.xml.nota_credito_efact', compact('obj'))->render();
        } else if ($obj->voucher_type_type == '09') {
            $textoXML = view('backend.xml.guia_remision_efact', compact('obj'))->render();
        } else if ($obj->voucher_type_type == '40') {
            $textoXML = view('backend.xml.comprobante_percepcion_efact', compact('obj'))->render();
        }

        $textoXML = mb_convert_encoding($textoXML, "UTF-8");
        $generate_xml = Storage::disk('public')->put($obj->nombre_ruta_xml, $textoXML);


        $document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate('| ' . $obj->company_document_number . ' | ' . $obj->voucher_type_type . ' | ' . $obj->serie_number . '-' . $obj->voucher_number . ' | ' . $obj->igv . ' | ' . $obj->total . ' | ' . $obj->issue_date . ' | ' . $obj->client_document_type . ' | ' . $obj->client_document_number));
        $pdf = PDF::loadView('backend.pdf_view_oficial', compact('obj', 'document_qrcode'));
        $contenido = $pdf->download();
        Storage::disk('public')->put($obj->nombre_ruta_pdf, $contenido);

        return $textoXML;
    }
}
