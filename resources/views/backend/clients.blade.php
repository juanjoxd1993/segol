@extends('backend.templates.app')

@section('title', 'Clientes')
@section('subtitle', '')

@section('content')
	<client-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.voucher.clients.validate_form') }}'"
	></client-form>

	<client-table
		:url = "'{{ route('dashboard.voucher.clients.list') }}'"
		:url_detail = "'{{ route('dashboard.voucher.clients.detail') }}'"
		:url_delete = "'{{ route('dashboard.voucher.clients.delete') }}'"
	></client-table>

	<client-modal
		:url = "'{{ route('dashboard.voucher.clients.store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.voucher.clients.get_ubigeos') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.clients.get_clients') }}'"
		:url_get_select2 = "'{{ route('dashboard.voucher.clients.get_select2') }}'"
		:document_types = "{{ $document_types }}"
		:payments = "{{ $payments }}"
		:perceptions = "{{ $perceptions }}"
		:business_units = "{{ $business_units }}"
		:client_zones = "{{ $client_zones }}"
		:client_channels = "{{ $client_channels }}"
		:client_routes = "{{ $client_routes }}"
		:client_sectors = "{{ $client_sectors }}"
	></client-modal>

	<client-address-modal
		:url = "'{{ route('dashboard.voucher.clients.address_store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.voucher.clients.get_ubigeos') }}'"
		:url_address_list = "'{{ route('dashboard.voucher.clients.address_list') }}'"
		:url_address_detail = "'{{ route('dashboard.voucher.clients.address_detail') }}'"
		:url_address_delete = "'{{ route('dashboard.voucher.clients.address_delete') }}'"
		{{-- :url_get_address_select2 = "'{{ route('dashboard.voucher.clients.get_address_select2') }}'" --}}
		:address_types = "{{ $address_types }}"
	></client-address-modal>

	<client-price-list-modal
		:url = "'{{ route('dashboard.voucher.clients.price_store') }}'"
		:url_price_list = "'{{ route('dashboard.voucher.clients.price_list') }}'"
		:url_price_articles = "'{{ route('dashboard.voucher.clients.price_articles') }}'"
		:url_price_min_effective_date = "'{{ route('dashboard.voucher.clients.price_min_effective_date') }}'"
	></client-price-list-modal>

	<loading></loading>
@endsection