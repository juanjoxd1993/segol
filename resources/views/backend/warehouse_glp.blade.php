@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Control GLP')
@section('subtitle', 'Stocks Almacenes')

@section('content')
	<warehouse-glp-form
		:warehouse_types = "{{ $warehouse_types }}"
		:url = "'{{ route('dashboard.operations.warehouse_glp.validate_form') }}'"
	></warehouse-glp-form>

	<warehouse-glp-table
		:url = "'{{ route('dashboard.operations.warehouse_glp.list') }}'"
		:url_detail = "'{{ route('dashboard.operations.warehouse_glp.detail') }}'"
		:url_delete = "'{{ route('dashboard.operations.warehouse_glp.delete') }}'"
		:url_export_record = "'{{ route('dashboard.operations.warehouse_glp.export_record') }}'"
	></warehouse-glp-table>

	<warehouse-glp-modal
		:url = "'{{ route('dashboard.operations.warehouse_glp.store') }}'"
		:families = "{{ $families }}"
		:groups = "{{ $groups }}"
		:subgroups = "{{ $subgroups }}"
		:operations = "{{ $operations }}"
		:units = "{{ $units }}"
	></warehouse-glp-modal>

	<loading></loading>
@endsection