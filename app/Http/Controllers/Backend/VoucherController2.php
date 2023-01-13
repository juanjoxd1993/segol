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

class VoucherController extends Controller
{
    private $env = 'production';

    public function sendOse() {
    	$companies = Company::select('id','name')->get();
    	$voucher_types = VoucherType::select('id','name')->get();
        $user_name = Auth::user()->user;
    	return view('backend.voucher_send_ose')->with(compact('companies','voucher_types','user_name'));
    }

    public function validate_voucher_form() {
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

	public function list() {
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

		if ( $voucher_type->id == 3 || $voucher_type->id == 4 ) {
			$serie_number = $voucher_type->serie_type . sprintf('%02d', $serie);
		} else {
			$serie_number = $voucher_type->serie_type . sprintf('%03d', $serie);
		}

		if ( $flag_ose == '' ) {
            $flag_ose = ($this->env == 'production' ? 0 : 1);
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
			->when($initial_number, function ($query) use ($initial_number) {
                return $query->where('voucher_number', '>=', $initial_number);
            })
            ->when($final_number, function ($query) use ($final_number) {
                return $query->where('voucher_number', '<=', $final_number);
            })
			->when($voucher_type->reference == 2, function ($query) use ($order_serie, $order_number, $date_of_issue) {
				return $query->when($order_serie, function($query) use ($order_serie) {
						return $query->where('order_series', $order_serie);
					})
					->when($order_number, function($query) use ($order_number) {
						return $query->where('order_number', $order_number);
					})
					->where('issue_date', $date_of_issue);
			})
			->where('ose', $flag_ose)
			->select('vouchers.id', 'voucher_types.type', 'serie_number', 'voucher_number', DB::Raw('DATE_FORMAT(issue_date, "%d-%m-%Y") as issue_date_formated'), 'payments.name as payment_name', 'order_series', 'order_number', 'clients.code as client_code', 'document_types.name as document_type_name', 'clients.document_number as client_document_number', 'client_name', 'currencies.symbol as currency_symbol', 'total', 'igv_perception', 'summary_number', 'low_number')
			->orderBy('serie_number', 'ASC')
            ->orderBy('voucher_number', 'ASC')
			->get();

		return $vouchers;
	}

    public function get_vouchers_for_table() {
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
            ->select('name','serie_type','type','reference')
            ->first();

        if ( $flagOse == '' ) {
            $flagOse = ($this->env == 'production' ? 0 : 1);
        }

        $query = DB::connection($company->database_name)
            ->table('FacturacionMarket')
            ->where('TipoDocumento', $voucher->reference)
            ->where('Estado', 1)
            ->when($serie, function($query, $serie) {
                return $query->where('NumSerie', $serie);
            })
            ->when($initial_number, function($query, $initial_number) {
                return $query->where('NumeroDocumento', '>=', $initial_number);
            })
            ->when($final_number, function($query, $final_number) {
                return $query->where('NumeroDocumento', '<=', $final_number);
            })
            ->where('flagOse', $flagOse);
            if ( $voucher->reference == 2 ) {
                $query->where('FechaEmision', $date_of_issue)
                    ->when($order_serie, function($query, $order_serie) {
                        return $query->where('AnioPedido', $order_serie);
                    })
                    ->when($order_number, function($query, $order_number) {
                        return $query->where('NumeroPedido', $order_number);
                    })
                    ->select('TipoDocumento','NumSerie','NumeroDocumento','CodCliente','FechaEmision','ValorNeto','IGV','ImporteTotal','TipoMoneda','CodFormaPago','PorcentajeIgv','NombreCliente','FechaVencimiento','AnioPedido','NumeroPedido');
            } else {
                $query->where('FechaEmision', '>=' , '2019-09-13')
                    ->select('TipoDocumento','NumSerie','NumeroDocumento','CodCliente','FechaEmision','ValorNeto','IGV','ImporteTotal','TipoMoneda','CodFormaPago','RucCliente','PorcentajeIgv','NombreCliente','FechaVencimiento','porcPercepcion','impPercepcion','AnioPedido','NumeroPedido','NumTarjeta','Comentario');
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
        for ($i = 1; $i <= $query->count() ; $i++) {
            $meta->rowIds[] = $i;
        }

        // Crear objetos de los elementos del query
        $objs = [];

        foreach ($query_response as $key => $obj) {
            $voucher_type = $voucher->type;

            $payment = Payment::where('reference', $obj->CodFormaPago)
                ->select('id','name','reference')
                ->first();

            $currency = Currency::where('reference', $obj->TipoMoneda)
                ->select('id','symbol')
                ->first();

            if ( $voucher->type == '07' ) {
                $reference_reason = CreditNoteReason::where('type', substr($obj->Comentario, 0, 2))
                    ->select('name','type')
                    ->first();
            }

            $client_query = DB::connection($company->database_name);
            if ( $voucher->reference == 2 ) {
                $client = $client_query->select("select CodCliente, Nombre, RazonSocial, Direccion, EMail, RucCliente, DNI from MaestroCliente where CodCliente = (SELECT CASE WHEN (SELECT DNI FROM MaestroCliente WHERE CodCliente = ".$obj->CodCliente.") = '' OR (SELECT DNI FROM MaestroCliente WHERE CodCliente = ".$obj->CodCliente.") = NULL THEN (SELECT CodCliente FROM MaestroCliente WHERE CodCliente = 4237) ELSE ".$obj->CodCliente." END)");
            } else {
                $client = $client_query->table('MaestroCliente')
                ->where('CodCliente', $obj->CodCliente)
                ->select('CodCliente','Nombre','RazonSocial','Direccion','EMail','RucCliente','DNI')
                ->get();
            }

            $aux = new \stdClass();

            $aux->RecordID = $key + 1;
            $aux->company_id = $company_id;

            $aux->client_address = (trim($client[0]->Direccion) ? $client[0]->Direccion : 'Lima - Lima - Perú');
            $aux->client_code = $client[0]->CodCliente;
            if ( $voucher->type == '03' ) {
                $aux->client_document_name = 'DNI';
                if ( trim($client[0]->DNI) == '' ) {
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
            $aux->document_expiration_date = ( $obj->FechaVencimiento ? date('d-m-Y', strtotime($obj->FechaVencimiento)) : '' );
            $aux->document_hour_of_issue = date('h:i:s', strtotime($obj->FechaEmision));
            $aux->document_igv = number_format(abs($obj->IGV), 2, '.', '');
            $aux->document_igv_percentage = number_format($obj->PorcentajeIgv, 2, '.', '') * 100;
            if ( $voucher->type == '07' ) {
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
            if ( $voucher->reference == 3 && $obj->impPercepcion > 0 && $company_id == 1 ) {
                if ( $payment->reference != 1 && trim($client[0]->RucCliente) == '20536075195' ) {
                    $aux->document_perception = '';
                } else {
                    $aux->document_perception = number_format(abs($obj->impPercepcion), 2, '.', '');
                }

                if ( $obj->porcPercepcion == 0 ) {
                    $aux->document_perception_percentage = number_format(((abs($obj->impPercepcion) * 100) / $obj->ImporteTotal) / 100, 3, '.', '');
                } else {
                    $aux->document_perception_percentage = number_format($obj->porcPercepcion, 3, '.', '');
                }
            } else {
                $aux->document_perception = '';
                $aux->document_perception_percentage = '';
            }

            if ( $voucher->type == '07' ) {
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

    public function get_voucher_detail() {
		$voucher_id = request('voucher_id');

		$voucher = Voucher::find($voucher_id, ['serie_number', 'voucher_number']);
		$voucher_details = VoucherDetail::where('voucher_id', $voucher_id)
			->select('id', 'name', 'unit_price', 'quantity', 'igv', 'total')
			->get();

		return response()->json([
			'voucher'			=> $voucher,
			'voucher_details'	=> $voucher_details
		]);
    }

    public function send_voucher() {
        $company_id = request('company_id');
        $ids = request('ids');
        $task = request('task');
        $flag_ose = request('flag_ose');

		$company = Company::where('id', $company_id)
                ->select('document_number', 'name', 'short_name', 'bizlinks_user','bizlinks_password','bizlinks_user_test','bizlinks_password_test','certificate_pem')
                ->first();
		// Datos de la Compañia
		if ( $this->env == 'local' ) {
			$bizlinks_user = $company->bizlinks_user_test;
			$bizlinks_password = $company->bizlinks_password_test;
		} elseif ( $this->env == 'production' ) {
			$bizlinks_user = $company->bizlinks_user;
			$bizlinks_password = $company->bizlinks_password;
		}

		$company->bizlinks_user = $bizlinks_user;
		$company->bizlinks_password = $bizlinks_password;

		$response = [];
		$routes = new stdClass();
		$mail_info = new stdClass();

		if ( $task == 'resumen' ) {
			$ids_count = count($ids);

			if ( $ids_count > 500 ) {
				$ids = array_slice($ids, 0, 500);
			}

			$issue_year = date('Y');
			$summary_date = date('Ymd');
			$summary_number = Voucher::where('company_id', $company_id)->max('summary_number');
			$summary_number = ( $summary_number ? $summary_number + 1 : 1 );

			$vouchers = Voucher::leftjoin('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
				->leftjoin('currencies', 'currencies.id', '=', 'vouchers.currency_id')
				->whereIn('vouchers.id', $ids)
				->select('vouchers.id', 'serie_number', 'voucher_number', 'issue_date', 'taxed_operation', 'unaffected_operation', 'exonerated_operation', 'igv', 'total', 'igv_perception', 'total_perception', 'igv_percentage', 'igv_perception_percentage', 'voucher_types.type as voucher_type_type', 'currencies.short_name as currency_short_name')
				->get();

			$nombre_xml = $company->document_number . '-RC-' . $summary_date . '-' . $summary_number;
			$nombre_ruta = 'uploads/' . $company->short_name . '/'. $issue_year;
			$routes->nombre_xml = $nombre_xml;
			$routes->nombre_ruta = $nombre_ruta;
			$routes->nombre_ruta_xml = $nombre_ruta .'/xml/' . $nombre_xml . '.xml';
			$routes->nombre_ruta_firma = $nombre_ruta .'/firma/' . $nombre_xml . '.xml';
			$routes->nombre_ruta_zip = $nombre_ruta .'/firma/' . $nombre_xml . '.zip';
			$routes->nombre_ruta_rspta = $nombre_ruta .'/rpta/R-' . $nombre_xml . '.zip';
			$routes->nombre_ruta_pdf = $nombre_ruta .'/pdf/' . $nombre_xml . '.pdf';
			$routes->company_certificate_pem = $company->certificate_pem;

			$summary_obj = new stdClass();
			$summary_obj->summary_date = $summary_date;
			$summary_obj->summary_number = $summary_number;
			$summary_obj->vouchers = $vouchers;
			$summary_obj->company = $company;
			$summary_obj->routes = $routes;
			$summary_obj->serie_number = $summary_date;
			$summary_obj->voucher_number = $summary_number;

			$xml_render = $this->xml_render($summary_obj, '', $summary_number);
			$xml_signed = $this->xml_signed($summary_obj, $xml_render, $summary_number);
			$create_zip = $this->create_zip($summary_obj->routes);
			$summary_ticket = $this->summary_voucher($summary_obj);

			if ( is_string($summary_ticket) ) {
				$vouchers_update = Voucher::whereIn('id', $ids)
					->update(['summary_number' => $summary_number, 'summary_ticket' => $summary_ticket]);

				$get_status_obj = new stdClass();
				$get_status_obj->company_bizlinks_user = $bizlinks_user;
				$get_status_obj->company_bizlinks_password = $bizlinks_password;
				$get_status_obj->nombre_ruta_rspta = $summary_obj->routes->nombre_ruta_rspta;
				$get_status_obj->serie_number = $summary_date;
				$get_status_obj->voucher_number = $summary_number;
				$get_status_obj->ids_count = $ids_count;

				$get_status = $this->get_status($get_status_obj, $summary_ticket, $task);

				if ( $get_status ) {
					$get_status_cdr = $this->get_status_cdr($bizlinks_user, $bizlinks_password, $summary_number, $company->document_number, $summary_date, 'RC', $ids, 'Resumen', $routes->nombre_xml, $routes->nombre_ruta_zip, $routes->nombre_ruta_rspta);

					$send_mail_obj = new stdClass();
					$send_mail_obj->voucher_type_type = 'RC';
					$send_mail_obj->serie_number = $summary_date;
					$send_mail_obj->voucher_number = $summary_number;
					$send_mail_obj->document_voucher_name = 'Resumen';

					$mail_info = new stdClass();
					$mail_info->voucher_type_type = 'RC';
					$mail_info->summary_number = $summary_number;
					$mail_info->company_name = $company->name;
					$mail_info->company_short_name = $company->short_name;
					$mail_info->summary_date = $summary_date;
					$mail_info->summary_ticket = $summary_ticket;
					$mail_info->client_name = '';
					$mail_info->ids_count = $ids_count;

					$send_mail = $this->send_mail($send_mail_obj, $routes, $mail_info);

					$response[] = $send_mail;

				} else {
					$response[] = $get_status;
				}

			} else {
				$response[] = $summary_ticket;
			}

		} else {
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
				->leftjoin('client_addresses', function ($join) {
					$join->on('client_addresses.client_id', '=', 'clients.id')
						->where('client_addresses.address_type_id', '=', 1);
				})
				->leftjoin('voucher_types', 'voucher_types.id', '=', 'vouchers.voucher_type_id')
				->whereIn('vouchers.id', $ids)
				->select('vouchers.id', 'serie_number', 'voucher_number', 'referral_guide_series', 'referral_guide_number', 'voucher_type_id', 'voucher_types.type as voucher_type_type', 'voucher_types.name as voucher_type_name', 'credit_note_reasons.id as credit_note_reason_id', 'credit_note_reasons.type as credit_note_reason_type', 'credit_note_reasons.name as credit_note_reason_name', 'credit_note_reference_serie', 'credit_note_reference_number', 'low_number', 'issue_date', DB::Raw('DATE_FORMAT(issue_date, "%Y") as issue_year'), 'issue_hour', 'expiry_date', 'currencies.name as currency_name', 'currencies.short_name as currency_short_name', 'currencies.symbol as currency_symbol', 'payments.id as payment_id', 'payments.name as payment_name', 'taxed_operation', 'unaffected_operation', 'exonerated_operation', 'vouchers.igv', 'total', 'igv_perception', 'total_perception', 'igv_percentage', 'igv_perception_percentage', 'ose', 'company_addresses.address as company_address', 'company_addresses.district as company_district', 'company_addresses.province as company_province', 'company_addresses.department as company_department', 'company_addresses.ubigeo as company_ubigeo', 'vouchers.company_id', 'companies.document_number as company_document_number', 'companies.name as company_name', 'companies.short_name as company_short_name', 'clients.code as client_code', 'clients.business_name as client_name', 'clients.document_number as client_document_number', 'clients.email as client_email', 'clients.manager_mail as manager_mail','clients.credit_limit_days as client_credit_limit_days', 'document_types.type as client_document_type', 'document_types.name as client_document_name', 'client_addresses.address as client_address')
				->with(['voucher_details' => static function ($query) {
					$query->leftjoin('units', 'units.id', '=', 'voucher_details.unit_id')
						->leftjoin('articles', function($join) {
							$join->on('voucher_details.name', '=', 'articles.name')
								->where('articles.warehouse_type_id', 5);
						})
						->select('voucher_id', 'voucher_details.id', 'unit_id', 'units.name as unit_name', 'units.short_name as unit_short_name', 'voucher_details.name', 'quantity', 'unit_price', 'sale_value', 'exonerated_value', 'inaccurate_value',  'voucher_details.igv', DB::Raw('ROUND(total - voucher_details.igv, 2) as subtotal'), 'total')
						->when('articles.igv = 1', function ($query) {
							return $query->leftjoin('rates', function ($join) {
									$join->on('rates.description', '=', DB::Raw("'IGV'"))
										->where('state', 1);
								})
								->addSelect(DB::Raw('rates.value as igv_percentage'));
						});
				}])
				->get();

			$vouchers->map(function ($item, $key) use ($company, $bizlinks_user, $bizlinks_password, $task, &$response, &$routes, &$mail_info, &$low_date, &$low_number) {
                $formatter = new NumeroALetras();
				$item->total_text = $formatter->toInvoice($item->total, 2, 'SOLES');
                $item->payment_due_date = CarbonImmutable::createFromDate(request($item->issue_date))->addDays($item->client_credit_limite_days)->format('Y-m-d');

				if ( $task == 'baja' ) {
					$summary_date = '';
					$summary_number = '';
					$low_date = date('Ymd');
					$low_number = Voucher::where('company_id', $item->company_id)->max('low_number');
					$low_number = ( $low_number ? $low_number + 1 : 1 );
				} else {
					$summary_date = '';
					$summary_number = '';
					$low_date = '';
					$low_number = '';
				}

				// Datos de Rutas
				if ( $task == 'baja' ) {
					$nombre_xml = $item->company_document_number . '-RA-' . $low_date . '-' . $low_number;
				} else {
					$nombre_xml = $item->company_document_number . '-' . $item->voucher_type_type . '-' . $item->serie_number . '-' . $item->voucher_number;
				}
				$nombre_ruta = 'uploads/' . $item->company_short_name . '/'. $item->issue_year;
				$item->nombre_xml = $nombre_xml;
				$item->nombre_ruta = $nombre_ruta;
				$item->nombre_ruta_xml = $nombre_ruta .'/xml/' . $nombre_xml . '.xml';
				$item->nombre_ruta_firma = $nombre_ruta .'/firma/' . $nombre_xml . '.xml';
				$item->nombre_ruta_zip = $nombre_ruta .'/firma/' . $nombre_xml . '.zip';
				$item->nombre_ruta_rspta = $nombre_ruta .'/rpta/R-' . $nombre_xml . '.zip';
				$item->nombre_ruta_pdf = $nombre_ruta .'/pdf/' . $nombre_xml . '.pdf';

				$item->company_certificate_pem = $company->certificate_pem;
				$item->company_bizlinks_user = $bizlinks_user;
				$item->company_bizlinks_password = $bizlinks_password;

				$item->summary_date = $summary_date;
				$item->summary_number = $summary_number;

				$item->low_date = $low_date;
				$item->low_number = $low_number;

				$mail_info->client_name = $item->client_name;
				$mail_info->low_number = $item->low_number;
				$mail_info->voucher_type_name = $item->voucher_type_name;
				$mail_info->voucher_type_type = $item->voucher_type_type;
				$mail_info->issue_date = $item->issue_date;
				$mail_info->serie_number = $item->serie_number;
				$mail_info->voucher_number = $item->voucher_number;
				$mail_info->company_document_number = $item->company_document_number;
				$mail_info->company_document_number = $item->company_document_number;
				$mail_info->currency_symbol = $item->currency_symbol;
				$mail_info->total = $item->total;
				$mail_info->expiry_date = $item->expiry_date;
				$mail_info->company_name = $item->company_name;

				$routes->nombre_xml = $nombre_xml;
				$routes->nombre_ruta = $nombre_ruta;
				$routes->nombre_ruta_xml = $nombre_ruta .'/xml/' . $nombre_xml . '.xml';
				$routes->nombre_ruta_firma = $nombre_ruta .'/firma/' . $nombre_xml . '.xml';
				$routes->nombre_ruta_zip = $nombre_ruta .'/firma/' . $nombre_xml . '.zip';
				$routes->nombre_ruta_rspta = $nombre_ruta .'/rpta/R-' . $nombre_xml . '.zip';
				$routes->nombre_ruta_pdf = $nombre_ruta .'/pdf/' . $nombre_xml . '.pdf';

				if ( $task == 'xml' ) {
					$xml_render = $this->xml_render($item, $low_number);

					$response[] = $xml_render;
				} elseif ( $task == 'pdf' ) {
					$create_pdf = $this->create_pdf($item);

					$response[] = $create_pdf;
				} elseif ( $task == 'mail' ) {
					$send_mail = $this->send_mail($item, $routes, $mail_info);

					$response[] = $send_mail;
				} elseif ( $task == 'save' ) {
					// $save_voucher = $this->save_voucher($item);

					// $response[] = $save_voucher;
				} else {
					$xml_render = $this->xml_render($item, $low_number);
					$xml_signed = $this->xml_signed($item, $xml_render);

					$create_zip = $this->create_zip($item);

					if ( $task == 'baja' ) {
						$low_voucher = $this->low_voucher($item);
						if ( is_object($low_voucher) ) {
							$response[] = $low_voucher;
						} else {
							$get_status = $this->get_status($item, $low_voucher);
							$send_mail = $this->send_mail($item, $routes, $mail_info);

							$low_voucher = Voucher::find($item->id);
							$low_voucher->taxed_operation = 0;
							$low_voucher->unaffected_operation = 0;
							$low_voucher->exonerated_operation = 0;
							$low_voucher->igv = 0;
							$low_voucher->total = 0;
							$low_voucher->igv_perception = 0;
							$low_voucher->total_perception = 0;
							$low_voucher->igv_percentage = 0;
							$low_voucher->igv_perception_percentage = 0;
							$low_voucher->save();

							$low_voucher_details = VoucherDetail::where('voucher_id', $item->id)->delete();
							$warehouse_document_type_id = WarehouseDocumentType::where('voucher_type_id', $item->voucher_type_id)
								->select('id')
								->first();

							if ( $item->voucher_type_id == 3 || $item->voucher_type_id == 4 ) {
								$low_serie_number = substr($item->serie_number, 2);
							} else {
								$low_serie_number = substr($item->serie_number, 1);
							}

							$low_sales = Sale::where('warehouse_document_type_id', $warehouse_document_type_id)
								->where('referral_serie_number', $low_serie_number)
								->where('referral_voucher_number', $item->voucher_number)
								->select('id')
								->get();

							foreach ($low_sales as $low_sale) {
								$low_sale_detail = SaleDetail::where('sale_id', $low_sale->id)->delete();
								$low_liquidations = Liquidation::where('sale_id', $low_sale->id)->delete();
								$low_sale->delete();
							}

							$response[] = $get_status;
						}
					} else {
						$ws_bizlinks = $this->ws_bizlinks($item);
						if ( $ws_bizlinks->response_code == 0 ) {
							// $save_voucher = $this->save_voucher($item);

							if ( $item->voucher_type_type != '03' ) {
								$create_pdf = $this->create_pdf($item);
								$send_mail = $this->send_mail($item, $routes, $mail_info);
							}
						}

						$response[] = $ws_bizlinks;
					}
				}
			});
		}

        return $response;
    }

    public function xml_render(object $obj, $low_number = '', $summary_number = '') {
        if ( $summary_number != '' ) {
			$textoXML = view('backend.xml.resumen', compact('obj'))->render();
		} elseif ( $low_number != '' ) {
            $textoXML = view('backend.xml.comunicacion_baja', compact('obj'))->render();
        } else if ( $obj->voucher_type_type == '01' ) {
            $textoXML = view('backend.xml.factura_gravada', compact('obj'))->render();
        } else if ( $obj->voucher_type_type == '03' ) {
            $textoXML = view('backend.xml.boleta', compact('obj'))->render();
        } else if ( $obj->voucher_type_type == '07' ) {
            $textoXML = view('backend.xml.nota_credito', compact('obj'))->render();
        }

        $textoXML = mb_convert_encoding($textoXML, "UTF-8");

        /*
        * Crear el XML sin firma
        */
		if ( $summary_number != '' ) {
        	$generate_xml = Storage::disk('public')->put($obj->routes->nombre_ruta_xml, $textoXML);
		} else {
        	$generate_xml = Storage::disk('public')->put($obj->nombre_ruta_xml, $textoXML);
		}

        return $textoXML;
    }

    public function xml_signed(object $obj, string $xml = '', $summary_number = '') {
        /*
         * Firmar XML
         */
		if ( $summary_number != '' ) {
			$xmlPath = $obj->routes->nombre_ruta . '/xml/' . $obj->routes->nombre_xml . '.xml';
			$certPath = base_path($obj->company->certificate_pem);
		} else {
			$xmlPath = $obj->nombre_ruta . '/xml/' . $obj->nombre_xml . '.xml';
			$certPath = base_path($obj->company_certificate_pem);
		}

        $signer = new SignedXml();
        $signer->setCertificateFromFile($certPath);
        $textoXMLSigned = $signer->signFromFile($xmlPath);

		if ( $summary_number != '' ) {
        	$sign_xml = Storage::disk('public')->put($obj->routes->nombre_ruta_firma, $textoXMLSigned);
		} else {
        	$sign_xml = Storage::disk('public')->put($obj->nombre_ruta_firma, $textoXMLSigned);
		}
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

	public function summary_voucher(object $obj) {
		/*
         * Convertir en Base64 el archivo .ZIP
         */
        $fd = fopen($obj->routes->nombre_ruta_zip, 'rb');
        $size = filesize($obj->routes->nombre_ruta_zip);
        $cont = fread($fd, $size);
        fclose($fd);
        $enc = base64_encode($cont);

        $zip = $obj->routes->nombre_xml.'.zip';

        /*
         * Consumo de Webservice de Bizlinks
         */
        $header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
            $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
            $header .= '<wsse:Username>'.$obj->company->bizlinks_user.'</wsse:Username>';
            $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$obj->company->bizlinks_password.'</wsse:Password>';
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

            return $xmlCdr;

        } catch (SoapFault $e) {
			if ( $e ) {
                if ( $e->faultstring == 'Gateway Time-out' ) {
                    $response_text = 'Tiempo de espera agotado';
                } elseif ( isset($e->detail) ) {
                    if ( isset($e->detail->message) ) {
                        $response_text = $e->detail->message;
                    } else {
                        $response_text = $e->detail;
                    }
                } else {
                    $response_text = $e;
                }
            }

			$response = new stdClass();
			$response->document_serie = $obj->serie_number;
			$response->document_voucher_number = $obj->voucher_number;
			$response->faultstring = $e->faultstring;
			$response->message = $response_text;

            // return $e;
			return $response;
        }
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

            $voucher = Voucher::find($obj->id);
			$voucher->low_number = $obj->low_number;
			$voucher->low_ticket = $xmlCdr;
            $voucher->save();

            // $response_obj = new \stdClass();
            // $response_obj->document_serie = $obj->document_serie;
            // $response_obj->document_voucher_number = $obj->document_voucher_number;
            // $response_obj->response_code = 0;
            // $response_obj->response_text = $obj->document_voucher_name.' enviada correctamente';

            return $xmlCdr;

        } catch (SoapFault $e) {
			$response = new stdClass();
			$response->document_serie = $obj->serie_number;
			$response->document_voucher_number = $obj->voucher_number;
			$response->faultstring = $e->faultstring;
			$response->message = $e->detail->message;

            // return $e;
			return $response;
        }
    }

    public function get_status(object $obj, string $ticket, $task = null) {
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
            $response_zip = Storage::disk('public')->put($obj->nombre_ruta_rspta, $xmlCdr);

			// if ( $task == 'resumen' ) {
			// 	$response_text = 'Resumen de '. $obj->ids_count . ($obj->ids_count > 1 ? ' comprobantes' : ' comprobante') .' enviado correctamente';
			// } else {
			// 	$response_text = 'Comunicación de Baja enviada correctamente';
			// }

            // $response_obj = new \stdClass();
            // $response_obj->document_serie = $obj->serie_number;
            // $response_obj->document_voucher_number = $obj->voucher_number;
            // $response_obj->response_code = $statusCode;
            // $response_obj->response_text = $response_text;

            return $response_zip;

        } catch (SoapFault $e) {
			if ( $e ) {
                if ( $e->faultstring == 'Gateway Time-out' ) {
                    $response_text = 'Tiempo de espera agotado';
                } elseif ( isset($e->response_text) && isset($e->response_text->detail) && isset($e->response_text->detail->message) ) {
					$response_text = $e->response_text->detail->message;
                } elseif ( isset($e->detail) && isset($e->detail->message) ) {
					$response_text = $e->detail->message;
				} elseif ( isset($e->detail) ) {
					$response_text = $e->detail;
				} elseif ( isset($e->message) ) {
					$response_text = $e->detail;
				} else {
                    $response_text = $e;
                }

				if ( isset($e->response_code) ) {
					$response_code = $e->response_code;
				} else {
					$response_code = '';
				}
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->serie_number;
            $response_obj->document_voucher_number = $obj->voucher_number;
            $response_obj->response_code = $response_code;
            $response_obj->response_text = $response_text;

            return $response_obj;
        }
    }

	public function get_status_cdr($bizlinks_user = null, $bizlinks_password = null, $voucher_number = null, $company_document_number = null, $serie_number = null, $voucher_type_type = null, $ids = null, $voucher_type_name = null, $nombre_xml = null, $nombre_ruta_zip = null, $nombre_ruta_rspta = null) {
		/*
         * Consumo de Webservice de Bizlinks
         */
        $header = '<wsse:Security xmlns:wsse="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd" xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" mustUnderstand="1">';
            $header .= '<wsse:UsernameToken wsu:Id="UsernameToken-c175cdb9-9a32-4291-b8c7-85dff8107561">';
            $header .= '<wsse:Username>'.$bizlinks_user.'</wsse:Username>';
            $header .= '<wsse:Password Type="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText">'.$bizlinks_password.'</wsse:Password>';
            $header .= '</wsse:UsernameToken>';
        $header .= '</wsse:Security>';

        $body = '<ns2:getStatusCdr xmlns:ns2="http://service.sunat.gob.pe">';
			$body .= '<statusCdr>';
				$body .= '<numeroComprobante>'.$voucher_number.'</numeroComprobante>';
				$body .= '<rucComprobante>'.$company_document_number.'</rucComprobante>';
				$body .= '<serieComprobante>'.$serie_number.'</serieComprobante>';
				$body .= '<tipoComprobante>'.$voucher_type_type.'</tipoComprobante>';
			$body .= '</statusCdr>';
        $body .= '</ns2:getStatusCdr>';

        $header_block = new SoapVar( $header, XSD_ANYXML, NULL, NULL, NULL, NULL );
        $body_block = new SoapVar( $body, XSD_ANYXML, NULL, NULL, NULL, NULL );

        $header = new SoapHeader( 'http://schemas.xmlsoap.org/soap/envelope/', 'Header', $header_block );

        if ( $this->env == 'local' ) {
            $webservice = 'https://osetesting.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        } elseif ( $this->env == 'production' ) {
            $webservice = 'https://ose.bizlinks.com.pe/ol-ti-itcpe/billService?wsdl';
        }

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
			$client->getStatusCdr($body_block);
            $client_response = $client->__getLastResponse();
            $client_response = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $client_response);
            $xml = new SimpleXMLElement($client_response);
            $body = $xml->xpath('//soapBody')[0];
            $array = json_decode(json_encode((array)$body), TRUE);
			$xmlCdr = base64_decode($array['ns2getStatusCdrResponse']['document']);

			/*
             * Crear el Zip de Respuesta
             */
            Storage::disk('public')->put($nombre_ruta_rspta, $xmlCdr);

            if ( $this->env == 'production' ) {
				Voucher::whereIn('id', $ids)
					->update(['ose' => 1]);
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $serie_number;
            $response_obj->document_voucher_number = $voucher_number;
            $response_obj->response_code = 0;
            $response_obj->response_text = $voucher_type_name.' enviado correctamente';

            return $response_obj;
		} catch (SoapFault $e) {
			if ( $e ) {
                if ( $e->faultstring == 'Gateway Time-out' ) {
                    $response_text = 'Tiempo de espera agotado';
                } elseif ( isset($e->detail) ) {
                    if ( isset($e->detail->message) ) {
                        $response_text = $e->detail->message;
                    } else {
                        $response_text = $e->detail;
                    }
                } else {
                    $response_text = $e;
                }
            }

			$response_obj = new \stdClass();
            $response_obj->document_serie = $serie_number;
            $response_obj->document_voucher_number = $voucher_number;
            $response_obj->response_code = $e->faultstring;
            $response_obj->response_text = $response_text;

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

            if ( $this->env == 'production' ) {
				$query = Voucher::find($obj->id)
					->update(['ose' => 1]);
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->serie_number;
            $response_obj->document_voucher_number = $obj->voucher_number;
            $response_obj->response_code = 0;
            $response_obj->response_text = $obj->voucher_type_name.' enviada correctamente';

            return $response_obj;

        } catch (SoapFault $e) {
            if ( $e->faultstring >= 2000 && $e->faultstring <= 2099 && $this->env == 'production' ) {
				$query = Voucher::find($obj->id)
					->update(['ose' => $e->faultstring]);
            }

            if ( $e ) {
                if ( $e->faultstring == 'Gateway Time-out' ) {
                    $response_text = 'Tiempo de espera agotado';
                } elseif ( isset($e->detail) ) {
                    if ( isset($e->detail->message) ) {
                        $response_text = $e->detail->message;
                    } else {
                        $response_text = $e->detail;
                    }
                } else {
                    $response_text = $e;
                }
            }

            $response_obj = new \stdClass();
            $response_obj->document_serie = $obj->serie_number;
            $response_obj->document_voucher_number = $obj->voucher_number;
            $response_obj->response_code = $e->faultstring;
            $response_obj->response_text = $response_text;

            return $response_obj;
        }
    }

    public function create_pdf(object $obj) {
		//
        $xmlfile = Storage::disk('public')->get($obj->nombre_ruta_firma);
        $xml_content = str_replace('ext:', '', $xmlfile);
        $xml_content = str_replace('ds:', '', $xml_content);
        $xml_content = str_replace('cbc:', '', $xml_content);
        $xml_obj = simplexml_load_string($xml_content);

        $document_hash = (string)$xml_obj->UBLExtensions->UBLExtension->ExtensionContent->Signature->SignedInfo->Reference->DigestValue;
        $document_qrcode = base64_encode(QrCode::format('png')->size(100)->generate('| '.$obj->company_document_number.' | '.$obj->voucher_type_type.' | '.$obj->serie_number.'-'.$obj->voucher_number.' | '.$obj->igv.' | '.$obj->total.' | '.$obj->issue_date.' | '.$obj->client_document_type.' | '.$obj->client_document_number));

        // $data = [
        //     'company_address'					=> $obj->company_address,
        //     'company_department'				=> $obj->company_department,
        //     'company_district'					=> $obj->company_district,
        //     'company_document_number'			=> $obj->company_document_number,
        //     'company_id'		            	=> $obj->company_id,
        //     'company_name'						=> $obj->company_name,
        //     'company_province'					=> $obj->company_province,
        //     'client_address'					=> $obj->client_address,
        //     'client_document_name'				=> $obj->client_document_name,
        //     'client_document_number'			=> $obj->client_document_number,
        //     'client_email'						=> $obj->client_email,
        //     'client_name'						=> $obj->client_name,
        //     'document_currency_name'			=> $obj->document_currency_name,
        //     'document_currency_symbol'			=> $obj->document_currency_symbol,
        //     'document_date_of_issue'			=> $obj->document_date_of_issue,
        //     'document_expiration_date'			=> ($obj->document_expiration_date ? $obj->document_expiration_date : '-'),
        //     'document_hash'						=> $document_hash,
        //     'document_igv'						=> number_format($obj->document_igv, 2),
        //     'document_payment_name'				=> $obj->document_payment_name,
        //     'document_perception'				=> number_format($obj->document_perception, 2),
        //     'document_perception_percentage'	=> $obj->document_perception_percentage * 100,
        //     'document_qrcode'					=> $document_qrcode,
        //     'document_referral_guide_number'	=> ( isset($referral_guide_number) ? $referral_guide_number : '' ),
        //     'document_referral_guide_series'	=> ( isset($referral_guide_series) ? $referral_guide_series : '' ),
        //     'document_reference_number'			=> $obj->document_reference_number,
        //     'document_reference_reason_name'	=> $obj->document_reference_reason_name,
        //     'document_reference_reason_type'    => $obj->document_reference_reason_type,
        //     'document_reference_serie'			=> $obj->document_reference_serie,
        //     'document_subtotal'					=> number_format($obj->document_subtotal, 2),
        //     'document_total'					=> number_format($obj->document_total, 2),
        //     'document_total_text'				=> $obj->document_total_text,
        //     'document_total_perception'			=> number_format($obj->document_total + $obj->document_perception, 2),
        //     'document_serie'					=> $obj->document_serie,
        //     'document_voucher_name'				=> $obj->document_voucher_name,
        //     'document_voucher_number'			=> $obj->document_voucher_number,
        //     'document_voucher_type'			    => $obj->document_voucher_type,
        //     'details'							=> $obj->details,
        // ];

        $pdf = PDF::loadView('backend.pdf_view', compact('obj', 'document_hash', 'document_qrcode'));
        if ( !file_exists($obj->nombre_ruta .'/pdf') ) {
            mkdir($obj->nombre_ruta .'/pdf/', 0777, true);
        }
        $pdf->save($obj->nombre_ruta_pdf);

        return response()->json(true);
    }

    public function send_mail(object $obj, $routes, $mail_info) {
        if ( $this->env == 'local' ) {
            Mail::to(env('DEV_TESTING_DESTINATION_EMAIL'))->queue(new VoucherMail($obj, $routes, $mail_info));
        } elseif ( $this->env == 'production' ) {
            if ( $obj->voucher_type_type == 'RC' ) {
				Mail::to(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj, $routes, $mail_info));
			} elseif ( $obj->voucher_type_type != '03' && $obj->client_email ) {
                Mail::to($obj->client_email)->cc(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj, $routes, $mail_info))->queue(new VoucherMail($obj, $routes, $mail_info));
            } else {
                Mail::to(env('BILLING_ADDRESS_DESTINATION_EMAIL'))->queue(new VoucherMail($obj, $routes, $mail_info));
            }
        }

        $response_obj = new \stdClass();
        $response_obj->document_serie = $obj->serie_number;
        $response_obj->document_voucher_number = $obj->voucher_number;
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

        if ( $obj->document_order_serie && $obj->document_order_number ) {
            $original_client_aux = DB::connection($obj->company_database_name)
                ->table('OrdenCompra')
                ->where('AnioOrdenCompra', $obj->document_order_serie)
                ->where('NumeroOrden', $obj->document_order_number)
                ->select('codCliente', 'SerieGuia', 'NumeroGuia')
                ->first();

            $original_client = Client::where('code', $original_client_aux->codCliente)
                ->where('company_id', $obj->company_id)
                ->select('id')
                ->first();

            $referral_guide_series = $original_client_aux->SerieGuia;
            $referral_guide_number = $original_client_aux->NumeroGuia;
        } else {
            $original_client = Client::find($client->id);
        }

        $client_address = ClientAddress::where('client_id', $client->id)
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
        $voucher->referral_guide_series = ( isset($referral_guide_series) ? $referral_guide_series : '' );
        $voucher->referral_guide_number = ( isset($referral_guide_number) ? $referral_guide_number : '' );
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
        $voucher->credit_note_reason_id = $obj->document_reference_reason_type;
        $voucher->credit_note_reference_serie = $obj->document_reference_serie;
        $voucher->credit_note_reference_number = $obj->document_reference_number;
        $voucher->ose = 1;
        $voucher->user = Auth::user()->user;
        $voucher->save();

        foreach ($obj->details as $detail) {
            if ( $voucher->order_series && $voucher->order_number ) {
                $original_price = DB::connection($obj->company_database_name)
                    ->table('DetalleOrdenCompra')
                    ->where('AnioOrdenCompra', $voucher->order_series)
                    ->where('NumeroOrden', $voucher->order_number)
                    ->select('Precio')
                    ->first();
            }

            $voucher_detail = new VoucherDetail();
            $voucher_detail->voucher_id = $voucher->id;
            $voucher_detail->unit_id = $detail->detail_unit_id;
            $voucher_detail->name = $detail->detail_name;
            $voucher_detail->quantity = $detail->detail_quantity;
            $voucher_detail->original_price = ( $voucher->order_series && $voucher->order_number ? $original_price->Precio : $detail->detail_price_igv );
            $voucher_detail->unit_price = $detail->detail_price;
            $voucher_detail->sale_value = $detail->detail_price_igv;
            $voucher_detail->exonerated_value = 0;
            $voucher_detail->inaccurate_value = 0;
            $voucher_detail->igv = $detail->detail_igv;
            $voucher_detail->total = $detail->detail_total;
            $voucher_detail->user = Auth::user()->user;
            $voucher_detail->save();
        }

        $response_obj = new \stdClass();
        $response_obj->document_serie = $obj->document_serie;
        $response_obj->document_voucher_number = $obj->document_voucher_number;
        $response_obj->response_code = 0;
        $response_obj->response_text = 'Documento guardado correctamente';

        return $response_obj;
    }
}
