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
                            <h5 class="modal-title" id="exampleModalLabel">Detalle de Movimientos: #{{ guide.referral_guide_series }}-{{ guide.referral_guide_number }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="kt-portlet__body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="kt-portlet__header">
                                            <div class="kt-form__actions">
                                                <div class="row">
                                                    <div class="col-lg-6">
                                                        <button type="submit" class="btn btn-outline-brand btn-bold btn-sm" @click="addArticleFunction()">
                                                            <i class="fa fa-plus"></i>
                                                            Agregar Articulo
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                                        <table class="table table-vertical-middle">
                                            <thead>
                                                <tr>
                                                    <th>Artículo</th>
                                                    <th style="text-align:right;width:110px;">Pre-Venta</th>
                                                    <th style="text-align:right;width:110px;" v-if="account_type_id == 1">Prestamo</th>
                                                    <th style="text-align:right;width:110px;" v-if="account_type_id == 1">Cesión</th>
                                                    <th style="text-align:right;width:80px;"></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(item, index) in articles" v-bind:key="index">
                                                    <td>{{ item.article_name }}</td>
                                                    <td style="text-align:right;width:110px;">
                                                        <input type="text" class="form-control" name="presale" id="presale" v-model="item.presale" @focus="$parent.clearErrorMsg($event)">
                                                    </td>
                                                    <td style="text-align:right;width:110px;" v-if="account_type_id == 1">
                                                        <input type="text" class="form-control" name="prestamo" id="prestamo" v-model="item.prestamo" @focus="$parent.clearErrorMsg($event)" v-if="item.group_id != 7">
                                                    </td>
                                                    <td style="text-align:right;width:110px;" v-if="account_type_id == 1">
                                                        <input type="text" class="form-control" name="cesion" id="cesion" v-model="item.cesion" @focus="$parent.clearErrorMsg($event)" v-if="item.group_id != 7">
                                                    </td>
                                                    <td style="text-align:right;width:80px;">
                                                        <a href="#" class="btn-sm btn btn-label-danger btn-bold" @click.prevent="removeArticle(index)">
                                                            <i class="la la-trash-o pr-0"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="updateArticles()">
                                Actualizar
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
            </div>
        </div>
        <!--end::Modal-->
        <!--begin::Modal Article-->
        <div class="modal fade" id="modal-article" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <!--begin::Form-->
                    <form class="kt-form" @submit.prevent="sendForm()">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Agregar artículo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="kt-portlet__body">
                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label class="form-control-label">Artículo:</label>
                                            <select class="form-control kt-select2" name="article-select" id="article-select" v-model="addArticle.article_id" @focus="$parent.clearErrorMsg($event)">
                                                <option value="">Seleccionar</option>
                                            </select>
                                            <div id="article-error" class="error invalid-feedback"></div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Cantidad:</label>
                                            <input type="tel" class="form-control" name="quantity" id="quantity" placeholder="0" v-model="addArticle.quantity" @focus="$parent.clearErrorMsg($event)">
                                            <div id="quantity-error" class="error invalid-feedback"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success" id="add_article_2">
                                <i class="fa fa-plus"></i> Agregar artículo
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </form>
                    <!--end::Form-->
                </div>
            </div>
        </div>
        <!--end::Modal Article-->
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
                            <a href="#" class="btn btn-success btn-bold btn-sm" @click.prevent="validate()">
                                <i class="fa fa-check"></i> Validar
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
    import Swal from 'sweetalert2';
    import EventBus from '../event-bus';

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
            url_list: {
                type: String,
                default: ''
            },
            url_remove_article: {
                type: String,
                default: ''
            },
            url_update_articles: {
                type: String,
                default: ''
            },
            url_get_articles: {
                type: String,
                default: ''
            },
            url_validate: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                show_table: false,
                data: [],
                guide: {},
                articles: [],
                account_type_id: 0,
                addArticle: {
                    article_name: '',
                    article_id: 0,
                    quantity: ''
                },
                ids: []
            }
        },
        created() {
            let context = this;
            $(document).on('click', '.edit', function () {
                EventBus.$emit('loading', true);
                const index = $(this).parents('tr').index();

                context.articles = [];

                const data = context.data[index];

                context.guide = data;
                context.guide.index = index;

                context.$store.state.model.company_id = parseInt(context.$store.state.model.company_id);
                context.$store.state.model.warehouse_movement_id = context.guide.id;

                axios.post(context.url_list, context.$store.state.model)
                    .then(response => {
                        const data = response.data;

                        const articles = data.articles;
                        const account_type_id = data.account_type_id;

                        context.articles = articles;
                        context.account_type_id = account_type_id;
                        EventBus.$emit('loading', false);

                        $("#modal").modal('show')
                    })
                    .catch(error => {
                        console.log(error);
                        console.log(error.response);
                        EventBus.$emit('loading', false);
                    });
            })
        },
        mounted() {
            this.newSelect2();
            EventBus.$on('show_table', function (response) {
                EventBus.$emit('loading', true);
                this.show_table = true;
                this.$store.commit('addModel', response);

                axios.post(this.url, {
                    company_id: parseInt(this.$store.state.model.company_id),
                }).then(response => {
                    const data = response.data;

                    this.data = data;

                    this.fillTableX(data);

                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                    EventBus.$emit('loading', false);
                });
            }.bind(this));
        },
        watch: {},
        computed: {},
        methods: {
            fillTableX(data) {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.datatable = $('.kt-datatable').KTDatatable({
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
                        {
                            field: 'id',
                            title: '#',
                            sortable: false,
                            width: 30,
                            selector: {class: 'kt-checkbox--solid'},
                            textAlign: 'center',
                        },
						{
							field: 'creation_date',
							title: 'Fecha de Creacion',
							width: 100,
						},
						{
							field: 'referral_guide_series',
							title: 'Numero de Serie',
							width: 60,
						},
                        {
                            field: 'referral_guide_number',
                            title: 'Numero de Guia',
                            width: 60,
                        },
                        {
                            field: 'account_name',
                            title: 'Cliente o Trabajador',
                            width: 300,
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
                    ]
                });

                $('.kt-datatable').on('kt-datatable--on-check', function(a, e) {

                    $.each(e, function(key, value) {
                        let index = vm.ids.findIndex((element) => element == value);
                        if ( index < 0 ) {
                            vm.ids.push(value);
                        }
                    });

                    vm.ids.sort(function(a, b) {
                        let aNumber = parseInt(a);
                        let bNumber = parseInt(b);
                        return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                    });

                    console.log(vm.ids);
                });

                $('.kt-datatable').on('kt-datatable--on-uncheck', function(a, e) {
                    $.each(e, function(key, value) {
                        let index = vm.ids.findIndex((element) => element == value);
                        if ( index >= 0 ) {
                            vm.ids.splice(index, 1);
                        }
                    });

                    vm.ids.sort(function(a, b) {
                        let aNumber = parseInt(a);
                        let bNumber = parseInt(b);
                        return ((aNumber < bNumber) ? -1 : ((aNumber > bNumber) ? 1 : 0));
                    });

                    console.log(vm.ids);
                });
            },
            removeArticle: function(index) {
                EventBus.$emit('loading', true);
                const article = this.articles[index];

                const id = article.id;

                Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el artículo?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', false);
                        if ( result.value ) {
                            if (id) {
                                axios.post(this.url_remove_article, {
                                    model: this.$store.state.model,
                                    article,
                                }).then(response => {
                                    this.articles.splice(index, 1);

                                    Swal.fire({
                                        title: 'Ok!',
                                        text: 'Se ha eliminado el artículo',
                                        type: "success",
                                        heightAuto: false,
                                    });
                                }).catch(error => {
                                    EventBus.$emit('loading', false);
                                    console.log(error);
                                    console.log(error.response);
                                })
                            } else {
                                this.articles.splice(index, 1);

                                Swal.fire({
                                    title: 'Ok!',
                                    text: 'Se ha eliminado el artículo',
                                    type: "success",
                                    heightAuto: false,
                                });
                            }
                        }
                    });
            },
            updateArticles: function() {
                EventBus.$emit('loading', true);
                axios.post(this.url_update_articles,{
                    model: this.$store.state.model,
                    articles: this.articles
                }).then(response => {
                    $("#modal").modal('hide');
                    EventBus.$emit('loading', false);

                    Swal.fire({
                        title: 'Ok!',
                        text: 'Se han editado los artículos',
                        type: "success",
                        heightAuto: false,
                    });
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error);
                    console.log(error.response);
                })
            },
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#article-select").select2({
                    placeholder: "Buscar",
                    allowClear: true,
                    language: {
                        noResults: function() {
                            return 'No hay resultados';
                        },
                        searching: function() {
                            return 'Buscando...';
                        },
                        inputTooShort: function() {
                            return 'Ingresa 1 o más caracteres';
                        },
                        errorLoading: function() {
                            return 'No se pudo cargar la información'
                        }
                    },
                    ajax: {
                        url: this.url_get_articles,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.$store.state.model.company_id,
                                _token: token,
                            }

                            return queryParameters;
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;

                            return {
                                results: data,
                                pagination: {
                                    more: (params.page * 30) < data.total_count
                                }
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1,
                }).on('select2:select', function(e) {
                    const selected_element = $(e.currentTarget);

                    const name = selected_element[0].textContent;
                    vm.addArticle.article_name = name.replace('Seleccionar', '');
                    vm.addArticle.article_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.addArticle.article_name = '';
                    vm.addArticle.article_id = 0;
                });
            },
            addArticleFunction: function() {
                this.newSelect2();
                this.addArticle = {
                    article_id: 0,
                    quantity: ''
                };

                $("#modal").modal('hide')
                $("#modal-article").modal('show')
            },
            sendForm: function() {
                const article_name = this.addArticle.article_name;
                const article_id = this.addArticle.article_id;
                const quantity = parseInt(this.addArticle.quantity);

                if (article_id && quantity) {
                    const obj = {
                        id: 0,
                        article_name: article_name,
                        article_id: article_id,
                        presale: quantity,
                        prestamo: 0,
                        cesion: 0,
                    };

                    this.articles.push(obj);
                    $("#modal-article").modal('hide');
                    $("#modal").modal('show');
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Falta completar campos',
                        type: "error",
                        heightAuto: false,
                    });
                };
            },
            validate: function() {
                EventBus.$emit('loading', true);

                const ids = this.ids.map(id => {
                    return parseInt(id);
                })

                let data = this.data;

                ids.map(id => {
                    data = data.filter(item => item.id != id);
                });

                axios.post(this.url_validate,{
                    ids
                })
                .then(response => {
                    EventBus.$emit('loading', false);
                    this.datatable.originalDataSet = data;
                    this.datatable.load();
                    console.log(response);
                    Swal.fire({
                        title: 'Ok!',
                        text: 'Se han validado las guias exitosamente',
                        type: "success",
                        heightAuto: false,
                    });
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error);
                    console.log(error.response);
                })
            }
        }
    }
</script>