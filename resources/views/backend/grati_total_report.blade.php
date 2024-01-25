@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Hoja de Trabajo Gratificación')

@section('content')
	<grati-total-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.grati_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.grati_total.get_clients') }}'"
		:ciclos = "{{ $ciclos }}"
	></grati-total-report-form>
	
	<grati-total-report-table
		:url = "'{{ route('dashboard.report.grati_total.list') }}'"
	></grati-total-report-table>

	<loading></loading>
@endsection