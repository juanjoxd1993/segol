<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php if( Route::currentRouteName() === 'dashboard.report.glp_fact' ): ?>
	<?php $__env->startSection('subtitle', 'Reporte de Facturas GLP'); ?>
<?php endif; ?>



<?php $__env->startSection('content'); ?>
	<glp-fact-report-form
		
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.report.glp_fact.validate_form')); ?>'"
	></glp-fact-report-form>

	<?php if( Route::currentRouteName() === 'dashboard.report.glp_fact' ): ?>
		<glp-fact-report-table
			:url = "'<?php echo e(route('dashboard.report.glp_fact.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.glp_fact.detail')); ?>'"
		></glp-fact-report-table>
	<?php endif; ?>


	<glp-fact-report-modal
		:url = "'<?php echo e(route('dashboard.report.glp_fact.update')); ?>'"
	></glp-fact-report-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/glp_fact_report.blade.php ENDPATH**/ ?>