@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Cálculo de CTS')

@section('content')
	<cts-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.cts.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.cts.get_clients') }}'"
	></cts-report-form>
	
	<cts-report-table
		:url = "'{{ route('dashboard.report.cts.list') }}'"
	></cts-report-table>

	<loading></loading>
@endsection