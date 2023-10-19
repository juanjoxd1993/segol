@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Gestión de Beneficios Sociales')

@section('content')
	<benefits-form
		:companies = "{{ $companies }}"
		:areas = "{{ $areas }}"
		:url = "'{{ route('dashboard.rrhh.benefit.validate_form') }}'"
	></benefits-form>
	
	<benefits-table
		:url = "'{{ route('dashboard.rrhh.benefit.list') }}'"
	></benefits-table>

	<benefits-modal
		:url = "'{{ route('dashboard.rrhh.benefit.store') }}'"
        :benefit_types = "{{ $benefit_types}}"
		:url_get_min_effective_date = "'{{ route('dashboard.rrhh.benefit.get_min_effective_date') }}'"
	></benefits-modal>

	<loading></loading>
@endsection