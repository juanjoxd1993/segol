<template>

    <div>
        <!--begin::Liquidation Modal-->
        <div class="modal fade" id="modal-liquidation" tabindex="-1" role="dialog" aria-labelledby="liquidationModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="liquidationModal">{{ title_text }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Condición de Pago:</label>
                                        <select class="form-control" name="payment_id" id="payment_id" v-model="model.payment_id" @focus="$parent.clearErrorMsg($event)" v-on:change="checkPayment">
                                            <option value="">Seleccionar</option>
                                            <option v-for="payment in payments" :value="payment.id" v-bind:key="payment.id">{{ payment.name }}</option>
                                        </select>
                                        <div id="payment_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Forma de Pago:</label>
                                        <select class="form-control" name="payment_method_id" id="payment_method_id" v-model="model.payment_method" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="payment_method in payment_methods" :value="payment_method.id" v-bind:key="model.payment_method.id">{{ payment_method.name }}</option>
                                        </select>
                                        <div id="payment_method_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Moneda:</label>
                                        <select class="form-control" name="currency_id" id="currency_id" v-model="model.currency" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="currency in currencies" :value="currency.id" v-bind:key="currency.id">{{ currency.name }}</option>
                                        </select>
                                        <div id="currency_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="model.currency != '' && model.currency != 1">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo de Cambio:</label>
                                        <input type="number" class="form-control" name="exchange_rate" id="exchange_rate" min="0" v-model="model.exchange_rate" @focus="$parent.clearErrorMsg($event)">
                                        <div id="exchange_rate-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="model.payment_method == 2 || model.payment_method == 3">
                                    <div class="form-group">
                                        <label class="form-control-label">Banco:</label>
                                        <select class="form-control" name="bank_account_id" id="bank_account_id" v-model="model.bank_account" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="bank_account in bank_accounts" :value="bank_account.id" v-bind:key="bank_account.id">{{ bank_account.name }}</option>
                                        </select>
                                        <div id="bank_account_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="model.payment_method == 1 || model.payment_method == 2 || model.payment_method == 3">
                                    <div class="form-group">
                                        <label class="form-control-label">Nº de Operación:</label>
                                        <input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)" v-on:change="manageOperationNumber">
                                        <div id="operation_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Monto:</label>
                                        <input type="number" class="form-control" name="amount" id="amount" min="0" v-model="model.amount" @focus="$parent.clearErrorMsg($event)">
                                        <div id="amount-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button id="add_payment" type="submit" class="btn btn-success" @click.prevent="addLiquidation(model)" :disabled="model.payment_method == 2">Agregar</button>
                                    <button type="button" class="btn btn-secondary" @click.prevent="resetLiquidation()">Cancelar</button>
                                    <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <table class="table table-vertical-middle table-layout-fixed">
                                        <thead>
                                            <tr>
                                                <th>Forma Pago</th>
                                                <th>Moneda</th>
                                                <th>Tipo Cambio</th>
                                                <th>Banco</th>
                                                <th>Nº Ope.</th>
                                                <th style="text-align:right;width:120px;">Monto</th>
                                                <th style="text-align:right;">Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in liquidations" v-bind:key="index">
                                                <td>{{ item.payment_method.name }}</td>
                                                <td>{{ item.currency.name }}</td>
                                                <td>{{ item.exchange_rate }}</td>
                                                <td>{{ item.bank_account.name }}</td>
                                                <td>{{ item.operation_number }}</td>
                                                <td style="text-align:right;width:120px;">{{ item.amount }}</td>
                                                <td style="text-align:right;">
                                                    <a href="#" class="btn-sm btn btn-label-danger btn-bold" @click.prevent="removeLiquidation(index)">
                                                        <i class="la la-trash-o pr-0"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td style="text-align:right;width:120px;font-weight:600;">{{ addTotals }}</td>
                                                <td style="text-align:right;"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" @click.prevent="addLiquidations()">Crear</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->
    </div>
</template>

<script>
    import EventBus from '../event-bus';
    export default {
        props: {
            payment_methods: {
                type: Array,
                default: '',
            },
            currencies: {
                type: Array,
                default: '',
            },
            url_get_bank_accounts: {
                type: String,
                default: ''
            },
            payments: {
                type: Array,
                default: [],
            },
            payment_cash: {
                type: Number,
                default: ''
            },
            payment_credit: {
                type: Number,
                default: ''
            }
        },
        data() {
            return {
                title_text: '',
                liquidation_datatable: undefined,
                model: {
                    payment_method: '',
                    currency: '',
                    exchange_rate: '',
                    bank_account: '',
                    operation_number: '',
                    amount: '',
                },
                liquidations: [],
                bank_accounts: [],
                total: '',
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('liquidation_modal', function() {
                let vm = this;

                this.title_text = this.$store.state.sale.client_name + ' - Total: ' + this.$store.state.sale.total_perception;

                this.model.payment_method = '';
                this.model.currency = '';
                this.model.exchange_rate = '';
                this.model.bank_account = '';
                this.model.operation_number = '';
                this.model.amount = '';

                $('#modal-liquidation').modal('show');
            }.bind(this));
        },
        watch: {
            'model.currency': function(val) {
                if ( this.model.payment_method == 2 || this.model.payment_method == 3 ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_bank_accounts, {
                        company_id: this.$store.state.model.company_id,
                        currency_id: val
                    }).then(response => {
                        // console.log(response);
                        this.model.bank_account = '';
                        this.bank_accounts = response.data;

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
            'model.payment_method': function(val) {
                if ( val.id == 2 || val.id == 3 ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_bank_accounts, {
                        company_id: this.$store.state.model.company_id,
                        currency_id: this.model.currency.id
                    }).then(response => {
                        // console.log(response);
                        this.model.bank_account = '';
                        this.bank_accounts = response.data;

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            }
        },
        computed: {
            addTotals() {
                return this.liquidations.reduce((a, {amount}) => Number(a) + Number(amount), 0);
            },
        },
        methods: {
            addLiquidation: function() {
                let liquidation = JSON.parse(JSON.stringify(this.model));
                let text = '';

                if ( liquidation.payment_method == '' && liquidation.payment_id == this.payment_cash) {
                    text = 'Debe seleccionar una Forma de Pago.';
                } else if ( liquidation.currency == '' && liquidation.payment_id == this.payment_cash) {
                    text = 'Debe seleccionar una Moneda.';
                } else if ( ( liquidation.payment_method == 2 || liquidation.payment_method == 3 ) && liquidation.bank_account == '' ) {
                    text = 'Debe seleccionar un Banco.';
                } else if ( ( liquidation.payment_method == 2 || liquidation.payment_method == 3 ) && liquidation.operation_number == '' ) {
                    text = 'El Nº de Operación es obligatorio.';
                } else if ( ( liquidation.currency == 2 || liquidation.currency == 3 ) && liquidation.exchange_rate == '' ) {
                    text = 'El Tipo de Cambio es obligatorio.';
                } else if ( (liquidation.amount == '' || liquidation.amount <= 0 ) && liquidation.payment_id == this.payment_cash) {
                    text = 'El Monto es obligatorio y debe ser mayor a 0.';
                } else if (this.$store.state.sale.payment_id != this.payment_credit && liquidation.payment_id == this.payment_credit) {
                    text = 'El cliente no cuenta con crédito disponible';
                }
                
               
				

                if ( text != '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: text,
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else {
                    liquidation.exchange_rate = ( liquidation.exchange_rate != '' ? accounting.toFixed(liquidation.exchange_rate, 4) : '' );
                    liquidation.amount = accounting.toFixed(liquidation.amount, 4);

                    const payment_method = this.payment_methods.filter(item => item.id === liquidation.payment_method)[0];
                    const currency = this.currencies.filter(item => item.id === liquidation.currency)[0];
                    const bank_account = this.bank_accounts.filter(item => item.id === liquidation.bank_account)[0];

                    if (payment_method) {
                        liquidation.payment_method = payment_method;
                    };

                    if (currency) {
                        liquidation.currency = currency;
                    };

                    if (bank_account) {
                        liquidation.bank_account = bank_account;
                    };

                    this.liquidations.push(liquidation);

                    this.model.payment_method = '';
                    this.model.currency = '';
                    this.model.exchange_rate = '';
                    this.model.bank_account = '';
                    this.model.operation_number = '';
                    this.model.amount = '';
                }
            },
            resetLiquidation: function() {
                this.model.payment_method = '';
                this.model.currency = '';
                this.model.exchange_rate = '';
                this.model.bank_account = '';
                this.model.operation_number = '';
                this.model.amount = '';
            },
            removeLiquidation: function(index) {
                this.liquidations.splice(index, 1);
            },
            addLiquidations: function() {
				let error = 0;

				if ( this.$store.state.sale.payment_id !== 2 && accounting.unformat(this.addTotals) !== accounting.unformat(this.$store.state.sale.total_perception)) {
					error = 1;
					Swal.fire({
                        title: '¡Error!',
                        text: 'Debe liquidar por el total de la Venta.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				}

               
				if ( this.$store.state.sale.payment_id == 2 && accounting.unformat(this.addTotals) > accounting.unformat(this.$store.state.sale.total_perception)) {
					error = 1;
					Swal.fire({
                        title: '¡Error!',
                        text: 'El Pago a cuenta no puede exceder el Total de la Venta.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
				}
				
				if ( this.$store.state.sale.payment_id !== 2 && this.liquidations < 1 ) {
					error = 1;
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe agregar al menos 1 Forma de Pago.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                }

                if ( this.model.payment_id == this.payment_credit && (this.$store.state.sale.total_perception - accounting.unformat(this.addTotals))> this.$store.state.sale.credit_limit ) {
                    error = 1;
                    Swal.fire({
                        title: '¡Error!',
                        text: 'La línea del crédito del cliente es insuficiente.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                }  else if ((this.$store.state.sale.total_perception - accounting.unformat(this.addTotals)) > this.$store.state.sale.credit_limit) {
                    error = 1;
                     Swal.fire({
                        title: '¡Error!',
                        text: 'La línea del crédito del cliente es insuficiente.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                }

				if ( error == 0 ) {
					let liquidations = JSON.parse(JSON.stringify(this.liquidations));
					let sale = JSON.parse(JSON.stringify(this.$store.state.sale));

					this.$store.commit('addLiquidations', liquidations);
					this.$store.commit('addSales');
					
					sale.details.forEach(element => {
						let article_id = element.article_id;
						let quantity = element.quantity;

						this.$store.commit('changeBalanceValue', {
							article_id,
							quantity
						});
					});

					EventBus.$emit('refresh_table_sale');

					this.liquidations = [];
					this.title_text = '';
					this.model.payment_method = '';
					this.model.currency = '';
					this.model.exchange_rate = '';
					this.model.bank_account = '';
					this.model.operation_number = '';
					this.model.amount = '';

					$('#modal-liquidation').modal('hide');

					EventBus.$emit('refresh_table_liquidation');
                }
            },
            checkPayment() {
                switch (this.model.payment_id) {
                    case this.payment_cash:
                        $('#modal-liquidation').find('#payment_method_id').prop('disabled', false);
                        $('#modal-liquidation').find('#currency_id').prop('disabled', false);
                        $('#modal-liquidation').find('#amount').prop('disabled', false);
                        break;
                    case this.payment_credit:
                        $('#modal-liquidation').find('#payment_method_id').prop('disabled', true);
                        $('#modal-liquidation').find('#currency_id').prop('disabled', true);
                        $('#modal-liquidation').find('#amount').prop('disabled', true);
                        break;
                    default:
                        $('#modal-liquidation').find('#payment_method_id').prop('disabled', false);
                        $('#modal-liquidation').find('#currency_id').prop('disabled', false);
                        $('#modal-liquidation').find('#amount').prop('disabled', false);
                        break;
                }
            },
            manageOperationNumber() {
                if (
                    this.model.payment_method === 1 ||
                    this.model.payment_method === 2 ||
                    this.model.payment_method === 3
                ) {
                    EventBus.$emit('loading', true);
                    $('#add_payment').prop('disabled', true);
                    $('#operation_number-error').hide();
                    $('#operation_number-error').text('');

                    // El metodo no existe por eso devuelve un error y devuelve que nro de operacion ya ha sido usado aunque eso no deberia ser asi incluso con un error hay que crear el metodo en el controller de liquidacion final
                    axios.get('/facturacion/liquidaciones-final/get-op-number', {
                        params: {
                            operation_number: this.model.operation_number,
                            payment_method: this.model.payment_method,
                            bank_account: this.model.bank_account
                        }
                    }).then(response => {
                        console.log('bien');
                        EventBus.$emit('loading', false);
                        $('#add_payment').prop('disabled', false);
                        $('#operation_number-error').text('');
                        $('#operation_number-error').hide();
                    }).catch(error => {
                        EventBus.$emit('loading', false);
                        // $('#add_payment').prop('disabled', true);
                        // $('#operation_number-error').text('El Nro. de Operación del Deposito ya fue usado anteriormente');
                        // $('#operation_number-error').show();
                    });
                    // Esto solo se debe activar al validar que el nro de operacion sea unico
                    $('#add_payment').prop('disabled', false);
                    $('#operation_number-error').text('');
                    $('#operation_number-error').hide();
                }
            }
        }
    };
</script>