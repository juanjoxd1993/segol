@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte Envases General')

@section('content')
    <reports-balones-press-form
      :url = "'{{ route('dashboard.report.report_balones_press.get_containers') }}'"
    ></reports-balones-press-form>
    <reports-balones-press-table></reports-balones-press-table>

    <loading></loading>
@endsection