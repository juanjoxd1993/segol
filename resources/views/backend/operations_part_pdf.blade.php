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
		<!--end::Global Theme Styles -->
	</head>
	<!-- end::Head -->

	<!-- begin::Body -->
	<body class="pdf-body warehouse-part-pdf">
		<header>
			<table class="pdf-head" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td>{{ $warehouse_movement->warehouse_type_name }}</td>
						<td style="text-align:right;"></td>
					</tr>
					<tr>
						<td>{{ $warehouse_movement->company_name }}</td>
						<td style="text-align:right;">{{ $warehouse_movement->creation_date }}</td>
					</tr>
					<tr><td colspan="2" height="16" style="font-size:0px;line-height:0;padding:0px;"></td></tr>
					<tr>
						<td colspan="2" class="pdf-head-title">PARTE DE {{ $warehouse_movement->movement_class_name }} POR {{ $warehouse_movement->movement_type_name }} Nº {{ $warehouse_movement->movement_number }}</td>
					</tr>
					<tr>
						<td class="pdf-subhead">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td>Nombre o Razón Social: {{ $warehouse_movement->account_name }}</td>
								</tr>
								<tr>
									<td>Tipo y Nro de Referencia: {{ $warehouse_movement->referral_document }}</td>
								</tr>
								<tr>
									<td>Guía Remisión: {{ $warehouse_movement->referral_guide }}</td>
								</tr>
							</table>
						</td>
						<td class="pdf-subhead">
							<table cellpadding="0" cellspacing="0" border="0">
								<tr>
									<td>{{ $warehouse_movement->account_document_name }}: {{ $warehouse_movement->account_document_number }}</td>
								</tr>
								<tr>
									<td>SCOP: {{ $warehouse_movement->scop_number }}</td>
								</tr>
								<tr>
									<td>Placa: {{ $warehouse_movement->license_plate }}</td>
								</tr>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</header>

		<footer>
			<table class="pdf-items-footer" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<tr>
						<td></td>
						<td>
							<div class="firma">Recibido por</div>
						</td>
						<td></td>
						<td>
							<div class="firma">Entregado por</div>
						</td>
						<td></td>
					</tr>
				</tbody>
			</table>
		</footer>

		<main>
			<table class="pdf-items" cellspacing="0" cellpadding="0" border="0">
				<thead>
					<tr>
						<td>Item</td>
						<td>Artículo</td>
						<td>Descripción</td>
						<td>Cantidad</td>
						<td>Precio Unit.</td>
						<td>Monto</td>
					</tr>
				</thead>
				<tbody>
					@foreach ($elements as $element)
					<tr>
						<td>{{ $loop->iteration }}</td>
						<td>{{ $element->article_code }}</td>
						<td>{{ $element->article_name }}</td>
						<td>{{ $element->converted_amount }}</td>
						<td>{{ $element->price }}</td>
						<td>{{ $element->total }}</td>
					</tr>
					@endforeach
				</tbody>
				<tfoot>
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>Total</td>
						<td>{{ $warehouse_movement->total }}</td>
					</tr>
				</tfoot>
			</table>
		</main>

		<script type="text/php">
			if ( isset($pdf) ) {
				$pdf->page_script('
					$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
					$pdf->text(488, 63, "Página $PAGE_NUM/$PAGE_COUNT", $font, 9, array(0.349, 0.3647, 0.4314));
				');
			}
		</script>
	</body>
	<!-- end::Body -->
</html>