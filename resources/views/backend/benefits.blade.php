@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Gestión de Beneficios Sociales')

@section('content')
	<benefit-form
		:companies = "{{ $companies }}"
		:areas = "{{ $areas }}"
		:url = "'{{ route('dashboard.rrhh.benefit.validate_form') }}'"
	></benefit-form>
	
	<benefit-table
		:url = "'{{ route('dashboard.rrhh.benefit.list') }}'"
	></benefit-table>

	<benefit-modal
		:url = "'{{ route('dashboard.rrhh.benefit.store') }}'"
        :benefit_types = "{{ $benefit_types}}"
		:url_get_min_effective_date = "'{{ route('dashboard.rrhh.benefit.get_min_effective_date') }}'"
	></benefit-modal>

	<loading></loading>
@endsection