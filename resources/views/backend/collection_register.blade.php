@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Registro de Cobranzas')

@section('content')
	<collection-register-first-step
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.voucher.collection_register.validate_first_step') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.collection_register.get_clients') }}'"
	></collection-register-first-step>

	<collection-register-second-step
		:max_sale_date = "'{{ $max_sale_date }}'"
		:payment_methods = "{{ $payment_methods }}"
		:currencies = "{{ $currencies }}"
		:bank_accounts = "{{ $bank_accounts }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.voucher.collection_register.validate_second_step') }}'"
		:url_get_saldos = "'{{ route('dashboard.voucher.collection_register.get_saldos_favor') }}'"
	></collection-register-second-step>

	<collection-register-third-step
		:url = "'{{ route('dashboard.voucher.collection_register.get_sales') }}'"
		:url_store = "'{{ route('dashboard.voucher.collection_register.store') }}'"
	></collection-register-third-step>

	<collection-register-third-step-modal
	></collection-register-third-step-modal>

	<loading></loading>
	{{-- <billing-ose-table-modal></billing-ose-table-modal> --}}
@endsection