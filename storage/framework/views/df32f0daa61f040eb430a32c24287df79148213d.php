<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Estado de Guías'); ?>

<?php $__env->startSection('content'); ?>
	<guides-scop-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.guides_scop.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.report.guides_scop.get_warehouse_movements')); ?>'"
	></guides-scop-report-form>
	
	<guides-scop-report-table
		:url = "'<?php echo e(route('dashboard.report.guides_scop.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.report.guides_scop.detail')); ?>'"
	></guides-scop-report-table>
	<guides-scop-report-modal
		:url = "'<?php echo e(route('dashboard.report.guides_scop.update')); ?>'"
	></guides-scop-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/guides_scop_report.blade.php ENDPATH**/ ?>