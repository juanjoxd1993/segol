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

		<style>
			/* @page  {
				size: A4;
			} */

			@page  {
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
						<td>REPORTE LISTA DE PRECIOS VIGENTE AL <?php echo e($current_datetime); ?></td>
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
					<?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<tr>
						<td><?php echo e($detail->company_short_name); ?></td>
						<td><?php echo e($detail->client_id); ?></td>
						<td><?php echo e($detail->document_type_name); ?></td>
						<td><?php echo e($detail->business_name); ?></td>
						<td><?php echo e($detail->article_code); ?></td>
						<td><?php echo e($detail->article_name); ?></td>
						<td><?php echo e($detail->price_igv); ?></td>
						<td><?php echo e($detail->initial_effective_date); ?></td>
						<td><?php echo e($detail->client_route_id); ?></td>
					</tr>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</tbody>
			</table>
		</main>
	</body>
	<!-- end::Body -->
</html><?php /**PATH /var/www/pdd/resources/views/backend/pdf/price_list_report.blade.php ENDPATH**/ ?>