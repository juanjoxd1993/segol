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
			<input type="hidden" name="client_id" v-model="model.client_id">
			<input type="hidden" name="bank_id" v-model="model.bank_id">
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
								:max-datetime="max_sale_date"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
                            <div id="sale_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Cancelación:</label>
							<select class="form-control" name="payment_method_id" id="payment_method_id" v-model="model.payment_method_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="payment_method in payment_methods" :value="payment_method.id" v-bind:key="payment_method.id">{{ payment_method.name }}</option>
							</select>
                            <div id="payment_method_id-error" class="error invalid-feedback"></div>
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
                            <label class="form-control-label">Banco:</label>
							<select class="form-control" name="bank_account_id" id="bank_account_id" v-model="model.bank_account_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="bank_account in filterBankAccounts" :value="bank_account.id" v-bind:key="bank_account.id">{{ bank_account.short_name }} - {{ bank_account.account_number }}</option>
							</select>
                            <div id="bank_account_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº Operación:</label>
							<input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="operation_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº Detracción:</label>
							<input type="text" class="form-control" name="detraction_number" id="detraction_number" v-model="model.detraction_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="detraction_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Documento Aplicación/Canje:</label>
							<select class="form-control" name="referral_warehouse_document_type_id" id="referral_warehouse_document_type_id" v-model="model.referral_warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
							</select>
                            <div id="referral_warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Documento Aplicación/Canje:</label>
							<input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="model.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº de Documento Aplicación/Canje:</label>
							<input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="model.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Total:</label>
							<input type="number" class="form-control" name="amount" id="amount" placeholder="0" step="any" min="0.00" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
                            <div id="amount-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
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
			max_sale_date: {
				type: String,
				default: ''
			},
			payment_methods: {
				type: Array,
				default: ''
			},
			currencies: {
				type: Array,
				default: ''
			},
			bank_accounts: {
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
        },
        data() {
            return {
				second_step: false,
                model: {
					company_id: '',
					client_id: '',
                    sale_date: '',
					payment_method_id: '',
					currency_id: '',
					exchange_rate: '',
					bank_account_id: '',
					bank_id: '',
					operation_number: '',
					detraction_number: '',
					referral_warehouse_document_type_id: '',
					referral_serie_number: '',
					referral_voucher_number: '',
					amount: '',
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
				vm.model.client_id = data.client_id;
			}.bind(this));

			EventBus.$on('clearSecondStep', function() {
				$('#second-step-form').find('input').prop('disabled', false);
				$('#second-step-form').find('select').prop('disabled', false);
				$('#second-step-form').find('button').prop('disabled', false);

				this.second_step = false;
                this.model = {
					company_id: '',
					client_id: '',
                    sale_date: '',
					payment_method_id: '',
					currency_id: '',
					exchange_rate: '',
					bank_account_id: '',
					bank_id: '',
					operation_number: '',
					detraction_number: '',
					referral_warehouse_document_type_id: '',
					referral_serie_number: '',
					referral_voucher_number: '',
					amount: '',
                };
			}.bind(this));
        },
        watch: {
			'model.bank_account_id': function (val) {
				let bank_account = this.bank_accounts.find(element => element.id == val);
				this.model.bank_id = bank_account ? bank_account.bank_id : '';
			}
        },
        computed: {
			filterBankAccounts: function() {
				return this.bank_accounts.filter(element => {
					return element.company_id == this.model.company_id && element.currency_id == this.model.currency_id;
				});
			}
        },
        methods: {
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