@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reporte')
@section('subtitle', 'Volumen de Ventas')

@section('content')
	<sales-volume-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.sales_volume.validate_form') }}'"
	></sales-volume-report-form>

	<sales-volume-report-table
		:url_list = "'{{ route('dashboard.report.sales_volume.list') }}'"
		:url_export = "'{{ route('dashboard.report.sales_volume.export') }}'"
	></sales-volume-report-table>

	<loading></loading>
	{{-- <billing-ose-table-modal></billing-ose-table-modal> --}}
@endsection