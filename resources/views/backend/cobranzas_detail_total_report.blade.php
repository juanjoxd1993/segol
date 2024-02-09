@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Cobranza Detallado Resumido')

@section('content')
	<cobranzas-detail-total-report-form
		:business_units = "{{ $business_units }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.administration.cobranzas_detail_total.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.administration.cobranzas_detail_total.get_clients') }}'"
	></cobranzas-detail-total-report-form>
	
	<cobranzas-detail-total-report-table
		:url = "'{{ route('dashboard.administration.cobranzas_detail_total.list') }}'"
	></cobranzas-detail-total-report-table>

	<loading></loading>
@endsection