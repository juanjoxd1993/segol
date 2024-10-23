@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Registro de Guías de salida Envasado')

@section('content')

	<guides-register-form
		:companies = "{{ $companies }}"
		:warehouse_account_types = "{{ $warehouse_account_types }}"
		:guide_series = "{{ $guide_series }}"
		:current_date = "'{{ $current_date }}'"
		:min_datetime = "'{{ $min_datetime }}'"
		:max_datetime = "'{{ $max_datetime }}'"
		:url = "'{{ route('dashboard.operations.guides_register.list') }}'"
		:url_get_clients = "'{{ route('dashboard.operations.guides_register.get_clients') }}'"
		:url_get_employees = "'{{ route('dashboard.operations.guides_register.get_employees') }}'"
		:url_next_correlative = "'{{ route('dashboard.operations.guides_register.next_correlative') }}'"
	></guides-register-form>

	<guides-register-table
		:url = "'{{ route('dashboard.operations.guides_register.store') }}'"
	></guides-register-table>

	<guides-register-modal
		:articles = "{{ $articles }}"
	></guides-register-modal>

	<loading></loading>
@endsection