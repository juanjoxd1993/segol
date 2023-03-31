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
                        <a href="#" @click.prevent="createRecord()" class="btn btn-primary" id="createRecord" v-show="flag_create == 1">
                            Crear
                        </a>
                        <a href="#" @click.prevent="addRecord()" class="btn btn-success" id="addRecord" v-show="flag_add == 1">
                            <i class="fa fa-plus"></i> Registro
                        </a>
                        <a href="#" @click.prevent="closeRecord()" class="btn btn-danger" id="closeRecord" v-show="flag_close == 1">
                            <i class="la la-close"></i> Cierre
                        </a>
                        <a href="#" @click.prevent="formRecord()" class="btn btn-primary" id="formRecord" v-show="flag_form == 1">
                            <i class="fa fa-file-alt"></i> Imprimir formulario
                        </a>
                        <a href="#" @click.prevent="exportRecord()" class="btn btn-primary" id="exportRecord" v-show="flag_form == 1">
                            <i class="fa fa-file-excel"></i> Exportar excel
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
            url_create_record: {
                type: String,
                default: ''
            },
            url_close_record: {
                type: String,
                default: ''
            },
            url_form_record: {
                type: String,
                default: ''
            },
            url_export_record: {
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
                model: '',
                flag_create: 0,
                flag_add: 0,
                flag_close: 0,
                flag_form: 0,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.model = response;
                if ( this.datatable == undefined ) {
                    this.fillTableX();
                } else {
                    this.datatable.setDataSourceParam('model', this.model);
                    this.datatable.load();
                }

                this.datatable.on('kt-datatable--on-ajax-done', function() {
                    EventBus.$emit('loading', false);
                });
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
            createRecord: function() {
                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea Crear el inventario?',
                    type: "warning",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    EventBus.$emit('loading', true);

                    if ( result.value ) {
                        this.flag_create = 0;
                        this.flag_add = 1;

                        axios.post(this.url_create_record, {
                            model: this.model,
                        }).then(response => {
                            // console.log(response.data);
                            this.datatable.load();
                            EventBus.$emit('loading', false);

                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se creó el Inventario con ' + response.data.total + ' registros.',
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
            },
            addRecord: function() {
                EventBus.$emit('create_modal', this.model);
            },
            closeRecord: function() {
                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea Cerrar el inventario?',
                    type: "error",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    EventBus.$emit('loading', true);

                    if ( result.value ) {
                        axios.post(this.url_close_record, {
                            company_id: this.model.company_id,
                            warehouse_type_id: this.model.warehouse_type_id,
                            creation_date: this.model.creation_date,
                        }).then(response => {
                            // console.log(response.data);

                            EventBus.$emit('loading', false);
                            this.datatable.load();

                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se cerró el Inventario exitosamente.',
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
            },
            formRecord: function() {
                EventBus.$emit('loading', true);

                axios.post(this.url_form_record, {
                    company_id: this.model.company_id,
                    warehouse_type_id: this.model.warehouse_type_id,
                    creation_date: this.model.creation_date,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);

                    let creation_date = new Date(this.model.creation_date);
                    let date = creation_date.getDate();
                    let month = creation_date.getMonth();
                    let year = creation_date.getFullYear();
                    creation_date = year+'-'+month+'-'+date;

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'formulario-inventario-'+creation_date+'.pdf'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
            exportRecord: function() {
                EventBus.$emit('loading', true);

                axios.post(this.url_export_record, {
                    company_id: this.model.company_id,
                    warehouse_type_id: this.model.warehouse_type_id,
                    creation_date: this.model.creation_date,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);

                    let creation_date = new Date(this.model.creation_date);
                    let date = creation_date.getDate();
                    let month = creation_date.getMonth();
                    let year = creation_date.getFullYear();
                    creation_date = year+'-'+month+'-'+date;

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-inventario-'+creation_date+'.xlsx'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
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
                                    data_flag: vm.data_flag,
                                    model: vm.model,
                                },
                                map: function(raw) {
                                    var dataSet = raw;
                                    if (typeof raw.data !== 'undefined') {
                                        dataSet = raw.data;
                                    }

                                    if ( raw.state === 1 ) {
                                        vm.flag_create = 0;
                                        vm.flag_add = 0;
                                        vm.flag_close = 0;
                                        vm.flag_form = 1;
                                    } else if ( raw.meta.total > 0 ) {
                                        vm.flag_create = 0;
                                        vm.flag_add = 1;
                                        vm.flag_close = 1;
                                        vm.flag_form = 1;
                                    } else if ( raw.meta.total === 0 && vm.flag_add === 1 ) {
                                        vm.flag_create = 0;
                                        vm.flag_add = 1;
                                        vm.flag_close = 0;
                                        vm.flag_form = 0;
                                    } else {
                                        vm.flag_create = 1;
                                        vm.flag_add = 0;
                                        vm.flag_close = 0;
                                        vm.flag_form = 0;
                                    }
                                    
                                    return dataSet;
                                },
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
							field: 'company_short_name',
							title: 'Compañía',
							width: 80,
						},
                        {
                            field: 'article_code',
                            title: 'Código',
                            width: 60,
                        },
                        {
                            field: 'article_name',
                            title: 'Artículo',
                            width: 150,
                        },
                        {
                            field: 'found_stock_good',
                            title: 'Cantidad en Buen Estado',
                            width: 150,
                        },
                        {
                            field: 'found_stock_damaged',
                            title: 'Cantidad en Mal Estado',
                            width: 150,
                        },
                        {
                            field: 'observations',
                            title: 'Observaciones',
                            width: 150,
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
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 80,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                if ( row.state == 0 ) {
                                    let actions = '<div class="actions">';
                                        actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                                            actions += '<i class="la la-edit"></i>';
                                        actions += '</a>';
                                        actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                            actions += '<i class="la la-trash"></i>';
                                        actions += '</a>';
                                    actions += '</div>';

                                    return actions;
                                }
                            },
                        },
                    ]
                });

                this.datatable.columns('id').visible(false);
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
                        // console.log(response.data);
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