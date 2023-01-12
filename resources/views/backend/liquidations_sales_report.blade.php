@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta Comercial')

@section('content')
	<liquidation-sales-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_sales.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations_sales.get_clients') }}'"
	></liquidation-sales-report-form>
	
	<liquidation-sales-report-table
		:url = "'{{ route('dashboard.report.liquidations_sales.list') }}'"
	></liquidation-sales-report-table>

	<loading></loading>
@endsection