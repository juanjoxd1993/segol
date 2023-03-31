@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Registro de compras GLP')

@section('content')
	<guides-glp-register-form
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:movement_stock_types = "{{ $movement_stock_types }}"
		:currencies = "{{ $currencies }}"
		:guide_series = "{{ $guide_series }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_types = "{{ $warehouse_types }}"
		:warehouse_providers = "{{ $warehouse_providers }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"	
		:vehicles = "{{ $vehicles }}"
		:url = "'{{ route('dashboard.operations.guides_glp_register.list') }}'"
		:url_get_accounts = "'{{ route('dashboard.operations.guides_glp_register.get_accounts') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.guides_glp_register.get_articles') }}'"
		:url_get_perception_percentage = "'{{ route('dashboard.operations.guides_glp_register.get_perception_percentage') }}'"
		:url_get_article = "'{{ route('dashboard.operations.guides_glp_register.get_article') }}'"
		:url_store = "'{{ route('dashboard.operations.guides_glp_register.store') }}'"
	></guides-glp-register-form>

	<guides-glp-register-table
		:igv = "{{ $igv }}"
		:url = "'{{ route('dashboard.operations.guides_glp_register.store') }}'"
		:url_get_article = "'{{ route('dashboard.operations.guides_glp_register.get_article') }}'"
	></guides-glp-register-table>

	<guides-glp-register-modal
		:url_get_article = "'{{ route('dashboard.operations.guides_glp_register.get_article') }}'"
	></guides-glp-register-modal>

	<loading></loading>
@endsection