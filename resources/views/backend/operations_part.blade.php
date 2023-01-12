@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Impresión de guías')

@section('content')
	<operations-part-form
		:warehouse_types = "{{ $warehouse_types }}"
		:movement_classes = "{{ $movement_classes }}"
		:url = "'{{ route('dashboard.operations.operations_part.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.operations.operations_part.get_warehouse_movements') }}'"
	></operations-part-form>
	
	<operations-part-table
		:url = "'{{ route('dashboard.operations.operations_part.list') }}'"
	></operations-part-table>

	<loading></loading>
@endsection