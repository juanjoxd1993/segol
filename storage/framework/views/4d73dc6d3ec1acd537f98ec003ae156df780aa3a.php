<?php $__env->startSection('body-class', 'kt-header--fixed kt-header-mobile--fixed'); ?>
<?php $__env->startSection('title', 'Facturación'); ?>
<?php $__env->startSection('subtitle', 'Reporte OSE'); ?>

<?php $__env->startSection('content'); ?>
	<voucher-report-ose-form
		:companies = "<?php echo e($companies); ?>"
		:voucher_types = "<?php echo e($voucher_types); ?>"
		:current_date = "'<?php echo e($current_date); ?>'"
		
		:url = "'<?php echo e(route('dashboard.voucher.reportOse.validateForm')); ?>'"
	></voucher-report-ose-form>

	<voucher-report-ose-table
		:url_get_vouchers_for_table = "'<?php echo e(route('dashboard.voucher.reportOse.getVouchersTable')); ?>'"
		:url_send_voucher = "'<?php echo e(route('dashboard.voucher.reportOse.sendVoucher')); ?>'"
		:url_get_voucher_detail = "'<?php echo e(route('dashboard.voucher.reportOse.getVoucherDetail')); ?>'"
		:user_name = "'<?php echo e($user_name); ?>'"
	></voucher-report-ose-table>

	<loading></loading>
	<billing-ose-table-modal></billing-ose-table-modal>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/voucher_report_ose.blade.php ENDPATH**/ ?>