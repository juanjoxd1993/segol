@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Logística')
@section('subtitle', 'Registro Movimiento de Existencias')

@section('content')
	<stock-register-form
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:movement_stock_types = "{{ $movement_stock_types }}"
		:currencies = "{{ $currencies }}"
		:current_date = "'{{ $current_date }}'"
		{{-- :min_datetime = "'{{ $min_datetime }}'" --}}
		:max_datetime = "'{{ $max_datetime }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.logistics.stock_register.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.logistics.stock_register.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.logistics.stock_register.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.logistics.stock_register.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.logistics.stock_register.get_article') }}'"
		:url_store = "'{{ route('dashboard.logistics.stock_register.store') }}'"
	></stock-register-form>

	<stock-register-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.logistics.stock_register.store') }}'"
		:url_get_article = "'{{ route('dashboard.logistics.stock_register.get_article') }}'"
	></stock-register-table>

	<stock-register-modal
		:url_get_article = "'{{ route('dashboard.logistics.stock_register.get_article') }}'"
	></stock-register-modal>

	<loading></loading>
@endsection