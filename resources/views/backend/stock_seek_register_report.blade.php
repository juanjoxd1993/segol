@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Reportes')
@section('subtitle', 'Reporte de Guías prueba')


@section('content')
	<stock-seek-register-report-form
		
		:companies = "{{ $companies }}"
		:current_date = "'{{ $current_date }}'"
		
		:url = "'{{ route('dashboard.operations.stock_seek_register.validate_form') }}'"
		
	></stock-seek-register-report-form>

		<stock-seek-register-report-table
			:url = "'{{ route('dashboard.operations.stock_seek_register.list') }}'"
			:url_detail = "'{{ route('dashboard.operations.stock_seek_register.detail') }}'"
		></stock-seek-register-report-table>


	<stock-seek-register-report-modal
		:url = "'{{ route('dashboard.operations.stock_seek_register.update') }}'"
	></stock-seek-register-report-modal>

	<loading></loading>
@endsection