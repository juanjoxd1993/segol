<template>
    <!--begin::Portlet-->
    <div class="kt-portlet kt-portlet--mobile" v-show="show_table" @click="manageActions">
        <div class="kt-portlet__head kt-portlet__head--lg">
            <div class="kt-portlet__head-label">
                <span class="kt-portlet__head-icon">
                    <i class="kt-font-brand la la-file-text"></i>
                </span>
                <h3 class="kt-portlet__head-title">
                    Lista de Documentos
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">

                        <a href="#" @click.prevent="sendVouchers('xml')" class="btn btn-outline-brand btn-bold btn-sm">
                            <i class="fa fa-file-exel"></i>
                            Generar XML
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet__body">
            <!--begin: Search Form -->
            <div class="kt-form kt-margin-t-20 kt-margin-b-10">
                <div class="row align-items-center">
                    <div class="col-xl-8 order-2 order-xl-1">
                        <div class="row align-items-center">
                            <div class="col-md-4 kt-margin-b-20-tablet-and-mobile">
                                <div class="kt-input-icon kt-input-icon--left">
                                    <input type="text" class="form-control" placeholder="Buscar..." id="generalSearch">
                                    <span class="kt-input-icon__icon kt-input-icon__icon--left">
                                        <span><i class="la la-search"></i></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--end: Search Form -->
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
        url_get_vouchers_for_table: {
            type: String,
            default: ''
        },
        url_send_voucher: {
            type: String,
            default: ''
        },
        url_get_voucher_detail: {
            type: String,
            default: ''
        },
        user_name: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            datatable: undefined,
            show_table: true,
            elements: [],
            element_details: [],
            model: {},
            ids: [],
        }
    },
    mounted() {
        EventBus.$on('show_table', function (response) {
            this.show_table = true;
            this.model = response.data;

            if (this.datatable != undefined) {
                this.elements = [];
                this.datatable.destroy();
                this.fillTableX();
                this.datatable.setDataSourceParam('model', this.model);
                this.datatable.load();
            } else {
                this.fillTableX();
            }

            this.datatable.on('kt-datatable--on-ajax-done', function () {
                EventBus.$emit('loading', false);
            });
        
        }.bind(this));

        // this.fillTableX();
    },
    methods: {
        getInfo: function (data) {
            this.fillTableX(data);
        },
        fillTableX: function () {
            let vm = this;
            let url_get_vouchers_for_table = this.url_get_vouchers_for_table;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            this.datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'remote',
                    source: {
                        read: {
                            url: this.url_list,
                            params: {
                                _token: token,
                                model: vm.model
                            },
                            map: function (raw) {
                                var dataSet = raw;
                                if (typeof raw.data !== 'undefined') {
                                    dataSet = raw.data;
                                }
                                dataSet.map(element => {
                                    element.total = accounting.formatNumber(element.total, 2);
                                    element.igv_perception = accounting.formatNumber(element.igv_perception, 2);
                                });
                                return dataSet;
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

                search: {
                    input: $('#generalSearch'),
                },

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
                    {
                        field: 'id',
                        title: '#',
                        sortable: false,
                        width: 30,
                        selector: { class: 'kt-checkbox--solid' },
                        textAlign: 'center',
                    },
                    {
                        field: 'route_id',
                        title: '#',
                        sortable: false,
                        width: 30,
                        textAlign: 'center',
                    },
                    {
                        field: 'options',
                        title: 'Opciones',
                        sortable: false,
                        width: 40,
                        overflow: 'visible',
                        autoHide: false,
                        textAlign: 'center',
                        template: function () {
                            return '\
                                <div class="actions">\
                                    <a href="#" class="view-detail btn btn-sm btn-clean btn-icon btn-icon-md" title="Ver">\
                                        <i class="la la-eye"></i>\
                                    </a>\
                                </div>\
                            ';
                        },
                    },
                    {
                        field: "type",
                        title: "Tipo",
                        width: 30,
                        sortable: true,
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
                        field: "voucher_number",
                        title: "Nº",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "summary_number",
                        title: "Nº Resumen",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "low_number",
                        title: "Nº Baja",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "issue_date_formated",
                        title: "Fecha de Emisión",
                        width: 90,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "payment_name",
                        title: "Método de Pago",
                        width: 80,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: 'order_series',
                        title: 'Serie de Pedido',
                        width: 40,
                        textAlign: 'center',
                    },
                    {
                        field: 'order_number',
                        title: 'Nº de Pedido',
                        width: 40,
                        textAlign: 'center',
                    },
                    {
                        field: "client_code",
                        title: "Código Cliente",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "document_type_name",
                        title: "Tipo de Doc.",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "client_document_number",
                        title: "Nº de Doc.",
                        width: 100,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "client_name",
                        title: "Nombre/Razón Social",
                        width: 130,
                        sortable: true,
                        textAlign: 'left',
                    },
                    {
                        field: "currency_symbol",
                        title: "Moneda",
                        width: 60,
                        sortable: true,
                        textAlign: 'center',
                    },
                    {
                        field: "total",
                        title: "Valor Total",
                        width: 80,
                        sortable: true,
                        textAlign: 'right',
                    },
                    {
                        field: "igv_perception",
                        title: "Percepción",
                        width: 80,
                        sortable: true,
                        textAlign: 'right',
                    },
                ]
            });

            // $('.kt-datatable').on('change','input[type="checkbox"]', function($event) {
            //     vm.elements = [];
            //     vm.ids = vm.datatable.checkbox().getSelectedId();
            // }.bind(this));

            $('.kt-datatable').on('kt-datatable--on-check', function (a, e) {

                $.each(e, function (key, value) {
                    let index = vm.ids.findIndex((element) => element == value);
                    if (index < 0) {
                        vm.ids.push(value);
                    }
                });

                vm.ids.sort(function (a, b) {
                    let aNumber = parseInt(a);
                    let bNumber = parseInt(b);
                    return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                });
            });

            $('.kt-datatable').on('kt-datatable--on-uncheck', function (a, e) {
                $.each(e, function (key, value) {
                    let index = vm.ids.findIndex((element) => element == value);
                    if (index >= 0) {
                        vm.ids.splice(index, 1);
                    }
                });

                vm.ids.sort(function (a, b) {
                    let aNumber = parseInt(a);
                    let bNumber = parseInt(b);
                    return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                });
            });
        },
        sendVouchers: function (task) {

            let vm = this;

            this.ids.sort(function (a, b) {
                let aNumber = parseInt(a);
                let bNumber = parseInt(b);
                return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
            });

            // console.log(this.elements);

            let data = {
                company_id: this.model.company_id,
                ids: this.ids,
                task: task,
                flag_ose: this.model.flag_ose
            };

            EventBus.$emit('loading', true);
            axios.post(this.url_send_voucher, data).then(response => {
                EventBus.$emit('loading', false);
                console.log(response.data);
                this.ids = [];

                Swal.fire({
                    title: 'Se generó correctamente el XML y se envió a OSE.',
                    html: "Bien",
                    type: "success",
                    confirmButtonText: 'Ok',
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
                    text: error.response.data.message,
                    type: "error",
                    confirmButtonText: 'Ok',
                });
            });
        },
        manageActions: function (event) {

            if ($(event.target).hasClass('view-detail')) {
                event.preventDefault();
                EventBus.$emit('loading', true);

                let voucher_id = $(event.target).parents('tr').find('td[data-field="id"] input').val();

                axios.post(this.url_get_voucher_detail, {
                    voucher_id: voucher_id
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
