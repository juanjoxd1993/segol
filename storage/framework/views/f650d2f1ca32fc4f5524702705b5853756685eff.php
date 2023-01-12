<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Liquidación CyC'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-credits-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
        :client_routes= "<?php echo e($client_routes); ?>"
		:url = "'<?php echo e(route('dashboard.credits.report.liquidations_credits.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.credits.report.liquidations_credits.get_clients')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.credits.report.liquidations_credits.list')); ?>'"
	></liquidation-credits-report-form>
	
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_credits_report.blade.php ENDPATH**/ ?>