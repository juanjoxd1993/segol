<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>PetroAmérica</title>
		<meta name="csrf-token" content="{{ csrf_token() }}">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

		<!--begin::Global Theme Styles -->
		<link href="{{ asset('backend/css/pdf.css') }}" rel="stylesheet" type="text/css" />

		<style>
			/* @page {
				size: A4;
			} */

			@page {
				size: 21cm 29.7cm;
			}
		</style>
		<!--end::Global Theme Styles -->
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body id="price-list-pdf">
		<header>
			<table class="pdf-head" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td>REPORTE LISTA DE PRECIOS VIGENTE AL {{ $current_datetime }}</td>
					</tr>
				</tbody>
			</table>
		</header>

		<main>
			<table class="pdf-items" cellspacing="0" cellpadding="0" border="0">
				<thead>
					<tr>
						<td>Compañía</td>
						<td>ID Cliente</td>
						<td>Doc. Cliente</td>
						<td>Razón Social</td>
						<td>Cód. Artículo</td>
						<td>Descripción</td>
						<td>Precio venta</td>
						<td>Vigencia desde</td>
						<td>Ruta</td>
					</tr>
				</thead>
				<tbody>
					@foreach ($elements as $detail)
					<tr>
						<td>{{ $detail->company_short_name }}</td>
						<td>{{ $detail->client_id }}</td>
						<td>{{ $detail->document_type_name }}</td>
						<td>{{ $detail->business_name }}</td>
						<td>{{ $detail->article_code }}</td>
						<td>{{ $detail->article_name }}</td>
						<td>{{ $detail->price_igv }}</td>
						<td>{{ $detail->initial_effective_date }}</td>
						<td>{{ $detail->client_route_id }}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</main>
	</body>
	<!-- end::Body -->
</html>