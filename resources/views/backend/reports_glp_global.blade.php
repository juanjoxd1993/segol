@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte GLP Global')

@section('content')
    <reports-glp-global-form
		:url = "'{{ route('dashboard.report.report_glp_global.get_stocks_articles') }}'"
    ></reports-glp-global-form>
    <reports-glp-global-table></reports-glp-global-table>
@endsection