<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Envío OSE'); ?>

<?php $__env->startSection('content'); ?>
	<billing-ose-form
		:companies = "<?php echo e($companies); ?>"
		:voucher_types = "<?php echo e($voucher_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.get_vouchers')); ?>'"
	></billing-ose-form>

	<billing-ose-table
		:url_send_ticket = "'<?php echo e(route('dashboard.voucher.send_ticket')); ?>'"
	></billing-ose-table>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\www\pdd\resources\views/backend/billing_ose.blade.php ENDPATH**/ ?>