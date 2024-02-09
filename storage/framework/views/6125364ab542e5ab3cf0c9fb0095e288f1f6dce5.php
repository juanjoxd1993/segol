<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Artículos'); ?>
<?php $__env->startSection('subtitle', ''); ?>

<?php $__env->startSection('content'); ?>
	<article-form
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:url = "'<?php echo e(route('dashboard.articles.validate_form')); ?>'"
	></article-form>

	<article-table
		:url = "'<?php echo e(route('dashboard.articles.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.articles.detail')); ?>'"
		:url_delete = "'<?php echo e(route('dashboard.articles.delete')); ?>'"
		:url_export_record = "'<?php echo e(route('dashboard.articles.export_record')); ?>'"
	></article-table>

	<article-modal
		:url = "'<?php echo e(route('dashboard.articles.store')); ?>'"
		:families = "<?php echo e($families); ?>"
		:groups = "<?php echo e($groups); ?>"
		:subgroups = "<?php echo e($subgroups); ?>"
		:operations = "<?php echo e($operations); ?>"
		:units = "<?php echo e($units); ?>"
	></article-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/articles.blade.php ENDPATH**/ ?>