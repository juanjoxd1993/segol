@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Liquidaciones')

@section('content')
	<liquidation-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations.get_clients') }}'"
	></liquidation-report-form>
	
	<liquidation-report-table
		:url = "'{{ route('dashboard.report.liquidations.list') }}'"
	></liquidation-report-table>

	<loading></loading>
@endsection