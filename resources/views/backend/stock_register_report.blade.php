@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.logistics.report.stock_register' )
	@section('subtitle', 'Movimiento de Existencias')
@endif

@if ( Route::currentRouteName() === 'dashboard.logistics.report.stock_register_valued' )
	@section('subtitle', 'Movimiento de Existencias Valorizado')
@endif

@section('content')
	<stock-register-report-form
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.logistics.report.stock_register.validate_form') }}'"
		:url_get_accounts = "'{{ route('dashboard.logistics.stock_register.get_accounts') }}'"
	></stock-register-report-form>

	@if ( Route::currentRouteName() === 'dashboard.logistics.report.stock_register' )
		<stock-register-report-table
			:url = "'{{ route('dashboard.logistics.report.stock_register.list') }}'"
			:url_detail = "'{{ route('dashboard.logistics.report.stock_register.detail') }}'"
		></stock-register-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.logistics.report.stock_register_valued' )
		<stock-register-report-valued-table
			:url = "'{{ route('dashboard.logistics.report.stock_register.list') }}'"
		></stock-register-report-valued-table>
	@endif

	<stock-register-report-modal
		:url = "'{{ route('dashboard.logistics.report.stock_register.update') }}'"
	></stock-register-report-modal>

	<loading></loading>
@endsection