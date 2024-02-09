@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Liquidaciones Resumido')

@section('content')
	<liquidation-total-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations_total.get_clients') }}'"
	></liquidation-total-report-form>
	
	<liquidation-total-report-table
		:url = "'{{ route('dashboard.report.liquidations_total.list') }}'"
	></liquidation-total-report-table>

	<loading></loading>
@endsection