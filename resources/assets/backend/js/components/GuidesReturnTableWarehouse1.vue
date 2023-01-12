<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Articulos
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-success btn-bold btn-sm" v-show="flag_add" @click.prevent="openModal()">
                                <i class="la la-plus"></i> Agregar articulo
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
                <!--begin: Datatable -->
                <div class="kt-datatable-warehouses"></div>
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
            
        },
        data() {
            return {
                warehouse_datatable: undefined,
                show_table: false,
                flag_add: false,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', () => {
                this.show_table = true;
                this.flag_add = true;

                this.$nextTick(function() {
                    if ( this.warehouse_datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        this.warehouse_datatable.originalDataSet = this.$store.state.warehouses;
                        this.warehouse_datatable.load();
                    }
                });
            });

            EventBus.$on('refresh_table_warehouse', () => {
                if ( this.warehouse_datatable != undefined ) {
                    this.warehouse_datatable.originalDataSet = this.$store.state.warehouses;
                    this.warehouse_datatable.load();
                }
            });
        },
        watch: {

        },
        computed: {

        },
        methods: {
            openModal: function() {
                EventBus.$emit('create_modal');
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.warehouse_datatable = $('.kt-datatable-warehouses').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.$store.state.warehouses,
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
                            field: 'client_name',
                            title: 'Cliente',
                            width: 200,
                            textAlign: 'left',
                        },
						{
                            field: 'warehouse_document_type_name',
                            title: 'Tipo',
                            width: 120,
                            textAlign: 'left',
                        },
                        {
                            field: 'total',
                            title: 'Valor Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'perception',
                            title: 'Percepción',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'total_perception',
                            title: 'Total',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'client_id',
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
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 120,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                let actions = '<div class="actions">';
                                    // actions += '<a href="#" class="view btn btn-sm btn-clean btn-icon btn-icon-md" title="Ver">';
                                    //     actions += '<i class="la la-eye"></i>';
                                    // actions += '</a>';
                                    // actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                                    //     actions += '<i class="la la-edit"></i>';
                                    // actions += '</a>';
                                    // actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                    //     actions += '<i class="la la-trash"></i>';
                                    // actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        }
                    ]
                });

                // this.sale_datatable.columns('client_id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let client_id = $(event.target).parents('tr').find('td[data-field="client_id"] span').html();

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el registro?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', true);

                        if ( result.value ) {
                            
                            this.warehouses.find(element => {
                                if ( element.client_id == client_id ) {
                                    let index = this.warehouses.indexOf(element);
                                    if ( index > -1 ) {
                                        this.warehouses.splice(index, 1);

                                        this.warehouse_datatable.originalDataSet = this.warehouses;
                                        this.warehouse_datatable.load();

                                        Swal.fire({
                                            title: '¡Ok!',
                                            text: 'Se ha eliminado correctamente',
                                            type: "success",
                                            heightAuto: false,
                                        });

                                        EventBus.$emit('loading', false);
                                    }
                                }
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    EventBus.$emit('loading', true);
                    let client_id = $(event.target).parents('tr').find('td[data-field="client_id"] span').html();

					let edit_sale = this.$store.getters.editSale(client_id);
					EventBus.$emit('edit_modal', edit_sale);

                    // this.sales.find(element => {
                    //     if ( element.client_id == client_id ) {
                    //         this.sale = JSON.parse(JSON.stringify(element));
                    //         EventBus.$emit('edit_modal', this.sale);
                    //     }
                    // })
                }
            },
        }
    };
</script>