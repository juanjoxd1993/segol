<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Detalle
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="addItem()">
								<i class="fa fa-plus"></i> Agregar item
							</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable-register-document-fact"></div>
                <!--end: Datatable -->
            </div>

			<div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button class="btn btn-primary" @click.prevent="saveDocumentCharge()">Guardar</button>
                        </div>
                    </div>
                </div>
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
                show_table: false,
				model: {},
                register_document_fact_datatable: undefined,
				items: [],
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('secondStateUpdate', function(response) {
				let vm = this;
                vm.show_table = true;
				vm.model = response;

				Vue.nextTick(function() {
					if ( vm.register_document_fact_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.register_document_fact_datatable.originalDataSet = vm.items;
						vm.register_document_fact_datatable.load();
					}
				
					vm.register_document_fact_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('update_table', function(item) {
				let vm = this;
				vm.items.push(item);
            }.bind(this));
        },
        watch: {
            items: function (val) {
				if ( val.length > 0 ) {
					console.log(val);
					this.items.map( (element, index) => {
						element.item = ++index;
						element.detraction = element.total * ( this.model.detraction_percentage / 100 );
					});

					this.register_document_fact_datatable.originalDataSet = this.items;
					this.register_document_fact_datatable.load();

					this.register_document_fact_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				}
			}
        },
        computed: {
			
        },
        methods: {
			addItem: function() {
				EventBus.$emit('open_modal', this.model.business_unit_id, this.model.igv_percentage);
			},
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.register_document_fact_datatable = $('.kt-datatable-register-document-fact').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.items,
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
                            field: 'item',
                            title: '#',
                            width: 40,
                            textAlign: 'center',
                        }, {
                            field: 'article_name',
                            title: 'Articulo',
                            width: 120,
                            textAlign: 'center',
                        }, {
                            field: 'unit_name',
                            title: 'Unidad de Medida',
                            width: 80,
                        }, {
                            field: 'quantity',
                            title: 'Cantidad',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'price_igv',
                            title: 'Precio Unitario',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'sale_value',
                            title: 'Valor Venta',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'inaccurate_value',
                            title: 'Valor Inafecto',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'exonerated_value',
                            title: 'Valor Exonerado',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'igv',
                            title: 'IGV',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'total',
                            title: 'Total',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'detraction',
                            title: 'Detracción',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'referral_guide_series',
                            title: 'Serie Remisión',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'referral_guide_number',
                            title: 'Nº Remisión',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'carrier_series',
                            title: 'Serie Transporte',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'carrier_number',
                            title: 'Nº Transporte',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'referential_quantity',
                            title: 'Cantidad Referencial',
                            width: 100,
                            textAlign: 'right',
                        }, {
                            field: 'referential_sale_value',
                            title: 'Valor Referencial',
                            width: 100,
                            textAlign: 'right',
                        },
                        // {
                        //     field: 'subgroup_id',
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

                // this.register_document_fact_datatable.columns('subgroup_id').visible(false);
            },
			saveDocumentCharge: function() {
				EventBus.$emit('loading', true);

				axios.post(this.url, {
					model: this.model,
					items: this.items
				}).then(response => {
					let vm = this;

					EventBus.$emit('loading', false);
					this.$parent.alertMsg({
						type: 1,
						title: response.data.title,
						msg: response.data.msg
					});

					EventBus.$emit('clearFirstStep');
					EventBus.$emit('clearSecondStep');
					this.model = {};
					this.register_document_fact_datatable.destroy();
					this.register_document_fact_datatable = undefined;
					this.items = [];
					this.show_table = false;
				}).catch(error => {
					EventBus.$emit('loading', false);
					console.log(error.response);
				});
			}
        }
    };
</script>