@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reporte')
@section('subtitle', 'Ventas')

@section('content')
	<sales-register-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.sales-register.validate_form') }}'"
	></sales-register-report-form>

	<sales-register-report-table
		:url_list = "'{{ route('dashboard.report.sales-register.list') }}'"
		:url_export = "'{{ route('dashboard.report.sales-register.export') }}'"
	></sales-register-report-table>

	<loading></loading>
	{{-- <billing-ose-table-modal></billing-ose-table-modal> --}}
@endsection