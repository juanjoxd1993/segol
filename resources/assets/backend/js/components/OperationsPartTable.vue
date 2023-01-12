<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Resultado
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-wrapper">
                    <div class="dropdown dropdown-inline">
                        <a href="#" class="btn btn-success" id="createRecord" v-show="flag_export == 1" @click.prevent="pdfExport()">
                            <i class="fa fa-file-pdf"></i> Exportar PDF
                        </a>
						<a href="#" class="btn btn-success" id="createRecord" v-show="flag_export_guide == 1" @click.prevent="pdfExportGuide()">
                            <i class="fa fa-file-pdf"></i> Exportar PDF Guía
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="kt-portlet__body kt-portlet__body--fit" v-if="flag_export == 1">
            <div class="row">
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Compañía</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.company_name }}</span>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Almacén</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.warehouse_type_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Clase de Movimiento</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.movement_class_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Tipo de Movimiento</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.movement_type_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Nº de Parte</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.movement_number }}</span>
                            </div> 
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Nº de Documento</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.account_document_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Nombre o Razón Social</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.account_name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Guía de Remisión</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.referral_guide }}</span>
                            </div> 
                        </div>
                    </div>
                </div>
               <!-- <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Tipo y Nro de Referencia</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.referral_document }}</span>
                            </div>
                        </div>
                    </div>
                </div>-->
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Nº de SCOP</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.scop_number }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Placa</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.license_plate }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Fecha</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.creation_date }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="kt-widget1">
                        <div class="kt-widget1__item">
                            <div class="kt-widget1__info">
                                <h3 class="kt-widget1__title">Total</h3>
                                <span class="kt-widget1__desc">{{ warehouse_movement.total }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <!--begin: Datatable -->
                    <div class="kt-datatable"></div>
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
        props: {
            url: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                datatable: undefined,
                model: '',
                warehouse_movement: '',
                flag_export: 0,
				flag_export_guide: 0,
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('show_table', function(response) {
                this.model = response;
                this.flag_export = 1;
				this.flag_export_guide = this.model.movement_class_id == 2 ? 1 : 0;

                this.$nextTick(() => {
                    if ( this.datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        this.datatable.setDataSourceParam('model', this.model);
                        this.datatable.load();
                    }

                    this.datatable.on('kt-datatable--on-ajax-done', function() {
                        EventBus.$emit('loading', false);
                    });
                });

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
            pdfExport: function() {
                EventBus.$emit('loading', true);

                axios.post(this.url, {
                    model: this.model,
                    flag_export: true,
                }, {
                    responseType: 'blob',
                }).then(response => {
                    EventBus.$emit('loading', false);
                    // console.log(response.data);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'parte-almacen'+Date.now()+'.pdf');
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            },
			pdfExportGuide: function() {
				EventBus.$emit('loading', true);

				axios.post(this.url, {
					model: this.model,
					flag_export_guide: true,
				}, {
                    responseType: 'blob',
                }).then(response => {
                    EventBus.$emit('loading', false);
                    console.log(response.data);

                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'guia-remision-'+this.warehouse_movement.movement_number+'-'+Date.now()+'.pdf');
                    document.body.appendChild(link);
                    link.click();
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
			},
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
                                },
                                map: function(raw) {
                                    var dataSet = raw;
                                    if (typeof raw.data !== 'undefined') {
                                        dataSet = raw.data;
                                    }

                                    if (typeof raw.warehouse_movement !== 'undefined') {
                                        vm.warehouse_movement = raw.warehouse_movement;
                                    }
                                    
                                    return dataSet;
                                },
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
                    pagination: true,

                    search: {
                        input: $('#generalSearch'),
                    },

                    extensions: {
                        checkbox: {
                            vars: {
                                selectedAllRows: 'selectedAllRows',
                                requestIds: 'requestIds',
                                rowIds: 'meta.rowIds',
                            },
                        },
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
                        // {
                        //     field: 'id',
                        //     title: '#',
                        //     sortable: false,
                        //     width: 30,
                        //     // selector: {class: 'kt-checkbox--solid'},
                        //     textAlign: 'center',
                        // },
                        {
                            field: 'article_code',
                            title: 'Código',
                            width: 60,
                        },
                        {
                            field: 'article_name',
                            title: 'Artículo',
                            width: 200,
                        },
                        {
                            field: 'converted_amount',
                            title: 'Cantidad',
                            width: 100,
                            textAlign: 'right'
                        },
                        {
                            field: 'price',
                            title: 'Precio',
                            width: 100,
                            textAlign: 'right'
                        },
                        {
                            field: 'total',
                            title: 'Monto',
                            width: 100,
                            textAlign: 'right'
                        },
                    ]
                });
            },
        }
    };
</script>