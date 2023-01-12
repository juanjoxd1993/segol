<template>
    <!--begin::Modal-->
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <!--begin::Form-->
                <form class="kt-form" @submit.prevent="formController(url, $event)">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ this.button_text }} clasificación</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <input type="hidden" name="id" id="id" v-model="model.id">
                                <div class="col-lg-3">
									<div class="form-group">
										<label class="form-control-label">Tipo de Clasificación:</label>
										<select class="form-control" name="classification_type_id" id="classification_type_id" v-model="model.classification_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="classification_type in classification_types" :value="classification_type.id" v-bind:key="classification_type.id">{{ classification_type.name }}</option>
										</select>
										<div id="classification_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
                                <div class="col-lg-4">
                                    <div class="form-group">
                                        <label class="form-control-label">Descripción</label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Nombre del Artículo" v-model="model.name" @focus="$parent.clearErrorMsg($event)">
                                        <div id="name-error" class="error invalid-feedback"></div>
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
            classification_types: {
                type: Array,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    id: '',
                    classification_type_id: '',
                    name: '',
                },
                button_text: '',
            }
        },
        watch: {
            
        },
        computed: {
            
        },
        created() {
            EventBus.$on('create_modal', function(classification_type_id) {
                this.model.classification_type_id = classification_type_id;
                this.button_text = 'Crear';

                this.model.name = '';

                $('#modal').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', function(model) {
                this.model = model;
                this.button_text = 'Actualizar';
                $('#modal').modal('show');
            }.bind(this));
        },
        mounted() {
            
        },
        methods: {
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

                    this.model.classification_type_id = '';
                    this.model.id = '';
                    this.model.name = '';

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