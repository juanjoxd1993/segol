<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Liquidaciones'); ?>

<?php $__env->startSection('content'); ?>
	<liquidation-final-form
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.liquidations_final.validate_form')); ?>'"
		:url_get_warehouse_movements = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_warehouse_movements')); ?>'"
		:url_get_sale_series = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_sale_serie')); ?>'"
	></liquidation-final-form>
	
	<liquidation-final-table
		:url = "'<?php echo e(route('dashboard.voucher.liquidations_final.list')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.voucher.liquidations_final.store')); ?>'"
	></liquidation-final-table>

	<liquidation-final-table-sale
	></liquidation-final-table-sale>

	<liquidation-final-modal-sale
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:currencies = "<?php echo e($currencies); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.liquidations_final.list')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_clients')); ?>'"
		:url_get_article_price = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_article_price')); ?>'"
		:url_get_sale_series = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_sale_serie')); ?>'"
		:url_get_articles_clients = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_articles_clients')); ?>'"
		:url_verify_document_type = "'<?php echo e(route('dashboard.voucher.liquidations_final.verify_document_type')); ?>'"
	></liquidation-final-modal-sale>

	<liquidation-final-modal-liquidation
		:payment_methods = "<?php echo e($payment_methods); ?>"
		:payments = "<?php echo e($payments); ?>"
		:currencies = "<?php echo e($currencies); ?>"                                
		:url_get_bank_accounts = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_bank_accounts')); ?>'"
		:url_get_saldo_favor = "'<?php echo e(route('dashboard.voucher.liquidations_final.get_saldo_favor')); ?>'"
		:payment_cash = "<?php echo e($payment_cash); ?>"
		:payment_credit = "<?php echo e($payment_credit); ?>"
	></liquidation-final-modal-liquidation>

	

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/liquidations_final.blade.php ENDPATH**/ ?>