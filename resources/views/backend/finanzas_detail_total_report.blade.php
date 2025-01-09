@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Liquidación Detallado Resumido')

@section('content')
	<finanzas-detail-total-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.finanzas_detail_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.finanzas_detail_total.get_clients') }}'"
	></finanzas-detail-total-report-form>
	
	<finanzas-detail-total-report-table
		:url = "'{{ route('dashboard.report.finanzas_detail_total.list') }}'"
		:url_state = "'{{ route('dashboard.report.finanzas_detail_total.get_state') }}'"
	></finanzas-detail-total-report-table>

	<loading></loading>
@endsection