
<template>
    <div>
        <!--begin::Modal-->
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <!--begin::Form-->
                    <div class="kt-form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Editar: {{ article.article_name }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="kt-portlet__body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Retorno Lleno:</label>
                                            <input type="text" class="form-control" v-model="article.retorno">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Cambios:</label>
                                            <input type="text" class="form-control" v-model="article.cambios">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Préstamo:</label>
                                            <input type="text" class="form-control" v-model="article.prestamo">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Cesión de Uso:</label>
                                            <input type="text" class="form-control" v-model="article.cesion">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Vacíos:</label>
                                            <input type="text" class="form-control" v-model="article.vacios" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Liquidar:</label>
                                            <input type="text" class="form-control" v-model="article.liquidar" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="update">
                                Actualizar
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
            </div>
        </div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Artículos de Preventa
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-outline-brand btn-bold btn-sm" @click.prevent="saveLiquidation()">
                                <i class="fa fa-check"></i> Cerrar Retorno
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit">
                <!--begin: Datatable -->
                <div class="kt-datatable-articles"></div>
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
        url_store: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            guides_return_datatable: undefined,
            show_table: false,
            table: null,
            data: [],
            article: {
                index: null,
                article_id: null,
                code: '',
                name: '',
                retorno: '',
                cambios: '',
                prestamo: '',
                cesion: '',
                vacios: '',
                liquidar: '',
            }
        }
    },
    created() {
        let context = this;
        $(document).on('click', '.edit', function () {
            let index = $(this).parents('tr').index();

            let data = context.data[index];

            context.article = data;
            context.article.index = index;


            $("#modal").modal('show')
        })
    },
    mounted() {
        EventBus.$on('show_table', function (response) {
            this.show_table = true;
            this.$store.commit('addModel', response);

            axios.post(this.url, {
                model: this.$store.state.model,
            }).then(response => {
                // console.log(response.data);
                this.$store.commit('addArticles', response.data);

                if (this.guides_return_datatable == undefined) {
                    this.fillTableX(response.data);
                } else {
                    this.guides_return_datatable.originalDataSet = this.articlesState;
                    this.guides_return_datatable.load();
                }

                EventBus.$emit('loading', false);
            }).catch(error => {
                console.log(error);
                console.log(error.response);
            });
        }.bind(this));

        EventBus.$on('refresh_table_guides_return', function () {
            if (this.guides_return_datatable != undefined) {
                // console.log(this.articlesState);
                this.guides_return_datatable.originalDataSet = this.articlesState;
                this.guides_return_datatable.load();
            }
        }.bind(this));
    },
    watch: {

    },
    computed: {
        articlesState: function () {
            let articles = this.$store.state.articles;
            articles.map(element => {
                element.presale_converted_amount = accounting.toFixed(element.presale_converted_amount, 4);

            });

            return articles;
        }
    },
    methods: {
        saveLiquidation: function () {

            EventBus.$emit('loading', true);

            axios.post(this.url_store, {
                articles: this.data
            }).then(response => {

                EventBus.$emit('loading', false);


                Swal.fire({
                    title: '¡Ok!',
                    text: 'Se creo el registro correctamente.',
                    type: "success",
                    heightAuto: false,
                });
                
            }).catch(error => {
                EventBus.$emit('loading', false);
                console.log(error);
                console.log(error.response);

                Swal.fire({
                    title: '¡Error!',
                    text: error,
                    type: "error",
                    heightAuto: false,
                });
            });

        },
        update() {
            this.table.destroy();

            this.article.vacios = this.article.presale_converted_amount - this.article.retorno - this.article.cambios - this.article.prestamo - this.article.cesion
            this.article.liquidar = this.article.presale_converted_amount - this.article.retorno - this.article.cambios;

            this.data[this.article.index] = this.article;

            let index = this.data.findIndex(el => el.parent == this.article.article_id)

            //Cambiar en su conversión
            this.data[index].retorno = this.article.retorno;
            this.data[index].cambios = this.article.cambios;
            this.data[index].prestamo = this.article.prestamo;
            this.data[index].cesion = this.article.cesion;
            this.data[index].vacios = this.article.vacios;
            this.data[index].liquidar = this.article.liquidar;

            this.fillTableX(this.data);

            $("#modal").modal('hide')

        },
        fillTableX(data) {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;

            this.table = $('.kt-datatable-articles').KTDatatable({
                // datasource definition
                data: {
                    type: 'local',
                    source: data,
                    pageSize: 10,
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
                        field: 'article_code',
                        title: 'Código',
                        width: 60,
                        textAlign: 'center',
                    },
                    {
                        field: 'article_name',
                        title: 'Artículo',
                        width: 300,
                    },
                    {
                        field: 'presale_converted_amount',
                        title: 'Pre-Venta',
                        width: 120,
                        textAlign: 'right',
                    },
                    {
                        field: 'retorno',
                        title: 'Retorno Lleno',
                        width: 90,
                        textAlign: 'right',
                    },
                    {
                        field: 'cambios',
                        title: 'Cambios',
                        width: 90,
                        textAlign: 'right',
                    },
                    {
                        field: 'prestamo',
                        title: 'Préstamo',
                        width: 90,
                        textAlign: 'right',
                    },
                    {
                        field: 'cesion',
                        title: 'Cesión de Uso',
                        width: 90,
                        textAlign: 'right',
                    },
                    {
                        field: 'vacios',
                        title: 'Vacíos',
                        width: 90,
                        textAlign: 'right',
                    },
                    {
                        field: 'liquidar',
                        title: 'Liquidar',
                        width: 90,
                        textAlign: 'right',
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
                        field: 'options',
                        title: 'Opciones',
                        sortable: false,
                        width: 60,
                        overflow: 'visible',
                        autoHide: false,
                        textAlign: 'right',
                        class: 'td-sticky',
                        template: function (row) {

                            let actions = '<div class="actions">';
                            actions += '<a style="cursor:pointer" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                            actions += '<i class="la la-edit"></i>';
                            actions += '</a>';
                            actions += '</div>';

                            return actions;

                        },
                    },

                    // {
                    //     field: 'options',
                    //     title: 'Opciones',
                    //     sortable: false,
                    //     width: 80,
                    //     overflow: 'visible',
                    //     autoHide: false,
                    //     textAlign: 'right',
                    //     template: function(row) {
                    //         let actions = '<div class="actions">';
                    //             actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                    //                 actions += '<i class="la la-edit"></i>';
                    //             actions += '</a>';
                    //             actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                    //                 actions += '<i class="la la-trash"></i>';
                    //             actions += '</a>';
                    //         actions += '</div>';

                    //         return actions;
                    //     },
                    // },
                ]
            });

            this.table.columns('id').visible(false);

            this.data = this.table.rows().data().KTDatatable.dataSet;
        },
    }
};
</script>