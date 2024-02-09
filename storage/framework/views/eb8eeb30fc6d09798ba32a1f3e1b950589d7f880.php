<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Relación de Cobranzas'); ?>

<?php $__env->startSection('content'); ?>
	<collection-report-form
		:companies = "<?php echo e($companies); ?>"
		:max_datetime = "'<?php echo e($max_datetime); ?>'"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:url = "'<?php echo e(route('dashboard.report.collection_report.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.collection_report.get_clients')); ?>'"
	></collection-report-form>

	<collection-report-table
		:url = "'<?php echo e(route('dashboard.report.collection_report.list')); ?>'"
	></collection-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/collection_report.blade.php ENDPATH**/ ?>