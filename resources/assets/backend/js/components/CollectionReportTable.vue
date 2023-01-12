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
                collection_report_table: undefined,
                show_table: false,
				model: {},
				export: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
				let vm = this;
                this.show_table = true;
				this.model = response;

				// axios.post(vm.url, vm.model).then(response => {
				// 	console.log(response.data);
				// }).catch(error => {
				// 	console.log(error)
				// });

				Vue.nextTick(function() {
					if ( vm.collection_report_table == undefined ) {
						vm.fillTableX();
					} else {
						vm.collection_report_table.setDataSourceParam('model', vm.model);
						vm.collection_report_table.load();
					}
				
					vm.collection_report_table.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.collection_report_table != undefined ) {
                    this.collection_report_table.setDataSourceParam('model', this.model);
                    this.collection_report_table.load();
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

                this.collection_report_table = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model,
                                },
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
                    sortable: false,
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
                            width: 80,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_id',
                            title: 'ID Cliente',
                            width: 80,
							textAlign: 'center',
                        },
                        {
                            field: 'client_code',
                            title: 'Cód. Cliente',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'document_type_name',
                            title: 'Doc. Cliente',
                            width: 60,
                            textAlign: 'center',
                        },
                        {
                            field: 'document_number',
                            title: 'Nº Doc.',
                            width: 100,
                            textAlign: 'center',
                        },
                        {
                            field: 'business_name',
                            title: 'Razón Social',
                            width: 160,
                            textAlign: 'left',
                        },
						{
                            field: 'origin',
                            title: 'Origen',
                            width: 100,
                            textAlign: 'left',
                        },
						{
                            field: 'liquidation_created_at',
                            title: 'Fecha Cobranza',
                            width: 120,
                            textAlign: 'center',
                        },
						{
                            field: 'sale_date',
                            title: 'Fecha emisión',
                            width: 120,
                            textAlign: 'center',
                        },
						{
                            field: 'expiry_date',
                            title: 'Fecha venc.',
                            width: 100,
                            textAlign: 'center',
                        },
						{
                            field: 'warehouse_document_type_name',
                            title: 'Tipo Doc.',
                            width: 100,
                            textAlign: 'left',
                        },
						{
                            field: 'referral_serie_number',
                            title: 'Nº Serie',
                            width: 80,
                            textAlign: 'center',
                        },
						{
                            field: 'referral_voucher_number',
                            title: 'Nº Doc.',
                            width: 80,
                            textAlign: 'center',
                        },
						{
                            field: 'amount_soles',
                            title: 'Monto S/',
                            width: 100,
                            textAlign: 'right',
                        },
						{
                            field: 'amount_dolares',
                            title: 'Monto US$',
                            width: 100,
                            textAlign: 'right',
                        },
						{
                            field: 'exchange_rate',
                            title: 'Tip. cambio',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'payment_method_name',
                            title: 'Forma pago',
                            width: 120,
                            textAlign: 'left',
                        },
						{
                            field: 'bank_account',
                            title: 'Banco',
                            width: 140,
                            textAlign: 'left',
                        },
						{
                            field: 'operation_number',
                            title: 'Nº Operación',
                            width: 80,
                            textAlign: 'left',
                        },
						// {
                        //     field: 'id',
                        //     title: 'ID',
                        //     width: 0,
                        //     overflow: 'hidden',
                        //     responsive: {
                        //         hidden: 'sm',
                        //         hidden: 'md',
                        //         hidden: 'lg',
                        //         hidden: 'xl'
                        //     }
                        // },
                    ]
                });

                // this.collection_report_table.columns('id').visible(false);
            },
			exportExcel: function() {
                EventBus.$emit('loading', true);
                this.export = true;

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
                    link.setAttribute('download', 'reporte-relacion-de-cobranzas-'+Date.now()+'.xls');
                    document.body.appendChild(link);
                    link.click();

                    this.export = false;
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                    EventBus.$emit('loading', false);
                });
            },
        }
    };
</script>