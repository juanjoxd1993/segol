<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Generación masiva de Comprobantes'); ?>

<?php $__env->startSection('content'); ?>
	<voucher-massive-generation-form
		:companies = "<?php echo e($companies); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:today = "'<?php echo e($today); ?>'"
		:user_name = "'<?php echo e($user_name); ?>'"
		:url = "'<?php echo e(route('dashboard.voucher.massive_generation.validate_form')); ?>'"
	></voucher-massive-generation-form>
	
	<voucher-massive-generation-table
		:url = "'<?php echo e(route('dashboard.voucher.massive_generation.list')); ?>'"
	></voucher-massive-generation-table>

	<voucher-massive-generation-modal
		:voucher_types = "<?php echo e($voucher_types); ?>"
		:min_datetime = "'<?php echo e($min_datetime); ?>'"
		:today = "'<?php echo e($today); ?>'"
		:url = "'<?php echo e(route('dashboard.voucher.massive_generation.store')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.voucher.massive_generation.get_clients')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.voucher.massive_generation.get_articles')); ?>'"
	></voucher-massive-generation-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/voucher_massive_generation.blade.php ENDPATH**/ ?>