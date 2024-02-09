@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Liquidación CyC')

@section('content')
	<liquidation-credits-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
        :client_routes= "{{ $client_routes }}"
		:url = "'{{ route('dashboard.credits.report.liquidations_credits.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.credits.report.liquidations_credits.get_clients') }}'"
		:url_export = "'{{ route('dashboard.credits.report.liquidations_credits.list') }}'"
	></liquidation-credits-report-form>
	
	<loading></loading>
@endsection