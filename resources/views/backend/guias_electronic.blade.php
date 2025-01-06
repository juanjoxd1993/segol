
@extends('backend.templates.app')

@section('title', 'Operaciones')
@section('subtitle', 'Guías de Remisión')

@section('content')
    <guide-electronic-report-form 
    :companies="{{ $companies }}"
    :url="'{{ route('dashboard.guias_electronic.validar_form') }}'">
    </guide-electronic-report-form>

    <guide-electronic-report-table 
    :url="'{{ route('dashboard.guias_electronic.list') }}'"
    :url_get_detail="'{{ route('dashboard.guias_electronic.detalle') }}'"
    :url_download="'{{ route('dashboard.guias_electronic.descargar') }}'">
    </guide-electronic-report-table>

    <guide-electronic-report-modal>
    </guide-electronic-report-modal>

    <loading></loading>

@endsection
