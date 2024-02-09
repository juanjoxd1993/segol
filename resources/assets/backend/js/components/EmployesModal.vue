<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} Empleado </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Tipo de Documento:</label>
										<select class="form-control" name="document_type_id" id="document_type_id" v-model="model.document_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="document_type in document_types" :value="document_type.id" v-bind:key="document_type.id">{{ document_type.name }}</option>
										</select>
										<div id="document_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Documento:</label>
                                        <input type="text" class="form-control" name="document_number" id="document_number" placeholder="123456789" v-model="model.document_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="document_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombres:</label>
                                        <input type="text" class="form-control" name="first_name" id="first_name" placeholder="Nombres" v-model="model.first_name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="first_name-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Apellidos:</label>
                                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="Apellidos" v-model="model.last_name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="last_name-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Licencia:</label>
                                        <input type="text" class="form-control" name="license" id="license" placeholder="Licencia" v-model="model.license" @focus="$parent.clearErrorMsg($event)">
                                        <div id="license-error" class="error invalid-feedback"></div>
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
            url_get_ubigeos: {
                type: String,
                default: ''
            },
            url_get_ubigeo: {
                type: String,
                default: ''
            },
            document_types: {
                type: Array,
                default: ''
            },
           
        },
        data() {
            return {
                model: {
                    id: '',
                    document_type_id: '',
                    document_number: '',
                    first_name: '',
                    last_name: '',
                    license: '',
                },
                button_text: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(provider) {
                this.button_text = 'Crear';

                this.model.document_type_id = '';
                this.model.document_number = '';
                this.model.fist_name = '';
                this.model.last_name = '';
                this.model.license = '';
                

                $('#ubigeo_id').val(null).trigger('change');
                $('#modal').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
                this.button_text = 'Actualizar';

                if ( this.model.ubigeo_id ) {
                    axios.post(this.url_get_ubigeo, {
                        ubigeo_id: this.model.ubigeo_id,
                    }).then(response => {
                        let option = new Option(response.data.text, response.data.id, true, true);
                        $('#ubigeo_id').append(option).trigger('change');
                        $('#ubigeo_id').trigger({
                            type: 'select2:select',
                            params: {
                                data: response.data
                            }
                        });
                        EventBus.$emit('loading', false);
                        $('#modal').modal('show');
                        console.log(response);
                    }).catch(error => {
                        console.log(error.response);
                        console.log(error.response);
                    });
                }
            }.bind(this));
        },
        mounted() {
            this.newSelect2();
        },
        methods: {
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#ubigeo_id").select2({
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
                        url: this.url_get_ubigeos,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
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
                    vm.model.ubigeo_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.ubigeo_id = '';
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
                    EventBus.$emit('loading', false);
                    $('#modal').modal('hide');
                    this.$parent.alertMsg(response.data);

                    this.model.document_type_id = '';
                    this.model.id = '';
                    this.model.document_number = '';
                    this.model.first_name = '';
                    this.model.last_name = '';
                    this.model.license = '';

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
                        if (!c_target.attr('data-required')) {
                            let p = c_target.prev();
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