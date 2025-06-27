@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Control de Planta')
@section('subtitle', 'Apertura de Planta')

@section('content')
	<register-form-opening-planta
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:url = "'{{ route('dashboard.opening.planta_register') }}'"
	></register-form-opening-planta>

	<register-table-opening-planta
		:url_list = "'{{ route('dashboard.opening.planta_list') }}'"
	></register-table-opening-planta>

	<loading></loading>
@endsection