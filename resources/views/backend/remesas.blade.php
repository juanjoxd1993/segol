@extends('backend.templates.app')

@section('title', 'Facturación')
@section('subtitle', 'Registro de Remesas')

@section('content')
  <remesa-form
    :url = "'{{ route('dashboard.facturation.voucher.remesas.store') }}'"
  ></remesa-form>

	<loading></loading>
@endsection