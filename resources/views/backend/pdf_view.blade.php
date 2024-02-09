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
	<body>
		<div id="pdf">
			<table class="pdf-head" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td>
						<div class="logo">
							@if ( $obj->company_id == 1 )
								<img src="{{ asset('backend/img/logo-pdf-puntod.png') }}" alt="">
							@elseif ( $obj->company_id == 2 )
								<img src="{{ asset('backend/img/logo-pdf-cordia.png') }}" alt="">
							@endif
						</div>
						<div class="info">
							<div class="business-name">{{ $obj->company_name }}</div>
							<div class="address">
								<p>{{ $obj->company_address }}</p>
								<p>{{ $obj->company_district }} - {{ $obj->company_province }} - {{ $obj->company_department }}</p>
							</div>
						</div>
					</td>

					<td>
						<div class="voucher-data">
							<p class="ruc">R.U.C. Nº {{ $obj->company_document_number }}</p>
							<p class="voucher-type">{{ $obj->voucher_type_name }}</p>
							<p class="voucher-number">{{ $obj->serie_number }}-{{ $obj->voucher_number }}</p>
							@if ( $obj->referral_guide_series && $obj->referral_guide_number && $obj->company_id != 2 )
								<p class="referral-guide-title">Guía de Remisión</p>
								<p class="referral-guide-number">{{ $obj->referral_guide_series }}-{{ $obj->referral_guide_number }}</p>
							@endif
						</div>
					</td>
				</tr>
				<tr><td height="20" style="line-height:1;font-size:0;"></td></tr>
			</table>

			<table class="pdf-subhead" cellpadding="0" cellspacing="0" border="0">
				<tr>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Fecha de Emisión</div>
							<div class="subhead-text">{{ $obj->issue_date }}</div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							@if ( $obj->voucher_type_type == '07' )
								<div class="subhead-title">Factura Electrónica</div>
								<div class="subhead-text">{{ $obj->credit_note_reference_serie }}-{{ $obj->credit_note_reference_number }}</div>
							@else
								<div class="subhead-title">Fecha de Vencimiento</div>
								<div class="subhead-text">{{ $obj->expiry_date }}</div>
							@endif
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Tipo de Moneda</div>
							<div class="subhead-text">{{ $obj->currency_name }}</div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							@if ( $obj->voucher_type_type == '07' )
								<div class="subhead-title">Motivo</div>
								<div class="subhead-text">{{ $obj->credit_note_reason_name }}</div>
							@else
								<div class="subhead-title">Condición de Pago</div>
								<div class="subhead-text">{{ $obj->payment_name }} @if ( ( $obj->voucher_type_id = 1 && $obj->payment_id == 2 ) || ( $obj->voucher_type_id = 3 ||$obj->credit_note_reason_id == 13 ) ) Cuota N.1 @endif</div>
							@endif
						</div>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Nombre/Razón Social</div>
							<div class="subhead-text">{{ $obj->client_name }}</div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">{{ $obj->client_document_name }}</div>
							<div class="subhead-text">{{ $obj->client_document_number }}</div>
						</div>
					</td>
					<td colspan="2" style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Dirección</div>
							<div class="subhead-text">{{ $obj->client_address }}</div>
						</div>
					</td>
				</tr>
			</table>

			<table class="pdf-items" cellspacing="0" cellpadding="0" border="0">
				<thead>
					<tr>
						<td>Descripción</td>
						<td>Und.</td>
						<td>Cantidad</td>
						<td>V. Unitario</td>
						<td>V. Venta</td>
					</tr>
				</thead>
				<tbody>
					@foreach ($obj->voucher_details as $detail)
						<tr>
							<td>{{ $detail->name }}</td>
							<td>{{ $detail->unit_name }}</td>
							<td>{{ $detail->quantity }}</td>
							<td>{{ $detail->unit_price }}</td>
							<td>{{ $detail->subtotal }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<table class="pdf-items-footer" cellspacing="0" cellpadding="0" border="0">
				<tbody>
					<tr>
						<td>
							<table class="pdf-total-text" cellspacing="0" cellpadding="0" border="0">
								<tr>
									<td>
										<div class="total-text-item">
											<div class="total-text-title">SON</div>
											<div class="total-text-text">{{ $obj->total_text }}</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="total-text-item">
											<div class="total-text-title">Código Hash</div>
											<div class="total-text-text">{{ $document_hash }}</div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="total-text-item">
											<img src="data:image/png;base64, {{ $document_qrcode }}">
										</div>
									</td>
								</tr>
							</table>
						</td>
						<td align="right">
							<table class="pdf-totals" cellspacing="0" cellpadding="0" border="0" align="right">
								<tbody>
									<tr>
										<td>Op. Gravada</td>
										<td>{{ $obj->currency_symbol }}</td>
										<td>{{ $obj->taxed_operation }}</td>
									</tr>
									<tr>
										<td>IGV (18.00)</td>
										<td>{{ $obj->currency_symbol }}</td>
										<td>{{ $obj->igv }}</td>
									</tr>
									<tr>
										<td>Op. Inafecta</td>
										<td>{{ $obj->currency_symbol }}</td>
										<td>{{ $obj->unaffected_operation }}</td>
									</tr>
									<tr>
										<td>Op. Exonerada</td>
										<td>{{ $obj->currency_symbol }}</td>
										<td>{{ $obj->exonerated_operation }}</td>
									</tr>
									<tr>
										<td>Importe Total</td>
										<td>{{ $obj->currency_symbol }}</td>
										<td>{{ $obj->total }}</td>
									</tr>
									@if ( $obj->igv_perception && $obj->igv_perception > 0 )
										<tr>
											<td>Percepción {{ $obj->igv_perception_percentage * 100 }}%</td>
											<td>{{ $obj->currency_symbol }}</td>
											<td>{{ $obj->igv_perception }}</td>
										</tr>
										<tr>
											<td>Total a Pagar</td>
											<td>{{ $obj->currency_symbol }}</td>
											<td>{{ $obj->total_perception }}</td>
										</tr>
									@endif
								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
	<!-- end::Body -->
</html>
