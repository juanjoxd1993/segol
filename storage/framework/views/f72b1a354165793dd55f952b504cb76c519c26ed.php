<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Logística'); ?>
<?php $__env->startSection('subtitle', 'Registro Movimiento de Existencias'); ?>

<?php $__env->startSection('content'); ?>
	<stock-register-form
		:companies = "<?php echo e($companies); ?>"
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:movement_types = "<?php echo e($movement_types); ?>"
		:movement_stock_types = "<?php echo e($movement_stock_types); ?>"
		:currencies = "<?php echo e($currencies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		
		:max_datetime = "'<?php echo e($max_datetime); ?>'"
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:warehouse_account_types = "<?php echo e($warehouse_account_types); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"	
		:url = "'<?php echo e(route('dashboard.logistics.stock_register.list')); ?>'"
		:url_get_accounts = "'<?php echo e(route('dashboard.logistics.stock_register.get_accounts')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.logistics.stock_register.get_articles')); ?>'"
		:url_get_perception_percentage = "'<?php echo e(route('dashboard.logistics.stock_register.get_perception_percentage')); ?>'"
		:url_get_article = "'<?php echo e(route('dashboard.logistics.stock_register.get_article')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.logistics.stock_register.store')); ?>'"
	></stock-register-form>

	<stock-register-table
		:igv = "<?php echo e($igv); ?>"
		:url = "'<?php echo e(route('dashboard.logistics.stock_register.store')); ?>'"
		:url_get_article = "'<?php echo e(route('dashboard.logistics.stock_register.get_article')); ?>'"
	></stock-register-table>

	<stock-register-modal
		:url_get_article = "'<?php echo e(route('dashboard.logistics.stock_register.get_article')); ?>'"
	></stock-register-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/stock_register.blade.php ENDPATH**/ ?>