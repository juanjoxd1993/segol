@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Parte de Almacén')

@section('content')
	<warehouse-part-form
		:warehouse_types = "{{ $warehouse_types }}"
		:movement_classes = "{{ $movement_classes }}"
		:url = "'{{ route('dashboard.report.warehouse_part.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.report.warehouse_part.get_warehouse_movements') }}'"
	></warehouse-part-form>
	
	<warehouse-part-table
		:url = "'{{ route('dashboard.report.warehouse_part.list') }}'"
	></warehouse-part-table>

	<loading></loading>
@endsection