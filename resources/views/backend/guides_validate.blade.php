@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Validacion de Guias')
@section('content')
    <guides-validate-form
        :companies = "{{ $companies }}"
		:url = "'{{ route('dashboard.operations.guides_validate.validate_form') }}'"
    ></guides-validate-form>

    <guides-validate-table
		:url = "'{{ route('dashboard.operations.guides_validate.get_warehouse_movements') }}'"
		:url_list = "'{{ route('dashboard.operations.guides_validate.list') }}'"
		:url_remove_article = "'{{ route('dashboard.operations.guides_validate.remove_article') }}'"
		:url_update_articles = "'{{ route('dashboard.operations.guides_validate.update_articles') }}'"
		:url_get_articles = "'{{ route('dashboard.operations.guides_validate.get_articles') }}'"
		:url_validate = "'{{ route('dashboard.operations.guides_validate.validate_guides') }}'"
    ></guides-validate-table>
@endsection