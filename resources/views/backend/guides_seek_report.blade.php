@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Buscador de Guías')

@section('content')
	<guides-seek-report-form
		:companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.operations.guides_seek.validate_form') }}'"
	></guides-seek-report-form>
	
	<guides-seek-report-table
		:url = "'{{ route('dashboard.operations.guides_seek.list') }}'"
	></guides-seek-report-table>

	<loading></loading>
@endsection