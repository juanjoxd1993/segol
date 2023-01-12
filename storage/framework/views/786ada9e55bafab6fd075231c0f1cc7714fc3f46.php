<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Liquidaciones'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-form
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.liquidations.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.voucher.liquidations.get_warehouse_movements')); ?>'"
	></liquidation-form>
	
	<liquidation-table
		:url = "'<?php echo e(route('dashboard.voucher.liquidations.list')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.voucher.liquidations.store')); ?>'"
	></liquidation-table>

	<liquidation-table-sale
	></liquidation-table-sale>

	<liquidation-modal-sale
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:currencies = "<?php echo e($currencies); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.liquidations.list')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.liquidations.get_clients')); ?>'"
		:url_get_article_price = "'<?php echo e(route('dashboard.voucher.liquidations.get_article_price')); ?>'"
		:url_verify_document_type = "'<?php echo e(route('dashboard.voucher.liquidations.verify_document_type')); ?>'"
	></liquidation-modal-sale>

	<liquidation-modal-liquidation
		:payment_methods = "<?php echo e($payment_methods); ?>"
		:payments = "<?php echo e($payments); ?>"
		:currencies = "<?php echo e($currencies); ?>"                                
		:url_get_bank_accounts = "'<?php echo e(route('dashboard.voucher.liquidations.get_bank_accounts')); ?>'"
		:payment_cash = "<?php echo e($payment_cash); ?>"
		:payment_credit = "<?php echo e($payment_credit); ?>"
	></liquidation-modal-liquidation>

	

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations.blade.php ENDPATH**/ ?>