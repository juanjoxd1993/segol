<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="show_table == 1">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Artículos
                </h3>
            </div>
            <div class="kt-portlet__head-toolbar">
                <div class="kt-portlet__head-actions">
                    <a href="#" @click.prevent="openModal()" class="btn btn-success">
                        <i class="fa fa-plus"></i> Agregar artículo
                    </a>
                </div>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-12" @click="manageActions">
                        <!--begin: Datatable -->
                        <div class="kt-datatable"></div>
                        <!--end: Datatable -->
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Crear</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <!--end::Form-->
    </div>
    <!--end::Portlet-->
</template>

<script>
import EventBus from '../event-bus';

export default {
    props: {
        igv: {
            type: Object,
            default: ''
        },
        url: {
            type: String,
            default: ''
        },
        url_get_article: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            show_table: 0,
            datatable: undefined,
            article_list: [],
            model: '',
            item_number: 0
        }
    },
    created() {
        EventBus.$on('show_table', function (data) {
            this.show_table = 1;
            this.model = data.model;

            this.$nextTick(function () {
                this.fillTableX();
            });
        }.bind(this));

        EventBus.$on('sendForm', function (model) {
            EventBus.$emit('loading', true);
            this.addArticle(model);
        }.bind(this));

        EventBus.$on('reset_stock_register', function () {
            this.show_table = 0;
            this.datatable = undefined;
            this.article_list = [];
            this.model = '';
            this.articles = [];
            this.item_number = 0;
        }.bind(this));
    },
    mounted() {

    },
    watch: {

    },
    computed: {

    },
    methods: {
        openModal: function () {
            EventBus.$emit('movement_register_modal');
        },
        addArticle: function (model) {


            let newModel = JSON.parse(JSON.stringify(model));

            const obj = this.article_list.find(article => article.id === model.id);

            if (obj) {
                obj.quantity = Number(obj.quantity) + Number(newModel.quantity);
            } else {
                this.item_number++;
                model.item_number = this.item_number;
                newModel.item_number = this.item_number;
                this.article_list.push(newModel);
            }

            this.datatable.destroy();
            this.fillTableX();
            EventBus.$emit('movement_register_modal_hide');
            EventBus.$emit('loading', false);
        },
        fillTableX: function () {
            let vm = this;
            this.datatable = $('.kt-datatable').KTDatatable({
                // datasource definition
                data: {
                    type: 'local',
                    source: vm.article_list,
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
                        field: 'item_number',
                        title: '#',
                        width: 20,
                        textAlign: 'center',
                    },
                    {
                        field: "id",
                        title: "Código",
                        width: 50,
                        textAlign: 'center',
                    },
                    {
                        field: "code",
                        title: "Code",
                        width: 50,
                        textAlign: 'center',
                    },
                    {
                        field: "name",
                        title: "Descripción",
                        width: 120,
                    },
                    {
                        field: "convertion",
                        title: "Conversión",
                        width: 70,
                        textAlign: 'center',
                    },
                    {
                        field: 'quantity',
                        title: 'Cantidad',
                        width: 80,
                        textAlign: 'right',
                    },
                    {
                        field: 'options',
                        title: 'Opciones',
                        sortable: false,
                        width: 120,
                        overflow: 'visible',
                        autoHide: false,
                        textAlign: 'center',
                        template: function () {
                            return '\
                                <div class="actions">\
                                    <a href="#" class="delete btn btn-danger btn-sm btn-icon btn-icon-md" title="Eliminar">\
                                        <i class="la la-trash"></i>\
                                    </a>\
                                </div>\
                            ';
                        },
                    },
                ]
            });
        },
        manageActions: function (event) {
            if ($(event.target).hasClass('delete')) {
                event.preventDefault();
                let item_number = $(event.target).parents('tr').find('td[data-field="item_number"] span').html();
                let index = this.article_list.findIndex((element) => element.item_number == item_number);

                Swal.fire({
                    title: '¡Cuidado!',
                    text: '¿Seguro que desea eliminar el artículo?',
                    type: "warning",
                    heightAuto: false,
                    showCancelButton: true,
                    confirmButtonText: 'Sí',
                    cancelButtonText: 'No'
                }).then(result => {
                    if (result.value) {
                        Swal.fire({
                            title: 'Ok!',
                            text: 'Se ha eliminado el artículo',
                            type: "success",
                            heightAuto: false,
                        });

                        this.article_list.splice(index, 1);
                        let new_item_number = 0;
                        this.article_list.map(function (item, index) {
                            item.item_number = ++new_item_number;
                        });
                        this.item_number--;
                        this.datatable.destroy();
                        this.fillTableX();

                    }
                });
            }
        },
        formController: function (url, event) {
            var target = $(event.target);
            var url = url;
            var fd = new FormData(event.target);

            if (this.article_list != '' && this.article_list != []) {
                EventBus.$emit('loading', true);
                axios.post(this.url, {
                    model: this.model,
                    article_list: this.article_list,
                }).then(response => {
                    EventBus.$emit('loading', false);
                    console.log(response);

                    this.$parent.alertMsg(response.data);

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
            } else {
                Swal.fire({
                    title: '¡Error!',
                    text: 'Debes agregar al menos 1 Artículo.',
                    type: "error",
                    heightAuto: false,
                });
            }
        },
    }
};
</script>