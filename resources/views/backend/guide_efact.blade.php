@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Envío de Guías de Remisión PUNTO')

@section('content')
    <guide-efact-form 
        :companies="{{ $companies }}"
        :url="'{{ route('dashboard.guide.efact.validate_voucher_form') }}'"
        :user_name="'{{ $user_name }}'">
    </guide-efact-form>

    <guide-efact-table 
        :url_list="'{{ route('dashboard.guide.efact.list') }}'"
        :url_send_voucher="'{{ route('dashboard.guide.efact.send_voucher') }}'"
        :url_get_detail="'{{ route('dashboard.guide.efact.get_detail') }}'"
        :user_name="'{{ $user_name }}'">
    </guide-efact-table>

    <loading></loading>
    <guide-efact-table-modal></guide-efact-table-modal>
@endsection
