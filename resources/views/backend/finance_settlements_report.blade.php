@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@section('subtitle', 'Finanzas y Liquidaciones')

@section('content')
	<!-- <finance-settlements-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations.get_clients') }}'"
	></finance-settlements-form> -->

	<finance-settlements-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations.get_clients') }}'"
	></finance-settlements-form>

	<finance-settlements-table
		:url = "'{{ route('dashboard.report.finance_settlements.list') }}'"
	></finance-settlements-table>


	<loading></loading>
@endsection