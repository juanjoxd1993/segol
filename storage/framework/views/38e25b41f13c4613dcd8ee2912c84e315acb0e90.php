<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Control GLP'); ?>
<?php $__env->startSection('subtitle', 'Registro Abastecimientos GLP'); ?>

<?php $__env->startSection('content'); ?>
	<stock-glp-register-form
		:companies = "<?php echo e($companies); ?>"
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:movement_types = "<?php echo e($movement_types); ?>"
		:movement_stock_types = "<?php echo e($movement_stock_types); ?>"
		:currencies = "<?php echo e($currencies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		
		:max_datetime = "'<?php echo e($max_datetime); ?>'"
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:warehouse_providers = "<?php echo e($warehouse_providers); ?>"
		:warehouse_receivers = "<?php echo e($warehouse_receivers); ?>"
		:warehouse_account_types = "<?php echo e($warehouse_account_types); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"	
		:url = "'<?php echo e(route('dashboard.operations.stock_glp_register.list')); ?>'"
		:url_get_accounts = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_accounts')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_articles')); ?>'"
		:url_get_perception_percentage = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_perception_percentage')); ?>'"
		:url_get_article = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_article')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.operations.stock_glp_register.store')); ?>'"
		:url_get_invoices = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_invoices')); ?>'"
	></stock-glp-register-form>

	<stock-glp-register-table
		:igv = "<?php echo e($igv); ?>"
		:url = "'<?php echo e(route('dashboard.operations.stock_glp_register.store')); ?>'"
		:url_get_article = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_article')); ?>'"
	></stock-glp-register-table>

	<stock-glp-register-modal
		:url_get_article = "'<?php echo e(route('dashboard.operations.stock_glp_register.get_article')); ?>'"
	></stock-glp-register-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/stock_glp_register.blade.php ENDPATH**/ ?>