@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Cuenta Corriente Clientes')

@section('content')
	<checking-account-report-form
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:business_units = "{{ $business_units }}"
		:url = "'{{ route('dashboard.report.checking_account_report.validate_form') }}'"
		:url_get_clients = "'{{ route('dashboard.report.checking_account_report.get_clients') }}'"
	></checking-account-report-form>

	<checking-account-report-table
		:url = "'{{ route('dashboard.report.checking_account_report.list') }}'"
	></checking-account-report-table>

	<loading></loading>
@endsection