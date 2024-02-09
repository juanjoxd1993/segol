@extends('backend.templates.app')

@section('title', 'Comercial')
@section('subtitle', 'Consulta de Ventas Resumen')

@section('content')
	<liquidation-channel-total-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.commercial.liquidations_channel_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.commercial.liquidations_channel_total.get_clients') }}'"
	></liquidation-channel-total-report-form>
	
	<liquidation-channel-total-report-table
		:url = "'{{ route('dashboard.commercial.liquidations_channel_total.list') }}'"
	></liquidation-channel-total-report-table>

	<loading></loading>
@endsection