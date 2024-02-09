@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Artículos')
@section('subtitle', '')

@section('content')
	<article-form
		:warehouse_types = "{{ $warehouse_types }}"
		:url = "'{{ route('dashboard.articles.validate_form') }}'"
	></article-form>

	<article-table
		:url = "'{{ route('dashboard.articles.list') }}'"
		:url_detail = "'{{ route('dashboard.articles.detail') }}'"
		:url_delete = "'{{ route('dashboard.articles.delete') }}'"
		:url_export_record = "'{{ route('dashboard.articles.export_record') }}'"
	></article-table>

	<article-modal
		:url = "'{{ route('dashboard.articles.store') }}'"
		:families = "{{ $families }}"
		:groups = "{{ $groups }}"
		:subgroups = "{{ $subgroups }}"
		:operations = "{{ $operations }}"
		:units = "{{ $units }}"
	></article-modal>

	<loading></loading>
@endsection