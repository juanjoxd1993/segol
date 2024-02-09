<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Resultado <span v-show="article_name">{{ article_name }}</span>
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
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
            url_export_record: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                datatable: undefined,
                model: '',
                article_name: '',
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
            exportRecord: function() {
                EventBus.$emit('loading', true);

                axios.post(this.url_export_record, {
                    warehouse_type_id: this.model.warehouse_type_id,
                    since_date: this.model.since_date,
                    to_date: this.model.to_date,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-kardex-'+Date.now()+'.xlsx'); //or any other extension
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
                                    model: vm.model,
                                },
                                map: function(raw) {
                                    var dataSet = raw;
                                    if (typeof raw.items !== 'undefined') {
                                        dataSet = raw.items;
                                    }

                                    vm.article_name = raw.article_name;

                                    console.log(dataSet);
                                    
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
							field: 'movement_class_name',
							title: 'Clase',
							width: 80,
						},
                        {
                            field: 'movement_type_name',
                            title: 'Movimiento',
                            width: 80,
                        },
                        {
                            field: 'movement_number',
                            title: '# Parte',
                            width: 60,
                        },
                        {
                            field: 'creation_date',
                            title: 'Fecha',
                            width: 80,
                        },
                        {
                            field: 'old_stock_good',
                            title: 'Saldo inicial Buen estado',
                            width: 80,
                        },
                        {
                            field: 'converted_amount_good',
                            title: 'Cantidad Buen estado',
                            width: 80,
                        },
                        {
                            field: 'new_stock_good',
                            title: 'Saldo final Buen estado',
                            width: 80,
                        },
                        {
                            field: 'old_stock_damaged',
                            title: 'Saldo inicial Mal estado',
                            width: 80,
                        },
                        {
                            field: 'converted_amount_damaged',
                            title: 'Cantidad Mal estado',
                            width: 80,
                        },
                        {
                            field: 'new_stock_damaged',
                            title: 'Saldo final Mal estado',
                            width: 80,
                        },
                        {
                            field: 'account_document_type_name',
                            title: 'Tipo Doc.',
                            width: 80,
                        },
                        {
                            field: 'account_document_number',
                            title: 'Documento',
                            width: 100,
                        },
                        {
                            field: 'account_name',
                            title: 'Nombre o Razón Social',
                            width: 150,
                        },
                        {
                            field: 'referral_guide',
                            title: 'Guía Remisión',
                            width: 80,
                        },
                        {
                            field: 'referral_warehouse_document_type_name',
                            title: 'Tipo de Ref.',
                            width: 80,
                        },
                        {
                            field: 'referral_document',
                            title: 'Referencia',
                            width: 80,
                        },
                        {
                            field: 'scop_number',
                            title: 'SCOP',
                            width: 100,
                            textAlign: 'right'
                        },
                        {
                            field: 'license_plate',
                            title: 'Placa',
                            width: 80,
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
                        // {
                        //     field: 'options',
                        //     title: 'Opciones',
                        //     sortable: false,
                        //     width: 80,
                        //     overflow: 'visible',
                        //     autoHide: false,
                        //     textAlign: 'right',
                        //     template: function(row) {
                        //         if ( row.state == 0 ) {
                        //             let actions = '<div class="actions">';
                        //                 actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                        //                     actions += '<i class="la la-edit"></i>';
                        //                 actions += '</a>';
                        //                 actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                        //                     actions += '<i class="la la-trash"></i>';
                        //                 actions += '</a>';
                        //             actions += '</div>';

                        //             return actions;
                        //         }
                        //     },
                        // },
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