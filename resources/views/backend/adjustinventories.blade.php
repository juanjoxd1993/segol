@extends('backend.templates.app')

@section('title', 'Ajuste de Inventario')
@section('subtitle', '')

@section('content')
	<inventory-form
		:companies = "{{ $companies }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.operations.inventories.validate_form') }}'"
	></inventory-form>

	<adjust-inventory-table
		:url = "'{{ route('dashboard.operations.inventories.list') }}'"
		:url_create_record = "'{{ route('dashboard.operations.inventories.create_record') }}'"
		:url_close_record = "'{{ route('dashboard.operations.inventories.close_record') }}'"
		:url_form_record = "'{{ route('dashboard.operations.inventories.form_record') }}'"
		:url_export_record = "'{{ route('dashboard.operations.inventories.export_record') }}'"
		:url_detail = "'{{ route('dashboard.operations.inventories.detail') }}'"
		:url_delete = "'{{ route('dashboard.operations.inventories.delete') }}'"
	></adjust-inventory-table>

	<inventory-modal
		:url = "'{{ route('dashboard.operations.inventories.store') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.inventories.get_articles') }}'"
		:url_get_select2 = "'{{ route('dashboard.operations.inventories.get_select2') }}'"
		:is_glp = "{{ true }}"
	></inventory-modal>

	<loading></loading>
@endsection