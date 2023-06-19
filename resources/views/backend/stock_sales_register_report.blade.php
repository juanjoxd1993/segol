@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.report.stock_sales_register' )
	@section('subtitle', 'Reporte de Compras GLP')
@endif

@if ( Route::currentRouteName() === 'dashboard.report.stock_seek_register_valued' )
	@section('subtitle', 'Movimiento de Existencias Valorizado')
@endif

@section('content')
	<stock-sales-register-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.stock_sales_register.validate_form') }}'"
	></stock-sales-register-report-form>

	@if ( Route::currentRouteName() === 'dashboard.report.stock_sales_register' )
		<stock-sales-register-report-table
			:url = "'{{ route('dashboard.report.stock_sales_register.list') }}'"
			:url_detail = "'{{ route('dashboard.report.stock_sales_register.detail') }}'"
			:url_validate_stock = "'{{ route('dashboard.report.stock_sales_register.validate_stock') }}'"
			:url_delete = "'{{ route('dashboard.report.stock_sales_register.delete') }}'"
		></stock-sales-register-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.report.stock_seek_register_valued' )
		<stock-sales-register-report-valued-table
			:url = "'{{ route('dashboard.report.stock_seek_register_valued.list') }}'"
		></stock-sales-register-valued-table>
	@endif

	<stock-sales-register-report-modal
		:url = "'{{ route('dashboard.report.stock_sales_register.update') }}'"
		:url_get_articles = "'{{ route('dashboard.report.stock_sales_register.get_articles') }}'"
		:url_get_warehouse_types = "'{{ route('dashboard.report.stock_sales_register.get_warehouse_type_two') }}'"
	></stock-sales-register-report-modal>

	<loading></loading>
@endsection