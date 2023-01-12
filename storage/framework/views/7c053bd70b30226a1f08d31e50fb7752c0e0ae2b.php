<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', '´Guías'); ?>

<?php $__env->startSection('content'); ?>
	<guides-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.guides.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.report.guides.get_warehouse_movements')); ?>'"
	></guides-report-form>
	
	<guides-report-table
		:url = "'<?php echo e(route('dashboard.report.guides.list')); ?>'"
	></guides-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/guides_report.blade.php ENDPATH**/ ?>