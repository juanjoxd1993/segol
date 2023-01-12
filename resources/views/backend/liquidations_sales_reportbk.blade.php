@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta Comercial')

@section('content')
	<liquidation-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_sales.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations_sales.get_clients') }}'"
	></liquidation-report-form>
	
	<liquidation-report-table
		:url = "'{{ route('dashboard.report.liquidations_sales.list') }}'"
	></liquidation-report-table>

	<loading></loading>
@endsection