<?php $__env->startSection('title', 'Reporte'); ?>
<?php $__env->startSection('subtitle', 'Reporte de Ventas / Administrar Presupuestos'); ?>

<?php $__env->startSection('content'); ?>
	<budget-form
		:business_units = "<?php echo e($business_units); ?>"
		:url = "'<?php echo e(route('dashboard.report.sales-report.budgets.list')); ?>'"
		:url_sales_report = "'<?php echo e(route('dashboard.report.sales-report')); ?>'"
	></budget-form>

	<budget-list
		:url = "'<?php echo e(route('dashboard.report.sales-report.budgets.store')); ?>'"
	></budget-list>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/budgets.blade.php ENDPATH**/ ?>