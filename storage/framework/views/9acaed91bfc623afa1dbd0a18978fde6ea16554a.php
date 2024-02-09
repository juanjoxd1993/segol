<?php $__env->startSection('title', 'Operaciones'); ?>
<?php $__env->startSection('subtitle', 'Retorno de Guías'); ?>

<?php $__env->startSection('content'); ?>
	<guides-return-form
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.operations.guides_return.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.operations.guides_return.get_warehouse_movements')); ?>'"
	></guides-return-form>
	
	<guides-return-table
		:url = "'<?php echo e(route('dashboard.operations.guides_return.list')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.operations.guides_return.update')); ?>'"
	></guides-return-table>
		
	

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/guides_return.blade.php ENDPATH**/ ?>