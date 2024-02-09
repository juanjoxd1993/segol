<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Proveedores'); ?>
<?php $__env->startSection('subtitle', ''); ?>

<?php $__env->startSection('content'); ?>
	<provider-form
		:url = "'<?php echo e(route('dashboard.providers.validate_form')); ?>'"
	></provider-form>

	<provider-table
		:url = "'<?php echo e(route('dashboard.providers.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.providers.detail')); ?>'"
		:url_delete = "'<?php echo e(route('dashboard.providers.delete')); ?>'"
	></provider-table>

	<provider-modal
		:url = "'<?php echo e(route('dashboard.providers.store')); ?>'"
		:url_get_ubigeos = "'<?php echo e(route('dashboard.providers.get_ubigeos')); ?>'"
		:url_get_ubigeo = "'<?php echo e(route('dashboard.providers.get_ubigeo')); ?>'"
		:document_types = "<?php echo e($document_types); ?>"
		:perceptions = "<?php echo e($perceptions); ?>"
	></provider-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/providers.blade.php ENDPATH**/ ?>