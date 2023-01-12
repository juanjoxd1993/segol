<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Clasificaciones'); ?>
<?php $__env->startSection('subtitle', ''); ?>

<?php $__env->startSection('content'); ?>
	<classification-form
		:classification_types = "<?php echo e($classification_types); ?>"
		:url = "'<?php echo e(route('dashboard.classifications.validate_form')); ?>'"
	></classification-form>

	<classification-table
		:url = "'<?php echo e(route('dashboard.classifications.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.classifications.detail')); ?>'"
		:url_delete = "'<?php echo e(route('dashboard.classifications.delete')); ?>'"
	></classification-table>

	<classification-modal
		:url = "'<?php echo e(route('dashboard.classifications.store')); ?>'"
		:classification_types = "<?php echo e($classification_types); ?>"
	></classification-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/classifications.blade.php ENDPATH**/ ?>