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
                days_report_datatable: undefined,
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
					if ( vm.days_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.days_report_datatable.setDataSourceParam('model', vm.model);
						vm.ldays_report_datatable.load();
					}
				
					vm.days_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.days_report_datatable != undefined ) {
                  this.days_report_datatable.setDataSourceParam('model', this.model);
                   this.days_report_datatable.load();
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

                this.days_report_datatable = $('.kt-datatable').KTDatatable({
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
                            field: 'client_channel_name',
                            title: 'Canal venta',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'name',
                            title: 'Nombre',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_tm',
                            title: 'TM_MA',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'tm',
                            title: 'TM',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'proy_tm',
                            title: 'PROY',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_percentaje_tm',
                            title: 'V%MA',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'tm_ppto',
                            title: 'PPTO',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'percentaje_tm',
                            title: 'V%PPTO',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_mb',
                            title: 'MB_MA',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_percentaje_mb',
                            title: 'V%MA',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'mb_ppto',
                            title: 'PPTO_MB',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_total',
                            title: 'S./ MES_ANT',
                            width: 40,
                            textAlign: 'left',
                        },

                        {
                            field: 'total',
                            title: 'S./ SOLES',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'proy_soles',
                            title: 'PROY',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'percentaje_soles',
                            title: 'V%MA',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'costo_ma',
                            title: 'CX_MA',
                            width: 40,
                            textAlign: 'left',
                        },
                         {
                            field: 'px_ma',
                            title: 'PX_MA',
                            width: 40,
                            textAlign: 'left',
                        },

                        {
                            field: 'mbu_ma',
                            title: 'MBU_MA',
                            width: 40,
                            textAlign: 'left',
                        },

                        {
                            field: 'costo',
                            title: 'CX',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'px',
                            title: 'PX',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'mbu',
                            title: 'MBU',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'dif_cx',
                            title: 'DIF_CX',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'dif_px',
                            title: 'DIF_PX',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'dif_mbu',
                            title: 'DIF_MBU',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'ratio_ppto',
                            title: 'RATIO PPTO',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'ratio_tm',
                            title: 'RATIO TM',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'ratio',
                            title: 'RATIO %',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_cost_glp',
                            title: 'C.Glp M_A',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'mbu_ma',
                            title: 'last_aprov',
                            width: 40,
                            textAlign: 'left',
                        },
                        {
                            field: 'last_cost_glp_final',
                            title: 'C. Total M_A',
                            width: 40,
                            textAlign: 'left',
                        },
                         {
                            field: 'cost_glp',
                            title: 'C.Glp',
                            width: 40,
                            textAlign: 'left',
                        },

                        {
                            field: 'aprov',
                            title: 'C.Aprov',
                            width: 40,
                            textAlign: 'left',
                        },

                        {
                            field: 'cost_glp_final',
                            title: 'C. Total',
                            width: 40,
                            textAlign: 'left',
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

                this.days_report_datatable.columns('id').visible(false);
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