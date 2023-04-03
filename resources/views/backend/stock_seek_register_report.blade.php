@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.operations.stock_seek_register' )
	@section('subtitle', 'Reporte de Guías prueba')
@endif

@if ( Route::currentRouteName() === 'dashboard.operations.stock_seek_register_valued' )
	@section('subtitle', 'Movimiento de Existencias Valorizado')
@endif

@section('content')
	<stock-seek-register-report-form
		
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		
		:url = "'{{ route('dashboard.operations.stock_seek_register.validate_form') }}'"
		
	></stock-seek-register-report-form>

	@if ( Route::currentRouteName() === 'dashboard.operations.stock_seek_register' )
		<stock-seek-register-report-table
			:url = "'{{ route('dashboard.operations.stock_seek_register.list') }}'"
			:url_detail = "'{{ route('dashboard.operations.stock_seek_register.detail') }}'"
		></stock-seek-register-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.operations.stock_seek_register' )
		<stock-seek-register-report-valued-table
			:url = "'{{ route('dashboard.operations.stock_seek_register.list') }}'"
		></stock-seek-register-valued-table>
	@endif

	<stock-seek-register-report-modal
		:url = "'{{ route('dashboard.operations.stock_seek_register.update') }}'"
	></stock-seek-register-report-modal>

	<loading></loading>
@endsection