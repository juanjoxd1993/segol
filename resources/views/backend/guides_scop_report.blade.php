@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Estado de Guías')

@section('content')
	<guides-scop-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.guides_scop.validate_form') }}'"
		:url_get_warehouse_movements = "'{{ route('dashboard.report.guides_scop.get_warehouse_movements') }}'"
	></guides-scop-report-form>
	
	<guides-scop-report-table
		:url = "'{{ route('dashboard.report.guides_scop.list') }}'"
		:url_detail = "'{{ route('dashboard.report.guides_scop.detail') }}'"
	></guides-scop-report-table>
	<guides-scop-report-modal
		:url = "'{{ route('dashboard.report.guides_scop.update') }}'"
	></guides-scop-report-modal>

	<loading></loading>
@endsection