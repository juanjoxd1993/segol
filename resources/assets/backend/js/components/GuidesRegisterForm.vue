<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Crear
                </h3>
            </div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo Movimiento:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id"
                                v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="12">Venta Planta</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Guía:</label>
                            <select class="form-control" name="electronic" id="electronic" v-model="model.electronic"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="0">Físico</option>
                                <option value="1">Electrónico</option>
                            </select>
                            <div id="electronic-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3" v-if="model.electronic == 1">
                        <div class="form-group">
                            <label class="form-control-label">Serie Electrónico:</label>
                            <input type="text" class="form-control" name="serie_electronic" id="serie_electronic" v-model="model.serie_electronic" readonly
                                @focus="$parent.clearErrorMsg($event)">
                            <div id="serie_electronic-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3" v-if="model.electronic == 1">
                        <div class="form-group">
                            <label class="form-control-label">N° Electrónico:</label>
                            <input type="text" class="form-control" name="number_electronic" id="number_electronic" v-model="model.number_electronic" readonly
                                @focus="$parent.clearErrorMsg($event)">
                            <div id="number_electronic-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Compañía:</label>
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{
                                    company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Emisión:</label>
                            <datetime v-model="model.since_date" placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'" input-id="since_date" name="since_date" value-zone="America/Lima"
                                zone="America/Lima" class="form-control" :min-datetime="this.min_datetime" :max-datetime="this.max_datetime"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Traslado:</label>
                            <datetime v-model="model.traslate_date" placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'" input-id="traslate_date" name="traslate_date"
                                value-zone="America/Lima" zone="America/Lima" class="form-control"
                                :min-datetime="this.min_datetime" :max-datetime="this.max_datetime"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="traslate_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" >
                        <div class="form-group">
                            <label class="form-control-label">Cliente:</label>
                            <select class="form-control kt-select2" name="client_id" id="client_id"
                                v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                            </select>
                            <div id="client_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Chofer:</label>
                            <select class="form-control kt-select2" name="chofer_id" id="chofer_id"
                                v-model="model.chofer_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                            </select>
                            <div id="chofer_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Placa:</label>
                            <input type="text" class="form-control" name="license_plate" id="license_plate"
                                placeholder="ABC-ABC" :maxlength="7" @input="formato" v-model="model.license_plate"
                                @focus="$parent.clearErrorMsg($event)">
                            <div id="license_plate-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tanque:</label>
                            <select class="form-control" name="tanque" id="tanque" v-model="model.tanque"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="4791">TANQUE 1</option>
                                <option value="4792">TANQUE 2</option>
                            </select>
                            <div id="tanque-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie:</label>
                            <select class="form-control" name="referral_guide_series" id="referral_guide_series"
                                v-model="model.referral_guide_series" v-on:change="getNextCorrelative()"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="guide_serie in guide_series" :value="guide_serie.num_serie"
                                    v-bind:key="guide_serie.id">{{ guide_serie.num_serie }}</option>
                            </select>
                            <div id="referral_guide_series-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Guía de Remisión:</label>
                            <input type="text" class="form-control readonly" name="referral_guide_number"
                                id="referral_guide_number" v-model="model.referral_guide_number"
                                @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_guide_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Siguiente</button>
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
import { h } from 'vue';
import EventBus from '../event-bus';
import Datetime from 'vue-datetime';
// You need a specific loader for CSS files
import 'vue-datetime/dist/vue-datetime.css';

Vue.use(Datetime);

export default {
    props: {
        companies: {
            type: Array,
            default: ''
        },
        min_datetime: {
            type: String,
            default: ''
        },
        max_electronic: {
            type: String,
            default: ''
        },
        max_datetime: {
            type: String,
            default: ''
        },
        warehouse_account_types: {
            type: Array,
            default: ''
        },
        url: {
            type: String,
            default: ''
        },
        url_get_clients: {
            type: String,
            default: ''
        },
        url_get_employees: {
            type: String,
            default: ''
        },
        guide_series: {
            type: Array,
            default: ''
        },
        url_next_correlative: {
            type: String,
            default: ''
        },
    },
    data() {
        return {
            model: {
                movement_type_id: 12,
                company_id: 2,
                since_date: this.max_datetime,
                traslate_date: this.max_datetime,
                client_id: '',
                chofer_id: '',
                license_plate: '',
                driver_name: '',
                referral_guide_series: '',
                referral_guide_number: '',
                electronic: 0,
                serie_electronic:'TC40',
                number_electronic: this.max_electronic,
                tanque: ''
            },
        }
    },
    created() {

    },
    mounted() {
        this.newSelect2();
        this.newSelect3();
    },
    watch: {
    },
    computed: {

    },
    methods: {
        formato() {
            this.model.license_plate = this.model.license_plate.trim();

            if (this.model.license_plate.length == 3) {

                this.model.license_plate = this.model.license_plate.substring(0, 3) + '-' + this.model.license_plate.substring(4, 3);
            }

        },
        newSelect2: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;
            $("#client_id").select2({
                placeholder: "Buscar",
                allowClear: true,
                language: {
                    noResults: function () {
                        return 'No hay resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    },
                    inputTooShort: function () {
                        return 'Ingresa 1 o más caracteres';
                    },
                    errorLoading: function () {
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
                            company_id: vm.model.company_id,
                            client_id: vm.model.client_id,
                            _token: token,
                        }
                        return queryParameters;
                    },
                    processResults: function (data, params) {
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
            }).on('select2:select', function (e) {
                var selected_element = $(e.currentTarget);
                vm.model.client_id = parseInt(selected_element.val());
            }).on('select2:unselect', function (e) {
                vm.model.client_id = '';
            });
        },
        newSelect3: function () {
            let vm = this;
            let token = document.head.querySelector('meta[name="csrf-token"]').content;
            $("#chofer_id").select2({
                placeholder: "Buscar",
                allowClear: true,
                language: {
                    noResults: function () {
                        return 'No hay resultados';
                    },
                    searching: function () {
                        return 'Buscando...';
                    },
                    inputTooShort: function () {
                        return 'Ingresa 1 o más caracteres';
                    },
                    errorLoading: function () {
                        return 'No se pudo cargar la información'
                    }
                },
                ajax: {
                    url: this.url_get_employees,
                    dataType: 'json',
                    delay: 250,
                    type: 'POST',
                    data: function (params) {
                        var queryParameters = {
                            q: params.term,
                            company_id: vm.model.company_id,
                            chofer_id: vm.model.chofer_id,
                            _token: token,
                        }

                        return queryParameters;
                    },
                    processResults: function (data, params) {
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
            }).on('select2:select', function (e) {
                var selected_element = $(e.currentTarget);
                vm.model.chofer_id = parseInt(selected_element.val());
            }).on('select2:unselect', function (e) {
                vm.model.chofer_id = '';
            });
        },
        formController: function (url, event) {
            var target = $(event.target);
            var url = url;
            var fd = new FormData(event.target);

            EventBus.$emit('loading', true);
            axios.post(url, fd, {
                headers: {
                    'Content-type': 'application/x-www-form-urlencoded',
                }
            }).then(response => {
                EventBus.$emit('loading', false);
                target.find('input').prop('disabled', true);
                target.find('select').prop('disabled', true);
                target.find('button').prop('disabled', true);
                EventBus.$emit('show_table', response.data);
                // console.log(response);
            }).catch(error => {
                EventBus.$emit('loading', false);
                console.log(error.response);
                var obj = error.response.data.errors;
                $('html, body').animate({
                    scrollTop: 0
                }, 500, 'swing');
                $.each(obj, function (i, item) {
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
        getNextCorrelative() {
            EventBus.$emit('loading', true);
            axios.get(this.url_next_correlative, {
                params: {
                    guide_serie: $('#referral_guide_series').val(),
                    company: $('#company_id').val()
                }
            }).then((response) => {
                EventBus.$emit('loading', false);
                const { data } = response;
                $('#referral_guide_number').val(data);
            }).catch((error) => {
                EventBus.$emit('loading', false);
                console.log(error.response);
            });
        },
    }
};
</script>
