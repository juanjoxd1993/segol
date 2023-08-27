<template>
    <!--begin::Portlet-->
    <div class="kt-portlet" v-if="second_step">
        <!-- <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Registro
                </h3>
            </div>
        </div> -->

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event)" id="second-step-form">
			<input type="hidden" name="company_id" v-model="model.company_id">
			<input type="hidden" name="warehouse_document_type_id" v-model="model.warehouse_document_type_id">
			<input type="hidden" name="voucher_type_id" v-model="model.voucher_type_id">
			<input type="hidden" name="serie_number" v-model="model.serie_number">
			<input type="hidden" name="voucher_number" v-model="model.voucher_number">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Emisión:</label>
							<datetime
								v-model="model.sale_date"
								placeholder="Selecciona una Fecha"
								:format="'dd-LL-yyyy'"
								input-id="sale_date"
								name="sale_date"
								value-zone="America/Lima"
								zone="America/Lima"
								class="form-control"
								:min-datetime="min_sale_date"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
                            <div id="sale_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Cliente:</label>
							<select class="form-control kt-select2" name="client_id" id="client_id" v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
							</select>
							<div id="client_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Condición de Venta:</label>
							<select class="form-control" name="payment_id" id="payment_id" v-model="model.payment_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="payment in payments" :value="payment.id" v-bind:key="payment.id">{{ payment.name }}</option>
							</select>
                            <div id="payment_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Vencimiento:</label>
							<datetime
								v-model="model.expiry_date"
								placeholder="Selecciona una Fecha"
								:format="'dd-LL-yyyy'"
								input-id="expiry_date"
								name="expiry_date"
								value-zone="America/Lima"
								zone="America/Lima"
								class="form-control"
								:min-datetime="min_expiry_date"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
                            <div id="expiry_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Moneda:</label>
							<select class="form-control" name="currency_id" id="currency_id" v-model="model.currency_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="currency in currencies" :value="currency.id" v-bind:key="currency.id">{{ currency.name }}</option>
							</select>
                            <div id="currency_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Cambio:</label>
							<input type="text" class="form-control" name="exchange_rate" id="exchange_rate" v-model="model.exchange_rate" @focus="$parent.clearErrorMsg($event)">
                            <div id="exchange_rate-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Unidad de Negocio:</label>
							<select class="form-control" name="business_unit_id" id="business_unit_id" v-model="model.business_unit_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="business_unit in business_units" :value="business_unit.id" v-bind:key="business_unit.id">{{ business_unit.name }}</option>
							</select>
                            <div id="business_unit_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Motivo:</label>
							<select class="form-control" name="credit_note_reason_id" id="credit_note_reason_id" v-model="model.credit_note_reason_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="credit_note_reason in credit_note_reasons" :value="credit_note_reason.id" v-bind:key="credit_note_reason.id">{{ credit_note_reason.name }}</option>
							</select>
                            <div id="credit_note_reason_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">% Detracción:</label>
							<input type="text" class="form-control" name="detraction_percentage" id="detraction_percentage" v-model="model.detraction_percentage" @focus="$parent.clearErrorMsg($event)">
                            <div id="detraction_percentage-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
				<!--	<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Referencia:</label>
							<select class="form-control" name="referral_warehouse_document_type_id" id="referral_warehouse_document_type_id" v-model="model.referral_warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
							</select>
                            <div id="referral_warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>-->

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Referencia:</label>
                            <select class="form-control" name="referral_warehouse_document_type_id" id="referral_warehouse_document_type_id" v-model="model.referral_warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option disabled value="">Seleccionar</option>
                                <option value="5">Factura Electrónica</option>
                                <option value="7">Boleta de Venta Electrónica</option>
                                <option value="3" v-if="model.warehouse_document_type_id == 28">Guía de prestamo de balones</option>
                            </select>
                            <div id="referral_warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Referencia:</label>
                            <select class="form-control" name="reference" id="reference" v-model="model.referencce" @focus="$parent.clearErrorMsg($event)">
                                <option disabled value="">Seleccionar</option>
								<option v-for="reference in references" :value="reference.id" v-bind:key="reference.id">{{ reference.referral_serie_number }} | {{ reference.referral_voucher_number }}</option>
                            </select>
                            <div id="reference-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

					<!-- <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Referencia:</label>
							<input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº de Referencia:</label>
							<input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div> -->

                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Detalle</button>
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
    export default {
        props: {
			min_sale_date: {
				type: String,
				default: ''
			},
			max_sale_date: {
				type: String,
				default: ''
			},
			payments: {
				type: Array,
				default: ''
			},
			min_expiry_date: {
				type: String,
				default: ''
			},
			max_expiry_date: {
				type: String,
				default: ''
			},
			currencies: {
				type: Array,
				default: ''
			},
			business_units: {
				type: Array,
				default: ''
			},
			credit_note_reasons: {
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
			url_get_clients: {
                type: String,
                default: ''
            },
			url_get_references: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
				second_step: false,
                references: [],
                model: {
					company_id: '',
					warehouse_document_type_id: '',
					voucher_type_id: '',
					serie_number: '',
					voucher_number: '',
                    sale_date: '',
					client_id: '',
					payment_id: '',
					expiry_date: '',
					currency_id: '',
					exchange_rate: '',
					business_unit_id: '',
					credit_note_reason_id: '',
					detraction_percentage: '',
					referral_warehouse_document_type_id: '',
					referral_serie_number: '',
					referral_voucher_number: '',
                    reference: '',
                },
            }
        },
        created() {

        },
        mounted() {
			EventBus.$on('firstStateUpdate', function(data) {
				let vm = this;

				vm.second_step = true;
				vm.model.company_id = data.company_id;
				vm.model.warehouse_document_type_id = data.warehouse_document_type_id;
				vm.model.voucher_type_id = data.voucher_type_id;
				vm.model.serie_number = data.serie_number;

				if ( data.min_sale_date ) {
					vm.model.min_sale_date = data.min_sale_date;
				}
				if ( data.min_expiry_date ) {
					vm.model.min_expiry_date = data.min_expiry_date;
				}
				if ( data.voucher_number ) {
					vm.model.voucher_number = data.voucher_number;
				}

				Vue.nextTick(function() {
					vm.newSelect2();
				});
			}.bind(this));

			EventBus.$on('clearSecondStep', function() {
				$('#second-step-form').find('input').prop('disabled', false);
				$('#second-step-form').find('select').prop('disabled', false);
				$('#second-step-form').find('button').prop('disabled', false);

				this.second_step = false;
                this.model = {
					company_id: '',
					warehouse_document_type_id: '',
					voucher_type_id: '',
					serie_number: '',
					voucher_number: '',
                    sale_date: '',
					client_id: '',
					payment_id: '',
					expiry_date: '',
					currency_id: '',
					exchange_rate: '',
					business_unit_id: '',
					credit_note_reason_id: '',
					detraction_percentage: '',
					referral_warehouse_document_type_id: '',
					referral_serie_number: '',
					referral_voucher_number: '',
                };
			}.bind(this));
        },
        watch: {
            'model.referral_warehouse_document_type_id'(value) {
                EventBus.$emit('loading', true);

                axios.post(this.url_get_references, {
                    referral_warehouse_document_type_id: value
                })
                    .then(res => {
                        EventBus.$emit('loading', false);

                        this.references = res.data;
                    })
                    .catch(err => {
                        EventBus.$emit('loading', false);
                        console.log(error.response);
                    })
            }
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
                        url: this.url_get_clients,
                        dataType: 'json',
                        delay: 250,
                        type: 'POST',
                        data: function (params) {
                            var queryParameters = {
                                q: params.term,
                                company_id: vm.model.company_id,
                                voucher_type_id: vm.model.voucher_type_id,
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

                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
					EventBus.$emit('loading', false);

					if ( response.data.error ) {
						vm.$parent.alertMsg({
							type: 5,
							title: response.data.error.title,
							msg: response.data.error.msg
						});
					} else {
						$('#second-step-form').find('input').prop('disabled', true);
						$('#second-step-form').find('select').prop('disabled', true);
						$('#second-step-form').find('button').prop('disabled', true);

						EventBus.$emit('secondStateUpdate', response.data);
					}
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