<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\CreditNoteReason;
use App\Currency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Payment;
use App\VoucherType;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function boletaBackup() {
        $company_id = 1;
        $voucher_type_id = 2;
        $initial_date = '2019-09-15';
        $flag_ose = 1;

        $company = Company::where('id', $company_id)
            ->select('database_name')
            ->first();
        
        $voucher = VoucherType::where('id', $voucher_type_id)
            ->select('name','serie_type','type','reference')
            ->first();

        $query = DB::connection($company->database_name)
            ->table('FacturacionMarket')
            ->where('TipoDocumento', $voucher->reference)
            ->where('Estado', 1)
            ->where('flagOse', $flag_ose)
            ->where('FechaEmision', $initial_date)
            // ->where('FechaEmision', '>=', $initial_date)
            // ->where('FechaEmision', '<=', '2019-09-16')
            ->select('TipoDocumento','NumSerie','NumeroDocumento','CodCliente','FechaEmision','ValorNeto','IGV','ImporteTotal','TipoMoneda','CodFormaPago','PorcentajeIgv','NombreCliente','FechaVencimiento','AnioPedido','NumeroPedido')
            ->orderBy('NumSerie', 'ASC')
            ->orderBy('NumeroDocumento', 'ASC')
            ->get();
        
        // Crear objetos de los elementos del query
        $objs = [];

        foreach ($query as $key => $obj) {
            $voucher_type = $voucher->type;

            $payment = Payment::where('reference', $obj->CodFormaPago)
                ->select('id','name','reference')
                ->first();

            $currency = Currency::where('reference', $obj->TipoMoneda)
                ->select('id','symbol')
                ->first();

            $client_query = DB::connection($company->database_name);
            $client = $client_query->select("select CodCliente, Nombre, RazonSocial, Direccion, EMail, RucCliente, DNI from MaestroCliente where CodCliente = (SELECT CASE WHEN (SELECT DNI FROM MaestroCliente WHERE CodCliente = ".$obj->CodCliente.") = '' OR (SELECT DNI FROM MaestroCliente WHERE CodCliente = ".$obj->CodCliente.") = NULL THEN (SELECT CodCliente FROM MaestroCliente WHERE CodCliente = 4237) ELSE ".$obj->CodCliente." END)");

            $aux = new \stdClass();

            $aux->RecordID = $key + 1;
            $aux->company_id = $company_id;

            $aux->client_address = (trim($client[0]->Direccion) ? $client[0]->Direccion : 'Lima - Lima - Perú');
            $aux->client_code = $client[0]->CodCliente;
            $aux->client_document_name = 'DNI';
            if ( trim($client[0]->DNI) == '' ) {
                $aux->client_document_number = '12345678';
            } else {
                $aux->client_document_number = trim($client[0]->DNI);
            }
            $aux->client_name = trim($client[0]->Nombre);
            $aux->client_email = $client[0]->EMail;

            $aux->document_currency_id = $currency->id;
            $aux->document_currency_symbol = $currency->symbol;
            $aux->document_date_of_issue = date('d-m-Y', strtotime($obj->FechaEmision));
            $aux->document_expiration_date = ( $obj->FechaVencimiento ? date('d-m-Y', strtotime($obj->FechaVencimiento)) : '' );
            $aux->document_hour_of_issue = date('h:i:s', strtotime($obj->FechaEmision));
            $aux->document_igv = number_format(abs($obj->IGV), 2, '.', '');
            $aux->document_igv_percentage = number_format($obj->PorcentajeIgv, 2, '.', '') * 100;
            $aux->document_serie = $voucher->serie_type . sprintf('%03d', $obj->NumSerie);
            $aux->document_serie_number = $obj->NumSerie;
            $aux->document_subtotal = number_format(abs($obj->ValorNeto), 2, '.', '');
            $aux->document_order_serie = $obj->AnioPedido;
            $aux->document_order_number = $obj->NumeroPedido;
            $aux->document_payment_id = $payment->id;
            $aux->document_payment_name = $payment->name;
            $aux->document_perception = '';
            $aux->document_perception_percentage = '';
            $aux->document_reference_number = '';
            $aux->document_reference_reason_name = '';
            $aux->document_reference_reason_type = '';
            $aux->document_reference_serie = '';
            $aux->document_total = number_format(abs($obj->ImporteTotal), 2);
            $aux->document_voucher_type = $voucher->type;
            $aux->document_voucher_number = $obj->NumeroDocumento;
            $aux->document_voucher_reference = $obj->TipoDocumento;

            $objs[$key] = $aux;
        }

        dd($objs);
    }
}
