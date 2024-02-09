<table>
    <thead>
		<tr>
			<th colspan="32" style="text-align:center;font-weight:bold;font-size:14pt;">REPORTE DE LIQUIDACIONES DEL <?php echo e($initial_date); ?> AL <?php echo e($final_date); ?></th>
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
			<td style="font-weight:bold;">Compañía</td>
			<td style="font-weight:bold;">Fecha de Liquidación</td>
			<td style="font-weight:bold;">Fecha de Despacho</td>
			<td style="font-weight:bold;">Unidad de Negocio</td>
			<td style="font-weight:bold;">Tipo</td>
			<td style="font-weight:bold;"># Serie</td>
			<td style="font-weight:bold;"># Documento</td>
			<td style="font-weight:bold;">Varlor Venta</td>
			<td style="font-weight:bold;">IGV</td>
			<td style="font-weight:bold;">Total</td>
			<td style="font-weight:bold;">Percepción</td>
			<td style="font-weight:bold;">Total Percepción</td>
			<td style="font-weight:bold;">Condición de Pago</td>
			<td style="font-weight:bold;">Crédito</td>
			<td style="font-weight:bold;">Efectivo</td>
			<td style="font-weight:bold;">Depósito/Transferencia</td>
			<td style="font-weight:bold;">Banco</td>
			<td style="font-weight:bold;">Código del Cliente</td>
			<td style="font-weight:bold;">Razón Social</td>
			<td style="font-weight:bold;">Tipo de Doc.</td>
			<td style="font-weight:bold;"># de Doc.</td>
			<td style="font-weight:bold;"># de Parte</td>
			<td style="font-weight:bold;">Tipo Movimiento</td>
			<td style="font-weight:bold;">Guía</td>
			<td style="font-weight:bold;">Galones</td>
			<td style="font-weight:bold;">1K</td>
			<td style="font-weight:bold;">5K</td>
			<td style="font-weight:bold;">10K</td>
			<td style="font-weight:bold;">15K</td>
			<td style="font-weight:bold;">45K</td>
			<td style="font-weight:bold;">Total Kg.</td>
		</tr>
		<?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
		<tr>
			<td><?php echo e($loop->iteration); ?></td>
			<td><?php echo e($element->company_short_name); ?></td>
			<td><?php echo e($element->liquidation_date); ?></td>
			<td><?php echo e($element->sale_date); ?></td>
			<td><?php echo e($element->business_unit_name); ?></td>
			<td><?php echo e($element->warehouse_document_type_short_name); ?></td>
			<td><?php echo e($element->referral_serie_number); ?></td>
			<td><?php echo e($element->referral_voucher_number); ?></td>
			<td><?php echo e($element->sale_value); ?></td>
			<td><?php echo e($element->igv); ?></td>
			<td><?php echo e($element->total); ?></td>
			<td><?php echo e($element->perception); ?></td>
			<td><?php echo e($element->total_perception); ?></td>
			<td><?php echo e($element->payment_name); ?></td>
			<td><?php echo e($element->credit); ?></td>
			<td><?php echo e($element->cash_liquidation_amount); ?></td>
			<td><?php echo e($element->deposit_liquidation_amount); ?></td>
			<td><?php echo e($element->bank_short_name); ?></td>
			<td><?php echo e($element->client_code); ?></td>
			<td><?php echo e($element->client_business_name); ?></td>
			<td><?php echo e($element->document_type_name); ?></td>
			<td><?php echo e($element->client_document_number); ?></td>
			<td><?php echo e($element->warehouse_movement_movement_number); ?></td>
			<td><?php echo e($element->movement_type_name); ?></td>
			<td><?php echo e($element->guide); ?></td>
			<td><?php echo e($element->gallons); ?></td>
			<td><?php echo e($element->sum_1k); ?></td>
			<td><?php echo e($element->sum_5k); ?></td>
			<td><?php echo e($element->sum_10k); ?></td>
			<td><?php echo e($element->sum_15k); ?></td>
			<td><?php echo e($element->sum_45k); ?></td>
			<td><?php echo e($element->sum_total); ?></td>
		</tr>
		<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table><?php /**PATH /var/www/pdd/resources/views/backend/excel/liquidations.blade.php ENDPATH**/ ?>