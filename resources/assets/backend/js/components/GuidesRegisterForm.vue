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
                            <select class="form-control" name="movement_type_id" id="movement_type_id" v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option disabled value="">Seleccionar</option>
                                            <option value="11">Pre-Venta</option>
                                            <option value="12">Venta Planta</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Compañía:</label>
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{ company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha Final:</label>
                            <datetime
                                v-model="model.since_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="since_date"
                                name="since_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="this.max_datetime"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo:</label>
                            <select class="form-control" name="warehouse_account_type_id" id="warehouse_account_type_id" v-model="model.warehouse_account_type_id" @focus="$parent.clearErrorMsg($event)" @change="warehouseAccountTypesChange()">
                                <option value="">Seleccionar</option>
                                <option v-for="warehouse_account_type in warehouseAccountTypes" :value="warehouse_account_type.id" v-bind:key="warehouse_account_type.id">{{ warehouse_account_type.name }}</option>
                            </select>
                            <div id="warehouse_account_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nombre o Razón Social:</label>
                            <select class="form-control kt-select2" name="warehouse_account_id" id="warehouse_account_id" v-model="model.warehouse_account_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                            </select>
                            <div id="warehouse_account_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <!-- <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de SCOP:</label>
                            <input type="text" class="form-control" name="scop_number" id="scop_number" v-model="model.scop_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="scop_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div> -->
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Placa:</label>
                            <input type="text" class="form-control" name="license_plate" id="license_plate" v-model="model.license_plate" @focus="$parent.clearErrorMsg($event)"> 
                            <div id="license_plate-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" v-if="model.movement_type_id == '11'">
                        <div class="form-group">
                            <label class="form-control-label">Cliente:</label>
                            <select class="form-control kt-select2" name="client_id" id="client_id" v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="client in clients" :value="client.id" v-bind:key="client.id">{{ client.business_name }}</option>
                            </select>
                            <div id="client_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Chofer:</label>
                            <input type="text" class="form-control" name="driver_id" id="driver_id" v-model="model.driver_id" @focus="$parent.clearErrorMsg($event)">
                            <div id="driver_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <!-- <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Ruta:</label>
                            <select class="form-control" name="route_id" id="route_id" v-model="model.route_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="client_route in client_routes" :value="client_route.id" v-bind:key="client_route.id">{{ client_route.name }}</option>
                            </select>                          
                            <div id="route_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>-->
                    <div class="form-group">
                            <label class="form-control-label">Fecha de Traslado:</label>
                            <datetime
                                v-model="model.traslate_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="traslate_date"
                                name="traslate_date"
                                value-zone="America/Lima"
                                zone="America/Lima"
                                class="form-control"
                                :min-datetime="this.min_datetime"
                                :max-datetime="this.max_datetime"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="traslate_date-error" class="error invalid-feedback"></div>
                        </div>
                            <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Guía de Remisión:</label>
                            <select class="form-control" name="referral_guide_series" id="referral_guide_series" v-model="model.referral_guide_series" v-on:change="getNextCorrelative()" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="guide_serie in guide_series" :value="guide_serie.num_serie" v-bind:key="guide_serie.num_serie">{{ guide_serie.num_serie }}</option>
                            </select>    
                            <div id="guide_serie.id-error" class="error invalid-feedback"></div>              
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Guía de Remisión:</label>
                            <input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)" v-on:change="manageNumberGuide()">
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
    import EventBus from '../event-bus';
    import Datetime from 'vue-datetime';
    // You need a specific loader for CSS files
    import 'vue-datetime/dist/vue-datetime.css';

    Vue.use(Datetime);

    export default {
        props: {
            drivers: {
                type: Array,
                default: ''
            },
            clients: {
                type: Array,
                default: () => []
            },
            movement_classes: {
                type: Array,
                default: ''
            },
            movement_types: {
                type: Array,
                default: ''
            },
            movement_stock_types: {
                type: Array,
                default: ''
            },
            warehouse_types: {
                type: Array,
                default: ''
            },
            companies: {
                type: Array,
                default: ''
            },
            currencies: {
                type: Array,
                default: ''
            },
            current_date: {
                type: String,
                default: ''
            },
			min_datetime: {
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
            warehouse_document_types: {
                type: Array,
                default: ''
            },
            url: {
                type: String,
                default: ''
            },
            url_get_accounts: {
                type: String,
                default: ''
            },
            url_get_correlative: {
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
                    client_id: '',
                    driver_id: '',
                    movement_class_id: '',
                    movement_type_id: '',
                    movement_stock_type_id: '',
                    warehouse_type_id: '',
                    company_id: '',
                    traslate_date: this.min_datetime,
                    since_date: this.current_date,
                    warehouse_account_type_id: '',
                    account_document_number: '',
                    warehouse_account_id: '',
                    referral_guide_series: '',
                    referral_guide_number: '',
                    scop_number: '',
                    license_plate: '',
                    // route_id:'',
                },
            }
        },
        created() {
            EventBus.$on('reset_stock_register', function() {
                this.model.movement_class_id = '';
                this.model.movement_type_id = '';
                this.model.movement_stock_type_id = '';
                this.model.warehouse_type_id = '';
                this.model.company_id = '';
                this.model.traslate_date = this.traslate_date;
                this.model.since_date = this.current_date;
                this.model.warehouse_account_type_id = '';
                this.model.account_document_number = '';
                this.model.warehouse_account_id = '';
                this.model.referral_guide_series = '';
                this.model.referral_guide_number = '';
                this.model.scop_number = '';
                this.model.license_plate = '';
             //   this.model.route_id = '';

                $('.kt-form').find('input').prop('disabled', false);
                $('.kt-form').find('select').prop('disabled', false);
                $('.kt-form').find('button').prop('disabled', false);
            }.bind(this));
        },
        mounted() {
            this.newSelect2();
        },
        watch: {
            'model.movement_type_id': function(newVal) {
                if (newVal == 12) {
                    this.initializeDriverSelect2();
                }
            }    
        },
        computed: {
            movementTypes: function() {
                if ( this.model.movement_class_id != '' ) {
                    return this.movement_types.filter(mt => mt.movent_class === this.model.movement_class_id || mt.movent_class === 3);
                }
            },
            warehouseAccountTypes: function() {
                if ( this.model.movement_type_id == 1 || this.model.movement_type_id == 2 || this.model.movement_type_id == 15 ) {
                    return this.warehouse_account_types.filter(wat => wat.id === 2);
                } else if ( this.model.movement_type_id == 12 || this.model.movement_type_id == 13 || this.model.movement_type_id == 14 || this.model.movement_type_id == 16 ) {
                    return this.warehouse_account_types.filter(wat => wat.id === 1);
                } else {
                    return this.warehouse_account_types;
                }
            },
        },
        methods: {
            warehouseAccountTypesChange: function() {
                this.model.warehouse_account_id = '';
                $('#warehouse_account_id').val(null).trigger('change');
            },
            initializeDriverSelect2: function() {
                let vm = this;
                $("#driver_id").select2({
                placeholder: "Buscar chofer",
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
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.driver_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.driver_id = '';
                });
            },
            newSelect2: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;
                $("#warehouse_account_id").select2({
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
                        url: this.url_get_accounts,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.model.company_id,
                                warehouse_account_type_id: vm.model.warehouse_account_type_id,
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
                
                  /*      url: this.url_get_correlative,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.model.company_id,
                                guides_serie_id: vm.model.guides_serie_id,
                                _token: token,
                            }

                            return queryParameters;
                        },*/

                        cache: true
                        
                    },
                    minimumInputLength: 1,
                }).on('select2:select', function(e) {
                    var selected_element = $(e.currentTarget);
                    vm.model.warehouse_account_id = parseInt(selected_element.val());
                }).on('select2:unselect', function(e) {
                    vm.model.warehouse_account_id = '';
                    vm.perception_percentage = '';
                });

            
            },
            formController: function(url, event) {
                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                const warehouse_account_type_id = this.model.warehouse_account_type_id;

                this.$store.commit('registerWarehouseAccountTypeId', warehouse_account_type_id);

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
            getNextCorrelative() {
                axios.get(this.url_next_correlative, {
                    params: {
                        guide_serie: $('#referral_guide_series').val(),
                        company: $('#company_id').val()
                    }
                }).then((response) => {
                    const { data } = response;

                    $('#referral_guide_number').val(data);
                }).catch((error) => {
                    console.log(error.response);
                });
            },
            manageNumberGuide() {
                EventBus.$emit('loading', true);
                $('#liquidar').prop('disabled', true);
                $('#referral_guide_number-error').hide();
                $('#referral_guide_number-error').text('');

                axios.post('/facturacion/liquidaciones-glp/get-guide-number', {
                    params: {
                        serie_number: this.model.referral_guide_series,
                        guide_number: this.model.referral_guide_number,
                    }
                }).then(response => {
                    EventBus.$emit('loading', false);
                    $('.kt-form').find('button').prop('disabled', false);
                    $('#referral_guide_number-error').text('');
                    $('#referral_guide_number-error').hide();
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    $('.kt-form').find('button').prop('disabled', true);
                    $('#referral_guide_number-error').text('El Nro. de Guía ya fue usado anteriormente');
                    $('#referral_guide_number-error').show();
                });
            },
        }
    };
</script>

