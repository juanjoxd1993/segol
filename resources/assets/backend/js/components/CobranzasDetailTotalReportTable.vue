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
                cobranzas_detail_total_report_datatable: undefined,
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
					if ( vm.cobranzas_detail_total_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.cobranzas_detail_total_report_datatable.setDataSourceParam('model', vm.model);
						vm.cobranzas_detail_total_report_datatable.load();
					}
				
					vm.cobranzas_detail_total_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.cobranzas_detail_total_report_datatable != undefined ) {
                  this.cobranzas_detail_total_report_datatable.setDataSourceParam('model', this.model);
                   this.cobranzas_detail_total_report_datatable.load();
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

                this.cobranzas_detail_total_report_datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model,
                                    export:vm.export,
			
                                },

                                map: function(raw) {
                                    var dataSet = raw;
                                    if (typeof raw.data !== 'undefined') {
                                        dataSet = raw.data;
                                        }
                                dataSet.map(element => {
                                element.total = accounting.toFixed(element.total, 2);
                                element.sum_total = accounting.toFixed(element.sum_total, 2);
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
                        height: 600,
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
                            field: 'business_unit_name',
                            title: 'Unidad de Negocio',
                            width: 60,
                            textAlign: 'left',
                        },

                        {
                            field: 'client_channel_name',
                            title: 'Canal de Venta',
                            width: 80,
                            textAlign: 'center',
                        },
                       
                         {
                            field: 'liquidation_date',
                            title: 'Fecha de Liquidación',
                            width: 60,
                            textAlign: 'left',
                        },

                        {
                            field: 'sale_date',
                            title: 'Fecha de Emisión',
                            width: 60,
                            textAlign: 'left',
                        },
                                   
                      
					

                        {
                            field: 'canc_total',
                            title: 'Cobranza',
                            width: 80,
                            textAlign: 'center',
                        },  

					

                        {
                            field: 'deposit',
                            title: 'Deposito',
                            width: 120,
                            textAlign: 'right',
                        },

                        {
                            field: 'efective',
                            title: 'Efectivo',
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
					
                      
                    ]
                });

                this.cobranzas_detail_total_report_datatable.columns('id').visible(false);
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
                    link.setAttribute('download', 'reporte-unidades-de-negocio'+Date.now()+'.xls');
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