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
      :url_validate = "'{{ route('dashboard.operations.guides_validate_press.validate_guides') }}'"
      :url_comodato = "'{{ route('dashboard.operations.guides_validate_press.comodato_guides') }}'"
      :url_view_detail = "'{{ route('dashboard.operations.guides_validate_press.view_detail') }}'"
    ></guides-validate-press-table>

    <guides-validate-press-modal></guides-validate-press-modal>

    <loading></loading>
@endsection