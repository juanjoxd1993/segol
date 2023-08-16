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
                finance_settlements_report_datatable: undefined,
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
					if ( vm.finance_settlements_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.finance_settlements_report_datatable.setDataSourceParam('model', vm.model);
						vm.finance_settlements_report_datatable.load();
					}
				
					vm.finance_settlements_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.finance_settlements_report_datatable != undefined ) {
                    this.finance_settlements_report_datatable.setDataSourceParam('model', this.model);
                    this.finance_settlements_report_datatable.load();
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

                this.finance_settlements_report_datatable = $('.kt-datatable').KTDatatable({
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
										element.sale_value = accounting.toFixed(element.sale_value, 2);
										element.perception = accounting.toFixed(element.perception, 2);
										element.total_perception = accounting.toFixed(element.total_perception, 2);
										element.efective = accounting.toFixed(element.efective, 2);
										element.deposit = accounting.toFixed(element.deposit, 2);
										element.pre_balance = accounting.toFixed(element.pre_balance, 2);
										element.payment_method_efective = accounting.toFixed(element.payment_method_efective, 2);
										element.payment_method_deposit = accounting.toFixed(element.payment_method_deposit, 2);
										element.total_efective_cobranza = accounting.toFixed(element.total_efective_cobranza, 2);
										element.total_deposit_cobranza = accounting.toFixed(element.total_deposit_cobranza, 2);
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
                            field: 'sale_date',
                            title: 'Fecha',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'total_perception',
                            title: 'Total Soles',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'efective',
                            title: 'Efectivo Forma Pago',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'deposit',
                            title: 'Depósito Forma Pago',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'pre_balance',
                            title: 'Venta a Credito',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'payment_method_efective',
                            title: 'Efectivo Cobranza',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'payment_method_deposit',
                            title: 'Depósito Cobranza',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'total_efective_cobranza',
                            title: 'Efectivo Total Cobranza',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'total_deposit_cobranza',
                            title: 'Depósito Total Cobranza',
                            width: 120,
                            textAlign: 'right',
                        }
                    ]
                });

                this.finance_settlements_report_datatable.columns('id').visible(false);
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
                    link.setAttribute('download', 'reporte-finanzas-liquidaciones-'+Date.now()+'.xls');
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