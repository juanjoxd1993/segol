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
                        <!-- <a href="#" @click.prevent="exportExcel()" class="btn btn-brand btn-icon-sm">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </a> -->
                    </div>
                </div>
            </div>
        </div>
        <div class="kt-portlet__body kt-portlet__body--fit">
            <!--begin: Datatable -->
            <div class="kt-datatable" id="kt-datatable-sales-report"></div>
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
                this.model = response;
                if ( this.datatable == undefined ) {
                    this.fillTableX();
                } else {
                    this.datatable.setDataSourceParam('model', this.model);
                    this.datatable.load();
                }

                this.datatable.on('kt-datatable--on-ajax-done', function() {
                    EventBus.$emit('loading', false);
                });
            }.bind(this));
        },
        methods: {
            fillTableX: function() {
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
                                    model: vm.model
                                },
								map: function(raw) {
									var dataSet = raw;
									if (typeof raw.data !== 'undefined') {
										dataSet = raw.data;
									}
									// dataSet.map(element => {
									// 	element.current_advance = accounting.toFixed(element.current_advance, 2);
									// 	element.current_projection = accounting.toFixed(element.current_projection, 2);
									// 	element.previous_month = accounting.toFixed(element.previous_month, 2);
									// 	element.previous_year = accounting.toFixed(element.previous_year, 2);
									// 	element.budget = accounting.toFixed(element.budget, 2);
									// 	element.v_previous_month = accounting.formatMoney(element.v_previous_month, { symbol: '%', format: '%v %s' }, 2);
									// 	element.v_previous_year = accounting.formatMoney(element.v_previous_year, { symbol: '%', format: '%v %s' }, 2);
									// 	element.v_budget = accounting.formatMoney(element.v_budget, { symbol: '%', format: '%v %s' }, 2);
									// 	element.money_current_advance = accounting.formatMoney(element.money_current_advance, { symbol: 'S/', format: '%s %v' }, 2);
									// 	element.money_current_projection = accounting.formatMoney(element.money_current_projection, { symbol: 'S/', format: '%s %v' }, 2);
									// 	element.money_previous_month = accounting.formatMoney(element.money_previous_month, { symbol: 'S/', format: '%s %v' }, 2);
									// 	element.money_previous_year = accounting.formatMoney(element.money_previous_year, { symbol: 'S/', format: '%s %v' }, 2);
									// 	element.money_budget = accounting.formatMoney(element.money_budget, { symbol: 'S/', format: '%s %v' }, 2);
									// 	element.money_v_previous_month = accounting.formatMoney(element.money_v_previous_month, { symbol: '%', format: '%v %s' }, 2);
									// 	element.money_v_previous_year = accounting.formatMoney(element.money_v_previous_year, { symbol: '%', format: '%v %s' }, 2);
									// 	element.money_v_budget = accounting.formatMoney(element.money_v_budget, { symbol: '%', format: '%v %s' }, 2);

									// 	if ( element.families ) {
									// 		element.families.map(subelement => {
									// 			subelement.current_advance = accounting.toFixed(subelement.current_advance, 2);
									// 			subelement.current_projection = accounting.toFixed(subelement.current_projection, 2);
									// 			subelement.previous_month = accounting.toFixed(subelement.previous_month, 2);
									// 			subelement.previous_year = accounting.toFixed(subelement.previous_year, 2);
									// 			subelement.budget = accounting.toFixed(subelement.budget, 2);
									// 			subelement.v_previous_month = accounting.formatMoney(subelement.v_previous_month, { symbol: '%', format: '%v %s' }, 2);
									// 			subelement.v_previous_year = accounting.formatMoney(subelement.v_previous_year, { symbol: '%', format: '%v %s' }, 2);
									// 			subelement.v_budget = accounting.formatMoney(subelement.v_budget, { symbol: '%', format: '%v %s' }, 2);
									// 			subelement.money_current_advance = accounting.formatMoney(subelement.money_current_advance, { symbol: 'S/', format: '%s %v' }, 2);
									// 			subelement.money_current_projection = accounting.formatMoney(subelement.money_current_projection, { symbol: 'S/', format: '%s %v' }, 2);
									// 			subelement.money_previous_month = accounting.formatMoney(subelement.money_previous_month, { symbol: 'S/', format: '%s %v' }, 2);
									// 			subelement.money_previous_year = accounting.formatMoney(subelement.money_previous_year, { symbol: 'S/', format: '%s %v' }, 2);
									// 			subelement.money_budget = accounting.formatMoney(subelement.money_budget, { symbol: 'S/', format: '%s %v' }, 2);
									// 			subelement.money_v_previous_month = accounting.formatMoney(subelement.money_v_previous_month, { symbol: '%', format: '%v %s' }, 2);
									// 			subelement.money_v_previous_year = accounting.formatMoney(subelement.money_v_previous_year, { symbol: '%', format: '%v %s' }, 2);
									// 			subelement.money_v_budget = accounting.formatMoney(subelement.money_v_budget, { symbol: '%', format: '%v %s' }, 2);
									// 		});
									// 	}
									// });
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
					// 	title: 'Load sub table',
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
                            field: "item_id",
                            title: "",
                            sortable: false,
                            width: 20,
                            // selector: {class: 'kt-checkbox--solid'},
                            textAlign: "center",
                        },
                        {
                            field: "item_name",
                            title: "Rubro",
                            width: 100,
                            textAlign: "left",
                        },
                        {
                            field: "current_advance",
                            title: "Avance",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "current_projection",
                            title: "Proyec.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "previous_month",
                            title: "M.A.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "previous_year",
                            title: "A.A.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "budget",
                            title: "Ppto",
                            width: 120,
                            textAlign: "right",
                        },
                        {
                            field: "v_previous_month",
                            title: "V% M.A.",
                            width: 120,
							textAlign: "right",
                        },
                        {
                            field: "v_previous_year",
                            title: "V% A.A.",
                            width: 120,
                            textAlign: "right",
                        },
						{
                            field: "v_budget",
                            title: "V% Ppto",
                            width: 120,
                            textAlign: "right",
                        },
						{
                            field: "money_current_advance",
                            title: "Avance",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "money_current_projection",
                            title: "Proyec.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "money_previous_month",
                            title: "M.A.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "money_previous_year",
                            title: "A.A.",
                            width: 120,
                            sortable: true,
                            textAlign: "right",
                        },
                        {
                            field: "money_budget",
                            title: "Ppto",
                            width: 120,
                            textAlign: "right",
                        },
                        {
                            field: "money_v_previous_month",
                            title: "V% M.A.",
                            width: 120,
							textAlign: "right",
                        },
                        {
                            field: "money_v_previous_year",
                            title: "V% A.A.",
                            width: 120,
                            textAlign: "right",
                        },
						{
                            field: "money_v_budget",
                            title: "V% Ppto",
                            width: 120,
                            textAlign: "right",
                        },
                    ]
                });
            },
            exportExcel: function() {
                axios.post(this.url_export, {
                    model: this.model
                }, {
                    responseType: 'blob',
                }).then(response => {
                    console.log(response);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-de-ventas.xls'); //or any other extension
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
        }
    };
</script>