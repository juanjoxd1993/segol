<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
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
                        <a href="#" @click.prevent="exportExcel()" class="btn btn-brand btn-icon-sm disabled" id="excel_export">
                            <i class="fa fa-file-excel"></i> Exportar Excel
                        </a>
                    </div>
                </div>
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
                        <input type="text" class="form-control" placeholder="Buscar..." id="generalSearch">
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
            url: {
                type: String,
                default: ''
            },
			url_detail: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                datatable: undefined,
                model: {},
                export: '',
                valued: 1,
				show_table: false
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(data) {
                this.model = data;
                if ( this.datatable == undefined ) {
                    this.fillTableX();
                } else {
                    this.datatable.setDataSourceParam('model', this.model);
                    this.datatable.load();
                }

                this.datatable.on('kt-datatable--on-ajax-done', function() {
                    EventBus.$emit('loading', false);
                });

                document.getElementById('excel_export').classList.remove('disabled');
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.datatable != undefined ) {
                    this.datatable.load();
                }
            }.bind(this));
        },
        watch: {
            
        },
        computed: {

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
                                url: vm.url,
                                params: {
                                    _token: token,
                                    model: vm.model,
                                    export: vm.export,
                                    valued: vm.valued,
                                }
                            },
                        },
                        pageSize: 0,
                        serverPaging: false,
                        serverFiltering: false,
                        serverSorting: false,
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
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
                        {
							field: 'company_short_name',
							title: 'Compañía',
							width: 80,
						},
						{
							field: 'movement_class',
							title: 'Clase de Mov.',
							width: 80,
						},
                        {
							field: 'movement_type',
							title: 'Tipo de Mov.',
							width: 120,
						},
                        {
                            field: 'movement_number',
                            title: 'Parte',
                            width: 60,
                        },
                        {
                            field: 'date',
                            title: 'Fecha',
                            width: 80,
                        },
                        {
                            field: 'article_code',
                            title: 'Artículo',
                            width: 80,
                        },
                        {
                            field: 'article_name',
                            title: 'Descripción',
                            width: 150,
                        },
                        {
                            field: 'quantity',
                            title: 'Cantidad',
                            width: 80,
                            textAlign: 'right'
                        },
                        {
                            field: 'price',
                            title: 'Precio Unit.',
                            width: 120,
                            textAlign: 'right'
                        },
                        {
                            field: 'sale_value',
                            title: 'Monto',
                            width: 120,
                            textAlign: 'right'
                        },
                        {
                            field: 'movement_stock_type',
                            title: 'Estado Stock',
                            width: 120,
                        },
                        {
                            field: 'account_document_type',
                            title: 'Tipo de Doc.',
                            width: 80,
                        },
                        {
                            field: 'account_document_number',
                            title: 'Documento',
                            width: 100,
                        },
                        {
                            field: 'account_name',
                            title: 'Nombre o Razón Social',
                            width: 150,
                        },
                        {
                            field: 'referral_guide',
                            title: 'Guía Remisión',
                            width: 80,
                        },
                        {
                            field: 'reference_document_type',
                            title: 'Tipo de Ref.',
                            width: 80,
                        },
                        {
                            field: 'reference_document',
                            title: 'Referencia',
                            width: 80,
                        },
                        {
                            field: 'scop_number',
                            title: 'SCOP',
                            width: 100,
                            textAlign: 'right'
                        },
                        {
                            field: 'license_plate',
                            title: 'Placa',
                            width: 80,
                        },
                        {
                            field: 'id',
                            title: 'ID',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
						{
                            field: 'warehouse_movement_id',
                            title: 'ID Movimiento de Almacén',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
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
                            template: function(row) {
                                let actions = '<div class="actions">';
									actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
										actions += '<i class="la la-edit"></i>';
									actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        },
                    ]
                });

                this.datatable.columns('id').visible(false);
				this.datatable.columns('warehouse_movement_id').visible(false);
            },
			manageActions: function(event) {
                if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="warehouse_movement_id"] span').html();
                    EventBus.$emit('loading', true);

                    axios.post(this.url_detail, {
                        id: id,
                    }).then(response => {
                        EventBus.$emit('edit_modal', response.data);
                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
            exportExcel: function() {
                EventBus.$emit('loading', true);
                this.export = 1;

                axios.post(this.url, {
                    model: this.model,
                    export: this.export,
                    valued: this.valued,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    console.log(response);
                    EventBus.$emit('loading', false);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'reporte-de-volumen-ventas-'+Date.now()+'.xls'); //or any other extension
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