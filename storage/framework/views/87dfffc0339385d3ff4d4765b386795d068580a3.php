<table>
    <thead>
		<tr>
			<th colspan="15" style="text-align:center;font-weight:bold;font-size:14pt;"><?php echo e($warehouse_type_name); ?></th>
		</tr>
		<tr>
			<th colspan="15" style="text-align:center;font-weight:bold;font-size:14pt;">REPORTE DE ARTÍCULOS AL <?php echo e($datetime); ?></th>
		</tr>
		<tr>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
			<th></th>
		</tr>
    </thead>
    <tbody>
		<tr>
			<td style="font-weight:bold;">#</td>
			<td style="font-weight:bold;">Código</td>
			<td style="font-weight:bold;">Descripción</td>
			<td style="font-weight:bold;">Unidad de Medida</td>
			<td style="font-weight:bold;"># de Empaque</td>
			<td style="font-weight:bold;">Unidad de Medida Almacén</td>
			<td style="font-weight:bold;"># de Empaque Almacén</td>
			<td style="font-weight:bold;">Stock buen estado</td>
			<td style="font-weight:bold;">Stock por reparar</td>
			<td style="font-weight:bold;">Stock por devolver</td>
			<td style="font-weight:bold;">Stock mal estado</td>
			<td style="font-weight:bold;">Valor</td>
			<td style="font-weight:bold;">Familia / Marca</td>
			<td style="font-weight:bold;">Grupo</td>
			<td style="font-weight:bold;">Subgrupo</td>
			<td style="font-weight:bold;">Ubicación</td>
		</tr>
		<?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<tr>
			<td><?php echo e($loop->iteration); ?></td>
			<td><?php echo e($element->article_code); ?></td>
			<td><?php echo e($element->article_name); ?></td>
			<td><?php echo e($element->sale_unit_name); ?></td>
			<td><?php echo e($element->package_sale); ?></td>
			<td><?php echo e($element->warehouse_unit_name); ?></td>
			<td><?php echo e($element->package_warehouse); ?></td>
			<td><?php echo e($element->stock_good); ?></td>
			<td><?php echo e($element->stock_repair); ?></td>
			<td><?php echo e($element->stock_return); ?></td>
			<td><?php echo e($element->stock_damaged); ?></td>
			<td><?php echo e($element->article_price); ?></td>
			<td><?php echo e($element->family_name); ?></td>
			<td><?php echo e($element->group_name); ?></td>
			<td><?php echo e($element->subgroup_name); ?></td>
			<td><?php echo e($element->ubication); ?></td>
		</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH /var/www/pdd/resources/views/backend/excel/articles.blade.php ENDPATH**/ ?>