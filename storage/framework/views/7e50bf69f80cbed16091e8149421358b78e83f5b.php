<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Envío OSE'); ?>

<?php $__env->startSection('content'); ?>
	<billing-ose-form
		:companies = "<?php echo e($companies); ?>"
		:voucher_types = "<?php echo e($voucher_types); ?>"
		:url = "'<?php echo e(route('dashboard.voucher.validate_voucher_form')); ?>'"
	></billing-ose-form>

	<billing-ose-table
		:url_get_vouchers_for_table = "'<?php echo e(route('dashboard.voucher.get_vouchers_for_table')); ?>'"
		:url_send_voucher = "'<?php echo e(route('dashboard.voucher.send_voucher')); ?>'"
		:url_get_voucher_detail = "'<?php echo e(route('dashboard.voucher.get_voucher_detail')); ?>'"
	></billing-ose-table>

	<loading></loading>
	<billing-ose-table-modal></billing-ose-table-modal>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/billing_ose.blade.php ENDPATH**/ ?>