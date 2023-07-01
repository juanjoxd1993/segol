<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Buscar
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">

					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha inicial:</label>
                            <datetime
                                v-model="model.initial_date"
                                placeholder="Selecciona una Fecha inicial"
                                :format="'dd-LL-yyyy'"
                                input-id="initial_date"
                                name="initial_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="current_date"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="initial_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipos:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id" v-model="model.warehouse_type_index" @focus="$parent.clearErrorMsg($event)">
                                <option selected="true" value="0">Todos</option>
                                <option value="1">Planta Callao</option>
                                <option value="2">Planta Ate</option>
                                <option value="3">Granel</option>
                                <!-- <option v-for="planta in plantas" v-bind:value="planta.id">{{ planta.name }}</option> -->
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-lg-6">
                            <button type="submit" class="btn btn-primary">Buscar</button>
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
	import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    export default {
        props: {

			current_date: {
                type: String,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
			url_get_clients: {
				type: String,
				default: ''
			}
        },
        data() {
            return {
                model: {
                    initial_date: '',
                    warehouse_type_index: 0,
                },
                warehouse_types: [
                    [1,4,13,''],
                    [4],
                    [13],
                    [1],
                ]
            }
        },
        created() {

        },
        mounted() {
            this.newSelect2();
        },
        watch: {
            
        },
        computed: {

        },
        methods: {
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
                        url: vm.url_get_clients,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.model.company_id,
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
                    var selected_element = $(e.currentTarget);
                    vm.model.client_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.client_id = '';
                });
            },
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);

                axios.post(url,{
                    model: {
                        initial_date: vm.model.initial_date,
                        warehouse_types: vm.warehouse_types[vm.model.warehouse_type_index]
                    }
                }).then(response => {
                    // console.log(response);
                    EventBus.$emit('show_table', {
                        data: response.data,
                        model: {
                            initial_date: vm.model.initial_date,
                            warehouse_types: vm.warehouse_types[vm.model.warehouse_type_index]
                        }
                    });
                }).catch(error => {
                    console.log(error.response);
                    EventBus.$emit('loading', false);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        // console.log(target);
                        let c_target = target.find("#" + i + "-error");
                        let p = c_target.parents('.form-group').find('#' + i);
                        p.addClass('is-invalid');
                        c_target.css('display', 'block');
                        c_target.html(item);
                    });
                });
            },
        }
    };
</script>