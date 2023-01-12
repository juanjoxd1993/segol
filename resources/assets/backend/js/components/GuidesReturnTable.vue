<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Artículos de Preventa
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="saveLiquidation()">
								<i class="fa fa-check"></i> Cerrar Retorno
							</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable-articles"></div>
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
			url_store: {
				type: String,
				default: ''
			}
        },
        data() {
            return {
                guides_return_datatable: undefined,
                show_table: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.show_table = true;
                this.$store.commit('addModel', response);

                axios.post(this.url, {
                    model: this.$store.state.model,
                }).then(response => {
                    // console.log(response.data);
                    this.$store.commit('addArticles', response.data);
                    
                    if ( this.guides_return_datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        this.guides_return_datatable.originalDataSet = this.articlesState;
                        this.guides_return_datatable.load();
                    }

                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            }.bind(this));

            EventBus.$on('refresh_table_guides_return', function() {
                if ( this.guides_return_datatable != undefined ) {
					// console.log(this.articlesState);
                    this.guides_return_datatable.originalDataSet = this.articlesState;
                    this.guides_return_datatable.load();
                }
            }.bind(this));
        },
        watch: {
            
        },
        computed: {
			articlesState: function() {
				let articles = this.$store.state.articles;
				articles.map(element => {
					element.presale_converted_amount = accounting.toFixed(element.presale_converted_amount, 4);

				});
				
				return articles;
			}
        },
        methods: {
			saveLiquidation: function() {
				let zeroBalance = this.$store.state.articles.filter(element => element.new_balance_converted_amount > 0);

				if ( zeroBalance.length > 0 ) {
					Swal.fire({
						title: '¡Error!',
						text: 'Tiene Artículos pendientes por liquidar.',
						type: "error",
						heightAuto: false,
					});
				} else {
					EventBus.$emit('loading', true);

						axios.post(this.url_store, {
						'model': this.$store.state.model,
						'sales': unformatSales
					}).then(response => {
						// console.log(response);
						this.$store.commit('resetState');

						EventBus.$emit('loading', false);


						Swal.fire({
							title: '¡Ok!',
							text: 'Se creo el registro correctamente.',
							type: "success",
							heightAuto: false,
						});
					}).catch(error => {
						EventBus.$emit('loading', false);
						console.log(error);
						console.log(error.response);

						Swal.fire({
							title: '¡Error!',
							text: error,
							type: "error",
							heightAuto: false,
						});
					});
				}
			},
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.return_guides_datatable = $('.kt-datatable-articles').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.articlesState,
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
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
                    
                        {
                            field: 'article_code',
                            title: 'Código',
                            width: 60,
                            textAlign: 'center',
                        },
                        {
                            field: 'article_name',
                            title: 'Artículo',
                            width: 300,
                        },
                        {
                            field: 'presale_converted_amount',
                            title: 'Pre-Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                       
                          {
                            field: 'id',
                            title: 'ID',
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
                            field: 'warehouse_movement_detail_id',
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
                      
                        // {
                        //     field: 'options',
                        //     title: 'Opciones',
                        //     sortable: false,
                        //     width: 80,
                        //     overflow: 'visible',
                        //     autoHide: false,
                        //     textAlign: 'right',
                        //     template: function(row) {
                        //         let actions = '<div class="actions">';
                        //             actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                        //                 actions += '<i class="la la-edit"></i>';
                        //             actions += '</a>';
                        //             actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                        //                 actions += '<i class="la la-trash"></i>';
                        //             actions += '</a>';
                        //         actions += '</div>';

                        //         return actions;
                        //     },
                        // },
                    ]
                });

                this.datatable.columns('id').visible(false);
                this.datatable.columns('warehouse_movement_detail_id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="warehouse_movement_detail_id"] span').html();
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
        }
    };
</script>