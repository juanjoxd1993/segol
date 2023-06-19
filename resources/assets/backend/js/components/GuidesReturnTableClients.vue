
<template>
    <div>
        <!--begin::Modal-->
        <div class="modal fade" id="modalClients" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <!--begin::Form-->
                    <div class="kt-form">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalLabelClients">Agregar Cliente</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="kt-portlet__body">
                                <div class="row">

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Cliente:</label>
                                            <select class="form-control kt-select2" name="client_id" id="client_id" v-model="client_id" @focus="$parent.clearErrorMsg($event)">
                                                <option value="">Seleccionar</option>
                                            </select>
                                            <div id="client_id-error" class="error invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <div class="col-lg-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Articulo:</label>
                                            <select class="form-control" name="article_id" id="article_id" v-model="article_id" @focus="$parent.clearErrorMsg($event)">
                                                <option value="0" selected>Seleccionar</option>
                                                <option v-for="article in articles" v-bind:key="article.article_id" :value="article.article_id">{{ article.article_name }}</option>
                                            </select>
                                            <div id="warehouse_movement_id-error" class="error invalid-feedback"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Monto a Liquidar:</label>
                                            <input type="number" class="form-control" v-model="liquidation">
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="form-control-label">Stock Pendiente:</label>
                                            <input type="number" class="form-control" v-model="liquidar" readonly>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" @click="agree">
                                Agregar
                            </button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="close">Cerrar</button>
                        </div>
                    </div>
                    <!--end::Form-->
                </div>
            </div>
        </div>
        <div>
            <!--begin::Portlet-->
            <div class="kt-portlet" v-if="show_table">
                <div class="kt-portlet__head">
                    <div class="kt-portlet__head-label">
                        <h3 class="kt-portlet__head-title">
                            Clientes
                        </h3>
                    </div>
                    <div class="kt-portlet__head-toolbar">
                        <div class="kt-portlet__head-wrapper">
                            <div class="dropdown dropdown-inline">
                                <a href="#" class="btn btn-success btn-bold btn-sm" @click.prevent="openModal()">
                                    <i class="la la-plus"></i> Agregar cliente
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="kt-portlet__body kt-portlet__body--fit">
                    <!--begin: Datatable -->
                    <div class="kt-datatable-sales"></div>
                    <!--end: Datatable -->
                </div>
            </div>
            <!--end::Portlet-->
        </div>
    </div>
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            url_get_clients: {
                type: String,
                default: ''
            },
            url_list: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                client_id: 0,
                client_name: '',
                article_id: 0,
                article_name: '',
                show_table: false,
                liquidation: 0,
                liquidar: 0,
                data: [],
                articles: [],
                datatable: undefined,
            }
        },
        created() {
            let context = this;
            $(document).on('click', '.delete', function (e) {
                const index = $(this).parents('tr').index();

                const data = context.data;
                const store_data = context.$store.state.clients;

                const client = context.$store.state.clients[index];
                const article_id = client.article_id;
                const liquidation = client.liquidation;

                const article_for_liquidation = context.$store.state.articles_for_liquidations.find(art => art.article_id === article_id);
                const article_for_liquidation_index = context.$store.state.articles_for_liquidations.findIndex(art => art.article_id === article_id);

                context.$store.state.articles_for_liquidations[article_for_liquidation_index].rest_liquidation = parseInt(article_for_liquidation.rest_liquidation) + parseInt(liquidation);

                data.splice(index, 1);
                store_data.splice(index, 1);

                context.data = data;
                context.$store.state.clients = store_data;

                context.datatable.destroy();

                context.fillTableX();
            });
        },
        mounted() {
            this.newSelect2();
            EventBus.$on('show_table', () => {
                this.show_table = true;
                this.articles = this.$store.state.articles;

                // axios.post(this.url_list, {
                //     model: this.$store.state.model,
                // }).then(response => {
                //     const data = response.data;

                //     this.articles = data;

                //     EventBus.$emit('loading', false);
                // }).catch(error => {
                //     console.log(error);
                //     console.log(error.response);
                // });

                this.$nextTick(function() {
                    if ( this.datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        // this.datatable.originalDataSet = this.$store.state.sales;
                        this.datatable.load();
                    }
                });
            });
            EventBus.$on('refresh_table_sale', () => {
                if ( this.sale_datatable != undefined ) {
                    this.sale_datatable.originalDataSet = this.$store.state.sales;
                    this.sale_datatable.load();
                }
            });
        },
        watch: {
            article_id(value, last) {
                const article = this.articles.find(article => article.article_id === value);
                const article_for_liquidations = this.$store.state.articles_for_liquidations.find(art => art.article_id === value);
                const liquidar = article_for_liquidations.rest_liquidation ? article_for_liquidations.rest_liquidation : 0;

                if (article) {
                    this.article_name = article.article_name;
                    this.liquidar = liquidar;
                } else {
                    this.article_name = '';
                    this.liquidar = 0;
                };
            }
        },
        computed: {

        },
        methods: {
            openModal: function() {
                this.articles = this.$store.state.articles;
                this.newSelect2();
                $("#modalClients").modal('show');
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.datatable = $('.kt-datatable-sales').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: this.data,
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
                        autoHide: false,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'id',
                            title: '#',
                            width: 60,
                            textAlign: 'left',
                        },
                        {
                            field: 'client_name',
                            title: 'Cliente',
                            width: 200,
                            textAlign: 'left',
                        },
                        {
                            field: 'article_name',
                            title: 'Tipo',
                            width: 120,
                            textAlign: 'left',
                        },
                        {
                            field: 'liquidation',
                            title: 'Liquidar',
                            width: 60,
                            textAlign: 'right',
                        },
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 120,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                let actions = '<div class="actions">';
                                actions += '<a title="Eliminar" class="delete btn btn-danger btn-sm btn-icon btn-icon-md" href="#">';
                                actions += '<i class="la la-trash"></i>';
                                actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        }
                    ]
                });

                // this.sale_datatable.columns('client_id').visible(false);
            },
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#client_id").select2({
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
                        url: this.url_get_clients,
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
                    // console.log(e.params.data)
                    vm.client_id = e.params.data.id;
                    vm.client_name = e.params.data.text;
                }).on('select2:unselect', function(e) {
                    vm.client_id = '';
                });
            },
            agree() {
                this.is_edit = false;
                const id = this.data.length + 1;
                const client_id = this.client_id;
                const article_id = this.article_id;
                const liquidation = this.liquidation;

                const article_for_liquidations = this.$store.state.articles_for_liquidations.find(art => art.article_id === article_id);
                const article_for_liquidations_index = this.$store.state.articles_for_liquidations.findIndex(art => art.article_id === article_id);
                const liquidar = article_for_liquidations.rest_liquidation ? article_for_liquidations.rest_liquidation : 0;

                if (Boolean(!client_id)) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debes seleccionar un cliente',
                        type: "error",
                        heightAuto: false,
                    });

                    return;
                };

                if (Boolean(!liquidation)) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'El monto a liquidar no puede estar vacio',
                        type: "error",
                        heightAuto: false,
                    });

                    return;
                };

                if (liquidation > liquidar) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'El monto digitado es mayor al monto a liquidar',
                        type: "error",
                        heightAuto: false,
                    });

                    return;
                };

                this.$store.state.articles_for_liquidations[article_for_liquidations_index].rest_liquidation = liquidar - liquidation;

                this.datatable.destroy();

                const client = {
                    id,
                    client_id,
                    client_name: this.client_name,
                    article_id,
                    article_name: this.article_name,
                    liquidation: this.liquidation,
                };

                this.data.push(client);

                this.$store.commit('addClient', client);

                this.close();

                this.fillTableX();

                $("#modalClients").modal('hide')
            },
            close() {
                this.is_edit = false;
                this.modal_title = 'Agregar Cliente'
                this.client_id = 0;
                this.client_name = '';
                this.article_id = 0;
                this.liquidation = 0;
            },
        }
    };
</script>