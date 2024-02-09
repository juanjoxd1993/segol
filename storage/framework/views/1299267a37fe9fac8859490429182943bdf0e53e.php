<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Liquidaciones'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations.get_clients')); ?>'"
	></liquidation-report-form>
	
	<liquidation-report-table
		:url = "'<?php echo e(route('dashboard.report.liquidations.list')); ?>'"
	></liquidation-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_report.blade.php ENDPATH**/ ?>