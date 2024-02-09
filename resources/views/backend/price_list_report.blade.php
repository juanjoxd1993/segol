@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Comercial')
@section('subtitle', 'Reporte de Precios')

@section('content')
	<price-list-report-form
		:business_units = "{{ $business_units }}"
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.commercial.price_list_report.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.commercial.price_list_report.get_clients') }}'"
		:url_get_articles = "'{{ route('dashboard.commercial.price_list_report.get_articles') }}'"
	></price-list-report-form>

	<price-list-report-table
		:url = "'{{ route('dashboard.commercial.price_list_report.list') }}'"
		:url_export_pdf = "'{{ route('dashboard.commercial.price_list_report.export_pdf') }}'"
	></price-list-report-table>

	<loading></loading>
@endsection