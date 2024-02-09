@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Placas')
@section('subtitle', '')

@section('content')
	<plates-form
		:url = "'{{ route('dashboard.plates.validate_form') }}'"
	></plates-form>

	<plates-table
		:url = "'{{ route('dashboard.plates.list') }}'"
		:url_detail = "'{{ route('dashboard.plates.detail') }}'"
		:url_delete = "'{{ route('dashboard.plates.delete') }}'"
	></plates-table>

	<plates-modal
		:url = "'{{ route('dashboard.plates.store') }}'"
		:url_get_ubigeos = "'{{ route('dashboard.plates.get_ubigeos') }}'"
		:url_get_ubigeo = "'{{ route('dashboard.plates.get_ubigeo') }}'"
	
	></plates-modal>

	<loading></loading>
@endsection