<?php $__env->startSection('title', 'Reportes'); ?>
<?php $__env->startSection('subtitle', 'Buscador de Guías'); ?>

<?php $__env->startSection('content'); ?>
	<guides-seek-report-form
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.report.guides_seek.validate_form')); ?>'"
		
	></guides-seek-report-form>
	
	<guides-seek-report-table
		:url = "'<?php echo e(route('dashboard.report.guides_seek.list')); ?>'"
	></guides-seek-report-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/guides_seek_report.blade.php ENDPATH**/ ?>