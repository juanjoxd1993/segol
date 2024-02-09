@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Operaciones')
@section('subtitle', 'Asignacion de Retornos')
@section('content')
    <guides-asign-return-form
        :companies = "{{ $companies }}"
        :url = "'{{ route('dashboard.operations.guides_asign_return.validate_form') }}'"
    ></guides-asign-return-form>

    <guides-asign-return-table
        :url = "'{{ route('dashboard.operations.guides_asign_return.get_warehouse_movements') }}'"
        :url_view_detail = "'{{ route('dashboard.operations.guides_asign_return.view_detail') }}'"
    ></guides-asign-return-table>

    <guides-asign-return-modal
        :url = "'{{ route('dashboard.operations.guides_asign_return.balons_press') }}'"
    ></guides-asign-return-modal>

    <loading></loading>
@endsection