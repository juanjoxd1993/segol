<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Gerencia Venta diaria'); ?>

<?php $__env->startSection('content'); ?>
	<gerencia-report-form
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.gerencia.validate_form')); ?>'"
	></gerencia-report-form>
	
	<gerencia-report-table
		:url = "'<?php echo e(route('dashboard.report.gerencia.list')); ?>'"
	></gerencia-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/gerencia_report.blade.php ENDPATH**/ ?>