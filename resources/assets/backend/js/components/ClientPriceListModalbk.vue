<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal-client-price-list" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Lista de Precios</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="client_id" id="client_id" v-model="model.client_id">
                                <input type="hidden" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Artículos:</label>
                                        <select class="form-control kt-select2" name="article_id" id="article_id" v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Precio:</label>
                                        <input type="text" class="form-control" name="price_igv" id="price_igv" placeholder="0.0000" v-model="model.price_igv" @focus="$parent.clearErrorMsg($event)">
                                        <div id="price_igv-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Fech. Vig. inicial:</label>
                                        <input type="text" class="form-control" name="initial_effective_date" id="initial_effective_date" v-model="model.initial_effective_date" @focus="$parent.clearErrorMsg($event)" readonly="readonly">
                                        <div id="initial_effective_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Fech. Vig. final:</label>
                                        <datetime
                                            v-model="model.final_effective_date"
                                            placeholder="Selecciona una Fecha"
                                            :format="'dd-LL-yyyy'"
                                            input-id="final_effective_date"
                                            name="final_effective_date"
                                            value-zone="America/Lima"
											zone="America/Lima"
                                            class="form-control"
                                            :min-datetime="min_effective_date"
                                            @focus="$parent.clearErrorMsg($event)">
                                        </datetime>
                                        <div id="final_effective_date-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-success" id="add_price">
                                        {{ button_text }}
                                    </button>
                                    <button class="btn btn-secondary" @click.prevent="resetForm()">
                                        Cancelar
                                    </button>
                                </div>
                            </div>
                            <div class="kt-separator kt-separator--border-dashed kt-separator--space-lg"></div>
                            <div class="row">
                                <div class="col-12" @click="manageActions">
                                    <!--begin: Datatable -->
                                    <div class="kt-price-list-datatable"></div>
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
    import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    Vue.use(Datetime);

    export default {
        props: {
            url: {
                type: String,
                default: ''
            },
            url_price_list: {
                type: String,
                default: ''
            },
            url_price_articles: {
                type: String,
                default: ''
            },
            url_price_min_effective_date: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    client_id: '',
                    warehouse_type_id: 5,
                    article_id: '',
                    price_igv: '',
                    initial_effective_date: '',
                    final_effective_date: '',
                },
                min_effective_date: '',
                button_text: '',
                price_list_datatable: undefined,
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('price_list_modal', function(client_id) {
                this.button_text = 'Crear';

                this.model.client_id = client_id;
                this.model.article_id = '';
                this.model.price_igv = '';
                this.model.initial_effective_date = '';
                this.model.final_effective_date = '';

                $('#article_id').val(null).trigger('change');
                EventBus.$emit('loading', false);
                $('#modal-client-price-list').modal('show');
                this.fillTableX();
                
            }.bind(this));
        },
        mounted() {
            this.newSelect2();

            $('#modal-client-price-list').on('hide.bs.modal', function(e) {
                this.price_list_datatable.destroy();
            }.bind(this));

            $('.kt-select2').on('select2:select', function() {
                $(this).parent().find('select').removeClass('is-invalid');
                $(this).parent().find('.error').html('');
            });
        },
        methods: {
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#article_id").select2({
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
                        url: this.url_price_articles,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                warehouse_type_id: vm.model.warehouse_type_id,
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
                    minimumInputLength: 0,
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.article_id = parseInt(selected_element.val());

                    axios.post(vm.url_price_min_effective_date, {
                        client_id: vm.model.client_id,
                        article_id: vm.model.article_id
                    }).then(response => {
                        // console.log(response);
                        vm.model.final_effective_date = '';
                        vm.model.initial_effective_date = response.data.initial_effective_date;
                        vm.min_effective_date = response.data.min_effective_date;
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }).on('select2:unselect', function(e) {
                    vm.model.article_id = '';
                });
            },
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);

                // EventBus.$emit('loading', true);
                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('loading', false);
                    // $('#modal-client-price-list').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.article_id = '';
                    this.model.price_igv = '';
                    this.model.initial_effective_date = '';
                    this.model.final_effective_date = '';

                    // EventBus.$emit('refresh_table');
                    $('#article_id').val(null).trigger('change');
                    this.price_list_datatable.load();

                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('.modal').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        let c_target = target.find("#" + i + "-error");
                        if (!c_target.attr('data-required')) {
                            let p = c_target.parent().find('.form-control');
                            p.addClass('is-invalid');
                        } else {
                            c_target.css('display', 'block');
                        }
                        c_target.html(item);
                    });
                });
            },
            resetForm: function() {
                this.button_text = 'Crear';

                this.model.client_id = '';
                this.model.article_id = '';
                this.model.price_igv = '';
                this.model.initial_effective_date = '';
                this.model.final_effective_date = '';

                $('#article_id').val(null).trigger('change');
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.price_list_datatable = $('.kt-price-list-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'remote',
                        source: {
                            read: {
                                url: vm.url_price_list,
                                params: {
                                    _token: token,
                                    client_id: vm.model.client_id,
                                }
                            },
                        },
                        pageSize: 10,
                        serverPaging: true,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 220,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: true,

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
							field: 'code',
							title: 'Código',
							width: 60,
						},
                        {
                            field: 'name',
                            title: 'Descripción',
                            width: 150,
                        },
                        {
                            field: 'price_igv',
                            title: 'Precio',
                            width: 100,
                        },
                        {
                            field: 'initial_effective_date',
                            title: 'Fec. Vig. inicial',
                            width: 120,
                        },
                        {
                            field: 'final_effective_date',
                            title: 'Fec. Vig. final',
                            width: 120,
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
						// 			actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
						// 				actions += '<i class="la la-edit"></i>';
						// 			actions += '</a>';
                        //             actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                        //                 actions += '<i class="la la-trash"></i>';
                        //             actions += '</a>';
                        //         actions += '</div>';

                        //         return actions;
                        //     },
                        // },
                    ]
                });

                this.price_list_datatable.columns('id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el registro?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', true);

                        if ( result.value ) {
                            axios.post(this.url_address_delete, {
                                id: id,
                            }).then(response => {
                                if ( this.model.id == id ) {
                                    this.button_text = 'Crear';

                                    this.model.id = '';
                                    this.model.client_id = '';
                                    this.model.address_type_id = '';
                                    this.model.item_number = '';
                                    this.model.address = '';
                                    this.model.address_ubigeo_id = '';

                                    $('#address_ubigeo_id').val(null).trigger('change');
                                }

								this.price_list_datatable.load();
                                EventBus.$emit('loading', false);
                                Swal.fire({
                                    title: '¡Ok!',
                                    text: 'Se ha eliminado correctamente',
                                    type: "success",
                                    heightAuto: false,
                                });
                            }).catch(error => {
                                console.log(error);
                                console.log(error.response);
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    let id = $(event.target).parents('tr').find('td[data-field="id"] span').html();
                    EventBus.$emit('loading', true);

                    axios.post(this.url_address_detail, {
                        id: id,
                    }).then(response => {
                        // console.log(response);
                        this.button_text = 'Actualizar';

                        this.model.id = response.data.element.id;
                        this.model.client_id = response.data.element.client_id;
                        this.model.address_type_id = response.data.element.address_type_id;
                        this.model.item_number = response.data.element.item_number;
                        this.model.address = response.data.element.address;
                        this.model.address_ubigeo_id = response.data.element.ubigeo_id;

                        let option = new Option(response.data.option.text, response.data.option.id, true, true);
                        $('#address_ubigeo_id').append(option).trigger('change');
                        $('#address_ubigeo_id').trigger({
                            type: 'select2:select',
                            params: {
                                data: response.data.option
                            }
                        });

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
        }
    };
</script>