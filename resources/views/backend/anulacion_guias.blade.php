@extends('backend.templates.app')

@section('title', 'Operaciones')
@section('subtitle', 'Anulación de Guías')

@section('content')
    
    <anulacion-guias
        :companies = "{{ $companies }}"
        :warehouse_types = "{{ $warehouse_types }}"
    />
@endsection