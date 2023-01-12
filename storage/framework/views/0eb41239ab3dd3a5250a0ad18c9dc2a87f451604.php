

<?php $__env->startSection('title', 'Importar'); ?>
<?php $__env->startSection('subtitle', 'Facturación'); ?>

<?php $__env->startSection('content'); ?>
	<facturation-import-form
		:url_post = "'<?php echo e(route('facturations.import')); ?>'"
	></facturation-import-form>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/sales_import.blade.php ENDPATH**/ ?>