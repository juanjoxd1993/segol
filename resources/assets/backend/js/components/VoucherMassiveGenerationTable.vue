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
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="massiveGeneration()">
								<i class="fa fa-database"></i> Datos
							</a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable-massive-generation"></div>
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
				model: {},
				sum_kg: '',
				sum_total: '',
                massive_generation_datatable: undefined,
                show_table: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
				let vm = this;
                vm.show_table = true;
				vm.model = response;

				Vue.nextTick(function() {
					if ( vm.massive_generation_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.massive_generation_datatable.setDataSourceParam('model', vm.model);
						vm.massive_generation_datatable.load();
					}
				
					vm.massive_generation_datatable.on('kt-datatable--on-ajax-done', function() {
						EventBus.$emit('loading', false);
					});
				});
            }.bind(this));

            EventBus.$on('clear_table', function() {
				let vm = this;
                if ( vm.massive_generation_datatable != undefined ) {
					vm.massive_generation_datatable.destroy();
					vm.massive_generation_datatable = undefined;
                }

				vm.show_table = false;
            }.bind(this));
        },
        watch: {
            
        },
        computed: {
			
        },
        methods: {
			massiveGeneration: function() {
				EventBus.$emit('open_modal', this.model, this.sum_kg, this.sum_total);
			},
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.massive_generation_datatable = $('.kt-datatable-massive-generation').KTDatatable({
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
								map: function(raw) {
									var dataSet = raw;
									if (typeof raw.data !== 'undefined') {
										dataSet = raw.data;
									}

									dataSet.map(element => {
										element.sum_kg = accounting.formatNumber(element.sum_kg, 2);
										element.sum_total = accounting.formatMoney(element.sum_total, { symbol: 'S/', format: '%s %v' }, 2);
									});

									vm.sum_kg = accounting.unformat(accounting.toFixed(raw.sum_kg, 2))
									vm.sum_total = accounting.unformat(accounting.toFixed(raw.sum_total, 2));

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
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'sale_date',
                            title: 'Fecha',
                            width: 120,
                            textAlign: 'center',
                        },
                        {
                            field: 'classification_name',
                            title: 'Presentación',
                            width: 120,
                        },
                        {
                            field: 'sum_kg',
                            title: 'KG',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'sum_total',
                            title: 'Monto',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'subgroup_id',
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

                this.massive_generation_datatable.columns('subgroup_id').visible(false);
            },
        }
    };
</script>