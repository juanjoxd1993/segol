<?php $__env->startSection('title', 'Comercial'); ?>
<?php $__env->startSection('subtitle', 'Venta diaria Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<commercial-channel-total-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.commercial.commercial_channel_total.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.commercial.commercial_channel_total.get_clients')); ?>'"
	></commercial-channel-total-report-form>
	
	<commercial-channel-total-report-table
		:url = "'<?php echo e(route('dashboard.commercial.commercial_channel_total.list')); ?>'"
	></commercial-channel-total-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/commercial_channel_total_report.blade.php ENDPATH**/ ?>