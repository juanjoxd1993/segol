@extends('backend.templates.app')

@section('title', 'Operaciones')
@section('subtitle', 'Retorno de Guías')

@section('content')
	<guides-return-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.operations.guides_return.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.operations.guides_return.get_warehouse_movements') }}'"
	></guides-return-form>

	<guides-return-table
		:url = "'{{ route('dashboard.operations.guides_return.list') }}'"
		:url_store = "'{{ route('dashboard.operations.guides_return.update') }}'"
	></guides-return-table>

	<guides-return-table-clients
		:url_list = "'{{ route('dashboard.operations.guides_return.list') }}'"
		:url_get_clients = "'{{ route('dashboard.operations.guides_return.get_clients') }}'"
	></guides-return-table-clients>
		
	{{-- <guides-return-modal
		:url_store = "'{{ route('dashboard.operations.guides_return.update') }}'"
	></guides-return-modal> --}}

	<loading></loading>
@endsection