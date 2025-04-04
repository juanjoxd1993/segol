@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Remesas')

@section('content')
	<liquidations-rem-report-form
		:current_date = "'{{ $current_date }}'"
		:url = "'{{ route('dashboard.report.liquidations_rem.validate_form') }}'"

	></liquidations-rem-report-form>
	
	<liquidations-rem-report-table
		:url = "'{{ route('dashboard.report.liquidations_rem.list') }}'"

	></liquidations-rem-report-table>

	<liquidations-rem-report-modal
		:url = "'{{ route('dashboard.report.liquidations_rem.update_voucher') }}'"
	></liquidations-rem-report-modal>

	<loading></loading>
@endsection