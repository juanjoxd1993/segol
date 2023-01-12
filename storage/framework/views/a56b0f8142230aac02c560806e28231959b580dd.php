<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Costo GLP'); ?>

<?php $__env->startSection('content'); ?>
	<days-glp-report-form
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.days_glp.validate_form')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.days_glp.list')); ?>'"	

	></days-glp-report-form>
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/days_glp_report.blade.php ENDPATH**/ ?>