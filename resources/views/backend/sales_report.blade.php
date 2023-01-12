@extends('backend.templates.app')

@section('title', 'Reporte')
@section('subtitle', 'Reporte de Ventas')

@section('content')
	<sales-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:max_date = "'{{ $max_date }}'"
		:sale_options = "{{ $sale_options }}"
		:url = "'{{ route('dashboard.report.sales-report.validate_form') }}'"
		:url_budgets = "'{{ route('dashboard.report.sales-report.budgets') }}'"
		:url_get_current_price = "'{{ route('dashboard.report.sales-report.get_current_price') }}'"
	></sales-report-form>

	<sales-report-table
		:url_list = "'{{ route('dashboard.report.sales-report.list') }}'"
		:url_export = "'{{ route('dashboard.report.sales-report.export') }}'"
	></sales-report-table>

	<sales-report-modal
		:url = "'{{ route('dashboard.report.sales-report.detail') }}'"
	></sales-report-modal>

	<loading></loading>
@endsection