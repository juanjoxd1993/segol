<template>
    <!--begin::Portlet-->
    <div class="kt-portlet kt-portlet--mobile" v-show="show_table">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand la la-file-text"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        <a href="#" @click.prevent="exportExcel()" class="btn btn-brand btn-icon-sm">
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
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            url_list: {
                type: String,
                default: ''
            },
            url_export: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                datatable: undefined,
                show_table: true,
                model: {},
            }
        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.show_table = true;
                this.model = response.data;

                if ( this.datatable != undefined ) {
                    this.elements = [];
                    this.datatable.destroy();
                    this.fillTableX();
                } else {
                    this.fillTableX();
                }

                // axios.post(this.url_list, {
                //     model: this.model
                // }).then(response => {
                //     console.log(response);
                // }).catch(error => {
                //     console.log(error);
                //     console.log(error.response);
                // });
            }.bind(this));

            // this.fillTableX();
        },
        methods: {
            getInfo: function(data) {
                this.fillTableX(data);
            },
            fillTableX: function() {
                let url_list = this.url_list;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                let vm = this;
                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: url_list,
                                params: {
                                    _token: token,
                                    model: vm.model
                                }
                            },
                        },
                        pageSize: 10,
                        // serverPaging: true,
                        // serverFiltering: true,
                        // serverSorting: true,
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

                    // search: {
                    //     input: $('#generalSearch'),
                    // },

                    extensions: {
                        checkbox: {},
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
                        // {
                        //     field: 'voucher_id',
                        //     title: '#',
                        //     sortable: false,
                        //     width: 0,
                        //     selector: {class: 'kt-checkbox--solid'},
                        //     textAlign: 'center',
                        //     responsive: {
                        //         hidden: 'sm',
                        //         hidden: 'md',
                        //         hidden: 'lg',
                        //         hidden: 'xl'
                        //     }
                        // },
                        {
                            field: "voucher_type",
                            title: "ID Doc.",
                            width: 50,
                            textAlign: 'center',
                        },
                        {
                            field: "serie_number",
                            title: "Serie",
                            width: 50,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "voucher_number_since",
                            title: "Doc. Inicial",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "voucher_number_to",
                            title: "Doc. Final",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "issue_date",
                            title: "Fec. Emisión",
                            width: 80,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: 'client_document_number',
                            title: 'RUC/DNI/CE',
                            width: 80,
                            textAlign: 'center',
                        },
                        {
                            field: 'client_name',
                            title: 'Razón Social',
                            width: 140,
                        },
                        {
                            field: "taxed_operation",
                            title: "Valor Venta",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "unaffected_operation",
                            title: "Valor Inafecto",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "exonerated_operation",
                            title: "Valor Exonerado",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "igv",
                            title: "IGV",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "total",
                            title: "Total",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "credit_note_reference_serie",
                            title: "Serie de ref.",
                            width: 50,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "credit_note_reference_number",
                            title: "Nº de ref.",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: 'payment_name',
                            title: 'Método de Pago',
                            width: 80,
                        },
                    ]
                });

                // $('.kt-datatable').on('change','input[type="checkbox"]', function($event) {
                //     vm.elements = [];
                //     vm.ids = vm.datatable.checkbox().getSelectedId();
                // }.bind(this));
            },
            exportExcel: function() {
                axios.post(this.url_export, {
                    model: this.model
                }, {
                    responseType: 'blob',
                }).then(response => {
                    console.log(response);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-de-ventas.xls'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
        }
    };
</script>