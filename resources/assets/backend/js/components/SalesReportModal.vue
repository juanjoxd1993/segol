<template>
    <!--begin::Modal-->
    <div class="modal fade" id="sales-report-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="sendForm()">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Detalle</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-12">
									<!--begin: Datatable -->
									<div class="kt-datatable" id="kt-datatable-sales-report-detail"></div>
									<!--end: Datatable -->
								</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
                <!--end::Form-->
            </div>
        </div>
    </div>
    <!--end::Modal-->
</template>

<script>
    import EventBus from '../event-bus';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
				model: {},
				sales_report_datatable: undefined,
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('sales_report_modal', function(id, model) {
                let vm = this;
                this.model = model;
				this.model.id = id;
				$('#sales-report-modal').modal('show');
				
				Vue.nextTick(function() {
					if ( vm.sales_report_datatable == undefined ) {
						vm.fillTableX();
					} else {
						vm.sales_report_datatable.setDataSourceParam('model', vm.model);
						vm.sales_report_datatable.load();
					}

					// vm.sales_report_datatable.on('kt-datatable--on-ajax-done', function() {
					// 	EventBus.$emit('loading', false);
					// });
				});
            }.bind(this));
        },
        mounted() {
            $('#sales-report-modal').on('hide.bs.modal', function(e) {
                this.model = {};
				this.sales_report_datatable.destroy();
            }.bind(this));
        },
        methods: {
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.sales_report_datatable = $('#kt-datatable-sales-report-detail').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model
                                },
								map: function(raw) {
									var dataSet = raw;
									if (typeof raw.data !== 'undefined') {
										dataSet = raw.data;
									}

									dataSet.map(element => {
										element.total = accounting.formatMoney(element.total, { symbol: 'S/', format: '%s %v' }, 2);
									});

									return dataSet;
								}
                            },
                        },
                        pageSize: 10,
                        serverPaging: true,
                        serverFiltering: true,
                        serverSorting: true,
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

					// detail: {
					// 	title: 'Ver detalles',
					// 	content: subTableInit,
					// },

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
                            field: "business_name",
                            title: "Cliente",
                            width: 120,
                            textAlign: "left",
                        },
                        {
                            field: "warehouse_document_type_name",
                            title: "Tipo de Doc.",
                            width: 80,
                            sortable: true,
                            textAlign: "left",
                        },
                        {
                            field: "referral_serie_number",
                            title: "# Serie",
                            width: 60,
                            sortable: true,
                            textAlign: "left",
                        },
                        {
                            field: "referral_voucher_number",
                            title: "# Doc",
                            width: 60,
                            sortable: true,
                            textAlign: "left",
                        },
                        {
                            field: "sale_date",
                            title: "Fecha",
                            width: 80,
                            sortable: true,
                            textAlign: "left",
                        },
						{
                            field: "total",
                            title: "Total",
                            width: 80,
                            sortable: true,
                            textAlign: "right",
                        },
                    ]
                });
            }
        }
    };
</script>