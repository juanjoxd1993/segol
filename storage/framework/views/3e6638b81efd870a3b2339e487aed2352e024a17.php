<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Liquidaciones Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-total-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations_total.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations_total.get_clients')); ?>'"
	></liquidation-total-report-form>
	
	<liquidation-total-report-table
		:url = "'<?php echo e(route('dashboard.report.liquidations_total.list')); ?>'"
	></liquidation-total-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_total_report.blade.php ENDPATH**/ ?>