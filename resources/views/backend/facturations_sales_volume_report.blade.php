@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Facturación Volumen')

@section('content')
	<facturation-sales-volume-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.facturations_sales_volume.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.facturations_sales_volume.get_clients') }}'"
	></facturation-sales-volume-report-form>
	
	<facturation-sales-volume-report-table
		:url = "'{{ route('dashboard.report.facturations_sales_volume.list') }}'"
	></facturation-sales-volume-report-table>

	<loading></loading>
@endsection