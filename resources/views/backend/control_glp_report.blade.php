@extends('backend.templates.app')

@section('title', 'Control GLP')
@section('subtitle', 'Reporte Detallado Movimientos Almacenes GLP')

@section('content')
	<control-glp-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:url = "'{{ route('dashboard.operations.control_glp.validate_form') }}'"
	></control-glp-report-form>
	
	<control-glp-report-table
		:url = "'{{ route('dashboard.operations.control_glp.list') }}'"
	></control-glp-report-table>

	<loading></loading>
@endsection