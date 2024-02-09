@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Logística')
@section('subtitle', 'Registro Movimiento de Existencias')

@section('content')
	<stock-register-beta-form
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:movement_stock_types = "{{ $movement_stock_types }}"
		:currencies = "{{ $currencies }}"
		:client_routes = "{{ $client_routes }}"
		:guide_series = "{{ $guide_series }}"
		:current_date = "'{{ $current_date }}'"
		{{-- :min_datetime = "'{{ $min_datetime }}'" --}}
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"	
		:url = "'{{ route('dashboard.logistics.stock_register_beta.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.logistics.stock_register_beta.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.logistics.stock_register_beta.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.logistics.stock_register_beta.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.logistics.stock_register_beta.get_article') }}'"
		:url_store = "'{{ route('dashboard.logistics.stock_register_beta.store') }}'"
		:url_next_correlative = "'{{ route('dashboard.logistics.stock_register_beta.next_correlative') }}'"
	></stock-register-beta-form>

	<stock-register-beta-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.logistics.stock_register_beta.store') }}'"
		:url_get_article = "'{{ route('dashboard.logistics.stock_register_beta.get_article') }}'"
	></stock-register-beta-table>

	<stock-register-beta-modal
		:url_get_article = "'{{ route('dashboard.logistics.stock_register_beta.get_article') }}'"
	></stock-register-beta-modal>

	<loading></loading>
@endsection