@extends('backend.templates.app')

@section('title', 'RRHH')
@section('subtitle', 'Boletas de Planilla')

@section('content')
    <planilla-boleta-report-form
        :ciclos="{{ $ciclos }}"
        :empleados="{{ $empleados }}"
        :url = "'{{ route('dashboard.report.planilla_boleta.list') }}'"
    ></planilla-boleta-report-form>
    
    <planilla-boleta-report-table
        :ciclos="{{ $ciclos }}"
    ></planilla-boleta-report-table>

    <loading></loading>
@endsection
