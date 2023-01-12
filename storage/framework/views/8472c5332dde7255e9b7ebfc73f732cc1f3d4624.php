<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Cobranza Detallado Resumido'); ?>

<?php $__env->startSection('content'); ?>
	<cobranzas-detail-total-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.administration.cobranzas_detail_total.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.administration.cobranzas_detail_total.get_clients')); ?>'"
	></cobranzas-detail-total-report-form>
	
	<cobranzas-detail-total-report-table
		:url = "'<?php echo e(route('dashboard.administration.cobranzas_detail_total.list')); ?>'"
	></cobranzas-detail-total-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/cobranzas_detail_total_report.blade.php ENDPATH**/ ?>