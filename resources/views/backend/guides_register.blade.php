@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Registro de Guías de salida Envasado')

@section('content')
	<guides-register-form
		:url_get_ubigeos="'{{ route('dashboard.operations.guides_register.get_ubigeos') }}'"
		:url_get_employees="'{{ route('dashboard.operations.guides_register.get_employees') }}'"
		:url_get_series="'{{ route('dashboard.operations.guides_register.get_series') }}'"
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:movement_stock_types = "{{ $movement_stock_types }}"
		:currencies = "{{ $currencies }}"
		:client_routes = "{{ $client_routes }}"
		:guide_series = "{{ $guide_series }}"
		:current_date = "'{{ $current_date }}'"
		:online = "'{{ $online }}'"
		{{-- :min_datetime = "'{{ $min_datetime }}'" --}}
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"	
		:vehicles = "{{ $vehicles }}"
		:url = "'{{ route('dashboard.operations.guides_register.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.operations.guides_register.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.guides_register.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.operations.guides_register.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.operations.guides_register.get_article') }}'"
		:url_store = "'{{ route('dashboard.operations.guides_register.store') }}'"
		:url_next_correlative = "'{{ route('dashboard.operations.guides_register.next_correlative') }}'"
	></guides-register-form>

	<guides-register-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.operations.guides_register.store') }}'"
		:url_get_article = "'{{ route('dashboard.operations.guides_register.get_article') }}'"
	></guides-register-table>

	<guides-register-modal
		:url_get_article = "'{{ route('dashboard.operations.guides_register.get_article') }}'"
	></guides-register-modal>

	<loading></loading>
@endsection