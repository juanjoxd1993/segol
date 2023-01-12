<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', 'Backend\BillingController@test')->name('home');

Route::get('/iniciar-sesion', 'Backend\UserController@index')->name('login');
Route::post('/iniciar-sesion/auth', 'Backend\UserController@login')->name('login.auth');
Route::get('/cerrar-sesion', 'Backend\UserController@logout')->name('logout');

Route::middleware(['auth'])->namespace('Backend')->group(function() {
	/** Facturación > Generación masiva de Comprobantes */
	Route::get('/facturacion/generacion-masiva-comprobantes', 'VoucherMassiveGenerationController@index')->name('dashboard.voucher.massive_generation');
	Route::post('/facturacion/generacion-masiva-comprobantes/validar-formulario', 'VoucherMassiveGenerationController@validateForm')->name('dashboard.voucher.massive_generation.validate_form');
	Route::post('/facturacion/generacion-masiva-comprobantes/listar', 'VoucherMassiveGenerationController@list')->name('dashboard.voucher.massive_generation.list');
	Route::post('/facturacion/generacion-masiva-comprobantes/obtener-clientes', 'VoucherMassiveGenerationController@getClients')->name('dashboard.voucher.massive_generation.get_clients');
	Route::post('/facturacion/generacion-masiva-comprobantes/obtener-articulos', 'VoucherMassiveGenerationController@getArticles')->name('dashboard.voucher.massive_generation.get_articles');
	Route::post('/facturacion/generacion-masiva-comprobantes/guardar', 'VoucherMassiveGenerationController@store')->name('dashboard.voucher.massive_generation.store');

	/** Facturación > Envío OSE */
	Route::get('/', 'VoucherController@sendOse')->name('dashboard.voucher.send_ose');
	Route::post('/facturacion/validar-formulario-documentos', 'VoucherController@validate_voucher_form')->name('dashboard.voucher.validate_voucher_form');
	Route::post('/facturacion/obtener-documentos-tabla', 'VoucherController@get_vouchers_for_table')->name('dashboard.voucher.get_vouchers_for_table');
	Route::post('/facturacion/listar', 'VoucherController@list')->name('dashboard.voucher.list');
	Route::post('/facturacion/obtener-detalle-documento', 'VoucherController@get_voucher_detail')->name('dashboard.voucher.get_voucher_detail');
	Route::post('/facturacion/enviar-documento', 'VoucherController@send_voucher')->name('dashboard.voucher.send_voucher');

	/** Facturación > Consulta Envío OSE */
	Route::get('/facturacion/reporte-ose', 'VoucherReportOseController@index')->name('dashboard.voucher.reportOse');
	Route::post('/facturacion/reporte-ose/validar-formulario-documentos', 'VoucherReportOseController@validateForm')->name('dashboard.voucher.reportOse.validateForm');
	Route::post('/facturacion/reporte-ose/obtener-documentos-tabla', 'VoucherReportOseController@getVouchersTable')->name('dashboard.voucher.reportOse.getVouchersTable');
	Route::post('/facturacion/reporte-ose/obtener-detalle-documento', 'VoucherReportOseController@getVoucherDetail')->name('dashboard.voucher.reportOse.getVoucherDetail');
	Route::post('/facturacion/reporte-ose/enviar-documento', 'VoucherReportOseController@sendVoucher')->name('dashboard.voucher.reportOse.sendVoucher');

	/** Facturación > Clientes */
	Route::get('/facturacion/clientes', 'ClientController@index')->name('dashboard.voucher.clients');
	Route::post('/facturacion/clientes/validar-formulario', 'ClientController@validateForm')->name('dashboard.voucher.clients.validate_form');
	Route::post('/facturacion/clientes/listar', 'ClientController@list')->name('dashboard.voucher.clients.list');
	Route::post('/facturacion/clientes/guardar', 'ClientController@store')->name('dashboard.voucher.clients.store');
	Route::post('/facturacion/clientes/detalle', 'ClientController@detail')->name('dashboard.voucher.clients.detail');
	Route::post('/facturacion/clientes/obtener-ubigeos', 'ClientController@getUbigeos')->name('dashboard.voucher.clients.get_ubigeos');
	Route::post('/facturacion/clientes/obtener-clientes', 'ClientController@getClients')->name('dashboard.voucher.clients.get_clients');
	Route::post('/facturacion/clientes/obtener-select2', 'ClientController@getSelect2')->name('dashboard.voucher.clients.get_select2');
	Route::post('/facturacion/clientes/eliminar', 'ClientController@delete')->name('dashboard.voucher.clients.delete');
	Route::post('/facturacion/clientes/listar-direcciones', 'ClientController@addressList')->name('dashboard.voucher.clients.address_list');
	Route::post('/facturacion/clientes/guardar-direccion', 'ClientController@addressStore')->name('dashboard.voucher.clients.address_store');
	Route::post('/facturacion/clientes/detalle-direccion', 'ClientController@addressDetail')->name('dashboard.voucher.clients.address_detail');
	Route::post('/facturacion/clientes/eliminar-direccion', 'ClientController@addressDelete')->name('dashboard.voucher.clients.address_delete');
	Route::post('/facturacion/clientes/listar-precios', 'ClientController@priceList')->name('dashboard.voucher.clients.price_list');
	Route::post('/facturacion/clientes/listar-articulos', 'ClientController@priceArticles')->name('dashboard.voucher.clients.price_articles');
	Route::post('/facturacion/clientes/obtener-dia', 'ClientController@priceMinEffectiveDate')->name('dashboard.voucher.clients.price_min_effective_date');
	Route::post('/facturacion/clientes/guardar-precio', 'ClientController@priceStore')->name('dashboard.voucher.clients.price_store');

	/** Facturación > Registro de Documentos por Cobrar */
	Route::get('/facturacion/documentos-por-cobrar', 'RegisterDocumentChargeController@index')->name('dashboard.voucher.register_document_charge');
	Route::post('/facturacion/documentos-por-cobrar/validar-primer-paso', 'RegisterDocumentChargeController@validateFirstStep')->name('dashboard.voucher.register_document_charge.validate_first_step');
	Route::post('/facturacion/documentos-por-cobrar/obtener-comprobante', 'RegisterDocumentChargeController@getVoucher')->name('dashboard.voucher.register_document_charge.get_voucher');
	Route::post('/facturacion/documentos-por-cobrar/validar-segundo-paso', 'RegisterDocumentChargeController@validateSecondStep')->name('dashboard.voucher.register_document_charge.validate_second_step');
	Route::post('/facturacion/documentos-por-cobrar/obtener-clientes', 'RegisterDocumentChargeController@getClients')->name('dashboard.voucher.register_document_charge.get_clients');
	Route::post('/facturacion/documentos-por-cobrar/validar-tercer-paso', 'RegisterDocumentChargeController@validateThirdStep')->name('dashboard.voucher.register_document_charge.validate_third_step');
	Route::post('/facturacion/documentos-por-cobrar/guardar', 'RegisterDocumentChargeController@store')->name('dashboard.voucher.register_document_charge.store');

	/** Facturación > Registro de Cobranzas */
	Route::get('/facturacion/cobranzas', 'CollectionRegisterController@index')->name('dashboard.voucher.collection_register');
	Route::post('/facturacion/cobranzas/obtener-clientes', 'CollectionRegisterController@getClients')->name('dashboard.voucher.collection_register.get_clients');
	Route::post('/facturacion/cobranzas/validar-primer-paso', 'CollectionRegisterController@validateFirstStep')->name('dashboard.voucher.collection_register.validate_first_step');
	Route::post('/facturacion/cobranzas/validar-segundo-paso', 'CollectionRegisterController@validateSecondStep')->name('dashboard.voucher.collection_register.validate_second_step');
	Route::post('/facturacion/cobranzas/obtener-ventas', 'CollectionRegisterController@getSales')->name('dashboard.voucher.collection_register.get_sales');
	Route::post('/facturacion/cobranzas/guardar', 'CollectionRegisterController@store')->name('dashboard.voucher.collection_register.store');

	/** Facturación > Liquidaciones */
	Route::get('/facturacion/liquidaciones', 'LiquidationController@index')->name('dashboard.voucher.liquidations');
	Route::post('/facturacion/liquidaciones/validar-formulario', 'LiquidationController@validateForm')->name('dashboard.voucher.liquidations.validate_form');
	Route::post('/facturacion/liquidaciones/obtener-movimientos-almacen', 'LiquidationController@getWarehouseMovements')->name('dashboard.voucher.liquidations.get_warehouse_movements');
	Route::post('/facturacion/liquidaciones/listar', 'LiquidationController@list')->name('dashboard.voucher.liquidations.list');
	Route::post('/facturacion/liquidaciones/obtener-clientes', 'LiquidationController@getClients')->name('dashboard.voucher.liquidations.get_clients');
	Route::post('/facturacion/liquidaciones/obtener-precio-articulo', 'LiquidationController@getArticlePrice')->name('dashboard.voucher.liquidations.get_article_price');
	Route::post('/facturacion/liquidaciones/obtener-cuentas-banco', 'LiquidationController@getBankAccounts')->name('dashboard.voucher.liquidations.get_bank_accounts');
	Route::post('/facturacion/liquidaciones/verificar-documento', 'LiquidationController@verifyDocumentType')->name('dashboard.voucher.liquidations.verify_document_type');
	Route::post('/facturacion/liquidaciones/guardar', 'LiquidationController@store')->name('dashboard.voucher.liquidations.store');

	/** Facturación > Lista de Precios */
	Route::get('/facturacion/lista-de-precios', 'PriceListController@index')->name('dashboard.voucher.price_list');
	Route::post('/facturacion/lista-de-precios/validar-formulario', 'PriceListController@validateForm')->name('dashboard.voucher.price_list.validate_form');
	Route::post('/facturacion/lista-de-precios/listar', 'PriceListController@list')->name('dashboard.voucher.price_list.list');
	Route::post('/facturacion/lista-de-precios/obtener-dia', 'PriceListController@getMinEffectiveDate')->name('dashboard.voucher.price_list.get_min_effective_date');
	Route::post('/facturacion/lista-de-precios/guardar', 'PriceListController@store')->name('dashboard.voucher.price_list.store');

	/** Reportes > Cuentas Corrientes Clientes */
	Route::get('/reporte/cuentas-corrientes-clientes', 'CheckingAccountReportController@index')->name('dashboard.report.checking_account_report');
	Route::post('/reporte/cuentas-corrientes-clientes/obtener-clientes', 'CheckingAccountReportController@getClients')->name('dashboard.report.checking_account_report.get_clients');
	Route::post('/reporte/cuentas-corrientes-clientes/validar-formulario', 'CheckingAccountReportController@validateForm')->name('dashboard.report.checking_account_report.validate_form');
	Route::post('/reporte/cuentas-corrientes-clientes/listar', 'CheckingAccountReportController@list')->name('dashboard.report.checking_account_report.list');

	/** Reportes > Kardex */
	Route::get('/reporte/kardex', 'KardexController@index')->name('dashboard.report.kardex');
	Route::post('/reporte/kardex/obtener-articulos', 'KardexController@getArticles')->name('dashboard.report.kardex.get_articles');
	Route::post('/reporte/kardex/obtener-cuentas', 'KardexController@getAccounts')->name('dashboard.report.kardex.get_accounts');
	Route::post('/reporte/kardex/validar-formulario', 'KardexController@validateForm')->name('dashboard.report.kardex.validate_form');
	Route::post('reporte/kardex/listar', 'KardexController@list')->name('dashboard.report.kardex.list');

	/** Reportes > Liquidaciones */
	Route::get('/reporte/liquidaciones', 'LiquidationReportController@index')->name('dashboard.report.liquidations');
	Route::post('/reporte/liquidaciones/validar-formulario', 'LiquidationReportController@validateForm')->name('dashboard.report.liquidations.validate_form');
	Route::post('/reporte/liquidaciones/obtener-clientes', 'LiquidationReportController@getClients')->name('dashboard.report.liquidations.get_clients');
	Route::post('/reporte/liquidaciones/listar', 'LiquidationReportController@list')->name('dashboard.report.liquidations.list');

    /** Reportes > Venta Comercial */
	Route::get('/reporte/venta-comercial', 'LiquidationSalesReportController@index')->name('dashboard.report.liquidations_sales');
	Route::post('/reporte/venta-comercial/validar-formulario', 'LiquidationSalesReportController@validateForm')->name('dashboard.report.liquidations_sales.validate_form');
	Route::post('/reporte/venta-comercial/obtener-clientes', 'LiquidationSalesReportController@getClients')->name('dashboard.report.liquidations_sales.get_clients');
	Route::post('/reporte/venta-comercial/listar', 'LiquidationSalesReportController@list')->name('dashboard.report.liquidations_sales.list');

	/** Reportes > Liquidación Detallado */
	Route::get('/reporte/liquidacion-detallado', 'CollectionSalesReportController@index')->name('dashboard.report.collection_sales_report');
	Route::post('/reporte/liquidacion-detallado/obtener-clientes', 'CollectionSalesReportController@getClients')->name('dashboard.report.collection_sales_report.get_clients');
	Route::post('/reporte/liquidacion-detallado/validar-formulario', 'CollectionSalesReportController@validateForm')->name('dashboard.report.collection_sales_report.validate_form');
	Route::post('/reporte/liquidacion-detallado/listar', 'CollectionSalesReportController@list')->name('dashboard.report.collection_sales_report.list');

	/** Reportes > Lista de Precios */
	Route::get('/reporte/lista-de-precios', 'PriceListReportController@index')->name('dashboard.report.price_list');
	Route::post('/reporte/lista-de-precios/validar-formulario', 'PriceListReportController@validateForm')->name('dashboard.report.price_list.validate_form');
	Route::post('/reporte/lista-de-precios/obtener-clientes', 'PriceListReportController@getClients')->name('dashboard.report.price_list.get_clients');
	Route::post('/reporte/lista-de-precios/obtener-articulos', 'PriceListReportController@getArticles')->name('dashboard.report.price_list.get_articles');
	Route::post('/reporte/lista-de-precios/listar', 'PriceListReportController@list')->name('dashboard.report.price_list.list');
	Route::post('/reporte/lista-de-precios/exportar-pdf', 'PriceListReportController@exportPdf')->name('dashboard.report.price_list.export_pdf');

	/** Reportes > Movimiento de Existencias */
	Route::get('/reporte/movimiento-existencias', 'StockRegisterReportController@index')->name('dashboard.report.stock_register');
	Route::get('/reporte/movimiento-existencias-valorizado', 'StockRegisterReportController@index')->name('dashboard.report.stock_register_valued');
	Route::post('/reporte/movimiento-existencias/validar-formulario', 'StockRegisterReportController@validateForm')->name('dashboard.report.stock_register.validate_form');
	Route::post('/reporte/movimiento-existencias/listar', 'StockRegisterReportController@list')->name('dashboard.report.stock_register.list');
	Route::post('/reporte/movimiento-existencias/detalle', 'StockRegisterReportController@detail')->name('dashboard.report.stock_register.detail');
	Route::post('/reporte/movimiento-existencias/actualizar', 'StockRegisterReportController@update')->name('dashboard.report.stock_register.update');

	/** Reportes > Parte de Almacén */
	Route::get('/reporte/parte-almacen', 'WarehousePartController@index')->name('dashboard.report.warehouse_part');
	Route::post('/reporte/parte-almacen/validar-formulario', 'WarehousePartController@validateForm')->name('dashboard.report.warehouse_part.validate_form');
	Route::post('/reporte/parte-almacen/obtener-movimientos-almacen', 'WarehousePartController@getWarehouseMovements')->name('dashboard.report.warehouse_part.get_warehouse_movements');
	Route::post('/reporte/parte-almacen/listar', 'WarehousePartController@list')->name('dashboard.report.warehouse_part.list');
	Route::post('/reporte/parte-almacen/exportar', 'WarehousePartController@export')->name('dashboard.report.warehouse_part.export');
	Route::get('/reporte/parte-almacen/exportar-guia-remision', 'WarehousePartController@exportReferralGuide')->name('dashboard.report.warehouse_part.export_referral_guide');

	/** Reportes > Registro de Ventas */
	Route::get('/reporte/registro-ventas', 'SalesRegisterReportController@index')->name('dashboard.report.sales-register');
	Route::post('/reporte/registro-ventas/validar-formulario', 'SalesRegisterReportController@validateForm')->name('dashboard.report.sales-register.validate_form');
	Route::post('/reporte/registro-ventas/listar', 'SalesRegisterReportController@list')->name('dashboard.report.sales-register.list');
	Route::post('/reporte/registro-ventas/exportar', 'SalesRegisterReportController@export')->name('dashboard.report.sales-register.export');

	/** Reportes > Relación de Clientes */
	Route::get('/reporte/relacion-de-clientes', 'ListClientsReportController@index')->name('dashboard.report.list_clients');
	Route::post('/reporte/relacion-de-clientes/validar-formulario', 'ListClientsReportController@validateForm')->name('dashboard.report.list_clients.validate_form');
	Route::post('/reporte/relacion-de-clientes/listar', 'ListClientsReportController@list')->name('dashboard.report.list_clients.list');

	/** Reportes > Relación de Cobranzas */
	Route::get('/reporte/relacion-de-cobranzas', 'CollectionReportController@index')->name('dashboard.report.collection_report');
	Route::post('/reporte/relacion-de-cobranzas/obtener-clientes', 'CollectionReportController@getClients')->name('dashboard.report.collection_report.get_clients');
	Route::post('/reporte/relacion-de-cobranzas/validar-formulario', 'CollectionReportController@validateForm')->name('dashboard.report.collection_report.validate_form');
	Route::post('/reporte/relacion-de-cobranzas/listar', 'CollectionReportController@list')->name('dashboard.report.collection_report.list');

	/** Reportes > Reporte de Ventas */
	Route::get('/reporte/reporte-de-ventas', 'SalesReportController@index')->name('dashboard.report.sales-report');
	Route::post('/reporte/reporte-de-ventas/validar-formulario', 'SalesReportController@validateForm')->name('dashboard.report.sales-report.validate_form');
	Route::post('/reporte/reporte-de-ventas/obtener-precio', 'SalesReportController@getCurrentPrice')->name('dashboard.report.sales-report.get_current_price');
	Route::post('/reporte/reporte-de-ventas/listar', 'SalesReportController@list')->name('dashboard.report.sales-report.list');
	Route::post('/reporte/reporte-de-ventas/detalle', 'SalesReportController@detail')->name('dashboard.report.sales-report.detail');
	Route::post('/reporte/reporte-de-ventas/exportar', 'SalesReportController@export')->name('dashboard.report.sales-report.export');
	// Route::get('/reporte/reporte-de-ventas/actualizar-id-cliente', 'SalesReportController@updateClientId');

	/** Reportes > Reporte de Ventas > Administrador de Presupuestos */
	Route::get('/reporte/reporte-de-ventas/presupuestos', 'BudgetController@index')->name('dashboard.report.sales-report.budgets');
	Route::post('/reporte/reporte-de-ventas/presupuestos/validar-formulario', 'BudgetController@validateForm')->name('dashboard.report.sales-report.budgets.validate_form');
	Route::post('/reporte/reporte-de-ventas/presupuestos/listar', 'BudgetController@list')->name('dashboard.report.sales-report.budgets.list');
	Route::get('/reporte/reporte-de-ventas/presupuestos/listar', 'BudgetController@list')->name('dashboard.report.sales-report.budgets.list');
	Route::post('/reporte/reporte-de-ventas/presupuestos/guardar', 'BudgetController@store')->name('dashboard.report.sales-report.budgets.store');
	Route::get('/reporte/reporte-de-ventas/presupuestos/guardar', 'BudgetController@store')->name('dashboard.report.sales-report.budgets.store');

	/** Reportes > Relación de Documentos Pendientes */
	Route::get('/reporte/relacion-de-documentos-pendientes', 'PendingDocumentReportController@index')->name('dashboard.report.pending_document_report');
	Route::post('/reporte/relacion-de-documentos-pendientes/obtener-clientes', 'PendingDocumentReportController@getClients')->name('dashboard.report.pending_document_report.get_clients');
	Route::post('/reporte/relacion-de-documentos-pendientes/validar-formulario', 'PendingDocumentReportController@validateForm')->name('dashboard.report.pending_document_report.validate_form');
	Route::post('/reporte/relacion-de-documentos-pendientes/listar', 'PendingDocumentReportController@list')->name('dashboard.report.pending_document_report.list');

	/** Reportes > Relación de Documentos Emitidos */
	Route::get('/reporte/relacion-de-documentos-por-cobrar', 'UncollectedDocumentReportController@index')->name('dashboard.report.uncollected_document_report');
	Route::post('/reporte/relacion-de-documentos-por-cobrar/obtener-clientes', 'UncollectedDocumentReportController@getClients')->name('dashboard.report.uncollected_document_report.get_clients');
	Route::post('/reporte/relacion-de-documentos-por-cobrar/validar-formulario', 'UncollectedDocumentReportController@validateForm')->name('dashboard.report.uncollected_document_report.validate_form');
	Route::post('/reporte/relacion-de-documentos-por-cobrar/listar', 'UncollectedDocumentReportController@list')->name('dashboard.report.uncollected_document_report.list');

	/** Reportes > Volumen de Ventas */
	Route::get('/reporte/volumen-ventas', 'SalesVolumeReportController@index')->name('dashboard.report.sales_volume');
	Route::post('/reporte/volumen-ventas/validar-formulario', 'SalesVolumeReportController@validateForm')->name('dashboard.report.sales_volume.validate_form');
	Route::post('/reporte/volumen-ventas/listar', 'SalesVolumeReportController@list')->name('dashboard.report.sales_volume.list');
	Route::post('/reporte/volumen-ventas/exportar', 'SalesVolumeReportController@export')->name('dashboard.report.sales_volume.export');

	/** Logística > Registro Movimiento de Existencias */
	Route::get('/logistica/registro-movimiento-existencias', 'StockRegisterController@index')->name('dashboard.logistics.stock_register');
	Route::post('/logistica/registro-movimiento-existencias/validar-formulario', 'StockRegisterController@validateForm')->name('dashboard.logistics.stock_register.validate_form');
	Route::post('/logistica/registro-movimiento-existencias/listar', 'StockRegisterController@list')->name('dashboard.logistics.stock_register.list');
	Route::post('/logistica/registro-movimiento-existencias/listar-cuentas', 'StockRegisterController@getAccounts')->name('dashboard.logistics.stock_register.get_accounts');
	Route::post('/logistica/registro-movimiento-existencias/listar-articulos', 'StockRegisterController@getArticles')->name('dashboard.logistics.stock_register.get_articles');
	Route::post('/logistica/registro-movimiento-existencias/obtener-percepcion', 'StockRegisterController@getPerceptionPercentage')->name('dashboard.logistics.stock_register.get_perception_percentage');
	Route::post('/logistica/registro-movimiento-existencias/obtener-tasas', 'StockRegisterController@getArticleRates')->name('dashboard.logistics.stock_register.get_article_rates');
	Route::post('/logistica/registro-movimiento-existencias/obtener-articulo', 'StockRegisterController@getArticle')->name('dashboard.logistics.stock_register.get_article');
	Route::post('/logistica/registro-movimiento-existencias/guardar', 'StockRegisterController@store')->name('dashboard.logistics.stock_register.store');

	/** Logística > Inventario */
	Route::get('/logistica/inventario', 'InventoryController@index')->name('dashboard.logistics.inventories');
	Route::post('/logistica/inventario/validar-formulario', 'InventoryController@validateForm')->name('dashboard.logistics.inventories.validate_form');
	Route::post('/logistica/inventario/listar', 'InventoryController@list')->name('dashboard.logistics.inventories.list');
	Route::post('/logistica/inventario/obtener-articulos', 'InventoryController@getArticles')->name('dashboard.logistics.inventories.get_articles');
	Route::post('/logistica/inventario/crear-registro', 'InventoryController@createRecord')->name('dashboard.logistics.inventories.create_record');
	Route::post('/logistica/inventario/cerrar-registro', 'InventoryController@closeRecord')->name('dashboard.logistics.inventories.close_record');
	Route::post('/logistica/inventario/detalle', 'InventoryController@detail')->name('dashboard.logistics.inventories.detail');
	Route::post('/logistica/inventario/obtener-select2', 'InventoryController@getSelect2')->name('dashboard.logistics.inventories.get_select2');
	Route::post('/logistica/inventario/guardar', 'InventoryController@store')->name('dashboard.logistics.inventories.store');
	Route::post('/logistica/inventario/eliminar', 'InventoryController@delete')->name('dashboard.logistics.inventories.delete');
	Route::post('/logistica/inventario/formulario-registro', 'InventoryController@formRecord')->name('dashboard.logistics.inventories.form_record');
	Route::post('/logistica/inventario/exportar-registro', 'InventoryController@exportRecord')->name('dashboard.logistics.inventories.export_record');

	/** Logística > Artículos */
	Route::get('/articulos', 'ArticleController@index')->name('dashboard.articles');
	Route::post('/articulos/validar-formulario', 'ArticleController@validateForm')->name('dashboard.articles.validate_form');
	Route::post('/articulos/listar', 'ArticleController@list')->name('dashboard.articles.list');
	Route::post('/articulos/guardar', 'ArticleController@store')->name('dashboard.articles.store');
	Route::post('/articulos/detalle', 'ArticleController@detail')->name('dashboard.articles.detail');
	Route::post('/articulos/eliminar', 'ArticleController@delete')->name('dashboard.articles.delete');
	Route::post('/articulos/exportar', 'ArticleController@exportRecord')->name('dashboard.articles.export_record');

	/** Logística > Clasificaciones */
	Route::get('/clasificaciones', 'ClassificationController@index')->name('dashboard.classifications');
	Route::post('/clasificaciones/validar-formulario', 'ClassificationController@validateForm')->name('dashboard.classifications.validate_form');
	Route::post('/clasificaciones/listar', 'ClassificationController@list')->name('dashboard.classifications.list');
	Route::post('/clasificaciones/guardar', 'ClassificationController@store')->name('dashboard.classifications.store');
	Route::post('/clasificaciones/detalle', 'ClassificationController@detail')->name('dashboard.classifications.detail');
	Route::post('/clasificaciones/eliminar', 'ClassificationController@delete')->name('dashboard.classifications.delete');

	/** Logística > Proveedores */
	Route::get('/proveedores', 'ProviderController@index')->name('dashboard.providers');
	Route::post('/proveedores/validar-formulario', 'ProviderController@validateForm')->name('dashboard.providers.validate_form');
	Route::post('/proveedores/listar', 'ProviderController@list')->name('dashboard.providers.list');
	Route::post('/proveedores/guardar', 'ProviderController@store')->name('dashboard.providers.store');
	Route::post('/proveedores/detalle', 'ProviderController@detail')->name('dashboard.providers.detail');
	Route::post('/proveedores/obtener-ubigeos', 'ProviderController@getUbigeos')->name('dashboard.providers.get_ubigeos');
	Route::post('/proveedores/obtener-ubigeo', 'ProviderController@getUbigeo')->name('dashboard.providers.get_ubigeo');
	Route::post('/proveedores/eliminar', 'ProviderController@delete')->name('dashboard.providers.delete');

	Route::get('/logistica/inventario/test', 'InventoryController@test')->name('dashboard.logistics.inventories.test');

	Route::get('/clientes/store/{id}', 'ClientController@store_tmp')->name('dashboard.client.store_tmp');
	Route::get('/clientes/update/{id}', 'ClientController@update')->name('dashboard.client.update');
	Route::get('/clientes/update-address/{id}', 'ClientController@update_address')->name('dashboard.client.update_address');

	Route::get('/boletas-backup', 'BackupController@boletaBackup');
});
