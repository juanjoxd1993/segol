<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Client;
use App\Company;
use App\Payment;
use App\Voucher;
use App\VoucherType;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesRegisterReportController extends Controller
{
    public function index() {
		$companies = Company::select('id','name')->get();
		$current_date = date(DATE_ATOM, mktime(0, 0, 0));

		return view('backend.sales_register_report')->with(compact('companies', 'current_date'));
	}

	public function validateForm() {
		$messages = [
			'company_id.required'       => 'Debe seleccionar una Compañía.',
			'since_date.required'       => 'Debe seleccionar una Fecha de Inicio.',
			'to_date.required'          => 'Debe seleccionar una Fecha de Fin.',
		];

		$rules = [
			'company_id'        => 'required',
			'since_date'		=> 'required',
			'to_date'           => 'required',
		];

		request()->validate($rules, $messages);
		return request()->all();
	}

    public function list() {
        $company_id = request('model.company_id');
		$since_date = date('Y-m-d', strtotime(request('model.since_date')));
		$to_date = date('Y-m-d', strtotime(request('model.to_date')));

        $query = DB::select(DB::raw("SELECT company_id, client_id, client_name, voucher_type_id, serie_number, voucher_number_since, voucher_number_to, credit_note_reference_serie, credit_note_reference_number, issue_date, payment_id, taxed_operation, unaffected_operation, exonerated_operation, igv, total FROM ( SELECT D.company_id, D.client_id, D.client_name, D.voucher_type_id, D.serie_number, D.voucher_number AS voucher_number_since, D.voucher_number AS voucher_number_to, D.credit_note_reference_serie, D.credit_note_reference_number, D.issue_date, D.payment_id, D.taxed_operation AS taxed_operation, D.unaffected_operation AS unaffected_operation, D.exonerated_operation AS exonerated_operation, D.igv AS igv, D.total AS total FROM vouchers AS D WHERE D.issue_date >= '".$since_date."' AND D.issue_date <= '".$to_date."' AND D.voucher_type_id <> 2 AND D.voucher_type_id <> 3 AND D.company_id = ".$company_id." UNION SELECT D1.company_id, D1.client_id, D1.client_name, D1.voucher_type_id, D1.serie_number, min( D1.voucher_number ) AS voucher_number_since, max( D1.voucher_number ) AS voucher_number_to, D1.credit_note_reference_serie, D1.credit_note_reference_number, D1.issue_date, D1.payment_id, sum( D1.taxed_operation ) AS taxed_operation, sum( D1.unaffected_operation ) AS unaffected_operation, sum( D1.exonerated_operation ) AS exonerated_operation, sum( D1.igv ) AS igv, sum( D1.total ) AS total FROM vouchers AS D1 WHERE D1.issue_date >= '".$since_date."' AND D1.issue_date <= '".$to_date."' AND D1.voucher_type_id = 2 AND D1.company_id = ".$company_id." GROUP BY D1.company_id, D1.client_id, D1.client_name, D1.voucher_type_id, D1.serie_number, D1.credit_note_reference_serie, D1.credit_note_reference_number, D1.issue_date, D1.payment_id UNION SELECT D2.company_id, D2.client_id, D2.client_name, D2.voucher_type_id, D2.serie_number, D2.voucher_number AS voucher_number_since, D2.voucher_number AS voucher_number_to, D2.credit_note_reference_serie, D2.credit_note_reference_number, D2.issue_date, D2.payment_id, ( D2.taxed_operation * - 1 ) AS taxed_operation, ( D2.unaffected_operation * - 1 ) AS unaffected_operation, ( D2.exonerated_operation * - 1 ) AS exonerated_operation, ( D2.igv * - 1 ) AS igv, ( D2.total * - 1 ) AS total FROM vouchers AS D2 WHERE D2.issue_date >= '".$since_date."' AND D2.issue_date <= '".$to_date."' AND D2.voucher_type_id = 3 AND D2.company_id = ".$company_id.") vouchers GROUP BY company_id, client_id, client_name, voucher_type_id, serie_number, voucher_number_since, voucher_number_to, credit_note_reference_serie, credit_note_reference_number, issue_date, payment_id, taxed_operation, unaffected_operation, exonerated_operation,igv, total ORDER BY company_id, voucher_type_id, serie_number, voucher_number_since, voucher_number_to"));
            
        $collection = collect($query);

        $collection->map(function($item) {
            $voucher_type = VoucherType::select('type')->where('id', $item->voucher_type_id)->first();
            $client = Client::select('document_number')->where('id', $item->client_id)->withTrashed()->first();
            $payment = Payment::select('name')->where('id', $item->payment_id)->first();

            $item->voucher_type = $voucher_type->type;
            $item->client_document_number = $client ? $client->document_number : '';
            $item->payment_name = $payment->name;
        });
        
        // Crear datos para la paginación de KT Datatable
        $meta = new \stdClass();
        $meta->page = 1;
        $meta->pages = 1;
        $meta->perpage = -1;
        $meta->total = $collection->count();
        $meta->field = 'voucher_id';
        for ($i = 1; $i <= $collection->count() ; $i++) {
            $meta->rowIds[] = $i;
        };
		
		return response()->json([
            'meta' => $meta,
            'data' => $collection,
        ]);
    }

    public function export() {
		$company_id = request('model.company_id');
		$since_date = date('Y-m-d', strtotime(request('model.since_date')));
		$to_date = date('Y-m-d', strtotime(request('model.to_date')));

		$query = DB::select(DB::raw("SELECT company_id, client_id, client_name, voucher_type_id, serie_number, voucher_number_since, voucher_number_to, credit_note_reference_serie, credit_note_reference_number, issue_date, payment_id, taxed_operation, unaffected_operation, exonerated_operation, igv, total FROM ( SELECT D.company_id, D.client_id, D.client_name, D.voucher_type_id, D.serie_number, D.voucher_number AS voucher_number_since, D.voucher_number AS voucher_number_to, D.credit_note_reference_serie, D.credit_note_reference_number, D.issue_date, D.payment_id, D.taxed_operation AS taxed_operation, D.unaffected_operation AS unaffected_operation, D.exonerated_operation AS exonerated_operation, D.igv AS igv, D.total AS total FROM vouchers AS D WHERE D.issue_date >= '".$since_date."' AND D.issue_date <= '".$to_date."' AND D.voucher_type_id <> 2 AND D.voucher_type_id <> 3 AND D.company_id = ".$company_id." UNION SELECT D1.company_id, D1.client_id, D1.client_name, D1.voucher_type_id, D1.serie_number, min( D1.voucher_number ) AS voucher_number_since, max( D1.voucher_number ) AS voucher_number_to, D1.credit_note_reference_serie, D1.credit_note_reference_number, D1.issue_date, D1.payment_id, sum( D1.taxed_operation ) AS taxed_operation, sum( D1.unaffected_operation ) AS unaffected_operation, sum( D1.exonerated_operation ) AS exonerated_operation, sum( D1.igv ) AS igv, sum( D1.total ) AS total FROM vouchers AS D1 WHERE D1.issue_date >= '".$since_date."' AND D1.issue_date <= '".$to_date."' AND D1.voucher_type_id = 2 AND D1.company_id = ".$company_id." GROUP BY D1.company_id, D1.client_id, D1.client_name, D1.voucher_type_id, D1.serie_number, D1.credit_note_reference_serie, D1.credit_note_reference_number, D1.issue_date, D1.payment_id UNION SELECT D2.company_id, D2.client_id, D2.client_name, D2.voucher_type_id, D2.serie_number, D2.voucher_number AS voucher_number_since, D2.voucher_number AS voucher_number_to, D2.credit_note_reference_serie, D2.credit_note_reference_number, D2.issue_date, D2.payment_id, ( D2.taxed_operation * - 1 ) AS taxed_operation, ( D2.unaffected_operation * - 1 ) AS unaffected_operation, ( D2.exonerated_operation * - 1 ) AS exonerated_operation, ( D2.igv * - 1 ) AS igv, ( D2.total * - 1 ) AS total FROM vouchers AS D2 WHERE D2.issue_date >= '".$since_date."' AND D2.issue_date <= '".$to_date."' AND D2.voucher_type_id = 3 AND D2.company_id = ".$company_id.") vouchers GROUP BY company_id, client_id, client_name, voucher_type_id, serie_number, voucher_number_since, voucher_number_to, credit_note_reference_serie, credit_note_reference_number, issue_date, payment_id, taxed_operation, unaffected_operation, exonerated_operation,igv, total ORDER BY company_id, voucher_type_id, serie_number, voucher_number_since, voucher_number_to"));
            
        $collection = collect($query);

        $collection->map(function($item) {
            $voucher_type = VoucherType::select('type')->where('id', $item->voucher_type_id)->first();
            $client = Client::select('document_number')->where('id', $item->client_id)->withTrashed()->first();
            $payment = Payment::select('name')->where('id', $item->payment_id)->first();

            $item->voucher_type = $voucher_type->type;
            $item->client_document_number = $client ? $client->document_number : '';
            $item->payment_name = $payment->name;
            if ( $item->voucher_type_id == 3 ) {
                $voucher_reference = Voucher::select('issue_date')
                    ->where('company_id', $item->company_id)
                    ->where('serie_number', $item->credit_note_reference_serie)
                    ->where('voucher_number', $item->credit_note_reference_number)
                    ->first();
                $item->credit_note_reference_date = ( $voucher_reference ? $voucher_reference->issue_date : '' );
            } else {
                $item->credit_note_reference_date = '';
            }
            
        });

		$data = new SalesReportExport($collection->toArray());
		$file = Excel::download($data, 'registro-de-ventas.xls');

		return $file;
	}
}
