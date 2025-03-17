@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte de excedente')

@section('content')
	<and-excedent-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.excedent.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.excedent.list') }}'"
	></and-excedent-report-form>

	<loading></loading>
@endsection