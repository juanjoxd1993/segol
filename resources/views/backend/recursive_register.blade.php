@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Gestión de Recursos del Trabajador')

@section('content')
	<recursive-form
		:companies = "{{ $companies }}"
		:areas = "{{ $areas }}"
		:url = "'{{ route('dashboard.rrhh.recursive.validate_form') }}'"
		:ciclos = "{{ $ciclos }}"
	></recursive-form>
	
	<recursive-table
		:url = "'{{ route('dashboard.rrhh.recursive.list') }}'"
	></recursive-table>

	<recursive-modal
		:url = "'{{ route('dashboard.rrhh.recursive.store') }}'"
		:url_get_min_effective_date = "'{{ route('dashboard.rrhh.recursive.get_min_effective_date') }}'"
	></recursive-modal>

	<loading></loading>
@endsection