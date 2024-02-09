<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php if( Route::currentRouteName() === 'dashboard.report.stock_sales_register' ): ?>
	<?php $__env->startSection('subtitle', 'Reporte de Compras GLP'); ?>
<?php endif; ?>

<?php if( Route::currentRouteName() === 'dashboard.report.stock_seek_register_valued' ): ?>
	<?php $__env->startSection('subtitle', 'Movimiento de Existencias Valorizado'); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
	<stock-sales-register-report-form
		
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		
		:url = "'<?php echo e(route('dashboard.report.stock_sales_register.validate_form')); ?>'"
		
	></stock-sales-register-report-form>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_sales_register' ): ?>
		<stock-sales-register-report-table
			:url = "'<?php echo e(route('dashboard.report.stock_sales_register.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.stock_sales_register.detail')); ?>'"
		
		></stock-sales-register-report-table>
	<?php endif; ?>

	<?php if( Route::currentRouteName() === 'dashboard.report.stock_seek_register_valued' ): ?>
		<stock-sales-register-report-valued-table
			:url = "'<?php echo e(route('dashboard.report.stock_seek_register_valued.list')); ?>'"
		></stock-sales-register-valued-table>
	<?php endif; ?>

	<stock-sales-register-report-modal
		:url = "'<?php echo e(route('dashboard.report.stock_sales_register.update')); ?>'"
	></stock-sales-register-report-modal>



	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/stock_sales_register_report.blade.php ENDPATH**/ ?>