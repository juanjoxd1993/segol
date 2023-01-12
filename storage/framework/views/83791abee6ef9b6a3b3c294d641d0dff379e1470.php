<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Lista de Precios'); ?>

<?php $__env->startSection('content'); ?>
	<price-list-form
		:companies = "<?php echo e($companies); ?>"
		:articles = "<?php echo e($articles); ?>"
		:client_sectors = "<?php echo e($client_sectors); ?>"
		:client_channels = "<?php echo e($client_channels); ?>"
		:client_routes = "<?php echo e($client_routes); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.price_list.validate_form')); ?>'"
	></price-list-form>
	
	<price-list-table
		:url = "'<?php echo e(route('dashboard.voucher.price_list.list')); ?>'"
	></price-list-table>

	<price-list-modal
		:url = "'<?php echo e(route('dashboard.voucher.price_list.store')); ?>'"
		:url_get_min_effective_date = "'<?php echo e(route('dashboard.voucher.price_list.get_min_effective_date')); ?>'"
	></price-list-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/price_list.blade.php ENDPATH**/ ?>