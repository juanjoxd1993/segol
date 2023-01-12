@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Relación de Documentos Pendientes Internos')

@section('content')
	<pending-document-int-report-form
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:business_units = "{{ $business_units }}"
		:client_routes = "{{ $client_routes }}"
		:url = "'{{ route('dashboard.report.pending_document_int_report.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.pending_document_int_report.get_clients') }}'"
	></pending-document-int-report-form>

	<pending-document-report-table
		:url = "'{{ route('dashboard.report.pending_document_int_report.list') }}'"
	></pending-document-int-report-table>

	<loading></loading>
@endsection