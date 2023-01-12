@extends('backend.templates.app')

@section('title', 'Inventario')
@section('subtitle', '')

@section('content')
	<inventory-form
		:companies = "{{ $companies }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.logistics.inventories.validate_form') }}'"
	></inventory-form>

	<inventory-table
		:url = "'{{ route('dashboard.logistics.inventories.list') }}'"
		:url_create_record = "'{{ route('dashboard.logistics.inventories.create_record') }}'"
		:url_close_record = "'{{ route('dashboard.logistics.inventories.close_record') }}'"
		:url_form_record = "'{{ route('dashboard.logistics.inventories.form_record') }}'"
		:url_export_record = "'{{ route('dashboard.logistics.inventories.export_record') }}'"
		:url_detail = "'{{ route('dashboard.logistics.inventories.detail') }}'"
		:url_delete = "'{{ route('dashboard.logistics.inventories.delete') }}'"
	></inventory-table>

	<inventory-modal
		:url = "'{{ route('dashboard.logistics.inventories.store') }}'"
		:url_get_articles = "'{{ route('dashboard.logistics.inventories.get_articles') }}'"
		:url_get_select2 = "'{{ route('dashboard.logistics.inventories.get_select2') }}'"
	></inventory-modal>

	<loading></loading>
@endsection