@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Gestión de Asistencia')

@section('content')
	<asistencia-form
		:companies = "{{ $companies }}"
		:areas = "{{ $areas }}"
		:url = "'{{ route('dashboard.rrhh.asistencia.validate_form') }}'"
		:ciclos = "{{ $ciclos }}"
	></asistencia-form>
	
	<asistencia-table
		:url = "'{{ route('dashboard.rrhh.asistencia.list') }}'"
		:asist_types = "{{ $asistTypes }}"
		:url_save = "'{{ route('dashboard.rrhh.asistencia.store') }}'"
	></asistencia-table>

	<asistencia-modal
		:url = "'{{ route('dashboard.rrhh.asistencia.store') }}'"
		:url_get_min_effective_date = "'{{ route('dashboard.rrhh.asistencia.get_min_effective_date') }}'"
	></asistencia-modal>

	<loading></loading>
@endsection