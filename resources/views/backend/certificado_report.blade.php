@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Certificado de Trabajo')

@section('content')
	<certificado-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.certificado.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.certificado.get_clients') }}'"
		:url_export = "'{{ route('dashboard.report.certificado.list') }}'"
	></certificado-report-form>

	<loading></loading>
@endsection