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
                            <label class="form-control-label">Almacén Proveedor:</label>
                            <select class="form-control" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option disabled value="">Seleccionar</option>
                                <option v-for="warehouseType in warehouse_providers" :value="warehouseType.id">{{ warehouseType.name }}</option>
                            </select>
                            <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>



                      <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de moneda:</label>
                            <select class="form-control" name="currency" id="currency" v-model="model.currency" @focus="$parent.clearErrorMsg($event)">
                                <option v-for="currency in currencies" :value="currency.id" v-bind:key="currency.id">{{ currency.name }}</option>
                            </select>
                            <div id="currency-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Facturación:</label>
                            <datetime
                                v-model="model.since_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="since_date"
                                name="since_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="this.current_date"
                                
                                 
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo:</label>
                            <select class="form-control readonly" name="warehouse_account_type_id" id="warehouse_account_type_id" v-model="model.warehouse_account_type_id" @focus="$parent.clearErrorMsg($event)" @change="warehouseAccountTypesChange()">
                                <option v-for="warehouse_account_type in warehouseAccountTypes" :value="warehouse_account_type.id" v-bind:key="warehouse_account_type.id">{{ warehouse_account_type.name }}</option>
                            </select>
                            <div id="warehouse_account_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Proveedor:</label>
                            <select class="form-control kt-select2" name="warehouse_account_id" id="warehouse_account_id" v-model="model.warehouse_account_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                            </select>
                            <div id="warehouse_account_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                 
              
                     <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Factura:</label>
                            <input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de Factura:</label>
                            <input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div> 

                    <div class="col-lg-3" v-if="this.model.currency >= 2 && this.model.currency <= 4" >
                         <div class="form-group">
                            <label class="form-control-label">Tipo Cambio:</label>
                            <input type="text" class="form-control" name="tc" id="tc" placeholder="0" v-model="model.tc" @focus="$parent.clearErrorMsg($event)">
                            <div id="tc-error" class="error invalid-feedback"></div>
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
                            <label class="form-control-label">Número de Orden de Compra:</label>
                            <input type="text" class="form-control" name="referral_guide_number" id="referral_guide_number" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
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
            tc: {
                type: String,
                default: ''
            },
            warehouse_providers: {
                type: Array,
                default: ''
            },
            current_date: {
                type: String,
                default: ''
            },
			// min_datetime: {
            //     type: String,
            //     default: ''
            // },
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
        //    client_routes: {
        //        type: Array,
        //        default: ''
        //    },
            vehicles: {
                type: Array,
                default: ''
            },
            guide_series: {
                type: Array,
                default: ''
            },
         //    url_next_correlative: {
         //       type: String,
          //      default: ''
          //  },
        },
        data() {
            return {
                model: {
                    movement_class_id: '',
                    movement_type_id: '',
                    movement_stock_type_id: '',
                    warehouse_type_id: '',
                    company_id: '',
                    currency:1,
                    tc:'',
             //    traslate_date: this.min_datetime,
                    since_date: this.current_date,
                    warehouse_account_type_id: 2,
                    warehouse_account_id: '',
                    referral_guide_series: '',
                    referral_guide_number: '',
                    referral_warehouse_document_type_id: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    scop_number: '',
                    vehicle_id: '',
            //        route_id:'',

                },
            }
        },
        created() {
            EventBus.$on('reset_stock_register', function() {
                this.model.movement_class_id = '';
                this.model.movement_type_id = '';
                this.model.movement_stock_type_id = '';
                this.model.warehouse_type_id = '';
                this.model.currency = 1;
                this.model.company_id = '';
            //    this.model.traslate_date = this.traslate_date;
                this.model.since_date = this.current_date;
                this.model.warehouse_account_type_id = 2;
                this.model.warehouse_account_id = '';
                this.model.referral_guide_series = '';
                this.model.referral_guide_number = '';
                 this.model.referral_warehouse_document_type_id = '';
                this.model.referral_serie_number = '';
                this.model.referral_voucher_number = '';
                this.model.scop_number = '';
                this.model.vehicle_id = '';
                this.model.tc = '';
            //    this.model.route_id = '';

                $('.kt-form').find('input').prop('disabled', false);
                $('.kt-form').find('select').prop('disabled', false);
                $('.kt-form').find('button').prop('disabled', false);
            }.bind(this));
        },
        mounted() {
            this.newSelect2();

            this.warehouse_account_types.filter(wat => wat.id === 2);
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
         /*     getNextCorrelative() {
                axios.get(this.url_next_correlative, {
                    params: {
                        guide_serie: $('#referral_guide_series').val(),
                        company: $('#company_id').val()
                    }
                }).then((response) => {
                    $('#referral_guide_number').val(response.data);
                }).catch((error) => {
                    console.log(error.response);
                });
            },*/
        }
    };
</script>