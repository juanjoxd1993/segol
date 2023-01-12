<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        <a href="#" @click.prevent="openModal()" class="btn btn-success disabled" id="add_product">
                            <i class="fa fa-plus"></i> Crear Producto
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-portlet__body">
            <div class="row">
                <div class="col-lg-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="flaticon flaticon-search"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control" name="generalSearch" id="generalSearch" placeholder="Buscar...">
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
            <!--begin: Datatable -->
            <div class="kt-datatable"></div>
            <!--end: Datatable -->
        </div>
    </div>
    <!--end::Portlet-->
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
          
            url_delete: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                datatable: undefined,
                category_id: '',
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.category_id = response.data;
                if ( this.datatable == undefined ) {
                    this.fillTableX();
                } else {
                    this.datatable.setDataSourceParam('category_id', this.category_id);
                    this.datatable.load();
                }

                this.datatable.on('kt-datatable--on-ajax-done', function() {
                    EventBus.$emit('loading', false);
                });

                document.getElementById('add_product').classList.remove('disabled');
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.datatable != undefined ) {
                    this.datatable.load();
                }
            }.bind(this));
        },
        watch: {
            
        },
        computed: {

        },
        methods: {
            openModal: function() {
                EventBus.$emit('create_modal', this.category_id);
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    category_id: vm.category_id,
                                }
                            },
                        },
                        pageSize: 10,
                        serverPaging: true,
                        serverFiltering: true,
                        serverSorting: true,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: true,

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
							field: 'document_type_name',
							title: 'Tipo de Doc.',
							width: 80,
						},
                        {
                            field: 'document_number',
                            title: '# de Doc.',
                            width: 100,
                        },
                        {
                            field: 'id',
                            title: 'ID',
                            width: 60,
                        },
                        {
                            field: 'business_name',
                            title: 'Nombre o Razón Social',
                            width: 180,
                        },
                        {
                            field: 'email',
                            title: 'Correo',
                            width: 100,
                        },
                        {
                            field: 'phone_number_1',
                            title: 'Teléfono 1',
                            width: 100,
                        },
                        {
                            field: 'phone_number_2',
                            title: 'Teléfono 2',
                            width: 100,
                        },
                        {
                            field: 'seller_id',
                            title: 'Vendedor',
                            width: 80,
                        },
                        {
                            field: 'credit_limit',
                            title: 'Límite de Crédito',
                            width: 100,
                            template: function(row) {
                                return 'S/ ' + row.credit_limit;
                            }
                        },
                        {
                            field: 'perception_percentage_value',
                            title: 'Percepción',
                            width: 80,
                            template: function(row) {
                                return row.perception_percentage_value + '%';
                            }
                        },
                   //     {
                    //        field: 'id',
                    //        title: 'ID',
                    //        width: 0,
                    //        overflow: 'hidden',
                    //        responsive: {
                    //            hidden: 'sm',
                    //            hidden: 'md',
                    //           hidden: 'lg',
                    //            hidden: 'xl'
                    //        }
                    //    },
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 160,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                let actions = '<div class="actions">';
                                    
									
									actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
										actions += '<i class="la la-edit"></i>';
									actions += '</a>';
                                    actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                        actions += '<i class="la la-trash"></i>';
                                    actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        },
                    ]
                });

             //   this.datatable.columns('id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

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
                            axios.post(this.url_delete, {
                                id: id,
                            }).then(response => {
								this.datatable.load();
                                EventBus.$emit('loading', false);
                                Swal.fire({
                                    title: '¡Ok!',
                                    text: 'Se ha eliminado correctamente',
                                    type: "success",
                                    heightAuto: false,
                                });
                            }).catch(error => {
                                console.log(error);
                                console.log(error.response);
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    EventBus.$emit('loading', true);

                    axios.post(this.url_detail, {
                        id: id,
                    }).then(response => {
                        EventBus.$emit('edit_modal', response.data);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                } 
            },
        }
    };
</script>