@extends('backend.templates.app')

@section('title', 'Comercial')
@section('subtitle', 'Estado de Guías')

@section('content')
	<guides-commercial-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.commercial.guides_commercial.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.commercial.guides_commercial.get_warehouse_movements') }}'"
	></guides-commercial-report-form>
	
	<guides-commercial-report-table
		:url = "'{{ route('dashboard.commercial.guides_commercial.list') }}'"
		:url_detail = "'{{ route('dashboard.commercial.guides_commercial.detail') }}'"
	></guides-commercial-report-table>
	<guides-commercial-report-modal
		:url = "'{{ route('dashboard.commercial.guides_commercial.update') }}'"
	></guides-commercial-report-modal>

	<loading></loading>
@endsection