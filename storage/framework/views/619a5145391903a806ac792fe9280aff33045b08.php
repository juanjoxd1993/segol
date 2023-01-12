<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Boleteo'); ?>

<?php $__env->startSection('content'); ?>
	<facturation-boletas-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.facturation_boletas.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.facturation_boletas.get_clients')); ?>'"
	></facturation-boletas-report-form>
	
	<facturation-boletas-report-table
		:url = "'<?php echo e(route('dashboard.report.facturation_boletas.list')); ?>'"
	></facturation-boletas-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/facturation_boletas_report.blade.php ENDPATH**/ ?>