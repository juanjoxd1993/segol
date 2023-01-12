<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Venta por Canales'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-channel-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations_channel.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations_channel.get_clients')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.liquidations_channel.list')); ?>'"
	></liquidation-channel-report-form>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_channel_report.blade.php ENDPATH**/ ?>