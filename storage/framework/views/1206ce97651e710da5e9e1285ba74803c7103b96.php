<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Facturación Volumen Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<facturation-total-sales-volume-report-form
	
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.facturations_total_sales_volume.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.facturations_total_sales_volume.get_clients')); ?>'"
	></facturation-total-sales-volume-report-form>
	
	<facturation-total-sales-volume-report-table
		:url = "'<?php echo e(route('dashboard.report.facturations_total_sales_volume.list')); ?>'"
	></facturation-total-sales-volume-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/facturations_total_sales_volume_report.blade.php ENDPATH**/ ?>