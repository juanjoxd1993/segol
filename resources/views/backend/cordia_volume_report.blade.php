@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reporte')
@section('subtitle', 'Boletas Cordia Ventas')

@section('content')
	<cordia-volume-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.cordia_volume.validate_form') }}'"
	></cordia-volume-report-form>

	<cordia-volume-report-table
		:url_list = "'{{ route('dashboard.report.cordia_volume.list') }}'"
		:url_export = "'{{ route('dashboard.report.cordia_volume.export') }}'"
	></cordia-volume-report-table>

	<loading></loading>
	{{-- <billing-ose-table-modal></billing-ose-table-modal> --}}
@endsection