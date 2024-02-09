@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Comercial')
@section('subtitle', 'Relación de Clientes')

@section('content')
	<list-clients-report-form
		:business_units = "{{ $business_units }}"
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.commercial.list_clients.validate_form') }}'"
	></list-clients-report-form>

	<list-clients-report-table
		:url = "'{{ route('dashboard.commercial.list_clients.list') }}'"
	></list-clients-report-table>

	<loading></loading>
@endsection