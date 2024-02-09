@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta diaria')

@section('content')
	<days-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.days.validate_form') }}'"
	></days-report-form>
	
	<days-report-table
		:url = "'{{ route('dashboard.report.days.list') }}'"
	></days-report-table>

	<loading></loading>
@endsection