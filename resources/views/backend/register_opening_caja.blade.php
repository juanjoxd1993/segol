@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Control de Planta')
@section('subtitle', 'Apertura de Caja')

@section('content')
	<register-form-opening-caja
		:companies = "{{ $companies }}"
		:max_datetime = "'{{ $max_datetime }}'"
		:url = "'{{ route('dashboard.opening.caja_register') }}'"
	></register-form-opening-caja>

	<register-table-opening-caja
		:url_list = "'{{ route('dashboard.opening.caja_list') }}'"
	></register-table-opening-caja>

	<loading></loading>
@endsection