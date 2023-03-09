@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Control GLP')
@section('subtitle', 'Transferencia de GLP entre Almacenes')

@section('content')
	<abastecimiento-register-form
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:movement_stock_types = "{{ $movement_stock_types }}"
		:currencies = "{{ $currencies }}"
		:current_date = "'{{ $current_date }}'"
		{{-- :min_datetime = "'{{ $min_datetime }}'" --}}
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_providers = "{{ $warehouse_providers }}"
		:warehouse_receivers = "{{ $warehouse_receivers }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"	
		:url = "'{{ route('dashboard.operations.abastecimiento_register.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.operations.abastecimiento_register.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.abastecimiento_register.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.operations.abastecimiento_register.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_register.get_article') }}'"
		:url_store = "'{{ route('dashboard.operations.abastecimiento_register.store') }}'"
		:url_get_invoices = "'{{ route('dashboard.operations.abastecimiento_register.get_invoices') }}'"
	></abastecimiento-register-form>

	<abastecimiento-register-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.operations.abastecimiento_register.store') }}'"
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_register.get_article') }}'"
	></abastecimiento-register-table>

	<abastecimiento-register-modal
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_register.get_article') }}'"
	></abastecimiento-register-modal>

	<loading></loading>
@endsection