@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.report.stock_final' )
	@section('subtitle', 'Reporte de prueba Guías')
@endif

@if ( Route::currentRouteName() === 'dashboard.report.stock_register_valued' )
	@section('subtitle', 'Movimiento de Existencias Valorizado')
@endif

@section('content')
	<stock-prueba-report-form
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.report.stock_prueba.validate_form') }}'"
		:url_get_accounts = "'{{ route('dashboard.logistics.stock_register.get_accounts') }}'"
	></stock-prueba-report-form>

	@if ( Route::currentRouteName() === 'dashboard.report.stock_final' )
		<stock-prueba-report-table
			:url = "'{{ route('dashboard.report.stock_prueba.list') }}'"
			:url_detail = "'{{ route('dashboard.report.stock_prueba.detail') }}'"
		></stock-prueba-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.report.stock_final_valued' )
		<stock-prueba-report-valued-table
			:url = "'{{ route('dashboard.report.stock_prueba.list') }}'"
		></stock-prueba-report-valued-table>
	@endif

	<stock-prueba-report-modal
		:url = "'{{ route('dashboard.report.stock_prueba.update') }}'"
	></stock-prueba-report-modal>

	<loading></loading>
@endsection