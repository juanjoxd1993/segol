<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Venta diaria'); ?>

<?php $__env->startSection('content'); ?>
	<commercial-channel-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.commercial.commercial_channel.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.commercial.commercial_channel.get_clients')); ?>'"
	></commercial-channel-report-form>
	
	<commercial-channel-report-table
		:url = "'<?php echo e(route('dashboard.commercial.commercial_channel.list')); ?>'"
	></commercial-channel-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/commercial_channel_report.blade.php ENDPATH**/ ?>