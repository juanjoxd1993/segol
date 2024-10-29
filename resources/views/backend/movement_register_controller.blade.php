@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Logística')
@section('subtitle', 'Registro de Producción')

@section('content')
	<movement-register-form
		:companies = "{{ $companies }}"
		:movement_classes = "{{ $movement_classes }}"
		:movement_types = "{{ $movement_types }}"
		:warehouse_types = "{{ $warehouse_types }}"
		:url = "'{{ route('dashboard.logistics.movement_register.list') }}'"
	></movement-register-form>

	<movement-register-table
		:url = "'{{ route('dashboard.logistics.movement_register.store') }}'"
	></movement-register-table>

	<movement-register-modal
		:articles = "{{ $articles }}"
	></movement-register-modal>

	<loading></loading>
@endsection