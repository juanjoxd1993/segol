<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Registro de Documentos por Cobrar'); ?>

<?php $__env->startSection('content'); ?>
	<register-document-charge-first-step
		:companies = "<?php echo e($companies); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_charge.get_voucher')); ?>'"
	></register-document-charge-first-step>

	<register-document-charge-second-step
		:min_sale_date = "'<?php echo e($min_sale_date); ?>'"
		:max_sale_date = "'<?php echo e($max_sale_date); ?>'"
		:payments = "<?php echo e($payments); ?>"
		:min_expiry_date = "'<?php echo e($min_expiry_date); ?>'"
		:max_expiry_date = "'<?php echo e($max_expiry_date); ?>'"
		:currencies = "<?php echo e($currencies); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:credit_note_reasons = "<?php echo e($credit_note_reasons); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_charge.validate_second_step')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.register_document_charge.get_clients')); ?>'"
	></register-document-charge-second-step>

	<register-document-charge-third-step
		:url = "'<?php echo e(route('dashboard.voucher.register_document_charge.store')); ?>'"
	></register-document-charge-third-step>

	<register-document-charge-third-step-modal
		:units = "<?php echo e($units); ?>"
		:value_types = "<?php echo e($value_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.register_document_charge.validate_third_step')); ?>'"
	></register-document-charge-third-step-modal>

	<loading></loading>
	
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/register_document_charge.blade.php ENDPATH**/ ?>