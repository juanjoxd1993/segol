@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Proveedores')
@section('subtitle', '')

@section('content')
	<provider-form
		:url = "'{{ route('dashboard.providers.validate_form') }}'"
	></provider-form>

	<provider-table
		:url = "'{{ route('dashboard.providers.list') }}'"
		:url_detail = "'{{ route('dashboard.providers.detail') }}'"
		:url_delete = "'{{ route('dashboard.providers.delete') }}'"
	></provider-table>

	<provider-modal
		:url = "'{{ route('dashboard.providers.store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.providers.get_ubigeos') }}'"
		:url_get_ubigeo = "'{{ route('dashboard.providers.get_ubigeo') }}'"
		:document_types = "{{ $document_types }}"
		:perceptions = "{{ $perceptions }}"
	></provider-modal>

	<loading></loading>
@endsection