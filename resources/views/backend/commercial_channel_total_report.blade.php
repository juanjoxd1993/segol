@extends('backend.templates.app')

@section('title', 'Comercial')
@section('subtitle', 'Venta diaria Resumido')

@section('content')
	<commercial-channel-total-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.commercial.commercial_channel_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.commercial.commercial_channel_total.get_clients') }}'"
	></commercial-channel-total-report-form>
	
	<commercial-channel-total-report-table
		:url = "'{{ route('dashboard.commercial.commercial_channel_total.list') }}'"
	></commercial-channel-total-report-table>

	<loading></loading>
@endsection