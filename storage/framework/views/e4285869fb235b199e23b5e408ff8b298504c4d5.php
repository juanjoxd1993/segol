<?php $__env->startSection('title', 'Clientes'); ?>
<?php $__env->startSection('subtitle', ''); ?>

<?php $__env->startSection('content'); ?>
	<client-form
		:companies = "<?php echo e($companies); ?>"
		:url = "'<?php echo e(route('dashboard.commercial.clients.validate_form')); ?>'"
	></client-form>

	<client-table
		:url = "'<?php echo e(route('dashboard.commercial.clients.list')); ?>'"
		:url_detail = "'<?php echo e(route('dashboard.commercial.clients.detail')); ?>'"
		:url_delete = "'<?php echo e(route('dashboard.commercial.clients.delete')); ?>'"
	></client-table>

	<client-modal
		:url = "'<?php echo e(route('dashboard.commercial.clients.store')); ?>'"
		:url_get_ubigeos = "'<?php echo e(route('dashboard.commercial.clients.get_ubigeos')); ?>'"
		:url_get_clients = "'<?php echo e(route('dashboard.commercial.clients.get_clients')); ?>'"
		:url_get_select2 = "'<?php echo e(route('dashboard.commercial.clients.get_select2')); ?>'"
		:document_types = "<?php echo e($document_types); ?>"
		:payments = "<?php echo e($payments); ?>"
		:perceptions = "<?php echo e($perceptions); ?>"
		:business_units = "<?php echo e($business_units); ?>"
		:client_zones = "<?php echo e($client_zones); ?>"
		:client_channels = "<?php echo e($client_channels); ?>"
		:client_routes = "<?php echo e($client_routes); ?>"
		:client_sectors = "<?php echo e($client_sectors); ?>"
		:url_search_client = "'<?php echo e(route('dashboard.commercial.clients.search_client')); ?>'"
	></client-modal>

	<client-address-modal
		:url = "'<?php echo e(route('dashboard.commercial.clients.address_store')); ?>'"
		:url_get_ubigeos = "'<?php echo e(route('dashboard.commercial.clients.get_ubigeos')); ?>'"
		:url_address_list = "'<?php echo e(route('dashboard.commercial.clients.address_list')); ?>'"
		:url_address_detail = "'<?php echo e(route('dashboard.commercial.clients.address_detail')); ?>'"
		:url_address_delete = "'<?php echo e(route('dashboard.commercial.clients.address_delete')); ?>'"
		
		:address_types = "<?php echo e($address_types); ?>"
	></client-address-modal>

	<client-price-list-modal
		:url = "'<?php echo e(route('dashboard.commercial.clients.price_store')); ?>'"
		:url_price_list = "'<?php echo e(route('dashboard.commercial.clients.price_list')); ?>'"
		:url_price_articles = "'<?php echo e(route('dashboard.commercial.clients.price_articles')); ?>'"
		:url_price_min_effective_date = "'<?php echo e(route('dashboard.commercial.clients.price_min_effective_date')); ?>'"
	></client-price-list-modal>

	<loading></loading>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('backend.templates.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/pdd/resources/views/backend/clients.blade.php ENDPATH**/ ?>