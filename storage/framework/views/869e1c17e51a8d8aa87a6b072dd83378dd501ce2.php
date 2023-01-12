<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Venta Comercial'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-sales-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations_sales.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations_sales.get_clients')); ?>'"
	></liquidation-sales-report-form>
	
	<liquidation-sales-report-table
		:url = "'<?php echo e(route('dashboard.report.liquidations_sales.list')); ?>'"
	></liquidation-sales-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_sales_report.blade.php ENDPATH**/ ?>