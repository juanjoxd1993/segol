@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Registro de Documentos por Cobrar')

@section('content')
	<register-document-fact-first-step
		:companies = "{{ $companies }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.voucher.register_document_fact.get_voucher') }}'"
	></register-document-fact-first-step>

	<register-document-fact-second-step
		:min_sale_date = "'{{ $min_sale_date }}'"
		:max_sale_date = "'{{ $max_sale_date }}'"
		:payments = "{{ $payments }}"
		:min_expiry_date = "'{{ $min_expiry_date }}'"
		:max_expiry_date = "'{{ $max_expiry_date }}'"
		:currencies = "{{ $currencies }}"
		:business_units = "{{ $business_units }}"
		:credit_note_reasons = "{{ $credit_note_reasons }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.voucher.register_document_fact.validate_second_step') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.register_document_fact.get_clients') }}'"
	></register-document-fact-second-step>

	<register-document-fact-third-step
		:url = "'{{ route('dashboard.voucher.register_document_fact.store') }}'"
	></register-document-fact-third-step>

	<register-document-fact-third-step-modal
		:units = "{{ $units }}"
		:articles = "{{ $articles }}"
		:value_types = "{{ $value_types }}"
		:url = "'{{ route('dashboard.voucher.register_document_fact.validate_third_step') }}'"
	></register-document-fact-third-step-modal>

	<loading></loading>
	{{-- <billing-ose-table-modal></billing-ose-table-modal> --}}
@endsection