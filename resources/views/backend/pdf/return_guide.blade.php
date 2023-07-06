<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>PetroAmérica</title>
		<meta name="csrf-token" content="name">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

		<!--begin::Global Theme Styles -->
		<link href="{{ asset('backend/css/pdf.css') }}" rel="stylesheet" type="text/css" />

		<style>
			@page {
				size: letter;
			}
		</style>
		<!--end::Global Theme Styles -->
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body id="guia-remision">

		<div class="justify-end grid grid-cols-header grid-rows-2 mb-6 font-bold text-11">
			<span class="text-end">Fecha Impresion: </span>
			<span>{{ $current_date }} {{ $current_time }}</span>
			<span class="text-end">Pagina: </span>
			<span>{{ $number_page }}</span>
		</div>

		<div class="w-full border border-black text-center text-base border-double mb-4" style="border: double;">
			<span>C/I -CONTROL INGRESO N° {{ $control_serie_number }} - {{ $control_serie_correlative }} - APERTURA</span>
		</div>

		<div class="w-full grid grid-cols-first-line gap-1 mb-2">
			<span class="text-xs font-bold">Nombre:</span>
			<span class="border-b border-black">{{ $name_full_data }}</span>
			<span class="text-xs font-bold">Estado:</span>
			<span class="text-xs font-bold">${{ $state }}</span>
		</div>

		<div class="w-full-100 grid grid-cols-first-line gap-1 mb-2">
			<span class="text-xs font-bold">Placa:</span>
			<span class="border-b border-black">{{ $number_placa }}</span>
			<span class="text-xs font-bold">Guia de Remisión:</span>
			<span class="text-xs font-bold border-b border-black">{{ $guide_remision }}</span>
		</div>

		<div class="w-[calc(100%-100px)] grid grid-cols-trhee-line gap-1 mb-6">
			<span class="text-xs font-bold">Fecha:</span>
			<span class="border-b border-black">{{ $current_date }}</span>
			<span class="text-xs font-bold">Hora:</span>
			<span class="text-xs font-bold border-b border-black">{{ $current_time }}</span>
			<span class="text-xs font-bold">Turno:</span>
			<span class="text-xs font-bold border-b border-black">{{ $turno }}</span>
		</div>

		<div class="w-full grid grid-cols-table auto-rows-fr text-11 text-center">
			<!-- Titulo -->
			<span class="col-span-9 col-start-4 col-end-13 text-base font-medium border-t w-full border-l border-r border-black">CONCEPTOS</span>

			<!-- Comienzo de header -->
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Ite</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Articulo</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Cantidad</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Llenos</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Vacios</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Cambios</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Transito</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Consignacion</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Devolucion</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Observador</span>
			<span class="row-start-2 w-full border-l border-t border-b border-black flex items-center justify-center font-bold">Deposito</span>
			<span class="row-start-2 w-full border-l border-t border-b border-r border-black flex items-center justify-center font-bold">TOTAL</span>
			<!-- Final de header -->

            @foreach($articles as $index => $article)
                <!-- Comienzo de body -->
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $index }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->name }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->quantity }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->return }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->vacios }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->damaged }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->transito }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->consignacion }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->devolucion }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->observador }}</span>
                <span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $article->deposito }}</span>
                <span class="w-full border-l border-b border-r border-black flex items-center justify-center font-bold">{{ $article->total }}</span>
                <!-- Final de body -->
            @endforeach

			<!-- Comienzo de footer -->
			<span class="col-start-2 w-full flex items-center justify-center font-bold">TOTALES >>></span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_quantity }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_return }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_vacios }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_damaged }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_transito }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_consignacion }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_devolucion }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_observador }}</span>
			<span class="w-full border-l border-b border-black flex items-center justify-center font-bold">{{ $total_deposito }}</span>
			<span class="w-full border-l border-b border-r border-black flex items-center justify-center font-bold">{{ $total }}</span>
			<!-- Final de footer -->
		</div>

		<div class="mt-20 justify-center grid grid-cols-footer grid-rows-2 gap-x-8">
			<span class="border-b border-black"></span>
			<span class="border-b border-black"></span>
			<span class="text-center font-bold text-sm">Jefe de Patio</span>
			<span class="text-center font-bold text-sm">Chofer</span>
		</div>

	</body>
	<!-- end::Body -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/less.js/3.12.2/less.min.js"></script>
	<script src="https://cdn.tailwindcss.com"></script>
</html>
