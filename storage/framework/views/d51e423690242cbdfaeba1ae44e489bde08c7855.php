<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Cuenta Corriente Clientes'); ?>

<?php $__env->startSection('content'); ?>
	<checking-account-report-form
		:companies = "<?php echo e($companies); ?>"
		:max_datetime = "'<?php echo e($max_datetime); ?>'"
		:business_units = "<?php echo e($business_units); ?>"
		:url = "'<?php echo e(route('dashboard.report.checking_account_report.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.checking_account_report.get_clients')); ?>'"
	></checking-account-report-form>

	<checking-account-report-table
		:url = "'<?php echo e(route('dashboard.report.checking_account_report.list')); ?>'"
	></checking-account-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/checking_account_report.blade.php ENDPATH**/ ?>