@extends('backend.templates.app')

@section('title', 'Gestión de Empleados')
@section('subtitle', '')

@section('content')
	<employee-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.rrhh.employees.validate_form') }}'"
	></employee-form>

	<employee-table
		:url = "'{{ route('dashboard.rrhh.employees.list') }}'"
		:url_detail = "'{{ route('dashboard.rrhh.employees.detail') }}'"
		:url_delete = "'{{ route('dashboard.rrhh.employees.delete') }}'"
	></employee-table>

	<employee-modal
		:url = "'{{ route('dashboard.rrhh.employees.store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.rrhh.employees.get_ubigeos') }}'"
		:url_get_clients = "'{{ route('dashboard.rrhh.employees.get_clients') }}'"
		:url_get_select2 = "'{{ route('dashboard.rrhh.employees.get_select2') }}'"
		:document_types = "{{ $document_types }}"
		:areas = "{{ $areas }}"
		:tasas = "{{ $tasas }}"
		:sctasas = "{{ $sctasas }}"
		:saludtasas = "{{ $saludtasas }}"
	></employee-modal>

	<employee-address-modal
		:url = "'{{ route('dashboard.rrhh.employees.address_store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.rrhh.employees.get_ubigeos') }}'"
		:url_address_list = "'{{ route('dashboard.rrhh.employees.address_list') }}'"
		:url_address_detail = "'{{ route('dashboard.rrhh.employees.address_detail') }}'"
		:url_address_delete = "'{{ route('dashboard.rrhh.employees.address_delete') }}'"
		{{-- :url_get_address_select2 = "'{{ route('dashboard.rrhh.employees.get_address_select2') }}'" --}}
		:address_types = "{{ $address_types }}"
	></employee-address-modal>

	<employee-price-list-modal
		:url = "'{{ route('dashboard.rrhh.employees.price_store') }}'"
		:url_price_list = "'{{ route('dashboard.rrhh.employees.price_list') }}'"
		:url_price_articles = "'{{ route('dashboard.rrhh.employees.price_articles') }}'"
		:url_price_min_effective_date = "'{{ route('dashboard.rrhh.employees.price_min_effective_date') }}'"
	></employee-price-list-modal>

	<loading></loading>
@endsection