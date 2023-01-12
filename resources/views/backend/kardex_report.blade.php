@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@section('subtitle', 'Kardex')

@section('content')
	<kardex-form
		:warehouse_types = "{{ $warehouse_types }}"
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:warehouse_document_types = "{{ $warehouse_document_types }}"
		:url = "'{{ route('dashboard.report.kardex.validate_form') }}'"
		:url_get_articles = "'{{ route('dashboard.report.kardex.get_articles') }}'"
		:url_get_accounts = "'{{ route('dashboard.report.kardex.get_accounts') }}'"
	></kardex-form>

	<kardex-table
		:url = "'{{ route('dashboard.report.kardex.list') }}'"
	></kardex-table>

	<loading></loading>
@endsection