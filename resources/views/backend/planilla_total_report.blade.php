@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Calculo de Planilla')

@section('content')
	<planilla-total-report-form
	
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.planilla_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.planilla_total.get_clients') }}'"
		:ciclos = "{{ $ciclos }}"
	></planilla-total-report-form>
	
	<planilla-total-report-table
		:url = "'{{ route('dashboard.report.planilla_total.list') }}'"
		:ciclos = "{{ $ciclos }}"
	></planilla-total-report-table>

	<loading></loading>
@endsection