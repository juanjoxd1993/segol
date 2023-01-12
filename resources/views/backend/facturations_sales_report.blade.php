@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Facturación')

@section('content')
	<facturation-sales-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.facturations_sales.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.facturations_sales.get_clients') }}'"
	></facturation-sales-report-form>
	
	<facturation-sales-report-table
		:url = "'{{ route('dashboard.report.facturations_sales.list') }}'"
	></facturation-sales-report-table>

	<loading></loading>
@endsection