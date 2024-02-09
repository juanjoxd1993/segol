@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta por rutas')

@section('content')
	<routes-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.routes.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.routes.get_clients') }}'"
	></routes-report-form>
	
	<routes-report-table
		:url = "'{{ route('dashboard.report.routes.list') }}'"
	></routes-report-table>

	<loading></loading>
@endsection