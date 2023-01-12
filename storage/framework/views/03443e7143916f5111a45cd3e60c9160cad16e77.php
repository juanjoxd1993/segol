<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Proyecciones'); ?>

<?php $__env->startSection('content'); ?>
	<proyection-report-form
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.proyection.validate_form')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.proyection.list')); ?>'"	

	></proyection-report-form>
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/proyection_report.blade.php ENDPATH**/ ?>