@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Lista de Precios')

@section('content')
	<price-list-form
		:companies = "{{ $companies }}"
		:articles = "{{ $articles }}"
		:client_sectors = "{{ $client_sectors }}"
		:client_channels = "{{ $client_channels }}"
		:client_routes = "{{ $client_routes }}"
		:url = "'{{ route('dashboard.commercial.price_list.validate_form') }}'"
	></price-list-form>
	
	<price-list-table
		:url = "'{{ route('dashboard.commercial.price_list.list') }}'"
	></price-list-table>

	<price-list-modal
		:url = "'{{ route('dashboard.commercial.price_list.store') }}'"
		:url_get_min_effective_date = "'{{ route('dashboard.commercial.price_list.get_min_effective_date') }}'"
	></price-list-modal>

	<loading></loading>
@endsection