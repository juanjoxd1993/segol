@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Costo GLP')

@section('content')
	<days-glp-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.days_glp.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.days_glp.list') }}'"	

	></days-glp-report-form>
	<loading></loading>
@endsection