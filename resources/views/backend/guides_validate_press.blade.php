@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Validacion de Prestamos')
@section('content')
    <guides-validate-press-form
      :companies = "{{ $companies }}"
      :url = "'{{ route('dashboard.operations.guides_validate_press.validate_form') }}'"
    ></guides-validate-press-form>

    <guides-validate-press-table
      :url = "'{{ route('dashboard.operations.guides_validate_press.get_warehouse_movements') }}'"
      :url_list = "'{{ route('dashboard.operations.guides_validate_press.list') }}'"
      :url_validate = "'{{ route('dashboard.operations.guides_validate_press.validate_guides') }}'"
    ></guides-validate-press-table>
@endsection