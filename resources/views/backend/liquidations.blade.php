@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Liquidaciones')

@section('content')
	<liquidation-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.voucher.liquidations.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.voucher.liquidations.get_warehouse_movements') }}'"
	></liquidation-form>
	
	<liquidation-table
		:url = "'{{ route('dashboard.voucher.liquidations.list') }}'"
		:url_store = "'{{ route('dashboard.voucher.liquidations.store') }}'"
	></liquidation-table>

	<liquidation-table-sale
	></liquidation-table-sale>

	<liquidation-modal-sale
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:currencies = "{{ $currencies }}"
		:url = "'{{ route('dashboard.voucher.liquidations.list') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.liquidations.get_clients') }}'"
		:url_get_article_price = "'{{ route('dashboard.voucher.liquidations.get_article_price') }}'"
		:url_verify_document_type = "'{{ route('dashboard.voucher.liquidations.verify_document_type') }}'"
	></liquidation-modal-sale>

	<liquidation-modal-liquidation
		:payment_methods = "{{ $payment_methods }}"
		:payments = "{{ $payments }}"
		:currencies = "{{ $currencies }}"                                
		:url_get_bank_accounts = "'{{ route('dashboard.voucher.liquidations.get_bank_accounts') }}'"
		:payment_cash = "{{ $payment_cash }}"
		:payment_credit = "{{ $payment_credit }}"
	></liquidation-modal-liquidation>

	{{-- <liquidation-modal
		:url = "'{{ route('dashboard.voucher.liquidations.store') }}'"
	></liquidation-modal> --}}

	<loading></loading>
@endsection