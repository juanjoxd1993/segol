@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Clasificaciones')
@section('subtitle', '')

@section('content')
	<classification-form
		:classification_types = "{{ $classification_types }}"
		:url = "'{{ route('dashboard.classifications.validate_form') }}'"
	></classification-form>

	<classification-table
		:url = "'{{ route('dashboard.classifications.list') }}'"
		:url_detail = "'{{ route('dashboard.classifications.detail') }}'"
		:url_delete = "'{{ route('dashboard.classifications.delete') }}'"
	></classification-table>

	<classification-modal
		:url = "'{{ route('dashboard.classifications.store') }}'"
		:classification_types = "{{ $classification_types }}"
	></classification-modal>

	<loading></loading>
@endsection