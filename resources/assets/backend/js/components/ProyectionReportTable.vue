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
                proyection_report_datatable: undefined,
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
					if ( vm.proyection_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.proyection_report_datatable.setDataSourceParam('model', vm.model);
						vm.proyection_report_datatable.load();
					}
				
					vm.proyection_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.proyection_report_datatable != undefined ) {
                  this.proyection_report_datatable.setDataSourceParam('model', this.model);
                   this.proyection_report_datatable.load();
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

                this.proyection_report_datatable = $('.kt-datatable').KTDatatable({
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
                            field: 'tm_01',
                            title: '01',
                            width: 20,
                            textAlign: 'left',
                        },
                    /*     {
                            field: 'tm_02',
                            title: '02',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_03',
                            title: '03',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_04',
                            title: '04',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_05',
                            title: '05',
                            width: 20,
                            textAlign: 'left',
                        },
                    /*     {
                            field: 'tm_06',
                            title: '06',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_07',
                            title: '07',
                            width: 20,
                            textAlign: 'left',
                        },
                    /*     {
                            field: 'tm_08',
                            title: '08',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_09',
                            title: '09',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_10',
                            title: '10',
                            width: 20,
                            textAlign: 'left',
                        },
                        {
                            field: 'tm_11',
                            title: '11',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_12',
                            title: '12',
                            width: 20,
                            textAlign: 'left',
                        },
                        {
                            field: 'tm_13',
                            title: '13',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_14',
                            title: '14',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_15',
                            title: '15',
                            width: 20,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_16',
                            title: '16',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_17',
                            title: '17',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_18',
                            title: '18',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_19',
                            title: '19',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_20',
                            title: '20',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_21',
                            title: '21',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_22',
                            title: '22',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_23',
                            title: '23',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_24',
                            title: '24',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_25',
                            title: '25',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_26',
                            title: '27',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_27',
                            title: '27',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_28',
                            title: '28',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_29',
                            title: '29',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_30',
                            title: '30',
                            width: 60,
                            textAlign: 'left',
                        },
                         {
                            field: 'tm_31',
                            title: '31',
                            width: 60,
                            textAlign: 'left',
                        },*/

                       



                      						
						{
                            field: 'udid',
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

                this.proyection_report_datatable.columns('udid').visible(false);
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