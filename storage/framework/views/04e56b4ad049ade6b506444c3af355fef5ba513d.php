<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Relación de Documentos Pendientes'); ?>

<?php $__env->startSection('content'); ?>
	<pending-document-report-form
		:companies = "<?php echo e($companies); ?>"
		:max_datetime = "'<?php echo e($max_datetime); ?>'"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:client_routes = "<?php echo e($client_routes); ?>"
		:url = "'<?php echo e(route('dashboard.report.pending_document_report.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.pending_document_report.get_clients')); ?>'"
	></pending-document-report-form>

	<pending-document-report-table
		:url = "'<?php echo e(route('dashboard.report.pending_document_report.list')); ?>'"
	></pending-document-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/pending_document_report.blade.php ENDPATH**/ ?>