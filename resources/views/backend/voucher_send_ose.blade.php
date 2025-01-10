@extends('backend.templates.app')

@section('body-class', 'kt-header--fixed kt-header-mobile--fixed')
@section('title', 'Facturación')
@section('subtitle', 'Envío OSE')

@section('content')
<iframe title="SEGOL-CANT_BALONES" width="100%" height="600" 
src="https://app.powerbi.com/view?r=eyJrIjoiN2YwYzRjOTEtMmU5My00MjAyLTk1OTUtOTVmNzQ1MTM2Mzg1IiwidCI6Ijc1MDRlMzE4LThlMWUtNGQ1NS1iZmZkLTg3NWI0ZGVlODI2MCIsImMiOjR9" 
frameborder="0" allowFullScreen="true"></iframe>
@endsection