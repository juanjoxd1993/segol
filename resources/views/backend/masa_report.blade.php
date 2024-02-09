@extends('backend.templates.app')

@section('title', 'Creditos')
@section('subtitle', 'Control de Masa')

@section('content')
	<masa-report-form
	
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.masa.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.masa.get_clients') }}'"
	></masa-report-form>
	
	<masa-report-table
		:url = "'{{ route('dashboard.report.masa.list') }}'"
	></masa-report-table>

	<loading></loading>
@endsection