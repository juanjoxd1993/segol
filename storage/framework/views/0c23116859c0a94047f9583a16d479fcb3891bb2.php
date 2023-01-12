<?php $__env->startSection('body-class', ''); ?>
<?php $__env->startSection('title', 'Control GLP'); ?>
<?php $__env->startSection('subtitle', 'Registro Costos GLP'); ?>

<?php $__env->startSection('content'); ?>
	<cost-glp-register-form
		
		:current_date = "'<?php echo e($current_date); ?>'"
		:url = "'<?php echo e(route('dashboard.report.cost_glp_register.validate_form')); ?>'"
		:url_store = "'<?php echo e(route('dashboard.report.cost_glp_register.store')); ?>'"
	></cost-glp-register-form>

	
	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/cost_glp_register.blade.php ENDPATH**/ ?>