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
	<body class="inventory-pdf">
		<header>
			<table class="pdf-head" cellpadding="0" cellspacing="0" border="0">
				<tbody>
					<tr>
						<td><?php echo e($warehouse_type->name); ?></td>
						<td align="right"></td>
					</tr>
					<tr>
						<td><?php echo e($company->name); ?></td>
						<td align="right"><?php echo e($creation_date); ?></td>
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
							<div class="firma">Inventariado por</div>
						</td>
						<td></td>
						<td>
							<div class="firma">Responsable de Inventario</div>
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
						<td>U.M.</td>
						<td>Empaque</td>
						<td>Cant. Buen estado</td>
						<td>Cant. Mal estado</td>
						<td>Observaciones</td>
					</tr>
				</thead>
				<tbody>
					<?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
					<tr>
						<td><?php echo e($loop->iteration); ?></td>
						<td><?php echo e($element->article_code); ?></td>
						<td><?php echo e($element->article_name); ?></td>
						<td><?php echo e($element->warehouse_unit_short_name); ?></td>
						<td><?php echo e($element->package_warehouse); ?></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
				</tbody>
			</table>
		</main>

		<script type="text/php">
			if ( isset($pdf) ) {
				$pdf->page_script('
					$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
					$pdf->text(482, 63, "Página $PAGE_NUM/$PAGE_COUNT", $font, 10, array(0.349, 0.3647, 0.4314));
				');
			}
		</script>
	</body>
	<!-- end::Body -->
</html><?php /**PATH /var/www/pdd/resources/views/backend/inventories_pdf.blade.php ENDPATH**/ ?>