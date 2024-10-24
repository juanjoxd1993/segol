@extends('backend.templates.app')

@section('title', 'Control GLP')
@section('subtitle', 'Liquidaciones')

@section('content')
	<liquidation-final-form-glp
		:companies = "{{ $companies }}"
		:warehouses_types = "{{ $warehouse_types }}"
		:url = "'{{ route('dashboard.operations.voucher.liquidations_glp.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_warehouse_movements') }}'"
		:url_get_accounts = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_accounts') }}'"
	></liquidation-final-form-glp>

	<liquidation-final-table-glp
		:url = "'{{ route('dashboard.voucher.liquidations_glp.list') }}'"
		:url_store = "'{{ route('dashboard.operations.voucher.liquidations_glp.store') }}'"
		:url_get_glp_series = "'{{ route('dashboard.voucher.liquidations_glp.get_glp_serie') }}'"
	></liquidation-final-table-glp>

	<liquidation-final-table-sale
	></liquidation-final-table-sale>

	<liquidation-final-modal-sale-glp
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:currencies = "{{ $currencies }}"
		:url = "'{{ route('dashboard.voucher.liquidations_glp.list') }}'"
		:url_get_clients = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_clients') }}'"
		:url_get_article_price = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_article_price') }}'"
		:url_verify_document_type = "'{{ route('dashboard.operations.voucher.liquidations_glp.verify_document_type') }}'"
	></liquidation-final-modal-sale-glp>

	<liquidation-final-modal-liquidation
		:payment_methods = "{{ $payment_methods }}"
		:payments = "{{ $payments }}"
		:currencies = "{{ $currencies }}"                                
		:url_get_bank_accounts = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_bank_accounts') }}'"
		:url_get_saldo_favor = "'{{ route('dashboard.operations.voucher.liquidations_glp.get_saldo_favor') }}'"
		:payment_cash = "{{ $payment_cash }}"
		:payment_credit = "{{ $payment_credit }}"
	></liquidation-final-modal-liquidation>

	{{-- <liquidation-final-modal
		:url = "'{{ route('dashboard.operations.voucher.liquidations_glp.store') }}'"
	></liquidation-final-modal> --}}

	<loading></loading>
@endsection