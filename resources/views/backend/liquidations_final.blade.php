@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Liquidaciones')

@section('content')
	<liquidation-final-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.voucher.liquidations_final.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.voucher.liquidations_final.get_warehouse_movements') }}'"
	></liquidation-final-form>
	
	<liquidation-final-table
		:url = "'{{ route('dashboard.voucher.liquidations_final.list') }}'"
		:url_store = "'{{ route('dashboard.voucher.liquidations_final.store') }}'"
	></liquidation-final-table>

	<liquidation-final-table-sale
	></liquidation-final-table-sale>

	<liquidation-final-modal-sale
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:currencies = "{{ $currencies }}"
		:url = "'{{ route('dashboard.voucher.liquidations_final.list') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.liquidations_final.get_clients') }}'"
		:url_get_article_price = "'{{ route('dashboard.voucher.liquidations_final.get_article_price') }}'"
		:url_get_sale_series = "'{{ route('dashboard.voucher.liquidations_final.get_sale_serie') }}'"
		:url_verify_document_type = "'{{ route('dashboard.voucher.liquidations_final.verify_document_type') }}'"
	></liquidation-final-modal-sale>

	<liquidation-final-modal-liquidation
		:payment_methods = "{{ $payment_methods }}"
		:payments = "{{ $payments }}"
		:currencies = "{{ $currencies }}"                                
		:url_get_bank_accounts = "'{{ route('dashboard.voucher.liquidations_final.get_bank_accounts') }}'"
		:payment_cash = "{{ $payment_cash }}"
		:payment_credit = "{{ $payment_credit }}"
	></liquidation-final-modal-liquidation>

	{{-- <liquidation-final-modal
		:url = "'{{ route('dashboard.voucher.liquidations_final.store') }}'"
	></liquidation-final-modal> --}}

	<loading></loading>
@endsection