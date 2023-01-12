<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Abastecimiento de GLP'); ?>




<?php $__env->startSection('content'); ?>
	<terminals-report-form
		:companies = "<?php echo e($companies); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.terminals.validate_form')); ?>'"
		
	></terminals-report-form>

	
		<terminals-report-table
			:url = "'<?php echo e(route('dashboard.report.terminals.list')); ?>'"
			:url_detail = "'<?php echo e(route('dashboard.report.terminals.detail')); ?>'"
			
		></terminals-report-table>

		<terminals-report-modal
		:url = "'<?php echo e(route('dashboard.report.terminals.update')); ?>'"
	></terminals-report-modal>

	
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/terminals_report.blade.php ENDPATH**/ ?>