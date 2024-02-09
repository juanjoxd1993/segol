<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php if( Route::currentRouteName() === 'dashboard.report.transportist_register' ): ?>
	<?php $__env->startSection('subtitle', 'Reporte de Guías Transportistas'); ?>
<?php endif; ?>

<?php if( Route::currentRouteName() === 'dashboard.report.transportist_register_valued' ): ?>
	<?php $__env->startSection('subtitle', 'Movimiento entre Trnasportista Global'); ?>
<?php endif; ?>

<?php $__env->startSection('content'); ?>
	<transportist-register-report-form
		
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		
		:url = "'<?php echo e(route('dashboard.report.transportist_register.validate_form')); ?>'"
		
	></transportist-register-report-form>

	<?php if( Route::currentRouteName() === 'dashboard.report.transportist_register' ): ?>
		<transportist-register-report-table
			:url = "'<?php echo e(route('dashboard.report.transportist_register.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.transportist_register.detail')); ?>'"
		></transportist-register-report-table>
	<?php endif; ?>

	<?php if( Route::currentRouteName() === 'dashboard.report.transportist_register' ): ?>
		<transportist-register-report-valued-table
			:url = "'<?php echo e(route('dashboard.report.transportist_register.list')); ?>'"
		></transportist-register-valued-table>
	<?php endif; ?>

	<transportist-register-report-modal
		:url = "'<?php echo e(route('dashboard.report.transportist_register.update')); ?>'"
	></transportist-register-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/transportist_register_report.blade.php ENDPATH**/ ?>