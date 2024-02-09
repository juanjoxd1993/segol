@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Boleteo')

@section('content')
	<facturation-boletas-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.facturation_boletas.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.facturation_boletas.get_clients') }}'"
	></facturation-boletas-report-form>
	
	<facturation-boletas-report-table
		:url = "'{{ route('dashboard.report.facturation_boletas.list') }}'"
	></facturation-boletas-report-table>

	<loading></loading>
@endsection