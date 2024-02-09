<table>
    <thead>
		<tr>
			<th colspan="3" style="font-size:12pt;"><?php echo e($warehouse_type->name); ?></th>
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
		<tr>
			<th colspan="3" style="font-size:12pt;"><?php echo e(date('Y/m/d H:i:s')); ?></th>
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
		</tr>
		<tr>
			<th colspan="12" style="text-align:center;font-weight:bold;font-size:14pt;">REPORTE DE INVENTARIO FÍSICO AL <?php echo e($creation_date); ?> <?php echo e(( $state == 1 ? ' - DEFINITIVO' : ' - PREVIO' )); ?></th>
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
		</tr>
    </thead>
    <tbody>
		<tr>
			<td style="font-weight:bold;">Item</td>
			<td style="font-weight:bold;">Artículo</td>
			<td style="font-weight:bold;">Descripción</td>
			<td style="font-weight:bold;">Unidad Medida</td>
			<td style="font-weight:bold;">Empaque</td>
			<td style="font-weight:bold;">Inventario Buen estado</td>
			<td style="font-weight:bold;">Kardex Buen estado</td>
			<td style="font-weight:bold;">Diferencia Buen estado</td>
			<td style="font-weight:bold;">Inventario Mal estado</td>
			<td style="font-weight:bold;">Kardex Mal estado</td>
			<td style="font-weight:bold;">Diferencia Mal estado</td>
			<td style="font-weight:bold;">Observaciones</td>
		</tr>
		<?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<tr>
			<td><?php echo e($loop->iteration); ?></td>
			<td><?php echo e($element->article_code); ?></td>
			<td><?php echo e($element->article_name); ?></td>
			<td><?php echo e($element->warehouse_unit_short_name); ?></td>
			<td><?php echo e($element->package_warehouse); ?></td>
			<td><?php echo e($element->found_stock_good); ?></td>
			<td><?php echo e($element->stock_good); ?></td>
			<td><?php echo e($element->difference_stock_good); ?></td>
			<td><?php echo e($element->found_stock_damaged); ?></td>
			<td><?php echo e($element->stock_damaged); ?></td>
			<td><?php echo e($element->difference_stock_damaged); ?></td>
			<td><?php echo e($element->observations); ?></td>
		</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH /var/www/pdd/resources/views/backend/excel/inventories.blade.php ENDPATH**/ ?>