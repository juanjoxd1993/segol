<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table == 1">
        <!--begin::Form-->

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
                            placeholder="Buscar por Serie y/o Correlativo">
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-portlet__body">
            <div class="row">
                <div class="col-12" @click="manageActions">
                    <!--begin: Datatable -->
                    <div class="kt-datatable"></div>
                    <!--end: Datatable -->
                </div>
            </div>
        </div>

        <!--end::Form-->
    </div>
    <!--end::Portlet-->
</template>

<script>
import EventBus from '../event-bus';

export default {
    props: {
        url: {
            type: String,
            default: '',
        },
        url_download: {
            type: String,
            default: '',
        },
        url_get_detail: {
            type: String,
            default: ''
        },
    },
    data() {
        return {
            show_table: 0,
            datatable: undefined,
            company_id: '',
            movement_type_id: '',
        }
    },
    created() {
        EventBus.$on('show_table', function (data) {

            let vm = this;
            this.show_table = 1;
            this.company_id = data.company_id;
            this.movement_type_id = data.movement_type_id;

            Vue.nextTick(function () {
                if (vm.datatable != undefined) {
                    vm.datatable.destroy();
                    vm.fillTableX();
                    vm.datatable.load();
                } else {

                    vm.fillTableX();

                }
            });

        }.bind(this));

    },
    mounted() {

    },
    watch: {

    },
    computed: {

    },
    methods: {

        fillTableX: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            // Define todas las columnas necesarias
            let columns = [
                {
                    field: 'id',
                    title: 'Id',
                    width: 40,
                    textAlign: 'center',
                },
                {
                    field: 'company_name',
                    title: 'Compañía',
                    width: 100,
                    textAlign: 'center',
                },
                {
                    field: 'movement_type_name',
                    title: 'Tipo de Movimiento',
                    width: 100,
                    textAlign: 'center',
                },
                {
                    field: 'serie',
                    title: 'Guía',
                    width: 80,
                    textAlign: 'center',
                },
                {
                    field: 'fecha_emision',
                    title: 'Fecha Despacho',
                    width: 80,
                    textAlign: 'center',
                },
                {
                    field: 'traslate_date',
                    title: 'Fecha de Traslado',
                    width: 80,
                    textAlign: 'center',
                },
                {
                    field: 'options',
                    title: 'Opciones',
                    sortable: false,
                    width: 80,
                    overflow: 'visible',
                    autoHide: false,
                    textAlign: 'right',
                    template: function (row) {
                        let actions = '<div class="actions">';
                        actions += '<a href="#" class="descargar btn btn-sm btn-clean btn-icon btn-icon-md" title="Descargar">';
                        actions += '<i class="la la-download"></i>';
                        actions += '</a>';
                        actions += '<a href="#" class="view-detail btn btn-sm btn-clean btn-icon btn-icon-md" title="Ver">';
                        actions += '<i class="la la-eye"></i>';
                        actions += '</a>';
                        actions += '</div>';
                        return actions;
                    },
                },
            ];

            if (this.movement_type_id == 11) {
                columns.push({
                    field: 'chofer',
                    title: 'Chofer',
                    width: 100,
                    textAlign: 'center',
                });
            } else if (this.movement_type_id == 12) {
                columns.push({
                    field: 'cliente',
                    title: 'Cliente',
                    width: 100,
                    textAlign: 'center',
                });
                columns.push({
                    field: 'chofer',
                    title: 'Chofer',
                    width: 100,
                    textAlign: 'center',
                });
            }

            this.datatable = $('.kt-datatable').KTDatatable({
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: vm.url,
                            params: {
                                _token: token,
                                company_id: vm.company_id,
                                movement_type_id: vm.movement_type_id,
                            }
                        },
                    },
                    pageSize: 10,
                    serverPaging: true,
                    serverFiltering: true,
                    serverSorting: true,
                },

                layout: {
                    scroll: true,
                    height: 400,
                    footer: false
                },

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
                    autoHide: false,
                },

                columns: columns
            });

            this.datatable.columns('id').visible(false);
        },
        manageActions: function (event) {

            if ($(event.target).hasClass('descargar')) {

                EventBus.$emit('loading', true);
                event.preventDefault();
                let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();



                axios.post(this.url_download, { 'id': id }, {
                    responseType: 'blob',
                })
                    .then(response => {
                        EventBus.$emit('loading', false);

                        const date = new Date();
                        const fecha = date.getFullYear() + '-' + (date.getMonth() + 1) + '-' + date.getDate();

                        console.log(response.data);
                        this.ids = [];

                        const url = window.URL.createObjectURL(new Blob([response.data]));
                        const link = document.createElement('a');
                        link.href = url;
                        link.setAttribute('download', "guia_electronica_" + fecha + '.pdf');
                        document.body.appendChild(link);
                        link.click();

                        Swal.fire({
                            title: 'Guía Electrónica descargado.',
                            html: "Bien",
                            type: "success",
                            confirmButtonText: 'Ok',
                            timer: 2000,
                        }).then((result) => {
                            // this.datatable.destroy();
                            // this.fillTableX();
                            this.datatable.load();
                        });
                    }).catch(error => {
                        EventBus.$emit('loading', false);
                        console.log(error);
                        console.log(error.response);

                        this.ids = [];

                        Swal.fire({
                            title: 'Error',
                            text: "Error al descargar la Guía Electrónica",
                            type: "error",
                            confirmButtonText: 'Ok',
                        });
                    });

            } else if ($(event.target).hasClass('view-detail')) {
                event.preventDefault();
                EventBus.$emit('loading', true);

                let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

                axios.post(this.url_get_detail, {
                    'id': id
                }).then(response => {
                    EventBus.$emit('loading', false);
                    EventBus.$emit('show_voucher_detail_modal', response.data);
                    // console.log(response.data);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            }
        },

    }
};
</script>