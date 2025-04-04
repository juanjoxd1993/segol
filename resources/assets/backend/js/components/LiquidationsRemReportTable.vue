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
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportExcel()">
                                <i class="fa fa-file-excel"></i> Exportar Excel
                            </a>
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
            liquidations_rem_report_datatable: undefined,
            show_table: false,
            model: {},
            export: '',
        }
    },
    created() {

    },
    mounted() {
        EventBus.$on('show_table', function (response) {
            let vm = this;
            this.show_table = true;
            this.model = response;

            Vue.nextTick(function () {
                if (vm.liquidations_rem_report_datatable == undefined) {
                    vm.fillTableX();
                } else {
                    vm.liquidations_rem_report_datatable.setDataSourceParam('model', vm.model);
                    vm.liquidations_rem_report_datatable.load();
                }

                vm.liquidations_rem_report_datatable.on('kt-datatable--on-ajax-done', function () {
                    EventBus.$emit('loading', false);
                });
            });
        }.bind(this));

        EventBus.$on('refresh_table', function () {
            if (this.liquidations_rem_report_datatable != undefined) {
                this.liquidations_rem_report_datatable.setDataSourceParam('model', this.model);
                this.liquidations_rem_report_datatable.load();
            }
        }.bind(this));
    },
    watch: {

    },
    computed: {

    },
    methods: {
        fillTableX: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            this.liquidations_rem_report_datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: vm.url,
                            params: {
                                _token: token,
                                model: vm.model,
                                export: vm.export,

                            },

                            map: function (raw) {
                                var dataSet = raw;
                                if (typeof raw.data !== 'undefined') {
                                    dataSet = raw.data;
                                }

                                dataSet.map(element => {
                                    element.total = accounting.toFixed(element.total, 2);
                                });

                                return dataSet;

                            }

                        },

                    },
                    pageSize: 10,
                },

                // layout definition
                layout: {
                    scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                    height: 600,
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
                        field: 'sale_date',
                        title: 'Fecha Emisión',
                        width: 80,
                        textAlign: 'center',
                    },
                    {
                        field: 'initial_voucher',
                        title: 'Recibo Inicial',
                        width: 120,
                        textAlign: 'left',
                    },
                    {
                        field: 'final_voucher',
                        title: 'Recibo Final',
                        width: 120,
                        textAlign: 'center',
                    },
                    {
                        field: 'sum_total',
                        title: 'Monto Remesa',
                        width: 120,
                        textAlign: 'right',
                    },

                    {
                        field: 'state',
                        title: 'Estado',
                        width: 120,
                        textAlign: 'right',
                    },
                    {
                        field: 'options',
                        title: 'Opciones',
                        sortable: false,
                        width: 60,
                        overflow: 'visible',
                        autoHide: false,
                        textAlign: 'right',
                        class: 'td-sticky',
                        template: function (row) {

                            let actions = '<div class="actions" style="display:flex">';
                            actions += '<a style="cursor:pointer" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                            actions += '<i class="la la-edit"></i>';
                            actions += '</a>';
                            actions += '</div>';
                            return actions;

                        },
                    },
                ]
            });

            //this.liquidations_rem_report_datatable.columns('id').visible(false);
        },
        exportExcel: function () {
            EventBus.$emit('loading', true);
            this.export = 1;

            axios.post(this.url, {
                model: this.model,
                export: this.export,
            }, {
                responseType: 'blob',
            }).then(response => {
                // console.log(response);
                EventBus.$emit('loading', false);

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'reporte-facturaciones-' + Date.now() + '.xls');
                document.body.appendChild(link);
                link.click();

                this.export = '';
            }).catch(error => {
                console.log(error);
                console.log(error.response);
                EventBus.$emit('loading', false);
            });
        },
        manageActions: function (event) {
            if ($(event.target).hasClass('delete')) {
                event.preventDefault();
                let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea eliminar la factura?',
                    type: "warning",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    EventBus.$emit('loading', true);

                    if (result.value) {
                        axios.post(this.url_delete, {
                            id: id,
                        }).then(response => {
                            // this.datatable.load();
                            EventBus.$emit('loading', false);
                            Swal.fire({
                                title: '¡Ok!',
                                text: 'Se ha eliminado correctamente',
                                type: "success",
                                heightAuto: false,
                            });
                        }).catch(error => {
                            EventBus.$emit('loading', false);
                            Swal.fire({
                                title: '¡Error!',
                                text: 'Ha ocurrido un error',
                                type: "error",
                                heightAuto: false,
                            });
                            console.log(error);
                            console.log(error.response);
                        });
                    } else if (result.dismiss == Swal.DismissReason.cancel) {
                        EventBus.$emit('loading', false);
                    }
                });
            } else if ($(event.target).hasClass('edit')) {
                event.preventDefault();

                
                let initial_voucher = $(event.target).parents('tr').find('td[data-field="initial_voucher"] span').html();
                let final_voucher = $(event.target).parents('tr').find('td[data-field="final_voucher"] span').html();
                let sum_total = $(event.target).parents('tr').find('td[data-field="sum_total"] span').html();

                EventBus.$emit('loading', true);
                EventBus.$emit('edit_modal', initial_voucher,final_voucher,sum_total);
            }
        },
    }
};
</script>