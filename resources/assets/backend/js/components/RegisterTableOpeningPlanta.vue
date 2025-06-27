<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Ultimos 7 dias
                </h3>
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
                        <input type="text" class="form-control" name="generalSearch" id="generalSearch"
                            placeholder="Buscar...">
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
    },
    data() {
        return {
            datatable: undefined,
        }
    },
    created() {

    },
    mounted() {
        if (this.datatable == undefined) {
            this.fillTableX();
        } else {
            this.datatable.load();
        }

        this.datatable.on('kt-datatable--on-ajax-done', function () {
            EventBus.$emit('loading', false);
        });

        EventBus.$on('refresh_table', function () {

            this.datatable.destroy();
            this.fillTableX();
            this.datatable.load();

            this.datatable.on('kt-datatable--on-ajax-done', function () {
                EventBus.$emit('loading', false);
            });
        }.bind(this));
    },
    methods: {

        fillTableX: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            this.datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: vm.url_list,
                            params: {
                                _token: token,
                            }
                        },
                    }
                },

                pageSize: 10,
                serverPaging: true,
                serverFiltering: true,
                serverSorting: true,

                search: {
                    input: $('#generalSearch'),
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
                        field: 'cierre_id',
                        title: 'ID',
                        width: 80,
                        textAlign: 'center',
                    },
                    {
                        field: 'cierre_date',
                        title: 'Fecha de Cierre',
                        width: 150,
                    },
                    {
                        field: 'company_name',
                        title: 'Compañía',
                        width: 200,
                    },
                    {
                        field: 'planta_state',
                        title: 'Estado de Planta',
                        width: 150,
                    },
                    {
                        field: 'planta_user',
                        title: 'Usuario',
                        width: 150,
                    },

                ]
            });

            this.datatable.columns('cierre_id').visible(false);
        },

    }
};
</script>