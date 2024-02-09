<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Resultado
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportExcel()">
								<i class="fa fa-file-excel"></i> Exportar Excel
							</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable"></div>
                <!--end: Datatable -->
            </div>
        </div>
        <!--end::Portlet-->
    </div>
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                liquidations_report_datatable: undefined,
                show_table: false,
				model: {},
				export: '',
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
				let vm = this;
                this.show_table = true;
				this.model = response;

				Vue.nextTick(function() {
					if ( vm.liquidations_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.liquidations_report_datatable.setDataSourceParam('model', vm.model);
						vm.liquidations_report_datatable.load();
					}
				
					vm.liquidations_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.liquidations_report_datatable != undefined ) {
                    this.liquidations_report_datatable.setDataSourceParam('model', this.model);
                    this.liquidations_report_datatable.load();
                }
            }.bind(this));
        },
        watch: {
            
        },
        computed: {
			
        },
        methods: {
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.liquidations_report_datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model,
									export: vm.export,
                                },
								map: function(raw) {
									var dataSet = raw;
									if (typeof raw.data !== 'undefined') {
										dataSet = raw.data;
									}
									dataSet.map(element => {
										element.total = accounting.toFixed(element.total, 2);
										element.total_perception = accounting.toFixed(element.total_perception, 2);
										element.credit = accounting.toFixed(element.credit, 2);
										element.cash_liquidation_amount = accounting.toFixed(element.cash_liquidation_amount, 2);
										element.deposit_liquidation_amount = accounting.toFixed(element.deposit_liquidation_amount, 2);
										element.gallons = accounting.toFixed(element.gallons, 4);
										element.sum_1k = accounting.toFixed(element.sum_1k, 4);
										element.sum_5k = accounting.toFixed(element.sum_5k, 4);
										element.sum_10k = accounting.toFixed(element.sum_10k, 4);
										element.sum_15k = accounting.toFixed(element.sum_15k, 4);
										element.sum_45k = accounting.toFixed(element.sum_45k, 4);
										element.sum_total = accounting.toFixed(element.sum_total, 4);
									});
									return dataSet;
								}
                            },
                        },
                        pageSize: 10,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: false,

                    search: {
                        input: $('#generalSearch'),
                    },

                    translate: {
                        records: {
                            processing: 'Espere porfavor...',
                            noRecords: 'No hay registros'
                        },
                        toolbar: {
                            pagination: {
                                items: {
                                    default: {
                                        first: 'Primero',
                                        prev: 'Anterior',
                                        next: 'Siguiente',
                                        last: 'Último',
                                        more: 'Más páginas',
                                        input: 'Número de página',
                                        select: 'Seleccionar tamaño de página'
                                    },
                                    info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                                }
                            }
                        }
                    },

                    rows: {
                        autoHide: false,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'company_short_name',
                            title: 'Compañía',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'liquidation_date',
                            title: 'Fecha Liquidación',
                            width: 80,
							textAlign: 'center',
                        },
                        {
                            field: 'sale_date',
                            title: 'Fecha Despacho',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'client_route_id',
                            title: 'Ruta',
                            width: 80,
                            textAlign: 'center',
                        },
                         {
                            field: 'client_id',
                            title: 'ID',
                            width: 80,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_code',
                            title: 'Código Cliente',
                            width: 80,
                            textAlign: 'left',
                        },
						{
                            field: 'client_business_name',
                            title: 'Razón Social',
                            width: 120,
                            textAlign: 'left',
                        },

                        {
                            field: 'warehouse_document_type_short_name',
                            title: 'Tipo',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'referral_serie_number',
                            title: 'Serie',
                            width: 60,
                            textAlign: 'left',
                        },
						{
                            field: 'referral_voucher_number',
                            title: '# Doc.',
                            width: 60,
                            textAlign: 'left',
                        },
						
					
						{
                            field: 'total',
                            title: 'Total',
                            width: 120,
                            textAlign: 'right',
                        },
						
						{
                            field: 'total_perception',
                            title: 'Total Percepción',
                            width: 120,
                            textAlign: 'right',
                        },
				//		{
                //            field: 'payment_name',
                //            title: 'Condición de Pago',
                //            width: 80,
                //            textAlign: 'left',
                //        },
						{
                            field: 'credit',
                            title: 'Crédito',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'cash_liquidation_amount',
                            title: 'Efectivo',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'deposit_liquidation_amount',
                            title: 'Depósito/Transf.',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'bank_short_name',
                            title: 'Banco',
                            width: 80,
                            textAlign: 'left',
                        },
						{
                            field: 'operation_number',
                            title: 'Nº Operación',
                            width: 80,
                            textAlign: 'left',
                        },
						
						{
                            field: 'gallons',
                            title: 'Galones',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_1k',
                            title: '1K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_5k',
                            title: '5K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_10k',
                            title: '10K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_15k',
                            title: '15K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_45k',
                            title: '45K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'id',
                            title: 'ID',
                            width: 0,
                            // overflow: 'hidden',
                            // responsive: {
                            //     hidden: 'sm',
                            //     hidden: 'md',
                            //     hidden: 'lg',
                            //     hidden: 'xl'
                            // }
                        },
						{
                            field: 'sum_total',
                            title: 'Total Kg.',
                            width: 120,
                            textAlign: 'right',
                        },
                    ]
                });

                this.liquidations_report_datatable.columns('id').visible(false);
            },
			exportExcel: function() {
                EventBus.$emit('loading', true);
                this.export = 1;

                axios.post(this.url, {
                    model: this.model,
                    export: this.export,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-liquidaciones-cyc-'+Date.now()+'.xls');
                    document.body.appendChild(link);
                    link.click();

                    this.export = '';
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                    EventBus.$emit('loading', false);
                });
            },
        }
    };
</script>