@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', '´Guías')

@section('content')
	<guides-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.guides.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.report.guides.get_warehouse_movements') }}'"
	></guides-report-form>
	
	<guides-report-table
		:url = "'{{ route('dashboard.report.guides.list') }}'"
	></guides-report-table>

	<loading></loading>
@endsection