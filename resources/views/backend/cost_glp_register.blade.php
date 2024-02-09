@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Control GLP')
@section('subtitle', 'Registro Costos GLP')

@section('content')
	<cost-glp-register-form
		
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.cost_glp_register.validate_form') }}'"
		:url_store = "'{{ route('dashboard.report.cost_glp_register.store') }}'"
	></cost-glp-register-form>

	
	<loading></loading>
@endsection