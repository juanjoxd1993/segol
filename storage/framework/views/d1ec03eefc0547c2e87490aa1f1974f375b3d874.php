<!DOCTYPE html>
<html lang="es">
	<!-- begin::Head -->
	<head>
		<meta charset="utf-8" />
		<title>PetroAmérica</title>
		<meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
		<meta name="description" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0, shrink-to-fit=no">

		<!--begin::Global Theme Styles -->
		<link href="<?php echo e(asset('backend/css/pdf.css')); ?>" rel="stylesheet" type="text/css" />
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
							<?php if( $obj->company_id == 1 ): ?>
								<img src="<?php echo e(asset('backend/img/logo-pdf-puntod.png')); ?>" alt="">
							<?php elseif( $obj->company_id == 2 ): ?>
								<img src="<?php echo e(asset('backend/img/logo-pdf-cordia.png')); ?>" alt="">
							<?php endif; ?>
						</div>
						<div class="info">
							<div class="business-name"><?php echo e($obj->company_name); ?></div>
							<div class="address">
								<p><?php echo e($obj->company_address); ?></p>
								<p><?php echo e($obj->company_district); ?> - <?php echo e($obj->company_province); ?> - <?php echo e($obj->company_department); ?></p>
							</div>
						</div>
					</td>

					<td>
						<div class="voucher-data">
							<p class="ruc">R.U.C. Nº <?php echo e($obj->company_document_number); ?></p>
							<p class="voucher-type"><?php echo e($obj->voucher_type_name); ?></p>
							<p class="voucher-number"><?php echo e($obj->serie_number); ?>-<?php echo e($obj->voucher_number); ?></p>
							<?php if( $obj->referral_guide_series && $obj->referral_guide_number && $obj->company_id != 2 ): ?>
								<p class="referral-guide-title">Guía de Remisión</p>
								<p class="referral-guide-number"><?php echo e($obj->referral_guide_series); ?>-<?php echo e($obj->referral_guide_number); ?></p>
							<?php endif; ?>
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
							<div class="subhead-text"><?php echo e($obj->issue_date); ?></div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<?php if( $obj->voucher_type_type == '07' ): ?>
								<div class="subhead-title">Factura Electrónica</div>
								<div class="subhead-text"><?php echo e($obj->credit_note_reference_serie); ?>-<?php echo e($obj->credit_note_reference_number); ?></div>
							<?php else: ?>
								<div class="subhead-title">Fecha de Vencimiento</div>
								<div class="subhead-text"><?php echo e($obj->expiry_date); ?></div>
							<?php endif; ?>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Tipo de Moneda</div>
							<div class="subhead-text"><?php echo e($obj->currency_name); ?></div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<?php if( $obj->voucher_type_type == '07' ): ?>
								<div class="subhead-title">Motivo</div>
								<div class="subhead-text"><?php echo e($obj->credit_note_reason_name); ?></div>
							<?php else: ?>
								<div class="subhead-title">Condición de Pago</div>
								<div class="subhead-text"><?php echo e($obj->payment_name); ?> <?php if( ( $obj->voucher_type_id = 1 && $obj->payment_id == 2 ) || ( $obj->voucher_type_id = 3 ||$obj->credit_note_reason_id == 13 ) ): ?> Cuota N.1 <?php endif; ?></div>
							<?php endif; ?>
						</div>
					</td>
				</tr>
				<tr>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Nombre/Razón Social</div>
							<div class="subhead-text"><?php echo e($obj->client_name); ?></div>
						</div>
					</td>
					<td style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title"><?php echo e($obj->client_document_name); ?></div>
							<div class="subhead-text"><?php echo e($obj->client_document_number); ?></div>
						</div>
					</td>
					<td colspan="2" style="vertical-align:top;">
						<div class="subhead-item">
							<div class="subhead-title">Dirección</div>
							<div class="subhead-text"><?php echo e($obj->client_address); ?></div>
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
					<?php $__currentLoopData = $obj->voucher_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
						<tr>
							<td><?php echo e($detail->name); ?></td>
							<td><?php echo e($detail->unit_name); ?></td>
							<td><?php echo e($detail->quantity); ?></td>
							<td><?php echo e($detail->unit_price); ?></td>
							<td><?php echo e($detail->subtotal); ?></td>
						</tr>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
											<div class="total-text-text"><?php echo e($obj->total_text); ?></div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="total-text-item">
											<div class="total-text-title">Código Hash</div>
											<div class="total-text-text"><?php echo e($document_hash); ?></div>
										</div>
									</td>
								</tr>
								<tr>
									<td>
										<div class="total-text-item">
											<img src="data:image/png;base64, <?php echo e($document_qrcode); ?>">
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
										<td><?php echo e($obj->currency_symbol); ?></td>
										<td><?php echo e($obj->taxed_operation); ?></td>
									</tr>
									<tr>
										<td>IGV (18.00)</td>
										<td><?php echo e($obj->currency_symbol); ?></td>
										<td><?php echo e($obj->igv); ?></td>
									</tr>
									<tr>
										<td>Op. Inafecta</td>
										<td><?php echo e($obj->currency_symbol); ?></td>
										<td><?php echo e($obj->unaffected_operation); ?></td>
									</tr>
									<tr>
										<td>Op. Exonerada</td>
										<td><?php echo e($obj->currency_symbol); ?></td>
										<td><?php echo e($obj->exonerated_operation); ?></td>
									</tr>
									<tr>
										<td>Importe Total</td>
										<td><?php echo e($obj->currency_symbol); ?></td>
										<td><?php echo e($obj->total); ?></td>
									</tr>
									<?php if( $obj->igv_perception && $obj->igv_perception > 0 ): ?>
										<tr>
											<td>Percepción <?php echo e($obj->igv_perception_percentage * 100); ?>%</td>
											<td><?php echo e($obj->currency_symbol); ?></td>
											<td><?php echo e($obj->igv_perception); ?></td>
										</tr>
										<tr>
											<td>Total a Pagar</td>
											<td><?php echo e($obj->currency_symbol); ?></td>
											<td><?php echo e($obj->total_perception); ?></td>
										</tr>
									<?php endif; ?>
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
<?php /**PATH /var/www/pdd/resources/views/backend/pdf_view.blade.php ENDPATH**/ ?>