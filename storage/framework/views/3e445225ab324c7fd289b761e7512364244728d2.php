<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Liquidación Detallado Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-detail-total-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.liquidations_detail_total.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.liquidations_detail_total.get_clients')); ?>'"
	></liquidation-detail-total-report-form>
	
	<liquidation-detail-total-report-table
		:url = "'<?php echo e(route('dashboard.report.liquidations_detail_total.list')); ?>'"
	></liquidation-detail-total-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_detail_total_report.blade.php ENDPATH**/ ?>