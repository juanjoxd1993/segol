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
                liquidations_channel_report_datatable: undefined,
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
					if ( vm.liquidations_channel_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.liquidations_channel_report_datatable.setDataSourceParam('model', vm.model);
						vm.liquidations_channel_report_datatable.load();
					}
				
					vm.liquidations_channel_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.liquidations_channel_report_datatable != undefined ) {
                  this.liquidations_channel_report_datatable.setDataSourceParam('model', this.model);
                   this.liquidations_channel_report_datatable.load();
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

                this.liquidations_channel_report_datatable = $('.kt-datatable').KTDatatable({
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
                            field: 'company_short_name',
                            title: 'Compañía',
                            width: 60,
                            textAlign: 'left',
                        },
                   
                        {
                            field: 'sale_date',
                            title: 'Fecha Despacho',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'business_unit_name',
                            title: 'Unidad de Negocio',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_channel_name',
                            title: 'Canal venta',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_zone_name',
                            title: 'Zona venta',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_sector_name',
                            title: 'Sector econom.',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_route_id',
                            title: 'Ruta',
                            width: 40,
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
                            field: 'article_name',
                            title: 'Articulo',
                            width: 120,
                            textAlign: 'right',
                        },
                      
						{
                            field: 'sum_total',
                            title: 'TM',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'price',
                            title: 'Precio',
                            width: 80,
                            textAlign: 'center',
                        },

						{
                            field: 'total',
                            title: 'Total',
                            width: 120,
                            textAlign: 'right',
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
                            field: 'warehouse_movement_movement_number',
                            title: '# Parte',
                            width: 60,
                            textAlign: 'left',
                        },
						{
                            field: 'movement_type_name',
                            title: 'Tipo Movimiento',
                            width: 80,
                            textAlign: 'left',
                        },
						{
                            field: 'guide',
                            title: 'Guía',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'plate',
                            title: 'PLaca',
                            width: 60,
                            textAlign: 'left',
                        },
                       {
							field: 'district',
							title: 'Distrito',
							width: 120,
							textAlign: 'left',
						},
                        {
							field: 'province',
							title: 'Provincia',
							width: 120,
							textAlign: 'left',
						},
						{
							field: 'department',
							title: 'Departamento',
							width: 120,
							textAlign: 'left',
						},
                        {
							field: 'seller_id',
							title: 'Chofer Vendedor',
							width: 120,
							textAlign: 'left',
						},
                        {
							field: 'manager',
							title: 'Supervisor',
							width: 120,
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

                this.liquidations_channel_report_datatable.columns('id').visible(false);
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