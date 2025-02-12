@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Control GLP')
@section('subtitle', 'Abastecimiento de Planta')

@section('content')
	<abastecimiento-planta-register-form
		:movement_types = "{{ $movement_types }}"
		:current_date = "'{{ $current_date }}'"
		{{-- :min_datetime = "'{{ $min_datetime }}'" --}}
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_providers = "{{ $warehouse_providers }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"	
		:url = "'{{ route('dashboard.operations.abastecimiento_planta_register.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_article') }}'"
		:url_store = "'{{ route('dashboard.operations.abastecimiento_planta_register.store') }}'"
		:url_get_invoices = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_invoices') }}'"
	></abastecimiento-planta-register-form>

	<abastecimiento-planta-register-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.operations.abastecimiento_planta_register.store') }}'"
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_article') }}'"
	></abastecimiento-planta-register-table>

	<abastecimiento-planta-register-modal
		:url_get_article = "'{{ route('dashboard.operations.abastecimiento_planta_register.get_article') }}'"
	></abastecimiento-planta-register-modal>

	<loading></loading>
@endsection