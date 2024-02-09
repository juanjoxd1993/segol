@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Facturación Volumen Resumido')

@section('content')
	<facturation-total-sales-volume-report-form
	
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.facturations_total_sales_volume.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.facturations_total_sales_volume.get_clients') }}'"
	></facturation-total-sales-volume-report-form>
	
	<facturation-total-sales-volume-report-table
		:url = "'{{ route('dashboard.report.facturations_total_sales_volume.list') }}'"
	></facturation-total-sales-volume-report-table>

	<loading></loading>
@endsection