@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Reportes')
@section('subtitle', 'Credito histórico')

@section('content')
	<credit-history-report-form
		:companies = "{{ $companies }}"
		:min_datetime = "'{{ $min_datetime }}'"
		:max_datetime = "'{{ $max_datetime }}'"
		:url = "'{{ route('dashboard.report.credit_history.validate_form') }}'"
	></credit-history-report-form>

	<credit-history-report-table
		:url = "'{{ route('dashboard.report.credit_history.list') }}'"
	></credit-history-report-table>

	<loading></loading>
@endsection