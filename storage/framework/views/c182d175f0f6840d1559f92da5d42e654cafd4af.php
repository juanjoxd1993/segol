<?php $__env->startSection('title', 'Reporte'); ?>
<?php $__env->startSection('subtitle', 'Reporte de Ventas'); ?>

<?php $__env->startSection('content'); ?>
	<sales-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:max_date = "'<?php echo e($max_date); ?>'"
		:sale_options = "<?php echo e($sale_options); ?>"
		:url = "'<?php echo e(route('dashboard.report.sales-report.validate_form')); ?>'"
		:url_budgets = "'<?php echo e(route('dashboard.report.sales-report.budgets')); ?>'"
		:url_get_current_price = "'<?php echo e(route('dashboard.report.sales-report.get_current_price')); ?>'"
	></sales-report-form>

	<sales-report-table
		:url_list = "'<?php echo e(route('dashboard.report.sales-report.list')); ?>'"
		:url_export = "'<?php echo e(route('dashboard.report.sales-report.export')); ?>'"
	></sales-report-table>

	<sales-report-modal
		:url = "'<?php echo e(route('dashboard.report.sales-report.detail')); ?>'"
	></sales-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/sales_report.blade.php ENDPATH**/ ?>