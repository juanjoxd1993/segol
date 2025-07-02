<?php

namespace App\Http\Controllers\Backend;

use App\Company;
use App\Http\Controllers\Controller;
use App\Sale;
use App\Exports\CreditHistoryReportExport;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use Maatwebsite\Excel\Facades\Excel;

class CreditHistoryReportController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name')->get();
        $min_datetime = CarbonImmutable::now()->startOfYear()->toAtomString();
        $max_datetime = CarbonImmutable::now()->toAtomString();

        return view('backend.credit_history_report')->with(compact('companies', 'min_datetime', 'max_datetime'));
    }

    public function validateForm()
    {
        $messages = [
            'date_type_id.required'                    => 'Debe seleccionar un Tipo de Fecha.',
            'initial_date.required'                    => 'Debe seleccionar una Fecha inicial.',
            'final_date.required'                    => 'Debe seleccionar una Fecha final.',
        ];

        $rules = [
            'date_type_id'                    => 'required',
            'initial_date'                    => 'required',
            'final_date'                    => 'required',
        ];

        request()->validate($rules, $messages);
        return request()->all();
    }

    public function list()
    {
        $export = request('export');

        $company_id = request('model.company_id');

        $company_ids = [];
        if ($company_id == 0) {
            $company_ids = Company::pluck('id')->toArray();
        } else {
            $company_ids = $company_id;
        }

        $date_type_id = request('model.date_type_id');
        $initial_date = CarbonImmutable::createFromDate(request('model.initial_date'));
        $final_date = CarbonImmutable::createFromDate(request('model.final_date'));



        if ($export) {

            $elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
                ->leftjoin('credit_histories', 'credit_histories.sale_id', '=', 'sales.id')
                ->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
                ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
                ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
                ->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
                ->leftjoin('payments', 'clients.payment_id', '=', 'payments.id')
                ->leftjoin('currencies', 'sales.currency_id', '=', 'currencies.id')
                ->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
                ->leftjoin('managers', 'clients.manager_id', '=', 'managers.id')
                ->when($company_ids, function ($query, $company_ids) {
                    return $query->whereIn('sales.company_id', $company_ids);
                })
                ->when($date_type_id == 1, function ($query) use ($initial_date, $final_date) {
                    return $query->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
                        ->where('sales.sale_date', '<=', $final_date->format('Y-m-d'));
                })
                ->when($date_type_id == 2, function ($query) use ($initial_date, $final_date) {
                    return $query->where('sales.expiry_date', '>=', $initial_date->startOfDay()->format('Y-m-d'))
                        ->where('sales.expiry_date', '<=', $final_date->endOfDay()->format('Y-m-d'));
                })
                ->where('sales.balance', '!=', 0)
                ->whereNotIn('sales.client_id', [1031, 427, 13326, 14072, 13783, 14269, 14274, 14294, 14328, 14329, 14258])
                ->whereNotIn('sales.warehouse_document_type_id', [1, 2, 3, 6, 9, 10, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32])
                ->select(
                    DB::Raw('DATE_FORMAT(credit_histories.created_at, "%Y-%m-%d") as fecha_cierre'),
                    'companies.short_name as company_short_name',
                    'warehouse_document_types.name as warehouse_document_type_name',
                    'sales.referral_serie_number',
                    'sales.referral_voucher_number',
                    DB::Raw('CONCAT(sales.guide_series,"-",sales.guide_number) as guia'),
                    'sales.sale_date',
                    'sales.expiry_date',
                    'clients.id as client_id',
                    DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'),
                    'clients.code as client_code',
                    'document_types.name as document_type_name',
                    'managers.name as manager',
                    'clients.document_number',
                    'clients.business_name',
                    'payments.name as payment_name',
                    'currencies.symbol as currency_symbol',
                    'credit_histories.total_perception',
                    'credit_histories.balance',
                    'credit_histories.paid',
                    'business_units.name as business_unit_name'
                )
                ->orderBy('company_short_name')
                ->orderBy('warehouse_document_type_name')
                ->orderBy('referral_serie_number')
                ->orderBy('referral_voucher_number')
                ->orderBy('sale_date')
                ->orderBy('expiry_date')
                ->get();

            $excel = new  CreditHistoryReportExport($elements);
            return Excel::download($excel, 'reporte-credito-historico.xlsx');
        } else {

            $elements = Sale::leftjoin('companies', 'sales.company_id', '=', 'companies.id')
                ->leftjoin('warehouse_document_types', 'sales.warehouse_document_type_id', '=', 'warehouse_document_types.id')
                ->leftjoin('clients', 'sales.client_id', '=', 'clients.id')
                ->leftjoin('client_routes', 'clients.route_id', '=', 'client_routes.id')
                ->leftjoin('document_types', 'clients.document_type_id', '=', 'document_types.id')
                ->leftjoin('payments', 'clients.payment_id', '=', 'payments.id')
                ->leftjoin('currencies', 'sales.currency_id', '=', 'currencies.id')
                ->leftjoin('business_units', 'clients.business_unit_id', '=', 'business_units.id')
                ->leftjoin('managers', 'clients.manager_id', '=', 'managers.id')
                ->when($company_ids, function ($query, $company_ids) {
                    return $query->whereIn('sales.company_id', $company_ids);
                })
                ->when($date_type_id == 1, function ($query) use ($initial_date, $final_date) {
                    return $query->where('sales.sale_date', '>=', $initial_date->format('Y-m-d'))
                        ->where('sales.sale_date', '<=', $final_date->format('Y-m-d'));
                })
                ->when($date_type_id == 2, function ($query) use ($initial_date, $final_date) {
                    return $query->where('sales.expiry_date', '>=', $initial_date->startOfDay()->format('Y-m-d'))
                        ->where('sales.expiry_date', '<=', $final_date->endOfDay()->format('Y-m-d'));
                })
                ->where('sales.balance', '!=', 0)
                ->whereNotIn('sales.client_id', [1031, 427, 13326, 14072, 13783, 14269, 14274, 14294, 14328, 14329, 14258])
                ->whereNotIn('sales.warehouse_document_type_id', [1, 2, 3, 6, 9, 10, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32])
                ->select(
                    'companies.short_name as company_short_name',
                    'warehouse_document_types.name as warehouse_document_type_name',
                    DB::Raw('CONCAT(sales.guide_series,"-",sales.guide_number) as guia'),
                    'sales.referral_serie_number',
                    'sales.referral_voucher_number',
                    'sales.sale_date',
                    'sales.expiry_date',
                    'clients.id as client_id',
                    DB::Raw('CONCAT("R-", client_routes.id) as client_route_id'),
                    'clients.code as client_code',
                    'document_types.name as document_type_name',
                    'clients.document_number',
                    'clients.business_name',
                    'managers.name as manager',
                    'payments.name as payment_name',
                    'currencies.symbol as currency_symbol',
                    'sales.total_perception',
                    'sales.balance',
                    'sales.paid',
                    'business_units.name as business_unit_name'
                )
                ->orderBy('company_short_name')
                ->orderBy('warehouse_document_type_name')
                ->orderBy('referral_serie_number')
                ->orderBy('referral_voucher_number')
                ->orderBy('sale_date')
                ->orderBy('expiry_date')
                ->get();

            return response()->json($elements);
        }
    }
}
