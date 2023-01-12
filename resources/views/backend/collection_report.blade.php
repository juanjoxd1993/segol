@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Relación de Cobranzas')

@section('content')
	<collection-report-form
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:business_units = "{{ $business_units }}"
		:url = "'{{ route('dashboard.report.collection_report.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.collection_report.get_clients') }}'"
	></collection-report-form>

	<collection-report-table
		:url = "'{{ route('dashboard.report.collection_report.list') }}'"
	></collection-report-table>

	<loading></loading>
@endsection