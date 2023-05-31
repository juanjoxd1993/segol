@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@section('subtitle', 'Abastecimiento de GLP')




@section('content')
	<terminals-report-form
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.terminals.validate_form') }}'"
		
	></terminals-report-form>

	<terminals-report-table
		:url = "'{{ route('dashboard.report.terminals.list') }}'"
		:url_detail = "'{{ route('dashboard.report.terminals.detail') }}'"
		:url_delete = "'{{ route('dashboard.report.terminals.delete') }}'"
	></terminals-report-table>

	<terminals-report-modal
		:url = "'{{ route('dashboard.report.terminals.update') }}'"
	></terminals-report-modal>

	<loading></loading>
@endsection