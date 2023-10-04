@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Gestión de Asistencia')

@section('content')
	<asistencia-form
		:companies = "{{ $companies }}"
		:articles = "{{ $articles }}"
		:client_sectors = "{{ $client_sectors }}"
		:client_channels = "{{ $client_channels }}"
		:client_routes = "{{ $client_routes }}"
		:url = "'{{ route('dashboard.rrhh.asistencia.validate_form') }}'"
	></asistencia-form>
	
	<asistencia-table
		:url = "'{{ route('dashboard.rrhh.asistencia.list') }}'"
	></asistencia-table>

	<asistencia-modal
		:url = "'{{ route('dashboard.rrhh.asistencia.store') }}'"
		:url_get_min_effective_date = "'{{ route('dashboard.rrhh.asistencia.get_min_effective_date') }}'"
	></asistencia-modal>

	<loading></loading>
@endsection