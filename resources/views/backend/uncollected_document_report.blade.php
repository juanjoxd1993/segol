@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Relación de Documentos Emitidos')

@section('content')
	<uncollected-document-report-form
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:business_units = "{{ $business_units }}"
		:url = "'{{ route('dashboard.report.uncollected_document_report.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.uncollected_document_report.get_clients') }}'"
	></uncollected-document-report-form>

	<uncollected-document-report-table
		:url = "'{{ route('dashboard.report.uncollected_document_report.list') }}'"
	></uncollected-document-report-table>

	<loading></loading>
@endsection
