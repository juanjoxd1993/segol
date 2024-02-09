@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte Envases General')

@section('content')
    <reports-saldos-favor-form
      :url = "'{{ route('dashboard.report.report_saldos_favor.get_saldos_favor') }}'"
      :plantas = "{{ $plantas }}"
    ></reports-saldos-favor-form>
    <reports-saldos-favor-table></reports-saldos-favor-table>

    <loading></loading>
@endsection