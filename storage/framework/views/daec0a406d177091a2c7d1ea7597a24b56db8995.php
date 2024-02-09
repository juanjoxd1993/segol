<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php if( Route::currentRouteName() === 'dashboard.report.stock_register' ): ?>
	<?php $__env->startSection('subtitle', 'Movimiento de Existencias'); ?>
<?php endif; ?>

<?php if( Route::currentRouteName() === 'dashboard.report.stock_register_valued' ): ?>
	<?php $__env->startSection('subtitle', 'Movimiento de Existencias Valorizado'); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
	<stock-register-report-form
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:movement_types = "<?php echo e($movement_types); ?>"
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:warehouse_account_types = "<?php echo e($warehouse_account_types); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.report.stock_register.validate_form')); ?>'"
		:url_get_accounts = "'<?php echo e(route('dashboard.logistics.stock_register.get_accounts')); ?>'"
	></stock-register-report-form>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_register' ): ?>
		<stock-register-report-table
			:url = "'<?php echo e(route('dashboard.report.stock_register.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.stock_register.detail')); ?>'"
		></stock-register-report-table>
	<?php endif; ?>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_register_valued' ): ?>
		<stock-register-report-valued-table
			:url = "'<?php echo e(route('dashboard.report.stock_register.list')); ?>'"
		></stock-register-report-valued-table>
	<?php endif; ?>

	<stock-register-report-modal
		:url = "'<?php echo e(route('dashboard.report.stock_register.update')); ?>'"
	></stock-register-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/stock_register_report.blade.php ENDPATH**/ ?>