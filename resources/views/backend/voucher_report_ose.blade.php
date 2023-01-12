@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Reporte OSE')

@section('content')
	<voucher-report-ose-form
		:companies = "{{ $companies }}"
		:voucher_types = "{{ $voucher_types }}"
		:current_date = "'{{ $current_date }}'"
		{{-- :url = "'{{ route('dashboard.voucher.reportOse.validateForm') }}'" --}}
		:url = "'{{ route('dashboard.voucher.reportOse.validateForm') }}'"
	></voucher-report-ose-form>

	<voucher-report-ose-table
		:url_get_vouchers_for_table = "'{{ route('dashboard.voucher.reportOse.getVouchersTable') }}'"
		:url_send_voucher = "'{{ route('dashboard.voucher.reportOse.sendVoucher') }}'"
		:url_get_voucher_detail = "'{{ route('dashboard.voucher.reportOse.getVoucherDetail') }}'"
		:user_name = "'{{ $user_name }}'"
	></voucher-report-ose-table>

	<loading></loading>
	<billing-ose-table-modal></billing-ose-table-modal>
@endsection