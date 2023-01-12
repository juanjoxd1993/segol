<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Lista de Precios'); ?>

<?php $__env->startSection('content'); ?>
	<price-list-report-form
		:business_units = "<?php echo e($business_units); ?>"
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.report.price_list.validate_form')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.report.price_list.get_clients')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.report.price_list.get_articles')); ?>'"
	></price-list-report-form>

	<price-list-report-table
		:url = "'<?php echo e(route('dashboard.report.price_list.list')); ?>'"
		:url_export_pdf = "'<?php echo e(route('dashboard.report.price_list.export_pdf')); ?>'"
	></price-list-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/price_list_report.blade.php ENDPATH**/ ?>