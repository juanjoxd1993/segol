<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reporte'); ?>
<?php $__env->startSection('subtitle', 'Ventas'); ?>

<?php $__env->startSection('content'); ?>
	<sales-register-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.sales-register.validate_form')); ?>'"
	></sales-register-report-form>

	<sales-register-report-table
		:url_list = "'<?php echo e(route('dashboard.report.sales-register.list')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.sales-register.export')); ?>'"
	></sales-register-report-table>

	<loading></loading>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/sales_register_report.blade.php ENDPATH**/ ?>