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
							<select class="form-control" name="payment_method_id" id="payment_method_id" v-model="model.payment_method_id" @focus="$parent.clearErrorMsg($event)" v-on:change="changePayment">
								<option value="">Seleccionar</option>
								<option v-for="payment_method in payment_methods" :value="payment_method.id" v-bind:key="payment_method.id">{{ payment_method.name }}</option>
							</select>
                            <div id="payment_method_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" v-if="model.payment_method_id == 9">
                        <div class="form-group">
                            <label class="form-control-label">Sede:</label>
                            <select class="form-control" name="payment_sede" id="payment_sede" v-model="model.payment_sede" @focus="$parent.clearErrorMsg($event)">
                                <option value="ATE">ATE</option>
                                <option value="CALLAO">CALLAO</option>
                                <option value="COLONIAL">COLONIAL</option>
                            </select>
                            <div id="payment_sede-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" v-if="model.payment_method_id == 9">
                        <div class="form-group">
                            <label class="form-control-label">Fecha Final:</label>
                            <datetime
                                v-model="model.payment_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="since_date"
                                name="since_date"
                                value-zone="America/Lima"
                                zone="America/Lima"
                                class="form-control"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="payment_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 10">
                        <div class="form-group">
                            <label class="form-control-label">Saldos a Favor:</label>
							<select class="form-control" name="saldo_favor_id" id="saldo_favor_id" v-model="model.saldo_favor_id" @focus="$parent.clearErrorMsg($event)" v-on:change="changeSaldo">
								<option value="">Seleccionar</option>
								<option v-for="saldo_favor in saldos_favor" :value="saldo_favor.id" v-bind:key="saldo_favor.id">{{ saldo_favor.name }}</option>
							</select>
                            <div id="saldo_favor_id-error" class="error invalid-feedback"></div>
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
					<div class="col-lg-3" v-if="model.currency_id != 1 && model.currency_id">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Cambio:</label>
							<input type="text" class="form-control" name="exchange_rate" id="exchange_rate" v-model="model.exchange_rate" @focus="$parent.clearErrorMsg($event)">
                            <div id="exchange_rate-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 2 || model.payment_method_id === 3">
                        <div class="form-group">
                            <label class="form-control-label">Banco:</label>
							<select class="form-control" name="bank_account_id" id="bank_account_id" v-model="model.bank_account_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="bank_account in filterBankAccounts" :value="bank_account.id" v-bind:key="bank_account.id">{{ bank_account.short_name }} - {{ bank_account.account_number }}</option>
							</select>
                            <div id="bank_account_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 2 || model.payment_method_id === 3">
                        <div class="form-group">
                            <label class="form-control-label">Nº Operación:</label>
							<input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="operation_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3" v-if="model.payment_method_id === 9">
                        <div class="form-group">
                            <label class="form-control-label">Nº Hermeticase:</label>
							<input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="operation_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 2 || model.payment_method_id === 3">
                        <div class="form-group">
                            <label class="form-control-label">Nº Detracción:</label>
							<input type="text" class="form-control" name="detraction_number" id="detraction_number" v-model="model.detraction_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="detraction_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 6">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Documento Aplicación/Canje:</label>
							<select class="form-control" name="referral_warehouse_document_type_id" id="referral_warehouse_document_type_id" v-model="model.referral_warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)" v-on:change="changeDocumentType">
								<option value="">Seleccionar</option>
								<option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
							</select>
                            <div id="referral_warehouse_document_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3" v-if="model.payment_method_id === 6">
                        <div class="form-group">
                            <label class="form-control-label">Documentos:</label>
							<select class="form-control" name="document_id" id="document_id" v-model="model.document_id" @focus="$parent.clearErrorMsg($event)" v-on:change="changeDocument">
								<option value="">Seleccionar</option>
								<option v-for="document in documents" :value="document.id" v-bind:key="document.id">{{ document.name }}</option>
							</select>
                            <div id="document-error" class="error invalid-feedback"></div>
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
            url_get_saldos: {
                type: String,
                default: ''
            },
            url_get_documents: {
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
                    payment_sede: '',
                    payment_date: '',
                },
                saldos_favor: [],
                documents: []
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

                EventBus.$emit('loading', true);

                axios.post(url, vm.model).then(response => {
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
            changePayment(e) {
                const value = e.target.value;

                if (value == 10) {
                    axios.post(this.url_get_saldos,{
                        client_id: this.model.client_id
                    }).then(res => {
                        const { data } = res;
                        this.saldos_favor = data;
                    }).catch(err => {
                        console.log(err);
                        console.log(err.response);
                    });
                };
            },
            changeDocumentType(e) {
                const value = e.target.value;

                axios.post(this.url_get_documents,{
                    client_id: this.model.client_id,
                    warehouse_document_type_id: value,
                }).then(res => {
                    const { data } = res;
                    this.documents = data;
                }).catch(err => {
                    console.log(err);
                    console.log(err.response);
                });
            },
            changeSaldo(e) {
                const value = e.target.value;

                const item = this.saldos_favor.find(element => element.id == value);

                if (item) {
                    this.model.amount = item.total_perception;
                    this.model.currency_id = item.currency_id;
                    document.getElementById('amount').disabled = true;
                    document.getElementById('currency_id').disabled = true;
                };
            },
            changeDocument(e) {
                const value = e.target.value;

                const item = this.documents.find(element => element.id == value);

                if (item) {
                    this.model.amount = item.total_perception;
                    this.model.currency_id = item.currency_id;
                    document.getElementById('amount').disabled = true;
                    document.getElementById('currency_id').disabled = true;
                };
            },
        }
    };
</script>