@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.report.stock_final' )
	@section('subtitle', 'Reporte de Guías')
@endif

@if ( Route::currentRouteName() === 'dashboard.report.stock_register_valued' )
	@section('subtitle', 'Movimiento de Existencias Valorizado')
@endif

@section('content')
	<stock-final-report-form
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.report.stock_final.validate_form') }}'"
		:url_get_accounts = "'{{ route('dashboard.logistics.stock_register.get_accounts') }}'"
	></stock-final-report-form>

	@if ( Route::currentRouteName() === 'dashboard.report.stock_final' )
		<stock-final-report-table
			:url = "'{{ route('dashboard.report.stock_final.list') }}'"
			:url_detail = "'{{ route('dashboard.report.stock_final.detail') }}'"
		></stock-final-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.report.stock_final_valued' )
		<stock-final-report-valued-table
			:url = "'{{ route('dashboard.report.stock_final.list') }}'"
		></stock-final-report-valued-table>
	@endif

	<stock-final-report-modal
		:url = "'{{ route('dashboard.report.stock_final.update') }}'"
	></stock-final-report-modal>

	<loading></loading>
@endsection