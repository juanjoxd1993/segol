@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte Envases General')

@section('content')
    <reports-envases-general-form
		  :url = "'{{ route('dashboard.report.report_envases_general.get_stocks_articles') }}'"
      :plantas = "{{ $plantas }}"
    ></reports-envases-general-form>
    <reports-envases-general-table></reports-envases-general-table>
@endsection