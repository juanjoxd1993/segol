@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Empleados')
@section('subtitle', '')

@section('content')
	<employes-form
		:url = "'{{ route('dashboard.employes.validate_form') }}'"
	></employes-form>

	<employes-table
		:url = "'{{ route('dashboard.employes.list') }}'"
		:url_detail = "'{{ route('dashboard.employes.detail') }}'"
		:url_delete = "'{{ route('dashboard.employes.delete') }}'"
	></employes-table>

	<employes-modal
		:url = "'{{ route('dashboard.employes.store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.employes.get_ubigeos') }}'"
		:url_get_ubigeo = "'{{ route('dashboard.employes.get_ubigeo') }}'"
		:document_types = "{{ $document_types }}"
	></employes-modal>

	<loading></loading>
@endsection