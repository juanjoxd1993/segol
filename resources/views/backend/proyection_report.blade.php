@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Proyecciones')

@section('content')
	<proyection-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.proyection.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.proyection.list') }}'"	

	></proyection-report-form>
	<loading></loading>
@endsection