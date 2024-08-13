@extends('backend.templates.app')

@section('body-class', '')
@section('title', 'Control GLP')
@section('subtitle', 'Guías Electrónicas')

@section('content')
    <guias-electronic-report-form 
    :companies="{{ $companies }}"
    :url="'{{ route('dashboard.guias_electronic.validar_form') }}'">
    </guias-electronic-report-form>

    <guias-electronic-report-table 
    :url="'{{ route('dashboard.guias_electronic.list') }}'"
    :url_get_detail="'{{ route('dashboard.guias_electronic.detalle') }}'"
    :url_download="'{{ route('dashboard.guias_electronic.descargar') }}'">
    </guias-electronic-report-table>

    <guias-electronic-report-modal>

    </guias-electronic-report-modal>

    <loading></loading>

@endsection
