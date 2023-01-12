@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Rutas')

@section('content')
	<rutas-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.rutas.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.rutas.list') }}'"	

	></rutas-report-form>
	<loading></loading>
@endsection