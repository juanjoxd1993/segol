@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Envío OSE')

@section('content')
	<billing-ose-form
		:companies = "{{ $companies }}"
		:voucher_types = "{{ $voucher_types }}"
		:url = "'{{ route('dashboard.voucher.validate_voucher_form') }}'"
		:user_name = "'{{ $user_name }}'"
	></billing-ose-form>

	<billing-ose-table
		:url_list = "'{{ route('dashboard.voucher.list') }}'"
		:url_get_vouchers_for_table = "'{{ route('dashboard.voucher.get_vouchers_for_table') }}'"
		:url_send_voucher = "'{{ route('dashboard.voucher.send_voucher') }}'"
		:url_get_voucher_detail = "'{{ route('dashboard.voucher.get_voucher_detail') }}'"
		:user_name = "'{{ $user_name }}'"
	></billing-ose-table>

	<loading></loading>
	<billing-ose-table-modal></billing-ose-table-modal>
@endsection