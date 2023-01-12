@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Lista de Precios')

@section('content')
	<price-list-report-form
		:business_units = "{{ $business_units }}"
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.report.price_list.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.price_list.get_clients') }}'"
		:url_get_articles = "'{{ route('dashboard.report.price_list.get_articles') }}'"
	></price-list-report-form>

	<price-list-report-table
		:url = "'{{ route('dashboard.report.price_list.list') }}'"
		:url_export_pdf = "'{{ route('dashboard.report.price_list.export_pdf') }}'"
	></price-list-report-table>

	<loading></loading>
@endsection