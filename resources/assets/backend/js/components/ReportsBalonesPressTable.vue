<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Balones
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        <a href="#" @click.prevent="exportExcel()" class="btn btn-brand btn-icon-sm" id="excel_export">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet__body">
            <div class="row">
                <div class="col-12">
                    <!--begin: Datatable -->
                    <div class="kt-datatable" id="kt-datatable"></div>
                    <!--end: Datatable -->
                </div>
            </div>
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
    },
    data() {
        return {
            show_table: false,
            datatable: undefined,
            model: {
                date_init: '',
                date_end: '',
                movement_type_id: 0,
                export: true,
            },
        }
    },
    created() { },
    mounted() {
        EventBus.$on('show_table', function (val) {
            const {
                data,
                model
            } = val;

            this.model = {
                ...model,
                export: true,
            };

            EventBus.$emit('loading', false);
            this.show_table = true;
            if (this.datatable) {
                this.datatable.destroy();
                this.$nextTick(function () {
                    this.fillTableX(data);
                });
            } else {
                this.$nextTick(function () {
                    this.fillTableX(val);
                });
            }
        }.bind(this));
    },
    watch: {},
    computed: {},
    methods: {
        fillTableX(data) {
            this.datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'local',
                    source: data,
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
                        field: "warehouse_type_name",
                        title: "Planta",
                        width: 120,
                    },
                    {
                        field: "client_name",
                        title: "Cliente",
                        width: 200,
                    },
                    {
                        field: "date",
                        title: "Fecha de Registro",
                        width: 120,
                    },
                    {
                        field: 'stock',
                        title: 'Stock',
                        width: 70,
                        textAlign: 'center',
                    },
                ]
            });

            this.datatable.columns('id').visible(false);
        },
        exportExcel: function() {
            EventBus.$emit('loading', true);
            const model = this.model;
            this.export = 1;

            axios.post(this.url, {
                ...model
            }, {
                responseType: 'blob',
            }).then(response => {
                console.log(response);
                EventBus.$emit('loading', false);

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'reporte-de-balones-presatados-'+Date.now()+'.xls'); //or any other extension
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
}
</script>