@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Coberturas')

@section('content')
	<cobert-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.cobert.validate_form') }}'"
		:url_export = "'{{ route('dashboard.report.cobert.list') }}'"	

	></cobert-report-form>
	<loading></loading>
@endsection