@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Venta por Canales')

@section('content')
	<liquidation-channel-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_channel.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations_channel.get_clients') }}'"
		:url_export = "'{{ route('dashboard.report.liquidations_channel.list') }}'"
	></liquidation-channel-report-form>

	<loading></loading>
@endsection