<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Relación de Clientes'); ?>

<?php $__env->startSection('content'); ?>
	<list-clients-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.report.list_clients.validate_form')); ?>'"
	></list-clients-report-form>

	<list-clients-report-table
		:url = "'<?php echo e(route('dashboard.report.list_clients.list')); ?>'"
	></list-clients-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/list_clients_report.blade.php ENDPATH**/ ?>