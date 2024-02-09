@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta diaria')

@section('content')
	<commercial-channel-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.commercial.commercial_channel.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.commercial.commercial_channel.get_clients') }}'"
	></commercial-channel-report-form>
	
	<commercial-channel-report-table
		:url = "'{{ route('dashboard.commercial.commercial_channel.list') }}'"
	></commercial-channel-report-table>

	<loading></loading>
@endsection