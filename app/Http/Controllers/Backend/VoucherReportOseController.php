<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Auth;
use DB;
use App\ClientAddress;
use App\Client;
use App\Company;
use App\CompanyAddress;
use App\Voucher;
use App\VoucherDetail;
use App\VoucherType;
use App\Mail\VoucherMail;
use NumeroALetras\NumeroALetras;
use QrCode;
use SimpleXMLElement;
use SoapClient;
use SoapFault;
use SoapHeader;
use SoapVar;

class VoucherReportOseController extends Controller
{
    private $env = 'local';

    public function index() {
        $companies = Company::select('id','name')->get();
    	$voucher_types = VoucherType::select('id','name')->get();
        $current_date = date(DATE_ATOM, mktime(0, 0, 0));
        $user_name = Auth::user()->user;
        return view('backend.voucher_report_ose')->with(compact('companies','voucher_types','current_date','user_name'));
    }

    public function validateForm() {
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

    public function getVouchersTable() {
        // Obtener datos del formulario
        $company_id = request('model.company_id');
        $voucher_type_id = request('model.voucher_type');
        $serie = request('model.serie');
        $since_date = ( request('model.since_date') ? date_format(date_create(request('model.since_date')), 'Y-m-d') : '' );
        $to_date = ( request('model.to_date') ? date_format(date_create(request('model.to_date')), 'Y-m-d') : '' );
        $initial_number = request('model.initial_number');
        $final_number = request('model.final_number');
        $order_series = request('model.order_series');
        $order_number = request('model.order_number');
        
        // $voucher_type = VoucherType::where('id', $voucher_id)
        //     ->select('id', 'name', 'serie_type', 'type', 'reference')
        //     ->first();

        // if ( $voucher_type->type == '07' ) {
        //     $serie = ( $serie ? $voucher_type->serie_type . sprintf('%02d', $serie) : '' );
        // } else {
        //     $serie = ( $serie ? $voucher_type->serie_type . sprintf('%03d', $serie) : '' );
        // }
        
        // $query = Voucher::where('company_id', $company_id)
        //     ->where('voucher_type_id', $voucher_id)
        //     ->when($serie, function($query, $serie) {
        //         return $query->where('serie_number', $serie);
        //     })
        //     ->when($since_date, function($query, $since_date) {
        //         return $query->where('issue_date', '>=', $since_date);
        //     })
        //     ->when($to_date, function($query, $to_date) {
        //         return $query->where('issue_date', '<=', $to_date);
        //     })
        //     ->when($initial_number, function($query, $initial_number) {
        //         return $query->where('voucher_number', '>=', $initial_number);
        //     })
        //     ->when($final_number, function($query, $final_number) {
        //         return $query->where('voucher_number', '<=', $final_number);
        //     })
        //     ->when($order_series, function($query, $order_series) {
        //         return $query->where('order_series', $order_series);
        //     })
        //     ->when($order_number, function($query, $order_number) {
        //         return $query->where('order_number', $order_number);
        //     })
        //     ->orderBy('serie_number', 'DESC')
        //     ->orderBy('voucher_number', 'DESC');
        
        // $vouchers = $query->get();

		$voucher_type = VoucherType::find($voucher_type_id, ['id', 'serie_type', 'type']);

		if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
			$serie_number = $voucher_type->serie_type . sprintf('%02d', $serie);
		} else {
			$serie_number = $voucher_type->serie_type . sprintf('%03d', $serie);
		}

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
			->when($since_date, function($query, $since_date) {
                return $query->where('issue_date', '>=', $since_date);
            })
            ->when($to_date, function($query, $to_date) {
                return $query->where('issue_date', '<=', $to_date);
            })
			->when($initial_number, function ($query) use ($initial_number) {
                return $query->where('voucher_number', '>=', $initial_number);
            })
            ->when($final_number, function ($query) use ($final_number) {
                return $query->where('voucher_number', '<=', $final_number);
            })
			->when($voucher_type->reference == 2, function ($query) use ($order_series, $order_number) {
				return $query->when($order_series, function($query) use ($order_series) {
						return $query->where('order_series', $order_series);
					})
					->when($order_number, function($query) use ($order_number) {
						return $query->where('order_number', $order_number);
					});
					// ->where('issue_date', $date_of_issue);
			})
			->where('ose', 1)
			->select('vouchers.id', 'voucher_types.type', 'serie_number', 'voucher_number', DB::Raw('DATE_FORMAT(issue_date, "%d-%m-%Y") as issue_date_formated'), 'payments.name as payment_name', 'order_series', 'order_number', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number as client_document_number', 'client_name', 'currencies.symbol as currency_symbol', 'total', 'igv_perception')
			->orderBy('serie_number', 'ASC')
            ->orderBy('voucher_number', 'ASC')
			->get();

        // Crear datos para la paginación de KT Datatable
        // $meta = new \stdClass();
        // $meta->page = 1;
        // $meta->pages = 1;
        // $meta->perpage = -1;
        // $meta->total = $vouchers->count();
        // $meta->field = 'voucher_id';
        // for ($i = 1; $i <= $vouchers->count() ; $i++) {
        //     $meta->rowIds[] = $i;
        // }

        // // Crear objetos de los elementos del query
        // $objs = [];

        // foreach ($vouchers as $key => $obj) {
        //     $aux = new \stdClass();

        //     $aux->voucher_id = $obj->id;
        //     $aux->company_id = $company_id;

        //     $aux->client_address = (trim($obj->client_address) ? $obj->client_address : 'Lima - Lima - Perú');
        //     $aux->client_code = $obj->client->code;
        //     $aux->client_document_name = $obj->client->document_type->name;
        //     if ( $voucher_type->type == '03' && $obj->client->document_number == '' ) {
        //         $aux->client_document_number = '12345678';
        //     } else {
        //         $aux->client_document_number = $obj->client->document_number;
        //     }
        //     $aux->client_name = trim($obj->client->business_name);
        //     $aux->client_email = $obj->client->email;

        //     $aux->document_currency_id = $obj->currency->id;
        //     $aux->document_currency_symbol = $obj->currency->symbol;
        //     $aux->document_date_of_issue = date('d-m-Y', strtotime($obj->issue_date));
        //     $aux->document_expiration_date = ( $obj->expiry_date ? date('d-m-Y', strtotime($obj->expiry_date)) : '' );
        //     $aux->document_hour_of_issue = date('h:i:s', strtotime($obj->issue_hour));
        //     $aux->document_igv = abs($obj->igv);
        //     $aux->document_igv_percentage = $obj->igv_percentage * 100;
        //     $aux->document_serie_number = $obj->serie_number;
        //     $aux->document_subtotal = $obj->taxed_operation;
        //     $aux->document_order_series = $obj->order_series;
        //     $aux->document_order_number = $obj->order_number;
        //     $aux->document_payment_id = $obj->payment_id;
        //     $aux->document_payment_name = $obj->payment->name;
        //     if ( $voucher_type->type == '01' && $obj->igv_perception > 0 && $company_id == 1 ) {
        //         if ( $obj->payment_id != 1 && $obj->client->document_number == '20536075195' ) {
        //             $aux->document_perception = '';
        //         } else {
        //             $aux->document_perception = $obj->igv_perception;
        //         }
                
        //         $aux->document_perception_percentage = $obj->igv_perception_percentage;
        //     } else {
        //         $aux->document_perception = '';
        //         $aux->document_perception_percentage = '';
        //     }

        //     if ( $voucher_type->type == '07' ) {
        //         $aux->document_reference_number = $obj->credit_note_reference_number;
        //         $aux->document_reference_reason_name = $obj->credit_note_reason->name;
        //         $aux->document_reference_reason_type = $obj->credit_note_reason->type;
        //         $aux->document_reference_serie = $obj->credit_note_reference_serie;
        //     } else {
        //         $aux->document_reference_number = '';
        //         $aux->document_reference_reason_name = '';
        //         $aux->document_reference_reason_type = '';
        //         $aux->document_reference_serie = '';
        //     }
        //     $aux->document_total = $obj->total;
        //     $aux->document_voucher_type = $voucher_type->type;
        //     $aux->document_voucher_number = $obj->voucher_number;

        //     $objs[$key] = $aux;
        // }

        // return response()->json([
        //     'meta' => $meta,
        //     'data' => $objs,
        // ]);

		return $vouchers;
    }

    public function getVoucherDetail() {
        $objs = [];
        $element_details = [];

        $voucher_id = request('voucher_id');
        $details = VoucherDetail::where('voucher_id', $voucher_id)->get();

        foreach ($details as $detail) {
            $detail_igv = $detail->igv;
            $detail_price = $detail->unit_price;
            $detail_price_igv = $detail->sale_value;
            $detail_subtotal = $detail->unit_price * $detail->quantity;

            $aux = new \stdClass();
            $aux->detail_name = $detail->name;
            $aux->detail_igv = number_format($detail_igv, 2);
            $aux->detail_price = $detail_price;
            $aux->detail_price_igv = $detail_price_igv;
            $aux->detail_quantity = number_format($detail->quantity, 4);
            $aux->detail_subtotal = number_format($detail_subtotal, 2);
            $aux->detail_total = number_format($detail->total, 2);
            $aux->detail_unit_name = ( $detail->unit_id ? $detail->unit->name : '' );
            $element_details[] = $aux;
        }

        $voucher = Voucher::find($voucher_id);

        $main_aux = new \stdClass();
        $main_aux->title = $voucher['serie_number'].'-'.$voucher['voucher_number'];
        $main_aux->element_details = $element_details;

        $objs[] = $main_aux;

        return $objs;
    }

    public function sendVoucher() {
        $main_obj = [];
        $response = [];

        $elements = request('elements');
        $task = request('task');

        foreach ($elements as $element) {
            $query = VoucherDetail::where('voucher_id', $element['voucher_id'])->get();

            $objs = [];

            foreach ($query as $key => $obj) {
                $aux = new \stdClass();
                $aux->detail_name = $obj->name;
                $aux->detail_igv = $obj->igv;
                $aux->detail_igv_percentage = $obj->voucher->igv_percentage;
                $aux->detail_price = $obj->unit_price;
                $aux->detail_price_igv = $obj->sale_value;
                $aux->detail_quantity = $obj->quantity;
                $aux->detail_subtotal = $obj->unit_price * $obj->quantity;
                $aux->detail_total = $obj->total;
                $aux->detail_unit_id = ( $obj->unit_id ? $obj->unit->id : '' );
                $aux->detail_unit_name = ( $obj->unit_id ? $obj->unit->name : '' );
                $aux->detail_unit_short_name = ( $obj->unit_id ? $obj->unit->short_name : '' );
                $objs[] = $aux;
            }

            $voucher = Voucher::where('id', $element['voucher_id'])->first();
            $company_address = CompanyAddress::where('id', $element['company_id'])->first();
            if ( $task == 'baja' ) {
                $low_date = date('Ymd');
                $low_number = Voucher::where('company_id', $voucher->company->id)->max('low_number');
                $low_number = ( $low_number ? $low_number + 1 : 1 );
            } else {
                $low_date = '';
                $low_number = '';
            }

            $main_aux = new \stdClass();

            // Datos de la Compañia
            if ( $this->env == 'local' ) {
                $bizlinks_user = $voucher->company->bizlinks_user_test;
                $bizlinks_password = $voucher->company->bizlinks_password_test;
            } elseif ( $this->env == 'production' ) {
                $bizlinks_user = $voucher->company->bizlinks_user;
                $bizlinks_password = $voucher->company->bizlinks_password;
            }
            $main_aux->company_address = $company_address->address;
            $main_aux->company_bizlinks_password = $bizlinks_password;
            $main_aux->company_bizlinks_user = $bizlinks_user;
            $main_aux->company_certificate_pem = $voucher->company->certificate_pem;
            $main_aux->company_database_name = $voucher->company->database_name;
            $main_aux->company_department = $company_address->department;
            $main_aux->company_document_number = $voucher->company->document_number;
            $main_aux->company_district = $company_address->district;
            $main_aux->company_id = $voucher->company->id;
            $main_aux->company_name = $voucher->company->name;
            $main_aux->company_province = $company_address->province;
            $main_aux->company_short_name = $voucher->company->short_name;
            $main_aux->company_ubigeo = $company_address->ubigeo;

            // Datos del CLiente
            $main_aux->client_address = $voucher->client_address;
            $main_aux->client_code = $voucher->client->code;
            $main_aux->client_document_name = $voucher->client->business_name;
            $main_aux->client_document_number = $voucher->client->document_number;
            $main_aux->client_document_type = $voucher->client->document_type->type;
            $main_aux->client_email = $voucher->client->email;
            $main_aux->client_name = $voucher->client_name;

            // Datos de Cabecera
            $main_aux->document_currency_id = $voucher->currency->id;
            $main_aux->document_currency_name = $voucher->currency->name;
            $main_aux->document_currency_short_name = $voucher->currency->short_name;
            $main_aux->document_currency_symbol = $voucher->currency->symbol;
            $main_aux->document_date_of_issue = $voucher->issue_date;
            $main_aux->document_expiration_date = $voucher->expiry_date;
            $main_aux->document_hour_of_issue = $voucher->issue_hour;
            $main_aux->document_igv = $voucher->igv;
            $main_aux->document_igv_percentage = $voucher->igv_percentage;
            $main_aux->document_low_date = $low_date;
            $main_aux->document_low_number = $low_number;
            $main_aux->document_order_number = $voucher->order_number;
            $main_aux->document_order_series = $voucher->order_series;
            $main_aux->document_payment_id = $voucher->payment_id;
            $main_aux->document_payment_name = $voucher->payment->name;
            $main_aux->document_perception = $voucher->igv_perception;
            $main_aux->document_perception_percentage = $voucher->igv_perception_percentage;
            $main_aux->document_reference_number = ( $voucher->credit_note_reason_id ? $voucher->credit_note_reference_number : '' );
            $main_aux->document_reference_reason_name = ( $voucher->credit_note_reason_id ? $voucher->credit_note_reason->name : '' );
            $main_aux->document_reference_reason_type = ( $voucher->credit_note_reason_id ? $voucher->credit_note_reason->type : '' );
            $main_aux->document_reference_serie = ( $voucher->credit_note_reason_id ? $voucher->credit_note_reference_serie : '' );
            $main_aux->document_serie = $voucher->serie_number;
            $main_aux->document_subtotal = $voucher->taxed_operation;
            $main_aux->document_total = $voucher->total;
            $main_aux->document_total_text = NumeroALetras::convertir(str_replace(',', '', $voucher->total), 'soles');
            $main_aux->document_voucher_id = $voucher->voucher_type->id;
            $main_aux->document_voucher_name = ( $low_number != '' ? 'Comunicación de Baja' : $voucher->voucher_type->name );
            $main_aux->document_voucher_number = $voucher->voucher_number;
            $main_aux->document_voucher_serie_type = $voucher->voucher_type->serie_type;
            $main_aux->document_voucher_type = $voucher->voucher_type->type;
            $main_aux->document_year = date('Y', strtotime($voucher->issue_date));
            $main_aux->voucher_id = $voucher->id;

            // Datos del Detalle
            $main_aux->details = $objs;

            // Datos de Rutas
			$nombre_ruta = 'uploads/' . $voucher->company->short_name . '/'. date('Y', strtotime($voucher->issue_date));
            if ( $task == 'baja' ) {
                $nombre_xml = $voucher->company->document_number . '-RA-' . $low_date . '-' . $low_number;
            } else {
                $nombre_xml = $voucher->company->document_number . '-' . $voucher->voucher_type->type . '-' . $voucher->serie_number . '-' . $voucher->voucher_number;
            }
            $main_aux->nombre_xml = $nombre_xml;
			$main_aux->nombre_ruta = $nombre_ruta;
			$main_aux->nombre_ruta_xml = $nombre_ruta .'/xml/' . $nombre_xml . '.xml';
			$main_aux->nombre_ruta_firma = $nombre_ruta .'/firma/' . $nombre_xml . '.xml';
			$main_aux->nombre_ruta_zip = $nombre_ruta .'/firma/' . $nombre_xml . '.zip';
			$main_aux->nombre_ruta_rspta = $nombre_ruta .'/rpta/R-' . $nombre_xml . '.zip';
			$main_aux->nombre_ruta_pdf = $nombre_ruta .'/pdf/' . $nombre_xml . '.pdf';

            $main_obj[] = $main_aux;
            
            if ( $task == 'pdf' ) {
                $create_pdf = $this->create_pdf($main_aux);

                $response[] = $create_pdf;
            } elseif ( $task == 'download_pdf' ) {
                $download_pdf = $this->download_pdf($main_aux);

                $response[] = $download_pdf;
            } elseif ( $task == 'mail' ) {
                $send_mail = $this->send_mail($main_aux);

                $response[] = $send_mail;
            } elseif ( $task == 'save' ) {
                $save_voucher = $this->save_voucher($main_aux);

                $response[] = $save_voucher;
            } else {
                $xml_render = $this->xml_render($main_aux, $low_number);
                $xml_signed = $this->xml_signed($main_aux, $xml_render);
                $create_zip = $this->create_zip($main_aux);
                if ( $task == 'baja' ) {
                    $low_voucher = $this->low_voucher($main_aux);
                    $get_status = $this->get_status($main_aux, $low_voucher);
                    $send_mail = $this->send_mail($main_aux);
                
                    $response[] = $get_status;
                } else {
                    $ws_bizlinks = $this->ws_bizlinks($main_aux, $low_voucher);
                    if ( $ws_bizlinks->response_code == 0 && $voucher->voucher_type->type != '03' ) {
                        $save_voucher = $this->save_voucher($main_aux);
                        $create_pdf = $this->create_pdf($main_aux);
                        $send_mail = $this->send_mail($main_aux);
                    }

                    $response[] = $ws_bizlinks;
                }
            }
        }

        return $response;
    }

    public function xml_render(object $obj, $low_number) {
        if ( $low_number != '' ) {
            $textoXML = view('backend.xml.comunicacion_baja', compact('obj'))->render();
        } else if ( $obj->document_voucher_type == '01' ) {
            $textoXML = view('backend.xml.factura_gravada', compact('obj'))->render();
        } else if ( $obj->document_voucher_type == '03' ) {
            $textoXML = view('backend.xml.boleta', compact('obj'))->render();
        } else if ( $obj->document_voucher_type == '07' ) {
            $textoXML = view('backend.xml.nota_credito', compact('obj'))->render();
        }

        $textoXML = mb_convert_encoding($textoXML, "UTF-8", "UTF-8");

        /*
        * Crear el XML sin firma
        */
        $generate_xml = Storage::disk('public')->put($obj->nombre_ruta_xml, $textoXML);

        return $generate_xml;
    }

    public function xml_signed(object $obj, bool $xml) {
        /*
         * Firmar XML
         */
        $xmlPath = $obj->nombre_ruta . '/xml/' . $obj->nombre_xml . '.xml';
        $certPath = base_path($obj->company_certificate_pem);

        $signer = new SignedXml();
        $signer->setCertificateFromFile($certPath);
        $textoXMLSigned = $signer->signFromFile($xmlPath);

        $sign_xml = Storage::disk('public')->put($obj->nombre_ruta_firma, $textoXMLSigned);
    }

    public function create_zip(object $obj) {
        $xml = $obj->nombre_xml.'.xml';

        /*
         * Crear ZIP con XML insertado
         */
        $zipArchive = new \ZipArchive;
        if ($zipArchive->open($obj->nombre_ruta_zip, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) === TRUE) {
            $zipResponse = $zipArchive->addFile($obj->nombre_ruta_firma, $xml);
            $zipArchive->close();
        }
        return response()->json($zipResponse);
    }

    public function low_voucher(object $obj) {
        /*
         * Convertir en Base64 el archivo .ZIP
         */
        $fd = fopen($obj->nombre_ruta_zip, 'rb');
        $size = filesize($obj->nombre_ruta_zip);
        $cont = fread($fd, $size);
        fclose($fd);
        $enc = base64_encode($cont);

        $zip = $obj->nombre_xml.'.zip';

        /*
         * Consumo de Webservice de Bizlinks
         */
        $header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
            $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
            $header .= '<wsse:Username>'.$obj->company_bizlinks_user.'</wsse:Username>';
            $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$obj->company_bizlinks_password.'</wsse:Password>';
            $header .= '</wsse:UsernameToken>';
        $header .= '</wsse:Security>';

        $body = '<ns2:sendSummary xmlns:ns2="http://service.sunat.gob.pe">';
        $body .= '<fileName>'.$zip.'</fileName>';
        $body .= '<contentFile>'.$enc.'</contentFile>';
        $body .= '</ns2:sendSummary>';

        $header_block = new SoapVar( $header, XSD_ANYXML, NULL, NULL, NULL, NULL );
        $body_block = new SoapVar( $body, XSD_ANYXML, NULL, NULL, NULL, NULL );

        $header = new SoapHeader( 'http://schemas.xmlsoap.org/soap/envelope/', 'Header', $header_block );

        if ( $this->env == 'local' ) {
            $webservice = 'https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        } elseif ( $this->env == 'production' ) {
            $webservice = 'https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        }
            // https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
            // https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
        $client = new SoapClient(
            $webservice,
            array(
                'trace' => 1,
                'use'	=> SOAP_LITERAL,
                'style'	=> SOAP_DOCUMENT,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'user_agent' => 'PDD',
                'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]),
            )
        );
        $client->__setSoapHeaders( $header );

        try {
            $client->sendSummary($body_block);
            $client_response = $client->__getLastResponse();
            $client_response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $client_response);
            $xml = new SimpleXMLElement($client_response);
            $body = $xml->xpath('//soapBody')[0];
            $array = json_decode(json_encode((array)$body), TRUE);
            $xmlCdr = $array['ns2sendSummaryResponse']['ticket'];

            /*
             * Crear el Zip de Respuesta
             */
            // Storage::disk('public')->put($obj->nombre_ruta_rspta, $xmlCdr);

            $voucher = Voucher::find($obj->voucher_id);
            $voucher->low_number = $obj->document_low_number;
            $voucher->low_ticket = $xmlCdr;
            $voucher->save();

            // $response_obj = new \stdClass();
            // $response_obj->document_serie = $obj->document_serie;
            // $response_obj->document_voucher_number = $obj->document_voucher_number;
            // $response_obj->response_code = 0;
            // $response_obj->response_text = $obj->document_voucher_name.' enviada correctamente';

            return $xmlCdr;

        } catch (SoapFault $e) {
            return $e;
        }
    }

    public function get_status(object $obj, string $ticket) {
        /*
         * Consumo de Webservice de Bizlinks
         */
        $header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
            $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
            $header .= '<wsse:Username>'.$obj->company_bizlinks_user.'</wsse:Username>';
            $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$obj->company_bizlinks_password.'</wsse:Password>';
            $header .= '</wsse:UsernameToken>';
        $header .= '</wsse:Security>';

        $body = '<ns2:getStatus xmlns:ns2="http://service.sunat.gob.pe">';
        $body .= '<ticket>'.$ticket.'</ticket>';
        $body .= '</ns2:getStatus>';

        $header_block = new SoapVar( $header, XSD_ANYXML, NULL, NULL, NULL, NULL );
        $body_block = new SoapVar( $body, XSD_ANYXML, NULL, NULL, NULL, NULL );

        $header = new SoapHeader( 'http://schemas.xmlsoap.org/soap/envelope/', 'Header', $header_block );

        if ( $this->env == 'local' ) {
            $webservice = 'https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        } elseif ( $this->env == 'production' ) {
            $webservice = 'https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        }
            // https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
            // https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
        $client = new SoapClient(
            $webservice,
            array(
                'trace' => 1,
                'use'	=> SOAP_LITERAL,
                'style'	=> SOAP_DOCUMENT,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'user_agent' => 'PDD',
                'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]),
            )
        );
        $client->__setSoapHeaders( $header );

        try {
            $client->getStatus($body_block);
            $client_response = $client->__getLastResponse();
            $client_response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $client_response);
            $xml = new SimpleXMLElement($client_response);
            $body = $xml->xpath('//soapBody')[0];
            $array = json_decode(json_encode((array)$body), TRUE);
            $xmlCdr = base64_decode($array['ns2getStatusResponse']['status']['content']);
            $statusCode = $array['ns2getStatusResponse']['status']['statusCode'];

            /*
             * Crear el Zip de Respuesta
             */
            Storage::disk('public')->put($obj->nombre_ruta_rspta, $xmlCdr);

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->document_serie;
            $response_obj->document_voucher_number = $obj->document_voucher_number;
            $response_obj->response_code = $statusCode;
            $response_obj->response_text = 'Comunicación de Baja enviada correctamente';

            return $response_obj;

        } catch (SoapFault $e) {
            if ( $e->faultstring >= 2000 && $e->faultstring <= 2099 && $this->env == 'production' ) {
                $query = DB::connection($obj->company_database_name)
                    ->table('DetalleFacturacionMarket')
                    ->where('TipoDocumento', $obj->document_voucher_reference)
                    ->where('NumSerie', $obj->document_serie_number)
                    ->where('NumeroDocumento', $obj->document_voucher_number)
                    ->where('Estado', 1)
                    ->update(['flagOse' => $e->faultstring]);
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->document_serie;
            $response_obj->document_voucher_number = $obj->document_voucher_number;
            $response_obj->response_code = $e->faultstring;
            $response_obj->response_text = $e;

            return $response_obj;
        }
    }

    public function ws_bizlinks(object $obj) {
        /*
         * Convertir en Base64 el archivo .ZIP
         */
        $fd = fopen($obj->nombre_ruta_zip, 'rb');
        $size = filesize($obj->nombre_ruta_zip);
        $cont = fread($fd, $size);
        fclose($fd);
        $enc = base64_encode($cont);

        $zip = $obj->nombre_xml.'.zip';

        /*
         * Consumo de Webservice de Bizlinks
         */
        $header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
            $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
            $header .= '<wsse:Username>'.$obj->company_bizlinks_user.'</wsse:Username>';
            $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$obj->company_bizlinks_password.'</wsse:Password>';
            $header .= '</wsse:UsernameToken>';
        $header .= '</wsse:Security>';

        $body = '<ns2:sendBill xmlns:ns2="http://service.sunat.gob.pe">';
        $body .= '<fileName>'.$zip.'</fileName>';
        $body .= '<contentFile>'.$enc.'</contentFile>';
        $body .= '</ns2:sendBill>';

        $header_block = new SoapVar( $header, XSD_ANYXML, NULL, NULL, NULL, NULL );
        $body_block = new SoapVar( $body, XSD_ANYXML, NULL, NULL, NULL, NULL );

        $header = new SoapHeader( 'http://schemas.xmlsoap.org/soap/envelope/', 'Header', $header_block );

        if ( $this->env == 'local' ) {
            $webservice = 'https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        } elseif ( $this->env == 'production' ) {
            $webservice = 'https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        }
            // https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
            // https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl
        $client = new SoapClient(
            $webservice,
            array(
                'trace' => 1,
                'use'	=> SOAP_LITERAL,
                'style'	=> SOAP_DOCUMENT,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'user_agent' => 'PDD',
                'stream_context' => stream_context_create([
                        'ssl' => [
                            'verify_peer' => false,
                            'verify_peer_name' => false,
                        ],
                    ]),
            )
        );
        $client->__setSoapHeaders( $header );

        try {
            $client->sendBill($body_block);
            $client_response = $client->__getLastResponse();
            $client_response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $client_response);
            $xml = new SimpleXMLElement($client_response);
            $body = $xml->xpath('//soapBody')[0];
            $array = json_decode(json_encode((array)$body), TRUE);
            $xmlCdr = base64_decode($array['ns2sendBillResponse']['applicationResponse']);

            /*
             * Crear el Zip de Respuesta
             */
            Storage::disk('public')->put($obj->nombre_ruta_rspta, $xmlCdr);

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->document_serie;
            $response_obj->document_voucher_number = $obj->document_voucher_number;
            $response_obj->response_code = 0;
            $response_obj->response_text = $obj->document_voucher_name.' enviada correctamente';

            return $response_obj;

        } catch (SoapFault $e) {
            if ( $e->faultstring >= 2000 && $e->faultstring <= 2099 && $this->env == 'production' ) {
                $query = DB::connection($obj->company_database_name)
                    ->table('DetalleFacturacionMarket')
                    ->where('TipoDocumento', $obj->document_voucher_reference)
                    ->where('NumSerie', $obj->document_serie_number)
                    ->where('NumeroDocumento', $obj->document_voucher_number)
                    ->where('Estado', 1)
                    ->update(['flagOse' => $e->faultstring]);
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->document_serie;
            $response_obj->document_voucher_number = $obj->document_voucher_number;
            $response_obj->response_code = $e->faultstring;
            $response_obj->response_text = $e->detail->message;

            return $response_obj;
        }
    }

    public function create_pdf(object $obj) {
        $xmlfile = Storage::disk('public')->get($obj->nombre_ruta_firma);
        $xml_content = str_replace('ext:', '', $xmlfile);
        $xml_content = str_replace('ds:', '', $xml_content);
        $xml_content = str_replace('cbc:', '', $xml_content);
        $xml_obj = simplexml_load_string($xml_content);

        $document_hash = (string)$xml_obj->UBLExtensions->UBLExtension->ExtensionContent->Signature->SignedInfo->Reference->DigestValue;
        $document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate('| '.$obj->company_document_number.' | '.$obj->document_voucher_type.' | '.$obj->document_serie.'-'.$obj->document_voucher_number.' | '.$obj->document_igv.' | '.number_format($obj->document_total, 2).' | '.$obj->document_date_of_issue.' | '.$obj->client_document_type.' | '.$obj->client_document_number));

        $data = [
            'company_address'					=> $obj->company_address,
            'company_department'				=> $obj->company_department,
            'company_district'					=> $obj->company_district,
            'company_document_number'			=> $obj->company_document_number,
            'company_id'		            	=> $obj->company_id,
            'company_name'						=> $obj->company_name,
            'company_province'					=> $obj->company_province,
            'client_address'					=> $obj->client_address,
            'client_document_name'				=> $obj->client_document_name,
            'client_document_number'			=> $obj->client_document_number,
            'client_email'						=> $obj->client_email,
            'client_name'						=> $obj->client_name,
            'document_currency_name'			=> $obj->document_currency_name,
            'document_currency_symbol'			=> $obj->document_currency_symbol,
            'document_date_of_issue'			=> $obj->document_date_of_issue,
            'document_expiration_date'			=> ($obj->document_expiration_date ? $obj->document_expiration_date : '-'),
            'document_hash'						=> $document_hash,
            'document_igv'						=> number_format($obj->document_igv, 2),
            'document_payment_name'				=> $obj->document_payment_name,
            'document_perception'				=> number_format($obj->document_perception, 2),
            'document_perception_percentage'	=> $obj->document_perception_percentage * 100,
            'document_qrcode'					=> $document_qrcode,
            'document_reference_number'			=> $obj->document_reference_number,
            'document_reference_reason_name'	=> $obj->document_reference_reason_name,
            'document_reference_reason_type'    => $obj->document_reference_reason_type,
            'document_reference_serie'			=> $obj->document_reference_serie,
            'document_subtotal'					=> number_format($obj->document_subtotal, 2),
            'document_total'					=> number_format($obj->document_total, 2),
            'document_total_text'				=> $obj->document_total_text,
            'document_total_perception'			=> number_format($obj->document_total + $obj->document_perception, 2),
            'document_serie'					=> $obj->document_serie,
            'document_voucher_name'				=> $obj->document_voucher_name,
            'document_voucher_number'			=> $obj->document_voucher_number,
            'document_voucher_type'			    => $obj->document_voucher_type,
            'details'							=> $obj->details,
        ];

        $pdf = PDF::loadView('backend.pdf_view', compact('data'));
        if ( !file_exists($obj->nombre_ruta .'/pdf') ) {
            mkdir($obj->nombre_ruta .'/pdf/', 0777, true);
        }
        $pdf->save($obj->nombre_ruta_pdf);

        return response()->json(true);
    }

    public function download_pdf(object $obj) {
        if ( $obj->document_voucher_type == '03' ) {
            $create_pdf = $this->create_pdf($obj);
        }
        $zip_name = $obj->nombre_xml.'.zip';
        $xml = $obj->nombre_xml.'.xml';
        $pdf = $obj->nombre_xml.'.pdf';
        $rspta = 'R-'.$obj->nombre_xml.'.zip';

        /*
         * Crear ZIP con XML insertado
         */
        $zipArchive = new \ZipArchive;
        if ($zipArchive->open($zip_name, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) === TRUE) {
            $zipArchive->addFile($obj->nombre_ruta_firma, $xml);
            $zipArchive->addFile($obj->nombre_ruta_pdf, $pdf);
            $zipArchive->addFile($obj->nombre_ruta_rspta, $rspta);
            $zipArchive->close();
        }

        $zipFile = Storage::disk('public')->url($zip_name);

        $response_obj = new \stdClass();
        $response_obj->name = $zip_name;
        $response_obj->file = $zipFile;
        $response_obj->document_serie = $obj->document_serie;
        $response_obj->document_voucher_number = $obj->document_voucher_number;
        $response_obj->response_code = 0;
        $response_obj->response_text = $obj->document_voucher_name.' descargada correctamente';

        return $response_obj;
    }

    public function send_mail(object $obj, $routes, $mail_info) {
        if ( $this->env == 'local' ) {
            Mail::to('gabriel@codea.pe')->queue(new VoucherMail($obj, $routes, $mail_info));
        } elseif ( $this->env == 'production' ) {
            if ( $obj->document_voucher_type != '03' && $obj->client_email ) {
                Mail::to($obj->client_email)->cc('enviofacturacion@puntodedistribucion.com')->queue(new VoucherMail($obj, $routes, $mail_info));
            } else {
                Mail::to('enviofacturacion@puntodedistribucion.com')->queue(new VoucherMail($obj, $routes, $mail_info));
            }
        }

        $response_obj = new \stdClass();
        $response_obj->document_serie = $obj->document_serie;
        $response_obj->document_voucher_number = $obj->document_voucher_number;
        $response_obj->response_code = 0;
        $response_obj->response_text = $obj->document_voucher_name.' ha sido enviada por email';

        return $response_obj;
    }

    public function save_voucher(object $obj) {
        $voucher = Voucher::where('company_id', $obj->company_id)
            ->where('voucher_type_id', $obj->document_voucher_id)
            ->where('serie_number', $obj->document_serie)
            ->where('voucher_number', $obj->document_voucher_number)
            ->first();

        if ( $voucher == null || $voucher == '' ) {
            $voucher = new Voucher();
        }

        $client = Client::where('code', $obj->client_code)
            ->where('company_id', $obj->company_id)
            ->select('id', 'business_name')
            ->first();
        
        $original_client_aux = DB::connection($obj->company_database_name)
            ->table('OrdenCompra')
            ->where('AnioOrdenCompra', $obj->document_order_serie)
            ->where('NumeroOrden', $obj->document_order_number)
            ->select('codCliente')
            ->first();
        
        $original_client = Client::where('code', $original_client_aux->codCliente)
            ->select('id')
            ->first();
        
        $client_address = ClientAddress::where('id', $client->id)
            ->where('address_type_id', 1)
            ->select('address')
            ->first();

        $voucher->company_id = $obj->company_id;
        $voucher->client_id = $client->id;
        $voucher->original_client_id = $original_client->id;
        $voucher->client_name = $client->business_name;
        $voucher->client_address = $client_address->address;
        $voucher->voucher_type_id = $obj->document_voucher_id;
        $voucher->serie_number = $obj->document_serie;
        $voucher->voucher_number = $obj->document_voucher_number;
        $voucher->order_series = $obj->document_order_serie;
        $voucher->order_number = $obj->document_order_number;
        $voucher->issue_date = $obj->document_date_of_issue;
        $voucher->issue_hour = $obj->document_hour_of_issue;
        $voucher->expiry_date = $obj->document_expiration_date;
        $voucher->currency_id = $obj->document_currency_id;
        $voucher->payment_id = $obj->document_payment_id;
        $voucher->taxed_operation = $obj->document_subtotal;
        $voucher->unaffected_operation = 0;
        $voucher->exonerated_operation = 0;
        $voucher->igv = $obj->document_igv;
        $voucher->total = $obj->document_total;
        $voucher->igv_perception = $obj->document_perception;
        $voucher->total_perception = $obj->document_total + $obj->document_perception;
        $voucher->igv_percentage = $obj->document_igv_percentage;
        $voucher->igv_perception_percentage = $obj->document_perception_percentage;
        $voucher->ose = 1;
        $voucher->user = Auth::user()->user;
        $voucher->save();

        foreach ($obj->details as $detail) {
            $count = VoucherDetail::where('voucher_id', $voucher->id)->count();
            
            if ( $count != 0 ) {
                $voucher_detail = VoucherDetail::where('voucher_id', $voucher->id)->delete();
            }

            $original_price = DB::connection($obj->company_database_name)
                ->table('DetalleOrdenCompra')
                ->where('AnioOrdenCompra', $voucher->order_series)
                ->where('NumeroOrden', $voucher->order_number)
                ->select('Precio')
                ->first();

            $voucher_detail = new VoucherDetail();
            $voucher_detail->voucher_id = $voucher->id;
            $voucher_detail->unit_id = $detail->detail_unit_id;
            $voucher_detail->name = $detail->detail_name;
            $voucher_detail->quantity = $detail->detail_quantity;
            $voucher_detail->original_price = $original_price->Precio;
            $voucher_detail->unit_price = $detail->detail_price;
            $voucher_detail->sale_value = $detail->detail_price_igv;
            $voucher_detail->exonerated_value = 0;
            $voucher_detail->inaccurate_value = 0;
            $voucher_detail->igv = $detail->detail_igv;
            $voucher_detail->total = $detail->detail_total;
            $voucher_detail->user = Auth::user()->user;
            $voucher_detail->save();
        }

        // $response_obj = new \stdClass();
        // $response_obj->document_serie = $obj->document_serie;
        // $response_obj->document_voucher_number = $obj->document_voucher_number;
        // $response_obj->response_code = 0;
        // $response_obj->response_text = 'Documento guardado correctamente' . $voucher->id;

        // return $response_obj;
    }

    public function test() {
        $current_date = date('Ymd');
        dd($current_date);
    }
}
