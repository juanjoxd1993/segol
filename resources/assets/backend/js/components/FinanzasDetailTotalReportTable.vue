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
                            <a href="#" :class="color" @click.prevent="saveData()">
                                <i class="fa fa-regular fa-folder"></i> {{ title_button }}
                            </a>
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="exportExcel()">
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
    </div>
</template>

<script>
import { update } from 'lodash';
import EventBus from '../event-bus';

export default {
    props: {
        url: {
            type: String,
            default: ''
        },
        url_state: {
            type: String,
            default: ''
        },

    },
    data() {
        return {
            finanzas_detail_total_report_datatable: undefined,
            show_table: false,
            model: {},
            export: '',
            title_button: '',
            color: ''
        }
    },
    created() {

    },
    mounted() {

        EventBus.$on('show_table', function (response) {
            let vm = this;
            this.show_table = true;
            this.model = response.model;
            this.updateState();

            Vue.nextTick(function () {

                $('.kt-datatable').KTDatatable('destroy');
                vm.finanzas_detail_total_report_datatable = undefined;

                vm.fillTableX();
                EventBus.$emit('loading', false);


                vm.finanzas_detail_total_report_datatable.on('kt-datatable--on-ajax-done', function () {
                    EventBus.$emit('loading', false);
                });
            });
        }.bind(this));

    },
    watch: {

    },
    computed: {

    },
    methods: {
        updateState: function () {
            axios.post(this.url_state, {
                model: this.model

            }).then(response => {
                if (response.data == 1) {
                    this.title_button = 'Cerrar Día';
                    this.color = 'btn btn-success';
                } else {
                    this.title_button = 'Día Cerrado';
                    this.color = 'btn btn-danger disabled';
                }
            }).catch(error => {
                console.log(error.response);
                EventBus.$emit('loading', false)
            });
        },
        saveData: function () {

            Swal.fire({
                title: '¡Cuidado!',
                text: '¿Seguro que desea Guardar la Data?',
                type: "warning",
                heightAuto: false,
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'No'
            }).then(result => {
                EventBus.$emit('loading', true);

                if (result.value) {

                    EventBus.$emit('loading', true);
                    this.export = 2;

                    axios.post(this.url, {
                        model: this.model,
                        export: this.export,
                    }, {}).then(response => {
                        // console.log(response);
                        EventBus.$emit('loading', false);
                        this.updateState();
                        Swal.fire({
                            title: 'Bien',
                            text: 'Data guardado correctamente.',
                            type: "success",
                            // timer: 2000,
                            heightAuto: false,
                        })

                        this.export = '';
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                        EventBus.$emit('loading', false);
                    });

                } else if (result.dismiss == Swal.DismissReason.cancel) {
                    EventBus.$emit('loading', false);
                }
            });
        },
        fillTableX: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            this.finanzas_detail_total_report_datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: vm.url,
                            params: {
                                _token: token,
                                model: vm.model,
                                export: vm.export
                            },
                        },
                        pageSize: 10,
                    },
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
                        field: 'company_short_name',
                        title: 'Liquidación',
                        width: 120,
                        textAlign: 'left',
                    },

                    /*    {
                           field: 'liquidation_date',
                           title: 'Fecha de Liquidación',
                           width: 120,
                           textAlign: 'left',
                       },     */


                    {
                        field: 'total',
                        title: 'Total',
                        width: 140,
                        textAlign: 'right',
                    },


                    {
                        field: 'id',
                        title: 'ID',
                        width: 0,
                        // overflow: 'hidden',
                        // responsive: {
                        //     hidden: 'sm',
                        //     hidden: 'md',
                        //     hidden: 'lg',
                        //     hidden: 'xl'
                        // }
                    },


                ]
            });

            this.finanzas_detail_total_report_datatable.columns('id').visible(false);
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
                link.setAttribute('download', 'reporte-cierre-caja-' + Date.now() + '.xls');
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
};
</script>