@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Generación masiva de Comprobantes')

@section('content')
	<voucher-massive-generation-form
		:companies = "{{ $companies }}"
		:business_units = "{{ $business_units }}"
		:today = "'{{ $today }}'"
		:user_name = "'{{ $user_name }}'"
		:url = "'{{ route('dashboard.voucher.massive_generation.validate_form') }}'"
	></voucher-massive-generation-form>
	
	<voucher-massive-generation-table
		:url = "'{{ route('dashboard.voucher.massive_generation.list') }}'"
	></voucher-massive-generation-table>

	<voucher-massive-generation-modal
		:voucher_types = "{{ $voucher_types }}"
		:min_datetime = "'{{ $min_datetime }}'"
		:today = "'{{ $today }}'"
		:url = "'{{ route('dashboard.voucher.massive_generation.store') }}'"
		:url_get_clients = "'{{ route('dashboard.voucher.massive_generation.get_clients') }}'"
		:url_get_articles = "'{{ route('dashboard.voucher.massive_generation.get_articles') }}'"
	></voucher-massive-generation-modal>

	<loading></loading>
@endsection