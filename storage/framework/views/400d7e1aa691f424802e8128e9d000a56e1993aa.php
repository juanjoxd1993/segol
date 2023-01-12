<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Impresión de guías'); ?>

<?php $__env->startSection('content'); ?>
	<operations-part-form
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:url = "'<?php echo e(route('dashboard.operations.operations_part.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.operations.operations_part.get_warehouse_movements')); ?>'"
	></operations-part-form>
	
	<operations-part-table
		:url = "'<?php echo e(route('dashboard.operations.operations_part.list')); ?>'"
	></operations-part-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/operations_part.blade.php ENDPATH**/ ?>