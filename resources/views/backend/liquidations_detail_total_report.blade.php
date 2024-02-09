@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Liquidación Detallado Resumido')

@section('content')
	<liquidation-detail-total-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_detail_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.liquidations_detail_total.get_clients') }}'"
	></liquidation-detail-total-report-form>
	
	<liquidation-detail-total-report-table
		:url = "'{{ route('dashboard.report.liquidations_detail_total.list') }}'"
	></liquidation-detail-total-report-table>

	<loading></loading>
@endsection