@extends('backend.templates.app')

@section('title', 'Productos')
@section('subtitle', '')

@section('content')
	<product-form
		:categories = "{{ $categories }}"
		:url = "'{{ route('dashboard.commercial.products.validate_form') }}'"
	></product-form>

	<product-table
		:url = "'{{ route('dashboard.commercial.products.list') }}'"
		:url_detail = "'{{ route('dashboard.commercial.products.detail') }}'"
		:url_delete = "'{{ route('dashboard.commercial.products.delete') }}'"
	></product-table>

	<product-modal
		:url = "'{{ route('dashboard.commercial.products.store') }}'"
		:url_get_clients = "'{{ route('dashboard.commercial.products.get_clients') }}'"
	></product-modal>


	<loading></loading>
@endsection