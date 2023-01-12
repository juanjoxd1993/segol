<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Venta diaria'); ?>

<?php $__env->startSection('content'); ?>
	<days-report-form
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.days.validate_form')); ?>'"
	></days-report-form>
	
	<days-report-table
		:url = "'<?php echo e(route('dashboard.report.days.list')); ?>'"
	></days-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/days_report.blade.php ENDPATH**/ ?>