@extends('backend.templates.app')

@section('title', 'Reporte')
@section('subtitle', 'Reporte de Ventas / Administrar Presupuestos')

@section('content')
	<budget-form
		:business_units = "{{ $business_units }}"
		:url = "'{{ route('dashboard.report.sales-report.budgets.list') }}'"
		:url_sales_report = "'{{ route('dashboard.report.sales-report') }}'"
	></budget-form>

	<budget-list
		:url = "'{{ route('dashboard.report.sales-report.budgets.store') }}'"
	></budget-list>

	<loading></loading>
@endsection