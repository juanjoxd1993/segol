<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Stocks
                </h3>
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
        props: {},
        data() {
            return {
                show_table: false,
                datatable: undefined,
            }
        },
        created() {},
        mounted() {
            EventBus.$on('show_table',function (val) {
                this.show_table = true;
                if (this.datatable) {
                    this.datatable.destroy();
                    this.$nextTick(function() {
                        this.fillTableX(val);
                    });
                } else {
                    this.$nextTick(function() {
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
                            field: "name",
                            title: "Nombre de Producto",
                            width: 120,
                        },
                        {
                            field: 'stock',
                            title: 'Stock Total',
                            width: 70,
                            textAlign: 'center',
                        },
                        {
                            field: "stock_comprado",
                            title: "Comprado",
                            width: 70,
                            textAlign: 'center',
                        },
                        {
                            field: "stock_transito",
                            title: "En Transito",
                            width: 70,
                            textAlign: 'center',
                        },
                        {
                            field: "stock_envasado",
                            title: "Plantas",
                            width: 70,
                            textAlign: 'center',
                        },
                        {
                            field: "stock_producido",
                            title: "Producido",
                            width: 70,
                            textAlign: 'center',
                        },
                    ]
                });

                this.datatable.columns('id').visible(false);
            },
        }
    }
</script>