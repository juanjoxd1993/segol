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
                facturations_total_sales_volume_report_datatable: undefined,
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
					if ( vm.facturations_total_sales_volume_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.facturations_total_sales_volume_report_datatable.setDataSourceParam('model', vm.model);
						vm.facturations_total_sales_volume_report_datatable.load();
					}
				
					vm.facturations_total_sales_volume_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.facturations_total_sales_volume_report_datatable != undefined ) {
                  this.facturations_total_sales_volume_report_datatable.setDataSourceParam('model', this.model);
                   this.facturations_total_sales_volume_report_datatable.load();
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

                this.facturations_total_sales_volume_report_datatable = $('.kt-datatable').KTDatatable({
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
                            field: 'business_unit_name',
                            title: 'Unidad de Negocio',
                            width: 60,
                            textAlign: 'left',
                        },

                
                        {
                            field: 'fecha_emision',
                            title: 'Fecha Emisión',
                            width: 60,
                            textAlign: 'center',
                        },
                   
                       							
				
                        {
                            field: 'sum_sale_value',
                            title: 'Valor Venta',
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: 'sum_igv',
                            title: 'IGV',
                            width: 80,
                            textAlign: 'right',
                        },

						{
                            field: 'sum_total',
                            title: 'Total',
                            width: 80,
                            textAlign: 'right',
                        },

                    
                        {
                            field: 'sum_24k',
                            title: 'Galones',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_1k',
                            title: 'Granel',
                            width: 80,
                            textAlign: 'right',
                        },
                       
						{
                            field: 'sum_5k',
                            title: '5K',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_10k',
                            title: '10K',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_15k',
                            title: '15K',
                            width: 80,
                            textAlign: 'right',
                        },
                        
                         {
                            field: 'sum_45k',
                            title: '45K',
                            width: 80,
                            textAlign: 'right',
                        },
						
                        {
                            field: 'sum_tm_total',
                            title: 'Total Kg.',
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: 'sum_1kf',
                            title: 'Granel',
                            width: 80,
                            textAlign: 'right',
                        },
                       
						{
                            field: 'sum_5kf',
                            title: '5K',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_10kf',
                            title: '10K',
                            width: 80,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_15kf',
                            title: '15K',
                            width: 80,
                            textAlign: 'right',
                        },
                        
                         {
                            field: 'sum_45kf',
                            title: '45K',
                            width: 80,
                            textAlign: 'right',
                        },
                          {
                            field: 'sum_tm_total_f',
                            title: 'Total Kg. fact',
                            width: 80,
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

                this.facturations_total_sales_volume_report_datatable.columns('id').visible(false);
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
                    link.setAttribute('download', 'reporte-facturaciones-'+Date.now()+'.xls');
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