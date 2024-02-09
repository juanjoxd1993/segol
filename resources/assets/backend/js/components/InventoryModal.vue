<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} artículo</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <input type="hidden" name="company_id" id="company_id" v-model="model.company_id">
                                <input type="hidden" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id">
                                <input type="hidden" name="creation_date" id="creation_date" v-model="model.creation_date">
                                <div class="col-lg-6">
									<div class="form-group">
                                        <label class="form-control-label">Artículo:</label>
                                        <select class="form-control kt-select2" name="article_id" id="article_id" v-model="model.article_id" @focus="$parent.clearErrorMsg($event)">
                                            <option disabled value="">Seleccionar</option>
                                        </select>
                                        <div id="article_id-error" class="error invalid-feedback"></div>
                                    </div>
								</div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad{{ !is_glp ? ' Buen estado' : '' }}:</label>
                                        <input type="tel" class="form-control" name="found_stock_good" id="found_stock_good" placeholder="0.00" v-model="model.found_stock_good" @focus="$parent.clearErrorMsg($event)">
                                        <div id="found_stock_good-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="!is_glp">
                                    <div class="form-group">
                                        <label class="form-control-label">Cantidad Mal estado:</label>
                                        <input type="tel" class="form-control" name="found_stock_damaged" id="found_stock_damaged" placeholder="0.00" v-model="model.found_stock_damaged" @focus="$parent.clearErrorMsg($event)">
                                        <div id="found_stock_damaged-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-12">
                                    <div class="form-group">
                                        <label class="form-control-label">Observaciones:</label>
                                        <textarea class="form-control" name="observations" id="observations" placeholder="" size="20" v-model="model.observations" @focus="$parent.clearErrorMsg($event)"></textarea>
                                        <div id="observations-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" id="add_article_2">
                            {{ button_text }}
                        </button>
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
            },
            url_get_select2: {
                type: String,
                default: ''
            },
            url_get_articles: {
                type: String,
                default: ''
            },
            is_glp: {
                type: Boolean,
                default: false
            }
        },
        data() {
            return {
                model: {
                    id: '',
                    company_id: '',
                    warehouse_type_id: '',
                    creation_date: '',
                    article_id: '',
                    found_stock_good: '',
                    found_stock_damaged: '',
                    observations: '',
                },
                button_text: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(model) {
                EventBus.$emit('loading', true);

                this.button_text = 'Agregar';

                this.model.id = '';
                this.model.company_id = model.company_id;
                this.model.warehouse_type_id = model.warehouse_type_id;
                this.model.creation_date = model.creation_date;
                this.model.article_id = '';
                this.model.found_stock_good = '';
                this.model.found_stock_damaged = '';
                this.model.observations = '';

                $('#article_id').val(null).trigger('change');
                $('#modal').modal('show');
                EventBus.$emit('loading', false);

                // axios.post(this.url_get_articles, {
                //     model: this.model,
                // }).then(response => {
                //     // console.log(response.data);
                //     this.articles = response.data;
                //     $('#article_id').val(null).trigger('change');
                //     $('#modal').modal('show');
                //     EventBus.$emit('loading', false);
                // }).catch(error => {
                //     console.log(error);
                //     console.log(error.response);
                // })
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model.id = model.id;
                this.model.article_id = model.article_id;
                this.model.found_stock_good = model.found_stock_good;
                this.model.found_stock_damaged = model.found_stock_damaged;
                this.model.observations = model.observations;
                this.button_text = 'Actualizar';
                
                axios.post(this.url_get_select2, {
                    article_id: this.model.article_id,
                }).then(response => {
                    // console.log(response);
                    let option = new Option(response.data.text, response.data.id, true, true);
                    $('#article_id').append(option).trigger('change');
                    $('#article_id').trigger({
                        type: 'select2:select',
                        params: {
                            data: response.data
                        }
                    });

                    $('#modal').modal('show');
                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            }.bind(this));
        },
        mounted() {
            this.newSelect2();
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
                        url: this.url_get_articles,
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

                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    EventBus.$emit('loading', false);
                    $('#modal').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.id = '';
                    this.model.article_id = '';
                    this.model.found_stock_good = '';
                    this.model.found_stock_damaged = '';
                    this.model.observations = '';

                    EventBus.$emit('refresh_table');

                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('.modal').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        let c_target = target.find("#" + i + "-error");
                        if ( !c_target.attr('data-required') ) {
                            let p = c_target.parent().find('#' + i);
                            p.addClass('is-invalid');
                        } else {
                            c_target.css('display', 'block');
                        }
                        c_target.html(item);
                    });
                });
            },
        }
    };
</script>