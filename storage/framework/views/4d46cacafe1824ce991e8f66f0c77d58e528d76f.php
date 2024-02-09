<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reporte'); ?>
<?php $__env->startSection('subtitle', 'Boletas Cordia Ventas'); ?>

<?php $__env->startSection('content'); ?>
	<cordia-volume-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.cordia_volume.validate_form')); ?>'"
	></cordia-volume-report-form>

	<cordia-volume-report-table
		:url_list = "'<?php echo e(route('dashboard.report.cordia_volume.list')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.cordia_volume.export')); ?>'"
	></cordia-volume-report-table>

	<loading></loading>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/cordia_volume_report.blade.php ENDPATH**/ ?>