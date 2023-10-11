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

    /** importación de facturas*/
	Route::get('/facturacion/importar', 'FacturationController@showImport')->name('facturations.import');
	Route::post('/facturacion/importar', 'FacturationController@postImport')->name('facturations.import');

	/** Facturación > Envío OSE */
	Route::get('/', 'VoucherController@sendOse')->name('dashboard.voucher.send_ose');
	Route::post('/facturacion/validar-formulario-documentos', 'VoucherController@validate_voucher_form')->name('dashboard.voucher.validate_voucher_form');
	Route::post('/facturacion/obtener-documentos-tabla', 'VoucherController@get_vouchers_for_table')->name('dashboard.voucher.get_vouchers_for_table');
	Route::post('/facturacion/listar', 'VoucherController@list')->name('dashboard.voucher.list');
	Route::post('/facturacion/obtener-detalle-documento', 'VoucherController@get_voucher_detail')->name('dashboard.voucher.get_voucher_detail');
	Route::post('/facturacion/enviar-documento', 'VoucherController@send_voucher')->name('dashboard.voucher.send_voucher');

	/** Facturación > Registro de Documentos por Cobrar */
	Route::get('/facturacion/documentos-por-cobrar', 'RegisterDocumentChargeController@index')->name('dashboard.voucher.register_document_charge');
	Route::post('/facturacion/documentos-por-cobrar/validar-primer-paso', 'RegisterDocumentChargeController@validateFirstStep')->name('dashboard.voucher.register_document_charge.validate_first_step');
	Route::post('/facturacion/documentos-por-cobrar/obtener-comprobante', 'RegisterDocumentChargeController@getVoucher')->name('dashboard.voucher.register_document_charge.get_voucher');
	Route::post('/facturacion/documentos-por-cobrar/validar-segundo-paso', 'RegisterDocumentChargeController@validateSecondStep')->name('dashboard.voucher.register_document_charge.validate_second_step');
	Route::post('/facturacion/documentos-por-cobrar/obtener-clientes', 'RegisterDocumentChargeController@getClients')->name('dashboard.voucher.register_document_charge.get_clients');
	Route::post('/facturacion/documentos-por-cobrar/validar-tercer-paso', 'RegisterDocumentChargeController@validateThirdStep')->name('dashboard.voucher.register_document_charge.validate_third_step');
	Route::post('/facturacion/documentos-por-cobrar/guardar', 'RegisterDocumentChargeController@store')->name('dashboard.voucher.register_document_charge.store');
	Route::post('/facturacion/documentos-por-cobrar/obetener-referencias', 'RegisterDocumentChargeController@getReferences')->name('dashboard.voucher.register_document_charge.get_references');

	/** Facturación > Registro de Cobranzas */
	Route::get('/facturacion/cobranzas', 'CollectionRegisterController@index')->name('dashboard.voucher.collection_register');
	Route::post('/facturacion/cobranzas/obtener-clientes', 'CollectionRegisterController@getClients')->name('dashboard.voucher.collection_register.get_clients');
	Route::post('/facturacion/cobranzas/validar-primer-paso', 'CollectionRegisterController@validateFirstStep')->name('dashboard.voucher.collection_register.validate_first_step');
	Route::post('/facturacion/cobranzas/validar-segundo-paso', 'CollectionRegisterController@validateSecondStep')->name('dashboard.voucher.collection_register.validate_second_step');
	Route::post('/facturacion/cobranzas/obtener-ventas', 'CollectionRegisterController@getSales')->name('dashboard.voucher.collection_register.get_sales');
	Route::post('/facturacion/cobranzas/guardar', 'CollectionRegisterController@store')->name('dashboard.voucher.collection_register.store');
	Route::post('/facturacion/cobranzas/obtener-saldos', 'CollectionRegisterController@getSaldosFavor')->name('dashboard.voucher.collection_register.get_saldos_favor');
	Route::post('/facturacion/cobranzas/obtener-documentos', 'CollectionRegisterController@getDocuments')->name('dashboard.voucher.collection_register.get_documents');

	/** Facturación > Liquidaciones Final */
	Route::get('/facturacion/liquidaciones-final', 'LiquidationFinalController@index')->name('dashboard.voucher.liquidations_final');
	Route::post('/facturacion/liquidaciones-final/validar-formulario', 'LiquidationFinalController@validateForm')->name('dashboard.voucher.liquidations_final.validate_form');
	Route::post('/facturacion/liquidaciones-final/obtener-movimientos-almacen', 'LiquidationFinalController@getWarehouseMovements')->name('dashboard.voucher.liquidations_final.get_warehouse_movements');
	Route::post('/facturacion/liquidaciones-final/listar', 'LiquidationFinalController@list')->name('dashboard.voucher.liquidations_final.list');
	Route::post('/facturacion/liquidaciones-final/obtener-clientes', 'LiquidationFinalController@getClients')->name('dashboard.voucher.liquidations_final.get_clients');
	Route::post('/facturacion/liquidaciones-final/obtener-sale-serie', 'LiquidationFinalController@getSaleSeries')->name('dashboard.voucher.liquidations_final.get_sale_serie');
	Route::post('/facturacion/liquidaciones-final/obtener-articles-clients', 'LiquidationFinalController@getArticles')->name('dashboard.voucher.liquidations_final.get_articles_clients');
	Route::post('/facturacion/liquidaciones-final/obtener-precio-articulo', 'LiquidationFinalController@getArticlePrice')->name('dashboard.voucher.liquidations_final.get_article_price');
	Route::post('/facturacion/liquidaciones-final/obtener-cuentas-banco', 'LiquidationFinalController@getBankAccounts')->name('dashboard.voucher.liquidations_final.get_bank_accounts');
	Route::post('/facturacion/liquidaciones-final/verificar-documento', 'LiquidationFinalController@verifyDocumentType')->name('dashboard.voucher.liquidations_final.verify_document_type');
	Route::post('/facturacion/liquidaciones-final/guardar', 'LiquidationFinalController@store')->name('dashboard.voucher.liquidations_final.store');
	Route::post('/facturacion/liquidaciones-final/obtener-saldo-favor', 'LiquidationFinalController@getSaldoFavor')->name('dashboard.voucher.liquidations_final.get_saldo_favor');

	/** Facturación > Liquidaciones Glp */
	Route::get('/facturacion/liquidaciones-glp', 'LiquidacionGlpController@index')->name('dashboard.operations.voucher.liquidations_glp');
	Route::post('/facturacion/liquidaciones-glp/validar-formulario', 'LiquidacionGlpController@validateForm')->name('dashboard.operations.voucher.liquidations_glp.validate_form');
	Route::post('/facturacion/liquidaciones-glp/obtener-movimientos-almacen', 'LiquidacionGlpController@getWarehouseMovements')->name('dashboard.operations.voucher.liquidations_glp.get_warehouse_movements');
	Route::post('/facturacion/liquidaciones-glp/listar', 'LiquidacionGlpController@list')->name('dashboard.voucher.liquidations_glp.list');
	Route::post('/facturacion/liquidaciones-glp/obtener-clientes', 'LiquidacionGlpController@getClients')->name('dashboard.operations.voucher.liquidations_glp.get_clients');
	Route::post('/facturacion/liquidaciones-glp/obtener-glp-serie', 'LiquidacionGlpController@getGlpSeries')->name('dashboard.voucher.liquidations_glp.get_glp_serie');
	Route::post('/facturacion/liquidaciones-glp/obtener-precio-articulo', 'LiquidacionGlpController@getArticlePrice')->name('dashboard.operations.voucher.liquidations_glp.get_article_price');
	Route::post('/facturacion/liquidaciones-glp/obtener-cuentas-banco', 'LiquidacionGlpController@getBankAccounts')->name('dashboard.operations.voucher.liquidations_glp.get_bank_accounts');
	Route::post('/facturacion/liquidaciones-glp/verificar-documento', 'LiquidacionGlpController@verifyDocumentType')->name('dashboard.operations.voucher.liquidations_glp.verify_document_type');
	Route::post('/facturacion/liquidaciones-glp/guardar', 'LiquidacionGlpController@store')->name('dashboard.operations.voucher.liquidations_glp.store');
	Route::post('/facturacion/liquidaciones-glp/get-guide-number', 'LiquidacionGlpController@getGuideNumber')->name('dashboard.operations.voucher.liquidations_glp.get_guide_number');
	Route::post('/facturacion/liquidaciones-glp/get-scop-number', 'LiquidacionGlpController@getScopNumber')->name('dashboard.operations.voucher.liquidations_glp.get_scop_number');
	Route::post('/facturacion/liquidaciones-glp/get-saldo-favor', 'LiquidacionGlpController@getSaldoFavor')->name('dashboard.operations.voucher.liquidations_glp.get_saldo_favor');
	Route::post('/facturacion/liquidaciones-glp/get-empleados', 'LiquidacionGlpController@getAccounts')->name('dashboard.operations.voucher.liquidations_glp.get_accounts');

	/** Facturación > Remesas */
	Route::get('/facturacion/remesas', 'RemesasController@index')->name('dashboard.facturation.voucher.remesas');
	Route::post('/facturacion/remesas/crear', 'RemesasController@store')->name('dashboard.facturation.voucher.remesas.store');

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

	/** Reportes > Liquidaciones Resumido*/
	Route::get('/reporte/liquidaciones-resumen', 'LiquidationTotalReportController@index')->name('dashboard.report.liquidations_total');
	Route::post('/reporte/liquidaciones-resumen/validar-formulario', 'LiquidationTotalReportController@validateForm')->name('dashboard.report.liquidations_total.validate_form');
	Route::post('/reporte/liquidaciones-resumen/obtener-clientes', 'LiquidationTotalReportController@getClients')->name('dashboard.report.liquidations_total.get_clients');
	Route::post('/reporte/liquidaciones-resumen/listar', 'LiquidationTotalReportController@list')->name('dashboard.report.liquidations_total.list');

	/** Reportes > Guias */
	Route::get('/reporte/guias', 'StockFinalReportController@index')->name('dashboard.report.stock_final');
	Route::post('/reporte/guias/validar-formulario', 'StockFinalReportController@validateForm')->name('dashboard.report.stock_final.validate_form');
	Route::post('/reporte/guias/obtener-guias', 'StockFinalReportController@getWarehouseMovements')->name('dashboard.report.stock_final.get_warehouse_movements');
	Route::post('/reporte/guias/listar', 'StockFinalReportController@list')->name('dashboard.report.stock_final.list');
	Route::post('/reporte/guias/detalle', 'StockFinalReportController@detail')->name('dashboard.report.stock_final.detail');
	Route::post('/reporte/guias/actualizar', 'StockFinalReportController@update')->name('dashboard.report.stock_final.update');

    /** Reportes > Estado de Guías */
	Route::get('/reporte/estado-guias', 'GuidesScopReportController@index')->name('dashboard.report.guides_scop');
	Route::post('/reporte/estado-guias/validar-formulario', 'GuidesScopReportController@validateForm')->name('dashboard.report.guides_scop.validate_form');
	Route::post('/reporte/estado-guias/obtener-clientes', 'GuidesScopReportController@getClients')->name('dashboard.report.guides_scop.get_warehouse_movements');
	Route::post('/reporte/estado-guias/listar', 'GuidesScopReportController@list')->name('dashboard.report.guides_scop.list');
	Route::post('/reporte/estado-guias/detalle', 'GuidesScopReportController@detail')->name('dashboard.report.guides_scop.detail');
	Route::post('/reporte/estado-guias/actualizar', 'GuidesScopReportController@update')->name('dashboard.report.guides_scop.update');

	/** Reportes > Facturas emitidas */
	Route::get('/reporte/facturas-emitidas', 'FacturationSalesReportController@index')->name('dashboard.report.facturations_sales');
	Route::post('/reporte/facturas-emitidas/validar-formulario', 'FacturationSalesReportController@validateForm')->name('dashboard.report.facturations_sales.validate_form');
	Route::post('/reporte/facturas-emitidas/obtener-clientes', 'FacturationSalesReportController@getClients')->name('dashboard.report.facturations_sales.get_clients');
	Route::post('/reporte/facturas-emitidas/listar', 'FacturationSalesReportController@list')->name('dashboard.report.facturations_sales.list');
	Route::post('/reporte/facturas-emitidas/obtener-voucher', 'FacturationSalesReportController@getVoucher')->name('dashboard.report.facturations_sales.get_voucher');
	Route::post('/reporte/facturas-emitidas/actualizar-voucher', 'FacturationSalesReportController@updateVoucher')->name('dashboard.report.facturations_sales.update_voucher');
	Route::post('/reporte/facturas-emitidas/eliminar-voucher', 'FacturationSalesReportController@deleteVoucher')->name('dashboard.report.facturations_sales.delete_voucher');

	/** Reportes > Boletas emitidas */
	Route::get('/reporte/boletas-emitidas', 'FacturationBoletasReportController@index')->name('dashboard.report.facturation_boletas');
	Route::post('/reporte/boletas-emitidas/validar-formulario', 'FacturationBoletasReportController@validateForm')->name('dashboard.report.facturation_boletas.validate_form');
	Route::post('/reporte/boletas-emitidas/obtener-clientes', 'FacturationBoletasReportController@getClients')->name('dashboard.report.facturation_boletas.get_clients');
	Route::post('/reporte/boletas-emitidas/listar', 'FacturationBoletasReportController@list')->name('dashboard.report.facturation_boletas.list');

	/** Reportes > Liquidación Detallado */
	Route::get('/reporte/liquidacion-detallado', 'CollectionSalesReportController@index')->name('dashboard.report.collection_sales_report');
	Route::post('/reporte/liquidacion-detallado/obtener-clientes', 'CollectionSalesReportController@getClients')->name('dashboard.report.collection_sales_report.get_clients');
	Route::post('/reporte/liquidacion-detallado/validar-formulario', 'CollectionSalesReportController@validateForm')->name('dashboard.report.collection_sales_report.validate_form');
	Route::post('/reporte/liquidacion-detallado/listar', 'CollectionSalesReportController@list')->name('dashboard.report.collection_sales_report.list');


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

	/** Reportes > GLP COSTO*/
	Route::get('/reporte/glp-diaria', 'DaysGlpReportController@index')->name('dashboard.report.days_glp');
	Route::post('/reporte/glp-diaria/validar-formulario', 'DaysGlpReportController@validateForm')->name('dashboard.report.days_glp.validate_form');
	Route::post('/reporte/glp-diaria/listar', 'DaysGlpReportController@list')->name('dashboard.report.days_glp.list');

	/** Reportes > GLP Global*/
	Route::get('/reporte/report-glp-global', 'ReportsGlpGlobalController@index')->name('dashboard.report.report_glp_global');
	Route::post('/reporte/report-glp-global/get-stocks', 'ReportsGlpGlobalController@getStockArticles')->name('dashboard.report.report_glp_global.get_stocks_articles');

	/** Reportes > Envases General*/
	Route::get('/reporte/report-envase-general', 'ReportsEnvasesGeneralController@index')->name('dashboard.report.report_envases_general');
	Route::post('/reporte/report-envase-general/get-stocks', 'ReportsEnvasesGeneralController@getStockArticles')->name('dashboard.report.report_envases_general.get_stocks_articles');

	/** Reportes > Saldos Favor*/
	Route::get('/reporte/report-saldos-favor', 'ReportsSaldosFavorController@index')->name('dashboard.report.report_saldos_favor');
	Route::post('/reporte/report-saldos-favor/obtener-saldos', 'ReportsSaldosFavorController@getSaldosFavor')->name('dashboard.report.report_saldos_favor.get_saldos_favor');

	/** Reportes > Balones Prestados*/
	Route::get('/reporte/report-balones-prestados', 'ReportsBalonesPressController@index')->name('dashboard.report.report_balones_press');
	Route::post('/reporte/report-balones-prestados/listar', 'ReportsBalonesPressController@getContainers')->name('dashboard.report.report_balones_press.get_containers');

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
	
	/** Logística > Registro Movimiento de Existencias 2 */
	Route::get('/logistica/registro-movimiento-existencias-prueba', 'StockRegisterBetaController@index')->name('dashboard.logistics.stock_register_beta');
	Route::post('/logistica/registro-movimiento-existencias/validar-formulario-prueba', 'StockRegisterBetaController@validateForm')->name('dashboard.logistics.stock_register_beta.validate_form');
	Route::post('/logistica/registro-movimiento-existencias/listar-prueba', 'StockRegisterBetaController@list')->name('dashboard.logistics.stock_register_beta.list');
	Route::post('/logistica/registro-movimiento-existencias/listar-cuentas-prueba', 'StockRegisterBetaController@getAccounts')->name('dashboard.logistics.stock_register_beta.get_accounts');
	Route::post('/logistica/registro-movimiento-existencias/listar-articulos-prueba', 'StockRegisterBetaController@getArticles')->name('dashboard.logistics.stock_register_beta.get_articles');
	Route::post('/logistica/registro-movimiento-existencias/obtener-percepcion-prueba', 'StockRegisterBetaController@getPerceptionPercentage')->name('dashboard.logistics.stock_register_beta.get_perception_percentage');
	Route::post('/logistica/registro-movimiento-existencias/obtener-tasas-prueba', 'StockRegisterBetaController@getArticleRates')->name('dashboard.logistics.stock_register_beta.get_article_rates');
	Route::post('/logistica/registro-movimiento-existencias/obtener-articulo-prueba', 'StockRegisterBetaController@getArticle')->name('dashboard.logistics.stock_register_beta.get_article');
	Route::post('/logistica/registro-movimiento-existencias/guardar-prueba', 'StockRegisterBetaController@store')->name('dashboard.logistics.stock_register_beta.store');
	Route::get('/logistica/registro-movimiento-existencias/obtener-siguiente-correlativo-prueba', 'StockRegisterBetaController@getNextcorrelative')->name('dashboard.logistics.stock_register_beta.next_correlative');

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

    /** Logística > Reporte de Movimiento de Existencias */
	Route::get('/logistica/reporte-movimiento-existencias', 'StockRegisterReportController@index')->name('dashboard.logistics.report.stock_register');
	Route::get('/logistica/reporte-movimiento-existencias-valorizado', 'StockRegisterReportController@index')->name('dashboard.logistics.report.stock_register_valued');
	Route::post('/logistica/reporte-movimiento-existencias/validar-formulario', 'StockRegisterReportController@validateForm')->name('dashboard.logistics.report.stock_register.validate_form');
	Route::post('/logistica/reporte-movimiento-existencias/listar', 'StockRegisterReportController@list')->name('dashboard.logistics.report.stock_register.list');
	Route::post('/logistica/reporte-movimiento-existencias/detalle', 'StockRegisterReportController@detail')->name('dashboard.logistics.report.stock_register.detail');
	Route::post('/logistica/reporte-movimiento-existencias/actualizar', 'StockRegisterReportController@update')->name('dashboard.logistics.report.stock_register.update');

    /** Operaciones > Guias */
	Route::get('/operaciones/registro-movimiento-existencias', 'GuidesRegisterController@index')->name('dashboard.operations.guides_register');
	Route::post('/operaciones/registro-movimiento-existencias/validar-formulario', 'GuidesRegisterController@validateForm')->name('dashboard.operations.guides_register.validate_form');
	Route::post('/operaciones/registro-movimiento-existencias/listar', 'GuidesRegisterController@list')->name('dashboard.operations.guides_register.list');
	Route::post('/operaciones/registro-movimiento-existencias/listar-cuentas', 'GuidesRegisterController@getAccounts')->name('dashboard.operations.guides_register.get_accounts');
	Route::post('/operaciones/registro-movimiento-existencias/listar-articulos', 'GuidesRegisterController@getArticles')->name('dashboard.operations.guides_register.get_articles');
	Route::post('/operaciones/registro-movimiento-existencias/obtener-percepcion', 'GuidesRegisterController@getPerceptionPercentage')->name('dashboard.operations.guides_register.get_perception_percentage');
	Route::post('/operaciones/registro-movimiento-existencias/obtener-tasas', 'GuidesRegisterController@getArticleRates')->name('dashboard.operations.guides_register.get_article_rates');
	Route::post('/operaciones/registro-movimiento-existencias/obtener-articulo', 'GuidesRegisterController@getArticle')->name('dashboard.operations.guides_register.get_article');
	Route::post('/operaciones/registro-movimiento-existencias/guardar', 'GuidesRegisterController@store')->name('dashboard.operations.guides_register.store');
	Route::get('/operaciones/registro-movimiento-existencias/obtener-siguiente-correlativo', 'GuidesRegisterController@getNextcorrelative')->name('dashboard.operations.guides_register.next_correlative');

	/** Operaciones > Parte de Almacén */
	Route::get('/operaciones/parte-almacen', 'OperationsPartController@index')->name('dashboard.operations.operations_part');
	Route::post('/operaciones/parte-almacen/validar-formulario', 'OperationsPartController@validateForm')->name('dashboard.operations.operations_part.validate_form');
	Route::post('/operaciones/parte-almacen/obtener-movimientos-almacen', 'OperationsPartController@getWarehouseMovements')->name('dashboard.operations.operations_part.get_warehouse_movements');
	Route::post('/operaciones/parte-almacen/listar', 'OperationsPartController@list')->name('dashboard.operations.operations_part.list');
	Route::post('/operaciones/parte-almacen/exportar', 'OperationsPartController@export')->name('dashboard.operations.operations_part.export');
	Route::get('/operaciones/parte-almacen/exportar-guia-remision', 'OperationsPartController@exportReferralGuide')->name('dashboard.operations.operations_part.export_referral_guide');

	/** Operaciones > Validar Guías */
	Route::get('/operaciones/validar-guias', 'GuidesValidateController@index')->name('dashboard.operations.guides_validate');
	Route::post('/operaciones/validar-guias/validar-formulario', 'GuidesValidateController@validateForm')->name('dashboard.operations.guides_validate.validate_form');
	Route::post('/operaciones/validar-guias/obtener-movimientos', 'GuidesValidateController@getWarehouseMovements')->name('dashboard.operations.guides_validate.get_warehouse_movements');
	Route::post('/operaciones/validar-guias/listar-detalle-movimientos', 'GuidesValidateController@list')->name('dashboard.operations.guides_validate.list');
	Route::post('/operaciones/validar-guias/remove-article', 'GuidesValidateController@removeArticle')->name('dashboard.operations.guides_validate.remove_article');
	Route::post('/operaciones/validar-guias/update-articles', 'GuidesValidateController@updateArticles')->name('dashboard.operations.guides_validate.update_articles');
	Route::post('/operaciones/validar-guias/get-articles', 'GuidesValidateController@getArticles')->name('dashboard.operations.guides_validate.get_articles');
	Route::post('/operaciones/validar-guias/validate-guides', 'GuidesValidateController@validateGuides')->name('dashboard.operations.guides_validate.validate_guides');

   /** Operaciones > Retorno de Guías */
    Route::get('/operaciones/retorno-guias', 'GuidesReturnController@index')->name('dashboard.operations.guides_return');
	Route::post('/operaciones/retorno-guias/validar-formulario', 'GuidesReturnController@validateForm')->name('dashboard.operations.guides_return.validate_form');
	Route::post('/operaciones/retorno-guias/obtener-movimientos-almacen', 'GuidesReturnController@getWarehouseMovements')->name('dashboard.operations.guides_return.get_warehouse_movements');
	Route::post('/operaciones/retorno-guias/listar', 'GuidesReturnController@list')->name('dashboard.operations.guides_return.list');
	Route::post('/operaciones/retorno-guias/actualizar', 'GuidesReturnController@update')->name('dashboard.operations.guides_return.update');
	Route::post('/operaciones/retorno-guias/obtener-clientes', 'GuidesReturnController@getClients')->name('dashboard.operations.guides_return.get_clients');
	Route::post('/operaciones/retorno-guias/obtener-balon', 'GuidesReturnController@getBalon')->name('dashboard.operations.guides_return.get_balon');
	Route::post('/operaciones/retorno-guias/obtener-balones', 'GuidesReturnController@getBalons')->name('dashboard.operations.guides_return.get_balons');

	/** Operaciones > Validar Prestamos */
	Route::get('/operaciones/validar-prestamos', 'GuidesValidatePressController@index')->name('dashboard.operations.guides_validate_press');
	Route::post('/operaciones/validar-prestamos/validar-formulario', 'GuidesValidatePressController@validateForm')->name('dashboard.operations.guides_validate_press.validate_form');
	Route::post('/operaciones/validar-prestamos/obtener-movimientos', 'GuidesValidatePressController@getWarehouseMovements')->name('dashboard.operations.guides_validate_press.get_warehouse_movements');
	Route::post('/operaciones/validar-prestamos/listar-detalle-movimientos', 'GuidesValidatePressController@list')->name('dashboard.operations.guides_validate_press.list');
	Route::post('/operaciones/validar-prestamos/validate-guides', 'GuidesValidatePressController@validateGuides')->name('dashboard.operations.guides_validate_press.validate_guides');
	Route::post('/operaciones/validar-prestamos/comodato-guides', 'GuidesValidatePressController@comodatoGuides')->name('dashboard.operations.guides_validate_press.comodato_guides');
	Route::post('/operaciones/validar-prestamos/view-detail', 'GuidesValidatePressController@viewDetail')->name('dashboard.operations.guides_validate_press.view_detail');

	/** Operaciones > Asignar Retornos */
	Route::get('/operaciones/asignar-retornos', 'GuidesAsignReturnController@index')->name('dashboard.operations.guides_asign_return');
	Route::post('/operaciones/asignar-retornos/asignar-retornos', 'GuidesAsignReturnController@validateForm')->name('dashboard.operations.guides_asign_return.validate_form');
	Route::post('/operaciones/asignar-retornos/obtener-movimientos', 'GuidesAsignReturnController@getWarehouseMovements')->name('dashboard.operations.guides_asign_return.get_warehouse_movements');
	Route::post('/operaciones/asignar-retornos/view-detail', 'GuidesAsignReturnController@viewDetail')->name('dashboard.operations.guides_asign_return.view_detail');
	Route::post('/operaciones/asignar-retornos/balons-press', 'GuidesAsignReturnController@balonsPress')->name('dashboard.operations.guides_asign_return.balons_press');

	/** Operaciones > Registro de producción */
	Route::get('/logistica/registro-produccion', 'ProductionController@index')->name('dashboard.logistics.production_register');
	Route::post('/logistica/registro-produccion/validar-formulario', 'ProductionController@validateForm')->name('dashboard.logistics.production_register.validate_form');
	Route::post('/logistica/registro-produccion/listar', 'ProductionController@list')->name('dashboard.logistics.production_register.list');
	Route::post('/logistica/registro-produccion/listar-cuentas', 'ProductionController@getAccounts')->name('dashboard.logistics.production_register.get_accounts');
	Route::post('/logistica/registro-produccion/listar-articulos', 'ProductionController@getArticles')->name('dashboard.logistics.production_register.get_articles');
	Route::post('/logistica/registro-produccion/obtener-percepcion', 'ProductionController@getPerceptionPercentage')->name('dashboard.logistics.production_register.get_perception_percentage');
	Route::post('/logistica/registro-produccion/obtener-tasas', 'ProductionController@getArticleRates')->name('dashboard.logistics.production_register.get_article_rates');
	Route::post('/logistica/registro-produccion/obtener-articulo', 'ProductionController@getArticle')->name('dashboard.logistics.production_register.get_article');
	Route::post('/logistica/registro-produccion/guardar', 'ProductionController@store')->name('dashboard.logistics.production_register.store');

	/** Reportes > Prueba Guias */
	Route::get('/operaciones/guias-registro', 'StockSeekRegisterReportController@index')->name('dashboard.operations.stock_seek_register');
	Route::post('/operaciones/guias-registro/validar-formulario', 'StockSeekRegisterReportController@validateForm')->name('dashboard.operations.stock_seek_register.validate_form');
	Route::post('/operaciones/guias-registro/obtener-guias', 'StockSeekRegisterReportController@getWarehouseMovements')->name('dashboard.operations.stock_seek_register.get_warehouse_movements');
	Route::post('/operaciones/guias-registro/listar', 'StockSeekRegisterReportController@list')->name('dashboard.operations.stock_seek_register.list');
	Route::post('/operaciones/guias-registro/detalle', 'StockSeekRegisterReportController@detail')->name('dashboard.operations.stock_seek_register.detail');
	Route::post('/operaciones/guias-registro/actualizar', 'StockSeekRegisterReportController@update')->name('dashboard.operations.stock_seek_register.update');

    /** Reportes > Buscador de Guías */
	Route::get('/operaciones/buscador-guias', 'GuidesSeekReportController@index')->name('dashboard.operations.guides_seek');
	Route::post('/operaciones/buscador-guias/validar-formulario', 'GuidesSeekReportController@validateForm')->name('dashboard.operations.guides_seek.validate_form');
	Route::post('/operaciones/buscador-guias/listar', 'GuidesSeekReportController@list')->name('dashboard.operations.guides_seek.list');

	Route::get('/operaciones/inventario', 'OpeAdjustInventoryController@index')->name('dashboard.operations.opeinventories');
	Route::post('/operaciones/inventario/validar-formulario', 'OpeAdjustInventoryController@validateForm')->name('dashboard.operations.opeinventories.validate_form');
	Route::post('/operaciones/inventario/listar', 'OpeAdjustInventoryController@list')->name('dashboard.operations.opeinventories.list');
	Route::post('/operaciones/inventario/obtener-articulos', 'OpeAdjustInventoryController@getArticles')->name('dashboard.operations.opeinventories.get_articles');
	Route::post('/operaciones/inventario/crear-registro', 'OpeAdjustInventoryController@createRecord')->name('dashboard.operations.opeinventories.create_record');
	Route::post('/operaciones/inventario/cerrar-registro', 'OpeAdjustInventoryController@closeRecord')->name('dashboard.operations.opeinventories.close_record');
	Route::post('/operaciones/inventario/detalle', 'OpeAdjustInventoryController@detail')->name('dashboard.operations.opeinventories.detail');
	Route::post('/operaciones/inventario/obtener-select2', 'OpeAdjustInventoryController@getSelect2')->name('dashboard.operations.opeinventories.get_select2');
	Route::post('/operaciones/inventario/guardar', 'OpeAdjustInventoryController@store')->name('dashboard.operations.opeinventories.store');
	Route::post('/operaciones/inventario/eliminar', 'OpeAdjustInventoryController@delete')->name('dashboard.operations.opeinventories.delete');
	Route::post('/operaciones/inventario/formulario-registro', 'AdjustInventoryController@formRecord')->name('dashboard.operations.opeinventories.form_record');
	Route::post('/operaciones/inventario/exportar-registro', 'OpeAdjustInventoryController@exportRecord')->name('dashboard.operations.opeinventories.export_record');

	Route::get('/operaciones/anulacionguias', 'AnulacionGuiaController@index')->name('dashboard.operations.anulacionguias');
	Route::post('/operaciones/anulacionguias/search', 'AnulacionGuiaController@searchGuides')->name('dashboard.operations.anulacionguias.search');
	Route::post('/operaciones/anulacionguias/anular', 'AnulacionGuiaController@anular')->name('dashboard.operations.anulacionguias.anular');

	/** Comercial > Estado de Guías */
	Route::get('/comercial/estado-guias', 'GuidesCommercialReportController@index')->name('dashboard.commercial.guides_commercial');
	Route::post('/comercial/estado-guias/validar-formulario', 'GuidesCommercialReportController@validateForm')->name('dashboard.commercial.guides_commercial.validate_form');
	Route::post('/comercial/estado-guias/obtener-clientes', 'GuidesCommercialReportController@getClients')->name('dashboard.commercial.guides_commercial.get_warehouse_movements');
	Route::post('/comercial/estado-guias/listar', 'GuidesCommercialReportController@list')->name('dashboard.commercial.guides_commercial.list');
	Route::post('/comercial/estado-guias/detalle', 'GuidesCommercialReportController@detail')->name('dashboard.commercial.guides_commercial.detail');
	Route::post('/comercial/estado-guias/actualizar', 'GuidesCommercialReportController@update')->name('dashboard.commercial.guides_commercial.update');

    /** Comercial > Lista de Precios */
	Route::get('/comercial/lista-de-precios', 'PriceListController@index')->name('dashboard.commercial.price_list');
	Route::post('/comercial/lista-de-precios/validar-formulario', 'PriceListController@validateForm')->name('dashboard.commercial.price_list.validate_form');
	Route::post('/comercial/lista-de-precios/listar', 'PriceListController@list')->name('dashboard.commercial.price_list.list');
	Route::post('/comercial/lista-de-precios/obtener-dia', 'PriceListController@getMinEffectiveDate')->name('dashboard.commercial.price_list.get_min_effective_date');
	Route::post('/comercial/lista-de-precios/guardar', 'PriceListController@store')->name('dashboard.commercial.price_list.store');

	/** comercial > Clientes */
	Route::get('/comercial/clientes', 'ClientController@index')->name('dashboard.commercial.clients');
	Route::post('/comercial/clientes/validar-formulario', 'ClientController@validateForm')->name('dashboard.commercial.clients.validate_form');
	Route::post('/comercial/clientes/listar', 'ClientController@list')->name('dashboard.commercial.clients.list');
	Route::post('/comercial/clientes/guardar', 'ClientController@store')->name('dashboard.commercial.clients.store');
	Route::post('/comercial/clientes/detalle', 'ClientController@detail')->name('dashboard.commercial.clients.detail');
	Route::post('/comercial/clientes/obtener-ubigeos', 'ClientController@getUbigeos')->name('dashboard.commercial.clients.get_ubigeos');
	Route::post('/comercial/clientes/obtener-clientes', 'ClientController@getClients')->name('dashboard.commercial.clients.get_clients');
	Route::post('/comercial/clientes/obtener-select2', 'ClientController@getSelect2')->name('dashboard.commercial.clients.get_select2');
	Route::post('/comercial/clientes/eliminar', 'ClientController@delete')->name('dashboard.commercial.clients.delete');
	Route::post('/comercial/clientes/listar-direcciones', 'ClientController@addressList')->name('dashboard.commercial.clients.address_list');
	Route::post('/comercial/clientes/guardar-direccion', 'ClientController@addressStore')->name('dashboard.commercial.clients.address_store');
	Route::post('/comercial/clientes/detalle-direccion', 'ClientController@addressDetail')->name('dashboard.commercial.clients.address_detail');
	Route::post('/comercial/clientes/eliminar-direccion', 'ClientController@addressDelete')->name('dashboard.commercial.clients.address_delete');
	Route::post('/comercial/clientes/listar-precios', 'ClientController@priceList')->name('dashboard.commercial.clients.price_list');
	Route::post('/comercial/clientes/listar-articulos', 'ClientController@priceArticles')->name('dashboard.commercial.clients.price_articles');
	Route::post('/comercial/clientes/obtener-dia', 'ClientController@priceMinEffectiveDate')->name('dashboard.commercial.clients.price_min_effective_date');
	Route::post('/comercial/clientes/guardar-precio', 'ClientController@priceStore')->name('dashboard.commercial.clients.price_store');
	Route::get('/comercial/clientes/bsucar-cliente-ruc', 'ClientController@searchClientByRuc')->name('dashboard.commercial.clients.search_client');


	 /** Comercial > Venta Canal*/
	Route::get('/comercial/venta-diaria', 'CommercialChannelReportController@index')->name('dashboard.commercial.commercial_channel');
	Route::post('/comercial/venta-diaria/validar-formulario', 'CommercialChannelReportController@validateForm')->name('dashboard.commercial.commercial_channel.validate_form');
	Route::post('/comercial/venta-diaria/obtener-clientes', 'CommercialChannelReportController@getClients')->name('dashboard.commercial.commercial_channel.get_clients');
	Route::post('/comercial/venta-diaria/listar', 'CommercialChannelReportController@list')->name('dashboard.commercial.commercial_channel.list');

	/** Comercial > Lista de Precios */
	Route::get('/comercial/lista/lista-de-precios', 'PriceListReportController@index')->name('dashboard.commercial.price_list_report');
	Route::post('/comercial/lista/lista-de-precios/validar-formulario', 'PriceListReportController@validateForm')->name('dashboard.commercial.price_list_report.validate_form');
	Route::post('/comercial/lista/lista-de-precios/obtener-clientes', 'PriceListReportController@getClients')->name('dashboard.commercial.price_list_report.get_clients');
	Route::post('/comercial/lista/lista-de-precios/obtener-articulos', 'PriceListReportController@getArticles')->name('dashboard.commercial.price_list_report.get_articles');
	Route::post('/comercial/lista/lista-de-precios/listar', 'PriceListReportController@list')->name('dashboard.commercial.price_list_report.list');
	Route::post('/comercial/lista/lista-de-precios/exportar-pdf', 'PriceListReportController@exportPdf')->name('dashboard.commercial.price_list_report.export_pdf');
		
	/** Comercial > Relación de Clientes */
	Route::get('/comercial/relacion-de-clientes', 'ListClientsReportController@index')->name('dashboard.commercial.list_clients');
	Route::post('/comercial/relacion-de-clientes/validar-formulario', 'ListClientsReportController@validateForm')->name('dashboard.commercial.list_clients.validate_form');
	Route::post('/comercial/relacion-de-clientes/listar', 'ListClientsReportController@list')->name('dashboard.commercial.list_clients.list');

	/** Comercial > Venta Canal*/
	Route::get('/comercial/venta-canal', 'LiquidationChannelReportController@index')->name('dashboard.commercial.liquidations_channel');
	Route::post('/comercial/venta-canal/validar-formulario', 'LiquidationChannelReportController@validateForm')->name('dashboard.commercial.liquidations_channel.validate_form');
	Route::post('/comercial/venta-canal/obtener-clientes', 'LiquidationChannelReportController@getClients')->name('dashboard.commercial.liquidations_channel.get_clients');
	Route::post('/comercial/venta-canal/listar', 'LiquidationChannelReportController@list')->name('dashboard.commercial.liquidations_channel.list');

	/** Comercial > Venta Canal Resumido*/
	Route::get('/comercial/venta-canal-resumido', 'LiquidationChannelTotalReportController@index')->name('dashboard.commercial.liquidations_channel_total');
	Route::post('/comercial/venta-canal-resumido/validar-formulario', 'LiquidationChannelTotalReportController@validateForm')->name('dashboard.commercial.liquidations_channel_total.validate_form');
	Route::post('/comercial/venta-canal-resumido/obtener-clientes', 'LiquidationChannelTotalReportController@getClients')->name('dashboard.commercial.liquidations_channel_total.get_clients');
	Route::post('/comercial/venta-canal-resumido/listar', 'LiquidationChannelTotalReportController@list')->name('dashboard.commercial.liquidations_channel_total.list');

	/** Creditos y Cobranzas> Liquidaciones */

	/** creditos > Clientes */
	Route::get('/creditos/clientes', 'ClientController@index')->name('dashboard.voucher.clients');
	Route::post('/creditos/clientes/validar-formulario', 'ClientController@validateForm')->name('dashboard.voucher.clients.validate_form');
	Route::post('/creditos/clientes/listar', 'ClientController@list')->name('dashboard.voucher.clients.list');
	Route::post('/creditos/clientes/guardar', 'ClientController@store')->name('dashboard.voucher.clients.store');
	Route::post('/creditos/clientes/detalle', 'ClientController@detail')->name('dashboard.voucher.clients.detail');
	Route::post('/creditos/clientes/obtener-ubigeos', 'ClientController@getUbigeos')->name('dashboard.voucher.clients.get_ubigeos');
	Route::post('/creditos/clientes/obtener-clientes', 'ClientController@getClients')->name('dashboard.voucher.clients.get_clients');
	Route::post('/creditos/clientes/obtener-select2', 'ClientController@getSelect2')->name('dashboard.voucher.clients.get_select2');
	Route::post('/creditos/clientes/eliminar', 'ClientController@delete')->name('dashboard.voucher.clients.delete');
	Route::post('/creditos/clientes/listar-direcciones', 'ClientController@addressList')->name('dashboard.voucher.clients.address_list');
	Route::post('/creditos/clientes/guardar-direccion', 'ClientController@addressStore')->name('dashboard.voucher.clients.address_store');
	Route::post('/creditos/clientes/detalle-direccion', 'ClientController@addressDetail')->name('dashboard.voucher.clients.address_detail');
	Route::post('/creditos/clientes/eliminar-direccion', 'ClientController@addressDelete')->name('dashboard.voucher.clients.address_delete');
	Route::post('/creditos/clientes/listar-precios', 'ClientController@priceList')->name('dashboard.voucher.clients.price_list');
	Route::post('/creditos/clientes/listar-articulos', 'ClientController@priceArticles')->name('dashboard.voucher.clients.price_articles');
	Route::post('/creditos/clientes/obtener-dia', 'ClientController@priceMinEffectiveDate')->name('dashboard.voucher.clients.price_min_effective_date');
	Route::post('/creditos/clientes/guardar-precio', 'ClientController@priceStore')->name('dashboard.voucher.clients.price_store');

	/** Creditos y Cobranzas> Liquidaciones */
	Route::get('creditos/reporte/liquidaciones-creditos', 'LiquidationCreditsReportController@index')->name('dashboard.credits.report.liquidations_credits');
	Route::post('creditos/reporte/liquidaciones-creditos/validar-formulario', 'LiquidationCreditsReportController@validateForm')->name('dashboard.credits.report.liquidations_credits.validate_form');
	Route::post('creditos/reporte/liquidaciones-creditos/obtener-clientes', 'LiquidationCreditsReportController@getClients')->name('dashboard.credits.report.liquidations_credits.get_clients');
	Route::post('creditos/reporte/liquidaciones-creditos/listar', 'LiquidationCreditsReportController@list')->name('dashboard.credits.report.liquidations_credits.list');
	Route::get('creditos/reporte/liquidaciones-creditos/registrar-voucher', 'LiquidationCreditsReportController@showRegisterVoucher')->name('dashboard.credits.register_voucher');
	Route::post('creditos/reporte/liquidaciones-creditos/registrar-voucher', 'LiquidationCreditsReportController@registerVoucher')->name('dashboard.credits.register_voucher');

	/** Creditos y Cobranzas > Relación de Documentos Pendientes Internos*/
	Route::get('/reporte/relacion-de-documentos-pendientes-internos', 'PendingDocumentIntReportController@index')->name('dashboard.report.pending_document_int_report');
	Route::post('/reporte/relacion-de-documentos-pendientes-internos/obtener-clientes', 'PendingDocumentIntReportController@getClients')->name('dashboard.report.pending_document_int_report.get_clients');
	Route::post('/reporte/relacion-de-documentos-pendientes-internos/validar-formulario', 'PendingDocumentIntReportController@validateForm')->name('dashboard.report.pending_document_int_report.validate_form');
	Route::post('/reporte/relacion-de-documentos-pendientes-internos/listar', 'PendingDocumentIntReportController@list')->name('dashboard.report.pending_document_int_report.list');

	/** Creditos y Cobranzas > Reporte de Masa */
	Route::get('creditos/reporte/reporte-masa', 'MasaReportController@index')->name('dashboard.report.masa');
	Route::post('creditos/reporte/reporte-masa/validar-formulario', 'MasaReportController@validateForm')->name('dashboard.report.masa.validate_form');
	Route::post('creditos/reporte/reporte-masa/obtener-clientes', 'MasaReportController@getClients')->name('dashboard.report.masa.get_clients');
	Route::post('creditos/reporte/reporte-masa/listar', 'MasaReportController@list')->name('dashboard.report.masa.list');


	/** Contabilidad > Generación masiva de Comprobantes */
	Route::get('/contabilidad/generacion-masiva-comprobantes', 'VoucherMassiveGenerationController@index')->name('dashboard.voucher.massive_generation');
	Route::post('/contabilidad/generacion-masiva-comprobantes/validar-formulario', 'VoucherMassiveGenerationController@validateForm')->name('dashboard.voucher.massive_generation.validate_form');
	Route::post('/contabilidad/generacion-masiva-comprobantes/listar', 'VoucherMassiveGenerationController@list')->name('dashboard.voucher.massive_generation.list');
	Route::post('/contabilidad/generacion-masiva-comprobantes/obtener-clientes', 'VoucherMassiveGenerationController@getClients')->name('dashboard.voucher.massive_generation.get_clients');
	Route::post('/contabilidad/generacion-masiva-comprobantes/obtener-articulos', 'VoucherMassiveGenerationController@getArticles')->name('dashboard.voucher.massive_generation.get_articles');
	Route::post('/contabilidad/generacion-masiva-comprobantes/guardar', 'VoucherMassiveGenerationController@store')->name('dashboard.voucher.massive_generation.store');

	/** Contabilidad > Reporte de guias transportistas */
	Route::get('/contabilidad/guias-registro', 'TransportistRegisterReportController@index')->name('dashboard.report.transportist_register');
	Route::post('/contabilidad/guias-registro/validar-formulario', 'TransportistRegisterReportController@validateForm')->name('dashboard.report.transportist_register.validate_form');
	Route::post('/contabilidad/guias-registro/obtener-guias', 'TransportistRegisterReportController@getWarehouseMovements')->name('dashboard.report.transportist_register.get_warehouse_movements');
	Route::post('/contabilidad/guias-registro/listar', 'TransportistRegisterReportController@list')->name('dashboard.report.transportist_register.list');
	Route::post('/contabilidad/guias-registro/detalle', 'TransportistRegisterReportController@detail')->name('dashboard.report.transportist_register.detail');
	Route::post('/contabilidad/guias-registro/actualizar', 'TransportistRegisterReportController@update')->name('dashboard.report.transportist_register.update');

	/** Contabilidad > Facturas emitidas volumen */
	Route::get('/contabilidad/facturas-emitidas-volumen', 'FacturationSalesVolumeReportController@index')->name('dashboard.report.facturations_sales_volume');
	Route::post('/contabilidad/facturas-emitidas-volumen/validar-formulario', 'FacturationSalesVolumeReportController@validateForm')->name('dashboard.report.facturations_sales_volume.validate_form');
	Route::post('/contabilidad/facturas-emitidas-volumen/obtener-clientes', 'FacturationSalesVolumeReportController@getClients')->name('dashboard.report.facturations_sales_volume.get_clients');
	Route::post('/contabilidad/facturas-emitidas-volumen/listar', 'FacturationSalesVolumeReportController@list')->name('dashboard.report.facturations_sales_volume.list');

	/** Contabilidad > Volumen de Ventas */
	Route::get('/contabilidad/volumen-ventas', 'SalesVolumeReportController@index')->name('dashboard.report.sales_volume');
	Route::post('/contabilidad/volumen-ventas/validar-formulario', 'SalesVolumeReportController@validateForm')->name('dashboard.report.sales_volume.validate_form');
	Route::post('/contabilidad/volumen-ventas/listar', 'SalesVolumeReportController@list')->name('dashboard.report.sales_volume.list');
	Route::post('/contabilidad/volumen-ventas/exportar', 'SalesVolumeReportController@export')->name('dashboard.report.sales_volume.export');

	/** Contabilidad > Volumen */
	Route::get('/contabilidad/volumen-cordia', 'CordiaVolumeReportController@index')->name('dashboard.report.cordia_volume');
	Route::post('/contabilidad/volumen-cordia/validar-formulario', 'CordiaVolumeReportController@validateForm')->name('dashboard.report.cordia_volume.validate_form');
	Route::post('/contabilidad/volumen-cordia/listar', 'CordiaVolumeReportController@list')->name('dashboard.report.cordia_volume.list');
	Route::post('/contabilidad/volumen-cordia/exportar', 'CordiaVolumeReportController@export')->name('dashboard.report.cordia_volume.export');

	/** Contabilidad > Facturas volumen */
	Route::get('/contabilidad/facturas-emitidas-volumen-resumido', 'FacturationTotalSalesVolumeReportController@index')->name('dashboard.report.facturations_total_sales_volume');
	Route::post('/contabilidad/facturas-emitidas-volumen-resumido/validar-formulario', 'FacturationTotalSalesVolumeReportController@validateForm')->name('dashboard.report.facturations_total_sales_volume.validate_form');
	Route::post('/contabilidad/facturas-emitidas-volumen-resumido/obtener-clientes', 'FacturationTotalSalesVolumeReportController@getClients')->name('dashboard.report.facturations_total_sales_volume.get_clients');
	Route::post('/contabilidad/facturas-emitidas-volumen-resumido/listar', 'FacturationTotalSalesVolumeReportController@list')->name('dashboard.report.facturations_total_sales_volume.list');

    /** Compras GLP > Compras GLP */
	Route::get('/controlglp/registro-movimiento-existencias', 'GuidesGlpRegisterController@index')->name('dashboard.operations.guides_glp_register');
	Route::post('/controlglp/registro-movimiento-existencias/validar-formulario', 'GuidesGlpRegisterController@validateForm')->name('dashboard.operations.guides_glp_register.validate_form');
	Route::post('/controlglp/registro-movimiento-existencias/listar', 'GuidesGlpRegisterController@list')->name('dashboard.operations.guides_glp_register.list');
	Route::post('/controlglp/registro-movimiento-existencias/listar-cuentas', 'GuidesGlpRegisterController@getAccounts')->name('dashboard.operations.guides_glp_register.get_accounts');
	Route::post('/controlglp/registro-movimiento-existencias/listar-articulos', 'GuidesGlpRegisterController@getArticles')->name('dashboard.operations.guides_glp_register.get_articles');
	Route::post('/controlglp/registro-movimiento-existencias/obtener-percepcion', 'GuidesGlpRegisterController@getPerceptionPercentage')->name('dashboard.operations.guides_glp_register.get_perception_percentage');
	Route::post('/controlglp/registro-movimiento-existencias/obtener-tasas', 'GuidesGlpRegisterController@getArticleRates')->name('dashboard.operations.guides_glp_register.get_article_rates');
	Route::post('/controlglp/registro-movimiento-existencias/obtener-articulo', 'GuidesGlpRegisterController@getArticle')->name('dashboard.operations.guides_glp_register.get_article');
	Route::post('/controlglp/registro-movimiento-existencias/guardar', 'GuidesGlpRegisterController@store')->name('dashboard.operations.guides_glp_register.store');

	/** Compras GLP > Registro de Abastecimiento Comercial*/
	Route::get('/controlglp/registro-movimiento-comercial', 'StockGlpRegisterController@index')->name('dashboard.operations.stock_glp_register');
	Route::post('/controlglp/registro-movimiento-comercial/validar-formulario', 'StockGlpRegisterController@validateForm')->name('dashboard.operations.stock_glp_register.validate_form');
	Route::post('/controlglp/registro-movimiento-comercial/listar', 'StockGlpRegisterController@list')->name('dashboard.operations.stock_glp_register.list');
	Route::post('/controlglp/registro-movimiento-comercial/listar-cuentas', 'StockGlpRegisterController@getAccounts')->name('dashboard.operations.stock_glp_register.get_accounts');
	Route::post('/controlglp/registro-movimiento-comercial/listar-articulos', 'StockGlpRegisterController@getArticles')->name('dashboard.operations.stock_glp_register.get_articles');
	Route::post('/controlglp/registro-movimiento-comercial/obtener-percepcion', 'StockGlpRegisterController@getPerceptionPercentage')->name('dashboard.operations.stock_glp_register.get_perception_percentage');
	Route::post('/controlglp/registro-movimiento-comercial/obtener-tasas', 'StockGlpRegisterController@getArticleRates')->name('dashboard.operations.stock_glp_register.get_article_rates');
	Route::post('/controlglp/registro-movimiento-comercial/obtener-articulo', 'StockGlpRegisterController@getArticle')->name('dashboard.operations.stock_glp_register.get_article');
	Route::post('/controlglp/registro-movimiento-comercial/obtener-articulo-receiver', 'StockGlpRegisterController@getArticleReceiver')->name('dashboard.operations.stock_glp_register.get_article_receiver');
	Route::post('/controlglp/registro-movimiento-comercial/guardar', 'StockGlpRegisterController@store')->name('dashboard.operations.stock_glp_register.store');
	Route::post('/controlglp/registro-movimiento-comercial/get-invoices', 'StockGlpRegisterController@getInvoices')->name('dashboard.operations.stock_glp_register.get_invoices');
    
	/** Compras GLP > Registro de Abastecimiento GLP*/
	Route::get('/controlglp/registro-movimiento-glp', 'StockAbastRegisterController@index')->name('dashboard.operations.stock_abast_register');
	Route::post('/controlglp/registro-movimiento-glp/validar-formulario', 'StockAbastRegisterController@validateForm')->name('dashboard.operations.stock_abast_register.validate_form');
	Route::post('/controlglp/registro-movimiento-glp/listar', 'StockAbastRegisterController@list')->name('dashboard.operations.stock_abast_register.list');
	Route::post('/controlglp/registro-movimiento-glp/listar-cuentas', 'StockAbastRegisterController@getAccounts')->name('dashboard.operations.stock_abast_register.get_accounts');
	Route::post('/controlglp/registro-movimiento-glp/listar-articulos', 'StockAbastRegisterController@getArticles')->name('dashboard.operations.stock_abast_register.get_articles');
	Route::post('/controlglp/registro-movimiento-glp/obtener-percepcion', 'StockAbastRegisterController@getPerceptionPercentage')->name('dashboard.operations.stock_abast_register.get_perception_percentage');
	Route::post('/controlglp/registro-movimiento-glp/obtener-tasas', 'StockAbastRegisterController@getArticleRates')->name('dashboard.operations.stock_abast_register.get_article_rates');
	Route::post('/controlglp/registro-movimiento-glp/obtener-articulo', 'StockAbastRegisterController@getArticle')->name('dashboard.operations.stock_abast_register.get_article');
	Route::post('/controlglp/registro-movimiento-glp/guardar', 'StockAbastRegisterController@store')->name('dashboard.operations.stock_abast_register.store');
	Route::post('/controlglp/registro-movimiento-glp/get-invoices', 'StockAbastRegisterController@getInvoices')->name('dashboard.operations.stock_abast_register.get_invoices');
    

	/** Compras GLP > Transferencia entre almacenes*/
	Route::get('/controlglp/registro-abastecimiento-glp', 'AbastecimientoRegisterController@index')->name('dashboard.operations.abastecimiento_register');
	Route::post('/controlglp/registro-abastecimiento-glp/validar-formulario', 'AbastecimientoRegisterController@validateForm')->name('dashboard.operations.abastecimiento_register.validate_form');
	Route::post('/controlglp/registro-abastecimiento-glp/listar', 'AbastecimientoRegisterController@list')->name('dashboard.operations.abastecimiento_register.list');
	Route::post('/controlglp/registro-abastecimiento-glp/listar-cuentas', 'AbastecimientoRegisterController@getAccounts')->name('dashboard.operations.abastecimiento_register.get_accounts');
	Route::post('/controlglp/registro-abastecimiento-glp/listar-articulos', 'AbastecimientoRegisterController@getArticles')->name('dashboard.operations.abastecimiento_register.get_articles');
	Route::post('/controlglp/registro-abastecimiento-glp/obtener-percepcion', 'AbastecimientoRegisterController@getPerceptionPercentage')->name('dashboard.operations.abastecimiento_register.get_perception_percentage');
	Route::post('/controlglp/registro-abastecimiento-glp/obtener-tasas', 'AbastecimientoRegisterController@getArticleRates')->name('dashboard.operations.abastecimiento_register.get_article_rates');
	Route::post('/controlglp/registro-abastecimiento-glp/obtener-articulo', 'AbastecimientoRegisterController@getArticle')->name('dashboard.operations.abastecimiento_register.get_article');
	Route::post('/controlglp/registro-abastecimiento-glp/guardar', 'AbastecimientoRegisterController@store')->name('dashboard.operations.abastecimiento_register.store');
	Route::post('/controlglp/registro-abastecimiento-glp/get-invoices', 'AbastecimientoRegisterController@getInvoices')->name('dashboard.operations.abastecimiento_register.get_invoices');

    /** Compras GLP > Empleados */
	Route::get('/empleados', 'EmployesController@index')->name('dashboard.employes');
	Route::post('/empleados/validar-formulario', 'EmployesController@validateForm')->name('dashboard.employes.validate_form');
	Route::post('/empleados/listar', 'EmployesController@list')->name('dashboard.employes.list');
	Route::post('/empleados/guardar', 'EmployesController@store')->name('dashboard.employes.store');
	Route::post('/empleados/detalle', 'EmployesController@detail')->name('dashboard.employes.detail');
	Route::post('/empleados/obtener-ubigeos', 'EmployesController@getUbigeos')->name('dashboard.employes.get_ubigeos');
	Route::post('/empleados/obtener-ubigeo', 'EmployesController@getUbigeo')->name('dashboard.employes.get_ubigeo');
	Route::post('/empleados/eliminar', 'EmployesController@delete')->name('dashboard.employes.delete'); 

   
	
    /** Compras GLP > Placas */
	Route::get('/placas', 'PlatesController@index')->name('dashboard.plates');
	Route::post('/placas/validar-formulario', 'PlatesController@validateForm')->name('dashboard.plates.validate_form');
	Route::post('/placas/listar', 'PlatesController@list')->name('dashboard.plates.list');
	Route::post('/placas/guardar', 'PlatesController@store')->name('dashboard.plates.store');
	Route::post('/placas/detalle', 'PlatesController@detail')->name('dashboard.plates.detail');
	Route::post('/placas/obtener-ubigeos', 'PlatesController@getUbigeos')->name('dashboard.plates.get_ubigeos');
	Route::post('/placas/obtener-ubigeo', 'PlatesController@getUbigeo')->name('dashboard.plates.get_ubigeo');
	Route::post('/placas/eliminar', 'PlatesController@delete')->name('dashboard.plates.delete'); 

	 /** Compras GLP > Reporte de Compras GLP */
	Route::get('/controlglp/compras-registro', 'StockSalesRegisterReportController@index')->name('dashboard.report.stock_sales_register');
	Route::post('/controlglp/compras-registro/validar-formulario', 'StockSalesRegisterReportController@validateForm')->name('dashboard.report.stock_sales_register.validate_form');
	Route::post('/controlglp/compras-registro/obtener-guias', 'StockSalesRegisterReportController@getWarehouseMovements')->name('dashboard.report.stock_sales_register.get_warehouse_movements');
	Route::post('/controlglp/compras-registro/listar', 'StockSalesRegisterReportController@list')->name('dashboard.report.stock_sales_register.list');
	Route::post('/controlglp/compras-registro/detalle', 'StockSalesRegisterReportController@detail')->name('dashboard.report.stock_sales_register.detail');
	Route::post('/controlglp/compras-registro/actualizar', 'StockSalesRegisterReportController@update')->name('dashboard.report.stock_sales_register.update');
	Route::post('/controlglp/compras-registro/get-warehouse-type-two', 'StockSalesRegisterReportController@getWarehouseTypeTwo')->name('dashboard.report.stock_sales_register.get_warehouse_type_two');
	Route::post('/controlglp/compras-registro/get-articles', 'StockSalesRegisterReportController@getArticles')->name('dashboard.report.stock_sales_register.get_articles');
	Route::post('/controlglp/compras-registro/validate-stock', 'StockSalesRegisterReportController@validateStock')->name('dashboard.report.stock_sales_register.validate_stock');
	Route::post('/controlglp/compras-registro/delete', 'StockSalesRegisterReportController@delete')->name('dashboard.report.stock_sales_register.delete');

	/** Compras GLP > Edición de Facturas */
	Route::get('/control-glp/editor', 'GlpFactReportController@index')->name('dashboard.report.glp_fact');
	Route::post('/control-glp/editor/validar-formulario', 'GlpFactReportController@validateForm')->name('dashboard.report.glp_fact.validate_form');
	Route::post('/control-glp/editor/obtener-guias', 'GlpFactReportController@getWarehouseMovements')->name('dashboard.report.glp_fact.get_warehouse_movements');
	Route::post('/control-glp/editor/listar', 'GlpFactReportController@list')->name('dashboard.report.glp_fact.list');
	Route::post('/control-glp/editor/detalle', 'GlpFactReportController@detail')->name('dashboard.report.glp_fact.detail');
	Route::post('/control-glp/editor/actualizar', 'GlpFactReportController@update')->name('dashboard.report.glp_fact.update');

	  /** Compras GLP > Stocks Artículos GLP */
	Route::get('/control-glp', 'WarehouseGlpController@index')->name('dashboard.operations.warehouse_glp');
	Route::post('/control-glp/validar-formulario', 'WarehouseGlpController@validateForm')->name('dashboard.operations.warehouse_glp.validate_form');
	Route::post('/control-glp/listar', 'WarehouseGlpController@list')->name('dashboard.operations.warehouse_glp.list');
	Route::post('/control-glp/guardar', 'WarehouseGlpController@store')->name('dashboard.operations.warehouse_glp.store');
	Route::post('/control-glp/detalle', 'WarehouseGlpController@detail')->name('dashboard.operations.warehouse_glp.detail');
	Route::post('/control-glp/eliminar', 'WarehouseGlpController@delete')->name('dashboard.operations.warehouse_glp.delete');
	Route::post('/control-glp/exportar', 'WarehouseGlpController@exportRecord')->name('dashboard.operations.warehouse_glp.export_record');



	 /** Compras GLP > Reporte de Abastecimiento */
	Route::get('/controlglp/movimiento-abastecimiento', 'TerminalsReportController@index')->name('dashboard.report.terminals');
	Route::post('/controlglp/movimiento-abastecimiento/validar-formulario', 'TerminalsReportController@validateForm')->name('dashboard.report.terminals.validate_form');
	Route::post('/controlglp/movimiento-abastecimiento/listar', 'TerminalsReportController@list')->name('dashboard.report.terminals.list');
	Route::post('/controlglp/movimiento-abastecimiento/detalle', 'TerminalsReportController@detail')->name('dashboard.report.terminals.detail');
	Route::post('/controlglp/movimiento-abastecimiento/actualizar', 'TerminalsReportController@update')->name('dashboard.report.terminals.update');
	Route::post('/controlglp/movimiento-abastecimiento/delete', 'TerminalsReportController@delete')->name('dashboard.report.terminals.delete');

   /** Compras GLP  > Reporte Control GLP */
	Route::get('/controlglp/reporte-movimientos-glp', 'ControlGlpReportController@index')->name('dashboard.operations.control_glp');
	Route::post('/controlglp/reporte-movimientos-glp/validar-formulario', 'ControlGlpReportController@validateForm')->name('dashboard.operations.control_glp.validate_form');
	Route::post('/controlglp/reporte-movimientos-glp/listar', 'ControlGlpReportController@list')->name('dashboard.operations.control_glp.list');

	//Ajuste GLP

    Route::get('/controlglp/inventario', 'AdjustInventoryController@index')->name('dashboard.operations.inventories');
    Route::post('/controlglp/inventario/validar-formulario', 'AdjustInventoryController@validateForm')->name('dashboard.operations.inventories.validate_form');
    Route::post('/controlglp/inventario/listar', 'AdjustInventoryController@list')->name('dashboard.operations.inventories.list');
    Route::post('/controlglp/inventario/obtener-articulos', 'AdjustInventoryController@getArticles')->name('dashboard.operations.inventories.get_articles');
    Route::post('/controlglp/inventario/crear-registro', 'AdjustInventoryController@createRecord')->name('dashboard.operations.inventories.create_record');
    Route::post('/controlglp/inventario/cerrar-registro', 'AdjustInventoryController@closeRecord')->name('dashboard.operations.inventories.close_record');
    Route::post('/controlglp/inventario/detalle', 'AdjustInventoryController@detail')->name('dashboard.operations.inventories.detail');
    Route::post('/controlglp/inventario/obtener-select2', 'AdjustInventoryController@getSelect2')->name('dashboard.operations.inventories.get_select2');
    Route::post('/controlglp/inventario/guardar', 'AdjustInventoryController@store')->name('dashboard.operations.inventories.store');
    Route::post('/controlglp/inventario/eliminar', 'AdjustInventoryController@delete')->name('dashboard.operations.inventories.delete');
    Route::post('/controlglp/inventario/formulario-registro', 'AdjustInventoryController@formRecord')->name('dashboard.operations.inventories.form_record');
    Route::post('/controlglp/inventario/exportar-registro', 'AdjustInventoryController@exportRecord')->name('dashboard.operations.inventories.export_record');



	/** Compras GLP > Registro de Costos */
	Route::get('/controlglp/costo', 'CostGlpRegisterController@index')->name('dashboard.report.cost_glp_register');
	Route::post('/controlglp/costo/validar-formulario', 'CostGlpRegisterController@validateForm')->name('dashboard.report.cost_glp_register.validate_form');
	Route::post('/controlglp/costo/guardar', 'CostGlpRegisterController@store')->name('dashboard.report.cost_glp_register.store');



	/** Finanzas > Cobranza Resumida*/
	Route::get('/administracion/cobranza-resumido', 'CobranzaDetailTotalReportController@index')->name('dashboard.administration.cobranzas_detail_total');
	Route::post('/administracion/cobranza-resumido/validar-formulario', 'CobranzaDetailTotalReportController@validateForm')->name('dashboard.administration.cobranzas_detail_total.validate_form');
	Route::post('/administracion/cobranza-resumido/obtener-clientes', 'CobranzaDetailTotalReportController@getClients')->name('dashboard.administration.cobranzas_detail_total.get_clients');
	Route::post('/administracion/cobranza-resumido/listar', 'CobranzaDetailTotalReportController@list')->name('dashboard.administration.cobranzas_detail_total.list');


	/** Reportes > Liquidación Detallado Resumido*/
	Route::get('/administracion/liquidacion-detallado-resumido', 'LiquidationDetailTotalReportController@index')->name('dashboard.report.liquidations_detail_total');
	Route::post('/administracion/liquidacion-detallado-resumido/validar-formulario', 'LiquidationDetailTotalReportController@validateForm')->name('dashboard.report.liquidations_detail_total.validate_form');
	Route::post('/administracion/liquidacion-detallado-resumido/obtener-clientes', 'LiquidationDetailTotalReportController@getClients')->name('dashboard.report.liquidations_detail_total.get_clients');
	Route::post('/administracion/liquidacion-detallado-resumido/listar', 'LiquidationDetailTotalReportController@list')->name('dashboard.report.liquidations_detail_total.list');

	/** Reportes > Liquidación Detallado Resumido*/ 
	Route::get('/administracion/finanzas-detallado-resumido', 'FinanzasDetailTotalReportController@index')->name('dashboard.report.finanzas_detail_total');
	Route::post('/administracion/finanzas-detallado-resumido/validar-formulario', 'FinanzasDetailTotalReportController@validateForm')->name('dashboard.report.finanzas_detail_total.validate_form');
	Route::post('/administracion/finanzas-detallado-resumido/obtener-clientes', 'FinanzasDetailTotalReportController@getClients')->name('dashboard.report.finanzas_detail_total.get_clients');
	Route::post('/administracion/finanzas-detallado-resumido/listar', 'FinanzasDetailTotalReportController@list')->name('dashboard.report.finanzas_detail_total.list');



	/** Reportes > Finanzas y Liquidaciones */
	Route::get('/reporte/finanzas-liquidaciones', 'FinanceSettlementsController@index')->name('dashboard.report.finance_settlements');
	Route::post('/reporte/finanzas-liquidaciones/obtener-articulos', 'FinanceSettlementsController@getArticles')->name('dashboard.report.finance_settlements.get_articles');
	Route::post('/reporte/finanzas-liquidaciones/obtener-cuentas', 'FinanceSettlementsController@getAccounts')->name('dashboard.report.finance_settlements.get_accounts');
	Route::post('/reporte/finanzas-liquidaciones/obtener-clientes', 'FinanceSettlementsController@getClients')->name('dashboard.report.finance_settlements.get_clients');
	Route::post('/reporte/finanzas-liquidaciones/validar-formulario', 'FinanceSettlementsController@validateForm')->name('dashboard.report.finance_settlements.validate_form');
	Route::post('reporte/finanzas-liquidaciones/listar', 'FinanceSettlementsController@list')->name('dashboard.report.finance_settlements.list');

	/** RRHH > Planilla General */
	Route::get('/rrhh/calculo-planilla', 'PlanillaTotalReportController@index')->name('dashboard.report.planilla_total');
	Route::post('/rrhh/calculo-planilla/validar-formulario', 'PlanillaTotalReportController@validateForm')->name('dashboard.report.planilla_total.validate_form');
	Route::post('/rrhh/calculo-planilla/obtener-clientes', 'PlanillaTotalReportController@getClients')->name('dashboard.report.planilla_total.get_clients');
	Route::post('/rrhh/calculo-planilla/listar', 'PlanillaTotalReportController@list')->name('dashboard.report.planilla_total.list');



	/** creditos > Clientes */
	Route::get('/rrhh/empleados', 'EmployeeController@index')->name('dashboard.rrhh.employees');
	Route::post('/rrhh/empleados/guardar', 'EmployeeController@store')->name('dashboard.rrhh.employees.store');
	Route::post('/rrhh/empleados/validar-formulario', 'EmployeeController@validateForm')->name('dashboard.rrhh.employees.validate_form');
	Route::post('/rrhh/empleados/detalle', 'EmployeeController@detail')->name('dashboard.rrhh.employees.detail');
	Route::post('/rrhh/empleados/obtener-ubigeos', 'EmployeeController@getUbigeos')->name('dashboard.rrhh.employees.get_ubigeos');
	Route::post('/rrhh/empleados/obtener-Employeees', 'EmployeeController@getEmployees')->name('dashboard.rrhh.employees.get_clients');
	Route::post('/rrhh/empleados/obtener-select2', 'EmployeeController@getSelect2')->name('dashboard.rrhh.employees.get_select2');
	Route::post('/rrhh/empleados/eliminar', 'EmployeeController@delete')->name('dashboard.rrhh.employees.delete');
	Route::post('/rrhh/empleados/listar-direcciones', 'EmployeeController@addressList')->name('dashboard.rrhh.employees.address_list');
	Route::post('/rrhh/empleados/guardar-direccion', 'EmployeeController@addressStore')->name('dashboard.rrhh.employees.address_store');
	Route::post('/rrhh/empleados/detalle-direccion', 'EmployeeController@addressDetail')->name('dashboard.rrhh.employees.address_detail');
	Route::post('/rrhh/empleados/eliminar-direccion', 'EmployeeController@addressDelete')->name('dashboard.rrhh.employees.address_delete');
	Route::post('/rrhh/empleados/listar-precios', 'EmployeeController@priceList')->name('dashboard.rrhh.employees.price_list');
	Route::post('/rrhh/empleados/listar-articulos', 'EmployeeController@priceArticles')->name('dashboard.rrhh.employees.price_articles');
	Route::post('/rrhh/empleados/obtener-dia', 'EmployeeController@priceMinEffectiveDate')->name('dashboard.rrhh.employees.price_min_effective_date');
	Route::post('/rrhh/empleados/guardar-precio', 'EmployeeController@priceStore')->name('dashboard.rrhh.employees.price_store');
	Route::post('/rrhh/empleados/listar', 'EmployeeController@list')->name('dashboard.rrhh.employees.list');
	

	/** Comercial > Lista de Precios */
	Route::get('/rrhh/asistencia', 'AsistenciaController@index')->name('dashboard.rrhh.asistencia');
	Route::post('/rrhh/asistencia/validar-formulario', 'AsistenciaController@validateForm')->name('dashboard.rrhh.asistencia.validate_form');
	Route::post('/rrhh/asistencia/listar', 'AsistenciaController@list')->name('dashboard.rrhh.asistencia.list');
	Route::post('/rrhh/asistencia/obtener-dia', 'AsistenciaController@getMinEffectiveDate')->name('dashboard.rrhh.asistencia.get_min_effective_date');
	Route::post('/rrhh/asistencia/guardar', 'AsistenciaController@store')->name('dashboard.rrhh.asistencia.store');








});



