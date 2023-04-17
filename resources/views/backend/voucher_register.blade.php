@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Créditos y Cobranzas')
@section('subtitle', 'Registro de Vouchers para pagos con depósito')

@section('content')
	<voucher-register
		:banks = "{{ $banks }}"
		:bank_accounts = "{{ $bank_accounts }}"
		:url = "'{{ route('dashboard.credits.register_voucher') }}'"
	></voucher-register>

	<loading></loading>
@endsection