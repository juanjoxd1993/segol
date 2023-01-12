@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.report.transportist_register' )
	@section('subtitle', 'Reporte de Guías Transportistas')
@endif

@if ( Route::currentRouteName() === 'dashboard.report.transportist_register_valued' )
	@section('subtitle', 'Movimiento entre Trnasportista Global')
@endif

@section('content')
	<transportist-register-report-form
		
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		
		:url = "'{{ route('dashboard.report.transportist_register.validate_form') }}'"
		
	></transportist-register-report-form>

	@if ( Route::currentRouteName() === 'dashboard.report.transportist_register' )
		<transportist-register-report-table
			:url = "'{{ route('dashboard.report.transportist_register.list') }}'"
			:url_detail = "'{{ route('dashboard.report.transportist_register.detail') }}'"
		></transportist-register-report-table>
	@endif

	@if ( Route::currentRouteName() === 'dashboard.report.transportist_register' )
		<transportist-register-report-valued-table
			:url = "'{{ route('dashboard.report.transportist_register.list') }}'"
		></transportist-register-valued-table>
	@endif

	<transportist-register-report-modal
		:url = "'{{ route('dashboard.report.transportist_register.update') }}'"
	></transportist-register-report-modal>

	<loading></loading>
@endsection