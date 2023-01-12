<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Registro de Cobranzas'); ?>

<?php $__env->startSection('content'); ?>
	<collection-register-first-step
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.collection_register.validate_first_step')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.collection_register.get_clients')); ?>'"
	></collection-register-first-step>

	<collection-register-second-step
		:max_sale_date = "'<?php echo e($max_sale_date); ?>'"
		:payment_methods = "<?php echo e($payment_methods); ?>"
		:currencies = "<?php echo e($currencies); ?>"
		:bank_accounts = "<?php echo e($bank_accounts); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.collection_register.validate_second_step')); ?>'"
	></collection-register-second-step>

	<collection-register-third-step
		:url = "'<?php echo e(route('dashboard.voucher.collection_register.get_sales')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.voucher.collection_register.store')); ?>'"
	></collection-register-third-step>

	<collection-register-third-step-modal
	></collection-register-third-step-modal>

	<loading></loading>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/collection_register.blade.php ENDPATH**/ ?>