@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Hoja de Trabajo CTS')

@section('content')
	<cts-total-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.cts_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.cts_total.get_clients') }}'"
		:ciclos = "{{ $ciclos }}"
	></cts-total-report-form>
	
	<cts-total-report-table
		:url = "'{{ route('dashboard.report.cts_total.list') }}'"
	></cts-total-report-table>

	<loading></loading>
@endsection