@extends('backend.templates.app')

@section('title', 'Operaciones Ate')
@section('subtitle', 'Reporte de Stock')

@section('content')
	<stock-ate-report-form
	
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.stock_ate.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.stock_ate.get_clients') }}'"
	></stock-ate-report-form>
	
	<stock-ate-report-table
		:url = "'{{ route('dashboard.report.stock_ate.list') }}'"
	></stock-ate-report-table>

	<loading></loading>
@endsection