<?php $__env->startSection('title', 'Comercial'); ?>
<?php $__env->startSection('subtitle', 'Estado de Guías'); ?>

<?php $__env->startSection('content'); ?>
	<guides-commercial-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.commercial.guides_commercial.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.commercial.guides_commercial.get_warehouse_movements')); ?>'"
	></guides-commercial-report-form>
	
	<guides-commercial-report-table
		:url = "'<?php echo e(route('dashboard.commercial.guides_commercial.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.commercial.guides_commercial.detail')); ?>'"
	></guides-commercial-report-table>
	<guides-commercial-report-modal
		:url = "'<?php echo e(route('dashboard.commercial.guides_commercial.update')); ?>'"
	></guides-commercial-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/guides_commercial_report.blade.php ENDPATH**/ ?>