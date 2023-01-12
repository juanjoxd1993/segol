<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Coberturas'); ?>

<?php $__env->startSection('content'); ?>
	<cobert-report-form
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.cobert.validate_form')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.cobert.list')); ?>'"	

	></cobert-report-form>
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/cobert_report.blade.php ENDPATH**/ ?>