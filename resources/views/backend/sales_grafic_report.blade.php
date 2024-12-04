@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reporte')
@section('subtitle', 'Gráfico de Ventas')

@section('content')
	<sales-grafic-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.sales_grafic.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.sales_grafic.export') }}'"
	></sales-grafic-report-form>

	<loading></loading>

@endsection