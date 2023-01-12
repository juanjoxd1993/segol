<?php $__env->startSection('title', 'Inventario'); ?>
<?php $__env->startSection('subtitle', ''); ?>

<?php $__env->startSection('content'); ?>
	<inventory-form
		:companies = "<?php echo e($companies); ?>"
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.logistics.inventories.validate_form')); ?>'"
	></inventory-form>

	<inventory-table
		:url = "'<?php echo e(route('dashboard.logistics.inventories.list')); ?>'"
		:url_create_record = "'<?php echo e(route('dashboard.logistics.inventories.create_record')); ?>'"
		:url_close_record = "'<?php echo e(route('dashboard.logistics.inventories.close_record')); ?>'"
		:url_form_record = "'<?php echo e(route('dashboard.logistics.inventories.form_record')); ?>'"
		:url_export_record = "'<?php echo e(route('dashboard.logistics.inventories.export_record')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.logistics.inventories.detail')); ?>'"
		:url_delete = "'<?php echo e(route('dashboard.logistics.inventories.delete')); ?>'"
	></inventory-table>

	<inventory-modal
		:url = "'<?php echo e(route('dashboard.logistics.inventories.store')); ?>'"
		:url_get_articles = "'<?php echo e(route('dashboard.logistics.inventories.get_articles')); ?>'"
		:url_get_select2 = "'<?php echo e(route('dashboard.logistics.inventories.get_select2')); ?>'"
	></inventory-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/inventories.blade.php ENDPATH**/ ?>