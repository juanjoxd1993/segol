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
                            <label class="form-control-label">Ingreso/Salida:</label>
                            <select class="form-control" name="movement_class_id" id="movement_class_id" v-model="model.movement_class_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="movement_class in movement_classes" :value="movement_class.id" v-bind:key="movement_class.id">{{ movement_class.name }}</option>
                            </select>
                            <div id="movement_class_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo Movimiento:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id" v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="movement_type in movementTypes" :value="movement_type.id" v-bind:key="movement_type.id">{{ movement_type.name }}</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Almacén:</label>
                            <select class="form-control" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="warehouse_type in warehouse_types" :value="warehouse_type.id" v-bind:key="warehouse_type.id">{{ warehouse_type.name }}</option>
                            </select>
                            <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
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
                            <label class="form-control-label">Fecha Inicial:</label>
                            <datetime
                                v-model="model.initial_date"
                                placeholder="Selecciona una Fecha"
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
                            <label class="form-control-label">Fecha Final:</label>
                            <datetime
                                v-model="model.final_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="final_date"
                                name="final_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="current_date"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="final_date-error" class="error invalid-feedback"></div>
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
                            <label class="form-control-label">Serie de Guía de Remisión:</label>
                            <input type="text" class="form-control" name="referral_guide_series" id="referral_guide_series" v-model="model.referral_guide_series" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_guide_series-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Guía de Remisión:</label>
                            <input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_guide_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo Referencia:</label>
                            <select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id" v-model="model.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
                            </select>
                            <div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Referencia:</label>
                            <input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Referencia:</label>
                            <input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de SCOP:</label>
                            <input type="text" class="form-control" name="scop_number" id="scop_number" v-model="model.scop_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="scop_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Placa:</label>
                            <input type="text" class="form-control" name="license_plate" id="license_plate" v-model="model.license_plate" @focus="$parent.clearErrorMsg($event)">
                            <div id="license_plate-error" class="error invalid-feedback"></div>
                        </div>
                    </div> -->
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

    Vue.use(Datetime);

    export default {
        props: {
            movement_classes: {
                type: Array,
                default: ''
            },
            movement_types: {
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
            current_date: {
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
        },
        data() {
            return {
                model: {
                    movement_class_id: '',
                    movement_type_id: '',
                    warehouse_type_id: '',
                    company_id: '',
                    initial_date: '',
                    final_date: this.current_date,
                    warehouse_account_type_id: '',
                    warehouse_account_id: '',
                    // referral_guide_series: '',
                    // referral_guide_number: '',
                    // warehouse_document_type_id: '',
                    // referral_serie_number: '',
                    // referral_voucher_number: '',
                    // scop_number: '',
                    // license_plate: '',
                },
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

                EventBus.$emit('loading', true);
                axios.post(url, fd, {
                    headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    console.log(response);
                    EventBus.$emit('show_table', response.data);
                    // this.alertMsg( response.data );
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
        }
    };
</script>