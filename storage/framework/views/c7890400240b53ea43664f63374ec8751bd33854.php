<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Registro de Documentos por Cobrar'); ?>

<?php $__env->startSection('content'); ?>
	<register-document-fact-first-step
		:companies = "<?php echo e($companies); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_fact.get_voucher')); ?>'"
	></register-document-fact-first-step>

	<register-document-fact-second-step
		:min_sale_date = "'<?php echo e($min_sale_date); ?>'"
		:max_sale_date = "'<?php echo e($max_sale_date); ?>'"
		:payments = "<?php echo e($payments); ?>"
		:min_expiry_date = "'<?php echo e($min_expiry_date); ?>'"
		:max_expiry_date = "'<?php echo e($max_expiry_date); ?>'"
		:currencies = "<?php echo e($currencies); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:credit_note_reasons = "<?php echo e($credit_note_reasons); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_fact.validate_second_step')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.register_document_fact.get_clients')); ?>'"
	></register-document-fact-second-step>

	<register-document-fact-third-step
		:url = "'<?php echo e(route('dashboard.voucher.register_document_fact.store')); ?>'"
	></register-document-fact-third-step>

	<register-document-fact-third-step-modal
		:units = "<?php echo e($units); ?>"
		:articles = "<?php echo e($articles); ?>"
		:value_types = "<?php echo e($value_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_fact.validate_third_step')); ?>'"
	></register-document-fact-third-step-modal>

	<loading></loading>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/register_document_fact.blade.php ENDPATH**/ ?>