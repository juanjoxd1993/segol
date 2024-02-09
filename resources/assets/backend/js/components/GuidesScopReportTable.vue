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
            url_detail: {
                type: String,
                default: ''
            },

        },
        data() {
            return {
                guides_scop_report_datatable: undefined,
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
					if (vm.guides_scop_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.guides_scop_report_datatable.setDataSourceParam('model', vm.model);
						vm.guides_scop_report_datatable.load();
					}
				
					vm.guides_scop_report_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.guides_scop_report_datatable != undefined ) {
                  this.guides_scop_report_datatable.setDataSourceParam('model', this.model);
                   this.datatable.load();
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

                this.guides_scop_report_datatable = $('.kt-datatable').KTDatatable({
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
                              //  element.presale_converted_amount = accounting.toFixed(element.presale_converted_amount, 4);
                               // element.return_converted_amount = accounting.toFixed(element.return_converted_amount, 4);
                              //  element.new_balance_converted_amount = accounting.toFixed(element.new_balance_converted_amount, 4);
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
                            field: 'guide_date',
                            title: 'Fecha Despacho',
                            width: 80,
                            textAlign: 'center',
                        },
                     //    {
                     //       field: 'article_code',
                     //       title: 'Código',
                     //      width: 60,
                    //        textAlign: 'center',
                    //    },
                    //    {
                    //       field: 'article_name',
                    //        title: 'Artículo',
                    //        width: 300,
                    //    },
                  //      {
                  //          field: 'presale_converted_amount',
                  //          title: 'Pre-Venta',
                  //          width: 120,
                  //          textAlign: 'right',
                  //      },
                  //      {
                  //          field: 'return_converted_amount',
                  //          title: 'Retorno',
                  //          width: 120,
                  //          textAlign: 'right',
                  //      },
                        
                  //      {
                  //          field: 'new_balance_converted_amount',
                  //          title: 'Saldo',
                  //          width: 120,
                  //          textAlign: 'right',
                  //      },
                        {
                           field: 'traslate_date',
                           title: 'Fecha de Traslado',
                           width: 60,
                           textAlign: 'left',
                       },
                       {
                           field: 'route_id',
                           title: 'Ruta',
                           width: 60,
                           textAlign: 'left',
                       },
                       {
                           field: 'movement_type_name',
                           title: 'Tipo Movimiento',
                           width: 60,
                           textAlign: 'left',
                       },
                        {
                           field: 'state',
                           title: 'Estado',
                           width: 60,
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
                            title: 'Placa',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'warehouse_movement_id',
                            title: 'ID Movimiento de Almacén',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                         {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 60,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
							class: 'td-sticky',
                            template: function(row) {
								
									let actions = '<div class="actions">';
										actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
											actions += '<i class="la la-edit"></i>';
                                            
										actions += '</a>';
									actions += '</div>';
                                
									return actions;
                            },
                        },
                                                              
                    ]
                });

                this.guides_scop_report_datatable.columns('id').visible(false);
                this.guides_scop_report_datatable.columns('warehouse_movement_id').visible(false);
            },

            manageActions: function(event) {
                if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="warehouse_movement_id"] span').html();
                    EventBus.$emit('loading', true);

                    axios.post(this.url_detail, {
                        id: id,
                    }).then(response => {
                        EventBus.$emit('edit_modal', response.data);
                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
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
                    link.setAttribute('download', 'reporte-guias-'+Date.now()+'.xls');
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