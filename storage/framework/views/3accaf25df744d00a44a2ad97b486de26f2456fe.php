<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Venta por Canales Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-channel-total-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations_channel_total.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations_channel_total.get_clients')); ?>'"
	></liquidation-channel-total-report-form>
	
	<liquidation-channel-total-report-table
		:url = "'<?php echo e(route('dashboard.report.liquidations_channel_total.list')); ?>'"
	></liquidation-channel-total-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_channel_total_report.blade.php ENDPATH**/ ?>