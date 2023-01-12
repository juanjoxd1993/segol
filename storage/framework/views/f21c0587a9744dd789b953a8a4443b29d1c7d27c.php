<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Parte de Almacén'); ?>

<?php $__env->startSection('content'); ?>
	<warehouse-part-form
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:url = "'<?php echo e(route('dashboard.report.warehouse_part.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.report.warehouse_part.get_warehouse_movements')); ?>'"
	></warehouse-part-form>
	
	<warehouse-part-table
		:url = "'<?php echo e(route('dashboard.report.warehouse_part.list')); ?>'"
	></warehouse-part-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/warehouse_part.blade.php ENDPATH**/ ?>