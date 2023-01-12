@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Gerencia Venta diaria')

@section('content')
	<gerencia-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.gerencia.validate_form') }}'"
	></gerencia-report-form>
	
	<gerencia-report-table
		:url = "'{{ route('dashboard.report.gerencia.list') }}'"
	></gerencia-report-table>

	<loading></loading>
@endsection