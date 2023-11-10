@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Cálculo de Pago de Gratificaciones')

@section('content')
	<grati-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.grati.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.grati.get_clients') }}'"
	></grati-report-form>
	
	<grati-report-table
		:url = "'{{ route('dashboard.report.grati.list') }}'"
	></grati-report-table>

	<loading></loading>
@endsection