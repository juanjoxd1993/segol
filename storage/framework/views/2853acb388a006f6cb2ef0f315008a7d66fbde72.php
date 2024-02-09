<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Kardex'); ?>

<?php $__env->startSection('content'); ?>
	<kardex-form
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:warehouse_account_types = "<?php echo e($warehouse_account_types); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.report.kardex.validate_form')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.report.kardex.get_articles')); ?>'"
		:url_get_accounts = "'<?php echo e(route('dashboard.report.kardex.get_accounts')); ?>'"
	></kardex-form>

	<kardex-table
		:url = "'<?php echo e(route('dashboard.report.kardex.list')); ?>'"
	></kardex-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/kardex_report.blade.php ENDPATH**/ ?>