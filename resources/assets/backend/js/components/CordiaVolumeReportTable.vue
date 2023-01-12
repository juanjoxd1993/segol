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
                                },
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
                            field: "voucher_type_type",
                            title: "ID Doc.",
                            width: 50,
                            textAlign: 'center',
                        },
                        {
                            field: "voucher_type_name",
                            title: "Tipo de Doc.",
                            width: 120,
                        },
                        {
                            field: "serie_number",
                            title: "Serie",
                            width: 50,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "initial_voucher_number",
                            title: "Nº Inicial",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "final_voucher_number",
                            title: "Nº Final",
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
                   //     {
                   //         field: 'article_code',
                   //         title: 'Cód. Artículo',
                   //         width: 80,
                   //         textAlign: 'center',
                   //     },
                   /*     {
                            field: 'article_name',
                            title: 'Descripción',
                            width: 140,
                        },*/
                        {
                            field: "sum_quantity",
                            title: "Cantidad",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "sum_sale_value",
                            title: "Valor Venta",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "sum_igv",
                            title: "IGV",
                            width: 80,
                            textAlign: 'right',
                        },
                        {
                            field: "sum_total",
                            title: "Total",
                            width: 80,
                            textAlign: 'right',
                        },


                        {
                            field: 'gallons',
                            title: 'Galones',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_1k',
                            title: '1K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_5k',
                            title: '5K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_10k',
                            title: '10K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_15k',
                            title: '15K',
                            width: 120,
                            textAlign: 'right',
                        },
						{
                            field: 'sum_45k',
                            title: '45K',
                            width: 120,
                            textAlign: 'right',
                        },
						
						{
                            field: 'sum_tm_total',
                            title: 'Total Kg.',
                            width: 120,
                            textAlign: 'right',
                        },




                    ]
                });

                // $('.kt-datatable').on('change','input[type="checkbox"]', function($event) {
                //     vm.elements = [];
                //     vm.ids = vm.datatable.checkbox().getSelectedId();
                // }.bind(this));
            },
            exportExcel: function() {
				EventBus.$emit('loading', true);

                axios.post(this.url_export, {
                    model: this.model
                }, {
                    responseType: 'blob',
                }).then(response => {
					EventBus.$emit('loading', false);
                    console.log(response);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-de-volumen-boletas.xls'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
					EventBus.$emit('loading', false);
                    console.log(error);
                    console.log(error.response);
                });
            },
        }
    };
</script>