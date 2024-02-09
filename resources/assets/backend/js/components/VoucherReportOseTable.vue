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
                        <a href="#" @click.prevent="sendVouchers('pdf')" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-file-pdf"></i> Crear PDF
                        </a>
                        <a href="#" @click.prevent="sendVouchers('download_pdf')" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-file-pdf"></i> Descargar PDF
                        </a>
                        <a href="#" @click.prevent="sendVouchers('mail')" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-envelope"></i> Enviar Email
                        </a>
                        <a href="#" @click.prevent="sendVouchers('save')" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-database"></i> Guardar
                        </a>
                        <a href="#" @click.prevent="sendVouchers('baja')" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-window-close"></i> Baja
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- <div class="kt-portlet__body"> -->
            <!--begin: Search Form -->
            <!-- <div class="kt-form kt-margin-t-20 kt-margin-b-10">
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
            </div> -->
            <!--end: Search Form -->
        <!-- </div> -->
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
                model: {},
                ids: []
            }
        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.show_table = true;
                this.model = response.data;
                // this.fillTableX();

                if ( this.datatable != undefined ) {
                    this.elements = [];
                    // this.datatable.destroy();
                    // this.fillTableX();
					this.datatable.load();
                } else {
                    this.fillTableX();
                }

                // axios.post(this.url_get_vouchers_for_table, {
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
                let url_get_vouchers_for_table = this.url_get_vouchers_for_table;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                console.log(url_get_vouchers_for_table, token);

                let vm = this;
                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: url_get_vouchers_for_table,
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
                        // height: 500,
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
                        {
                            field: 'voucher_id',
                            title: '#',
                            sortable: false,
                            width: 30,
                            selector: {class: 'kt-checkbox--solid'},
                            textAlign: 'center',
                        },
                        {
                            field: 'options',
                            title: '',
                            sortable: false,
                            width: 40,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'center',
                            template: function() {
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
                            field: "document_voucher_type",
                            title: "Tipo",
                            width: 30,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "document_serie_number",
                            title: "Serie",
                            width: 50,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "document_voucher_number",
                            title: "Nº",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "document_date_of_issue",
                            title: "Fecha de Emisión",
                            width: 90,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "document_payment_name",
                            title: "Método de Pago",
                            width: 80,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: 'document_order_series',
                            title: 'Serie de Pedido',
                            width: 40,
                            textAlign: 'center',
                        },
                        {
                            field: 'document_order_number',
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
                            field: "client_document_name",
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
                            field: "document_currency_symbol",
                            title: "Moneda",
                            width: 60,
                            sortable: true,
                            textAlign: 'center',
                        },
                        {
                            field: "document_total",
                            title: "Valor Total",
                            width: 80,
                            sortable: true,
                            textAlign: 'right',
                        },
                        {
                            field: "document_perception",
                            title: "Percepción",
                            width: 80,
                            sortable: true,
                            textAlign: 'right',
                        },
                        {
                            field: 'company_id',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_currency_id',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_voucher_reference',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_subtotal',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_igv',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_igv_percentage',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_expiration_date',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_hour_of_issue',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_perception_percentage',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'client_address',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'client_email',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_reference_number',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_reference_reason_name',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_reference_reason_type',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_reference_serie',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'document_payment_id',
                            title: '',
                            width: 0,
                            textAlign: 'center',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                    ]
                });

                $('.kt-datatable').on('change','input[type="checkbox"]', function($event) {
                    vm.elements = [];
                    vm.ids = vm.datatable.checkbox().getSelectedId();
                }.bind(this));
            },
            sendVouchers: function(task) {
                let vm = this;

                $.each(vm.ids, function(key, value) {
                    let args = {};
                    args['voucher_id'] = value;
                    args['client_address'] = vm.datatable.getRecord(value).getColumn('client_address').getValue();
                    args['client_code'] = vm.datatable.getRecord(value).getColumn('client_code').getValue();
                    args['client_document_name'] = vm.datatable.getRecord(value).getColumn('client_document_name').getValue();
                    args['client_document_number'] = vm.datatable.getRecord(value).getColumn('client_document_number').getValue();
                    args['client_email'] = vm.datatable.getRecord(value).getColumn('client_email').getValue();
                    args['client_name'] = vm.datatable.getRecord(value).getColumn('client_name').getValue();
                    args['company_id'] = vm.datatable.getRecord(value).getColumn('company_id').getValue();
                    args['document_currency_id'] = vm.datatable.getRecord(value).getColumn('document_currency_id').getValue();
                    args['document_date_of_issue'] = vm.datatable.getRecord(value).getColumn('document_date_of_issue').getValue();
                    args['document_expiration_date'] = vm.datatable.getRecord(value).getColumn('document_expiration_date').getValue();
                    args['document_hour_of_issue'] = vm.datatable.getRecord(value).getColumn('document_hour_of_issue').getValue();
                    args['document_igv'] = vm.datatable.getRecord(value).getColumn('document_igv').getValue();
                    args['document_igv_percentage'] = vm.datatable.getRecord(value).getColumn('document_igv_percentage').getValue();
                    args['document_order_number'] = vm.datatable.getRecord(value).getColumn('document_order_number').getValue();
                    args['document_order_series'] = vm.datatable.getRecord(value).getColumn('document_order_series').getValue();
                    args['document_payment_id'] = vm.datatable.getRecord(value).getColumn('document_payment_id').getValue();
                    args['document_payment_name'] = vm.datatable.getRecord(value).getColumn('document_payment_name').getValue();
                    args['document_perception'] = vm.datatable.getRecord(value).getColumn('document_perception').getValue();
                    args['document_perception_percentage'] = vm.datatable.getRecord(value).getColumn('document_perception_percentage').getValue();
                    args['document_reference_number'] = vm.datatable.getRecord(value).getColumn('document_reference_number').getValue();
                    args['document_reference_reason_name'] = vm.datatable.getRecord(value).getColumn('document_reference_reason_name').getValue();
                    args['document_reference_reason_type'] = vm.datatable.getRecord(value).getColumn('document_reference_reason_type').getValue();
                    args['document_reference_serie'] = vm.datatable.getRecord(value).getColumn('document_reference_serie').getValue();
                    args['document_serie_number'] = vm.datatable.getRecord(value).getColumn('document_serie_number').getValue();
                    args['document_subtotal'] = vm.datatable.getRecord(value).getColumn('document_subtotal').getValue();
                    args['document_total'] = vm.datatable.getRecord(value).getColumn('document_total').getValue();
                    args['document_voucher_reference'] = vm.datatable.getRecord(value).getColumn('document_voucher_reference').getValue();
                    args['document_voucher_number'] = vm.datatable.getRecord(value).getColumn('document_voucher_number').getValue();

                    vm.elements.push(args);
                });

                this.elements.sort(function(a, b) {
                    let aNumber = parseInt(a.id);
                    let bNumber = parseInt(b.id);
                    return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                });

                // console.log(this.elements);

                let data = {
                    elements: this.elements,
                    task: task,
                };
                EventBus.$emit('loading', true);
                axios.post(this.url_send_voucher, data).then(response => {
                    EventBus.$emit('loading', false);
                    // console.log(response.data);

                    let swalText = new Array();
                    if ( data.task == 'pdf' ) {
                        $.each(response.data, function(key, value) {
                            const link = document.createElement('a');
                            link.href = value.file;
                            link.setAttribute('download', value.name); //or any other extension
                            document.body.appendChild(link);
                            link.click();

                            swalText.push("<div>"+value.document_serie_number+"-"+value.document_voucher_number+" | "+value.response_code+": "+value.response_text+"</div>");
                        });
                    } else {
                        $.each(response.data, function(key, value) {
                            swalText.push("<div>"+value.document_serie_number+"-"+value.document_voucher_number+" | "+value.response_code+": "+value.response_text+"</div>");
                        });
                    }
                    
                    swalText = swalText.toString();

                    Swal.fire({
                        title: 'Resultado de envío',
                        html: swalText,
                        type: "success",
                        confirmButtonText: 'Ok',
                    }).then((result) => {
                        this.elements = [];
                        // this.datatable.destroy();
                        // this.fillTableX();
						this.datatable.load();
                    });
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error);
                    console.log(error.response);

                    Swal.fire({
                        title: 'Error',
                        text: error.response.data.message,
                        type: "error",
                        confirmButtonText: 'Ok',
                    });
                });
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('view-detail') ) {
                    event.preventDefault();
                    let voucher_id = $(event.target).parents('tr').find('td[data-field="voucher_id"] input').val();

                    axios.post(this.url_get_voucher_detail, {
                        voucher_id: voucher_id
                    }).then(response => {
                        console.log(response);
                        EventBus.$emit('show_voucher_detail_modal', response.data);
                        // console.log(response.data);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
            getInfoByIndex: function(target,c_dt,index) {
                var c_row = $(target).parents('tr');
                var i = c_row.index();
                var cell = c_row.find('.m-datatable__cell .m-checkbox [type="checkbox"]');
                var c_page = c_dt.getCurrentPage();
                var p_size = c_dt.getPageSize();
                var ind = ( (c_page - 1) * p_size ) + i;
                // c_dt.setActive(cell);
                var id = c_dt.getRecord(ind).getColumn(index).getValue();
                // c_dt.setInactive(cell);

                return id;
            },
        }
    };
</script>