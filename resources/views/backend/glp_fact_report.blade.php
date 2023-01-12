@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@if ( Route::currentRouteName() === 'dashboard.report.glp_fact' )
	@section('subtitle', 'Reporte de Facturas GLP')
@endif



@section('content')
	<glp-fact-report-form
		
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.report.glp_fact.validate_form') }}'"
	></glp-fact-report-form>

	@if ( Route::currentRouteName() === 'dashboard.report.glp_fact' )
		<glp-fact-report-table
			:url = "'{{ route('dashboard.report.glp_fact.list') }}'"
			:url_detail = "'{{ route('dashboard.report.glp_fact.detail') }}'"
		></glp-fact-report-table>
	@endif


	<glp-fact-report-modal
		:url = "'{{ route('dashboard.report.glp_fact.update') }}'"
	></glp-fact-report-modal>

	<loading></loading>
@endsection