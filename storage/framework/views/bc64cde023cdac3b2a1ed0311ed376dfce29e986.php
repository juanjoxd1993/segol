<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php if( Route::currentRouteName() === 'dashboard.report.stock_final' ): ?>
	<?php $__env->startSection('subtitle', 'Reporte de Guías'); ?>
<?php endif; ?>

<?php if( Route::currentRouteName() === 'dashboard.report.stock_register_valued' ): ?>
	<?php $__env->startSection('subtitle', 'Movimiento de Existencias Valorizado'); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
	<stock-final-report-form
		:movement_classes = "<?php echo e($movement_classes); ?>"
		:movement_types = "<?php echo e($movement_types); ?>"
		:warehouse_types = "<?php echo e($warehouse_types); ?>"
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:warehouse_account_types = "<?php echo e($warehouse_account_types); ?>"
		:warehouse_document_types = "<?php echo e($warehouse_document_types); ?>"
		:url = "'<?php echo e(route('dashboard.report.stock_final.validate_form')); ?>'"
		:url_get_accounts = "'<?php echo e(route('dashboard.logistics.stock_register.get_accounts')); ?>'"
	></stock-final-report-form>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_final' ): ?>
		<stock-final-report-table
			:url = "'<?php echo e(route('dashboard.report.stock_final.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.stock_final.detail')); ?>'"
		></stock-final-report-table>
	<?php endif; ?>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_final_valued' ): ?>
		<stock-final-report-valued-table
			:url = "'<?php echo e(route('dashboard.report.stock_final.list')); ?>'"
		></stock-final-report-valued-table>
	<?php endif; ?>

	<stock-final-report-modal
		:url = "'<?php echo e(route('dashboard.report.stock_final.update')); ?>'"
	></stock-final-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/stock_final_report.blade.php ENDPATH**/ ?>