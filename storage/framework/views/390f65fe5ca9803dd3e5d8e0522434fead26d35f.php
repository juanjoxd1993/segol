<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Facturación'); ?>

<?php $__env->startSection('content'); ?>
	<facturation-sales-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.facturations_sales.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.facturations_sales.get_clients')); ?>'"
	></facturation-sales-report-form>
	
	<facturation-sales-report-table
		:url = "'<?php echo e(route('dashboard.report.facturations_sales.list')); ?>'"
	></facturation-sales-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/facturations_sales_report.blade.php ENDPATH**/ ?>