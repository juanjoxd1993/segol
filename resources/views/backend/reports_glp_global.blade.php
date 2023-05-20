@extends('backend.templates.app')

@section('title', 'Reportes')
@section('subtitle', 'Reporte GLP Global')

@section('content')
    <form-general
        title_header = "Buscar"
    >
        <div slot = "body_content">hola</div>
        <form-actions
            slot = "foot_content"
            :buttons = "[
                {
                    type: 'submit',
                    class: 'btn btn-primary',
                    text: 'Buscar'
                }
            ]"
        ></form-actions>
    </form-general>
@endsection