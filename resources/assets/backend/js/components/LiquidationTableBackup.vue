<template>
    <div>
        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Artículos por Liquidar
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
                <!--begin: Datatable -->
                <div class="kt-datatable"></div>
                <!--end: Datatable -->
            </div>
        </div>
        <!--end::Portlet-->

        <!--begin::Portlet-->
        <div class="kt-portlet" v-if="show_table">
            <div class="kt-portlet__head">
                <div class="kt-portlet__head-label">
                    <h3 class="kt-portlet__head-title">
                        Ventas
                    </h3>
                </div>
                <div class="kt-portlet__head-toolbar">
                    <div class="kt-portlet__head-wrapper">
                        <div class="dropdown dropdown-inline">
                            <a href="#" class="btn btn-success" v-show="flag_add" @click.prevent="openModal()">
                                <i class="la la-plus"></i> Agregar venta
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kt-portlet__body kt-portlet__body--fit" @click="manageActions">
                <!--begin: Datatable -->
                <div class="kt-datatable-liquidation"></div>
                <!--end: Datatable -->
            </div>
        </div>
        <!--end::Portlet-->

        <!--begin::Modal-->
        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">{{ title_text }} venta</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="kt-portlet__body">
                            <div class="row">
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Cliente:</label>
                                        <select class="form-control kt-select2" name="client_id" id="client_id" v-model="sale.client_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                        </select>
                                        <div id="client_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo Referencia:</label>
                                        <select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id" v-model="sale.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
                                        </select>
                                        <div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Serie de Referencia:</label>
                                        <input type="text" class="form-control" name="referral_serie_number" id="referral_serie_number" v-model="sale.referral_serie_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_serie_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Número de Referencia:</label>
                                        <input type="text" class="form-control" name="referral_voucher_number" id="referral_voucher_number" v-model="sale.referral_voucher_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="referral_voucher_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="kt-separator kt-separator--space kt-separator--dashed"></div>
                                    <table class="table table-vertical-middle">
                                        <thead>
                                            <tr>
                                                <th>Artículo</th>
                                                <th style="text-align:right;width:120px;">Precio</th>
                                                <th style="text-align:right;width:120px;">Cantidad</th>
                                                <th style="text-align:right;width:120px;">Valor Venta</th>
                                                <th style="text-align:right;width:120px;">Percepción</th>
                                                <th style="text-align:right;width:120px;">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in sale.details" v-bind:key="index">
                                                <td>{{ item.article_name }}</td>
                                                <td style="text-align:right;width:120px;">{{ item.price_igv }}</td>
                                                <td style="text-align:right;width:120px;">
                                                    <input type="tel" class="form-control text-right" v-model="item.quantity" @keypress="quantityChange($event, sale.details[index])">
                                                </td>
                                                <td style="text-align:right;width:120px;">{{ item.sale_value }}</td>
                                                <td style="text-align:right;width:120px;">{{ item.igv_perception }}</td>
                                                <td style="text-align:right;width:120px;">{{ item.total_perception }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td style="text-align:right;width:120px;font-weight:600;"></td>
                                                <td style="text-align:right;width:120px;font-weight:600;"></td>
                                                <td style="text-align:right;width:120px;font-weight:600;">{{ sale.total }}</td>
                                                <td style="text-align:right;width:120px;font-weight:600;">{{ sale.perception }}</td>
                                                <td style="text-align:right;width:120px;font-weight:600;">{{ sale.total_perception }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" v-if="sale.payment_id == 1" @click.prevent="liquidationModal()">Liquidar</button>
                        <button type="submit" class="btn btn-success" v-if="sale.payment_id == 2" @click.prevent="addSale()">Crear</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Modal-->

        <!--begin::Liquidation Modal-->
        <div class="modal fade" id="liquidation-modal" tabindex="-1" role="dialog" aria-labelledby="liquidationModal" aria-hidden="true">
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
                                        <label class="form-control-label">Forma de Pago:</label>
                                        <select class="form-control" name="payment_method_id" id="payment_method_id" v-model="liquidation.payment_method" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="payment_method in payment_methods" :value="payment_method" v-bind:key="payment_method.id">{{ payment_method.name }}</option>
                                        </select>
                                        <div id="payment_method_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Moneda:</label>
                                        <select class="form-control" name="currency_id" id="currency_id" v-model="liquidation.currency" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="currency in currencies" :value="currency" v-bind:key="currency.id">{{ currency.name }}</option>
                                        </select>
                                        <div id="currency_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="liquidation.currency != '' && liquidation.currency.id != 1">
                                    <div class="form-group">
                                        <label class="form-control-label">Tipo de Cambio:</label>
                                        <input type="text" class="form-control" name="exchange_rate" id="exchange_rate" v-model="liquidation.exchange_rate" @focus="$parent.clearErrorMsg($event)">
                                        <div id="exchange_rate-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="liquidation.payment_method.id == 2 || liquidation.payment_method.id == 3">
                                    <div class="form-group">
                                        <label class="form-control-label">Banco:</label>
                                        <select class="form-control" name="bank_account_id" id="bank_account_id" v-model="liquidation.bank_account" @focus="$parent.clearErrorMsg($event)">
                                            <option value="">Seleccionar</option>
                                            <option v-for="bank_account in bank_accounts" :value="bank_account" v-bind:key="bank_account.id">{{ bank_account.name }}</option>
                                        </select>
                                        <div id="bank_account_id-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3" v-if="liquidation.payment_method.id == 2 || liquidation.payment_method.id == 3">
                                    <div class="form-group">
                                        <label class="form-control-label">Nº de Operación:</label>
                                        <input type="text" class="form-control" name="operation_number" id="operation_number" v-model="liquidation.operation_number" @focus="$parent.clearErrorMsg($event)">
                                        <div id="operation_number-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Monto:</label>
                                        <input type="tel" class="form-control" name="amount" id="amount" v-model="liquidation.amount" @focus="$parent.clearErrorMsg($event)">
                                        <div id="amount-error" class="error invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-success" @click.prevent="addLiquidation(liquidation)">Agregar</button>
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
                                                <th>Opciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in sale.liquidations" v-bind:key="index">
                                                <td>{{ item.payment_method.name }}</td>
                                                <td>{{ item.currency.name }}</td>
                                                <td>{{ item.exchange_rate }}</td>
                                                <td>{{ item.bank_account.name }}</td>
                                                <td>{{ item.operation_number }}</td>
                                                <td style="text-align:right;width:120px;">{{ item.amount }}</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td style="text-align:right;width:120px;font-weight:600;"></td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success" @click.prevent="addSale()">{{ title_text }}</button>
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
            warehouse_document_types: {
                type: Array,
                default: '',
            },
            payment_methods: {
                type: Array,
                default: '',
            },
            currencies: {
                type: Array,
                default: '',
            },
            url: {
                type: String,
                default: ''
            },
            url_get_clients: {
                type: String,
                default: ''
            },
            url_get_client_price_list: {
                type: String,
                default: ''
            },
            url_get_bank_accounts: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                edit_flag: 0,
                title_text: '',
                button_text: '',
                datatable: undefined,
                sale_datatable: undefined,
                flag_add: false,
                liquidation: [{'client_id': 1, "business_name": 'Gabriel Sosa', "payment_condition_name": 'Contado', "payment_method_name": 'Depósito', 'operation_number': '5484596365', 'currency_short_name': '$', 'exchange_rate': '3.34', 'total': '352,000.0000', 'items': [{'article_code': 25, 'article_name': 'GLP', 'price': '0.574', 'quantity': '1,000.0000', 'sale_value': '574.0000', 'igv': '103.32', 'total': '677.3200', 'perception': '33.866', 'article_id': 5}]}],
                model: {},
                sales: [],
                liquidation: {
                    payment_method: '',
                    currency: '',
                    exchange_rate: '',
                    bank_account: '',
                    operation_number: '',
                    amount: '',
                },
                sale: {
                    client_id: '',
                    client_name: '',
                    warehouse_document_type_id: '',
                    referral_serie_number: '',
                    referral_voucher_number: '',
                    details: [],
                    liquidations: [],
                    total: '',
                    perception: '',
                    total_perception: '',
                    payment_id: '',
                },
                show_table: false,
                warehouse_part: '',
                bank_accounts: [],
            }
        },
        created() {

        },
        mounted() {
            this.newSelect2();

            EventBus.$on('show_table', function(response) {
                this.show_table = true;
                this.flag_add = true;
                this.model = response;

                axios.post(this.url, {
                    model: this.model,
                }).then(response => {
                    // console.log(response.data);
                    this.warehouse_part = response.data;
                    
                    if ( this.datatable == undefined ) {
                        this.fillTableX();
                    } else {
                        this.datatable.originalDataSet = this.warehouse_part;
                        this.datatable.load();
                    }

                    if ( this.sale_datatable == undefined ) {
                        this.fillLiquidationTable();
                    } else {
                        this.sale_datatable.originalDataSet = this.sales;
                        this.sale_datatable.load();
                    }

                    EventBus.$emit('loading', false);
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
            }.bind(this));

            EventBus.$on('refresh_table', function() {
                if ( this.datatable != undefined ) {
                    this.datatable.load();
                }

                if ( this.sale_datatable != undefined ) {
                    this.sale_datatable.load();
                }
            }.bind(this));

            EventBus.$on('create_modal', function() {
                let vm = this;

                this.title_text = 'Crear';
                this.sale.client_id = '';
                this.sale.client_name = '';
                this.sale.warehouse_document_type_id = '';
                this.sale.referral_serie_number = '';
                this.sale.referral_voucher_number = '';
                this.sale.details = [];
                this.sale.liquidations = [];
                this.sale.total = '';
                this.sale.perception = '';
                this.sale.total_perception = '';
                this.sale.payment_id = '';

                this.warehouse_part.forEach(element => {
                    let item = {};

                    item.item_number = element.item_number;
                    item.article_id = element.article_id;
                    item.article_name = element.article_name;
                    item.price_igv = 0;
                    item.quantity = 0;
                    item.sale_value = 0;
                    item.perception = 0;
                    item.igv_perception = 0;
                    item.igv_perception_percentage = 0;
                    item.total_perception = 0;

                    vm.sale.details.push(item);
                });

                $('#client_id').val(null).trigger('change');

                $('#modal').modal('show');
            }.bind(this));

            EventBus.$on('edit_modal', data => {
                this.edit_flag = 1;
                this.title_text = 'Actualizar';

                axios.post(this.url_get_clients, {
                    client_id: this.sale.client_id
                }).then(response => {
                    let option = new Option(response.data.text, response.data.id, true, true);
                    $('#client_id').append(option).trigger('change');
                    $('#client_id').trigger({
                        type: 'select2:select',
                        params: {
                            data: response.data
                        }
                    });
                }).catch(error => {
                    console.log(error);
                    console.log(error.response);
                });
                
                $('#modal').modal('show');
            });

            EventBus.$on('liquidation_modal', function() {
                let vm = this;
                $('#modal').modal('hide');
                this.title_text = this.sale.client_name + ' - Total: ' + this.sale.total_perception;

                this.liquidation.payment_method = '';
                this.liquidation.currency = '';
                this.liquidation.exchange_rate = '';
                this.liquidation.bank_account = '';
                this.liquidation.operation_number = '';
                this.liquidation.amount = '';

                $('#liquidation-modal').modal('show');
            }.bind(this));
        },
        watch: {
            'liquidation.currency': function(val) {
                if ( this.liquidation.payment_method.id == 2 || this.liquidation.payment_method.id == 3 ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_bank_accounts, {
                        company_id: this.model.company_id,
                        currency_id: val.id
                    }).then(response => {
                        // console.log(response);
                        this.liquidation.bank_account = '';
                        this.bank_accounts = response.data;

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response);
                    });
                }
            },
            'liquidation.payment_method': function(val) {
                if ( val.id == 2 || val.id == 3 ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_bank_accounts, {
                        company_id: this.model.company_id,
                        currency_id: this.liquidation.currency.id
                    }).then(response => {
                        // console.log(response);
                        this.liquidation.bank_account = '';
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

        },
        methods: {
            openModal: function() {
                let balance = this.warehouse_part.find(element => element.balance_converted_amount > 0);
                if ( balance ) {
                    EventBus.$emit('create_modal');
                } else {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'No hay Saldo por liquidar.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                }
            },
            liquidationModal: function() {
                if ( this.sale.total == '' ) {
                    Swal.fire({
                        title: '¡Error!',
                        text: 'Debe digitar al menos 1 Artículo.',
                        type: "error",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else if ( this.sale.total == 0 ) {
                    Swal.fire({
                        title: '¡Cuidado!',
                        text: 'La Cantidad debe ser mayor a 0.',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: false,
                        confirmButtonText: 'Ok',
                    });
                } else {
                    EventBus.$emit('liquidation_modal');
                }
            },
            quantityChange: function(event, detail) {
                let isNumber = this.$parent.isNumber(event);
                
                if ( isNumber ) {
                    let perception_percentage = detail.igv_perception_percentage / 100;
                    let quantity = this.$parent.numberFormat(1, detail.quantity, 4);
                    let warehouse_part = this.warehouse_part.find(element => detail.article_id == element.article_id );
                    let balance_converted_amount = this.$parent.numberFormat(1, warehouse_part.balance_converted_amount, 4);

                    if ( quantity > balance_converted_amount ) {
                        Swal.fire({
                            title: '¡Cuidado!',
                            text: 'La Cantidad de ' + warehouse_part.article_name + ' supera el Saldo.',
                            type: "warning",
                            heightAuto: false,
                            showCancelButton: false,
                            confirmButtonText: 'Ok',
                        });

                        $(event.target).parents('.modal-content').find('button[type="submit"]').attr('disabled', true);
                    } else {
                        $(event.target).parents('.modal-content').find('button[type="submit"]').removeAttr('disabled');
                    }

                    detail.sale_value = (event.target.value * detail.price_igv).toFixed(4);
                    detail.igv_perception = ( detail.perception == 1 ? (detail.sale_value * perception_percentage).toFixed(2) : (0).toFixed(2) );
                    detail.total_perception = (Number(detail.sale_value) + Number(detail.igv_perception)).toFixed(2);
                    this.sale.total = (this.sale.details.reduce((a, {sale_value}) => Number(a) + Number(sale_value), 0)).toFixed(4);
                    this.sale.perception = (this.sale.details.reduce((a, {igv_perception}) => Number(a) + Number(igv_perception), 0)).toFixed(2);
                    this.sale.total_perception = (this.sale.details.reduce((a, {total_perception}) => Number(a) + Number(total_perception), 0)).toFixed(2);
                } else {
                    event.preventDefault();
                }

                // console.log(detail);
            },
            addLiquidation: function() {
                let liquidation = JSON.parse(JSON.stringify(this.liquidation))
                let text = '';

                if ( liquidation.payment_method == '' ) {
                    text = 'Debe seleccionar una Forma de Pago.';
                } else if ( liquidation.currency == '' ) {
                    text = 'Debe seleccionar una Moneda.';
                } else if ( ( liquidation.payment_method.id == 2 || liquidation.payment_method.id == 3 ) && liquidation.bank_account == '' ) {
                    text = 'Debe seleccionar un Banco.';
                } else if ( ( liquidation.payment_method.id == 2 || liquidation.payment_method.id == 3 ) && liquidation.operation_number == '' ) {
                    text = 'El Nº de Operación es obligatorio.';
                } else if ( liquidation.currency.id == 2 || liquidation.currency.id == 3 ) {
                    text = 'El Tipo de Cambio es obligatorio.';
                } else if ( liquidation.amount == '' || liquidation.amount <= 0 ) {
                    text = 'El Monto es obligatorio y debe ser mayor a 0.';
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
                    liquidation.amount = this.$parent.numberFormat(2, Number(liquidation.amount), 4);
                    liquidation.exchange_rate = ( liquidation.exchange_rate != '' ? this.$parent.numberFormat(2, Number(liquidation.exchange_rate), 4) : '' );

                    this.sale.liquidations.push(liquidation);
                    
                    this.liquidation.payment_method = '';
                    this.liquidation.currency = '';
                    this.liquidation.exchange_rate = '';
                    this.liquidation.bank_account = '';
                    this.liquidation.operation_number = '';
                    this.liquidation.amount = '';
                }
            },
            resetLiquidation: function() {
                this.liquidation.payment_method = '';
                this.liquidation.currency = '';
                this.liquidation.exchange_rate = '';
                this.liquidation.bank_account = '';
                this.liquidation.operation_number = '';
                this.liquidation.amount = '';
            },
            addSale: function() {
                let sale = JSON.parse(JSON.stringify(this.sale));

                if ( sale.payment_id == 1 ) {
                    if ( Array.isArray(sale.liquidations) && sale.liquidations.length < 1 ) {
                        Swal.fire({
                            title: '¡Error!',
                            text: 'Debe agregar al menos 1 forma de pago.',
                            type: "error",
                            heightAuto: false,
                            showCancelButton: false,
                            confirmButtonText: 'Ok',
                        });

                        throw '';
                    }
                }

                if ( this.edit_flag == 0 ) {
                    this.sales.push(sale);
                } else if ( this.edit_flag == 1 ) {
                    let client_id = sale.client_id;
                    let current_sale = this.sales.filter((element, index) => {
                        if ( element.client_id == client_id ) {
                            this.sales[index] = sale;
                        }
                    });
                }

                this.sale_datatable.originalDataSet = this.sales;
                this.sale_datatable.load();

                sale.details.filter(element => {
                    if ( element.quantity > 0 ) {
                        this.warehouse_part.find(item => {
                            if ( element.article_id == item.article_id ) {
                                let sale_converted_amount = this.$parent.numberFormat(1, item.sale_converted_amount, 4);
                                let balance_converted_amount = this.$parent.numberFormat(1, item.balance_converted_amount, 4);
                                
                                sale_converted_amount += this.$parent.numberFormat(1, element.quantity, 4);
                                balance_converted_amount -= this.$parent.numberFormat(1, element.quantity, 4);

                                item.sale_converted_amount = this.$parent.numberFormat(2, sale_converted_amount, 4);
                                item.balance_converted_amount = this.$parent.numberFormat(2, balance_converted_amount, 4);
                            }
                        });

                        this.datatable.originalDataSet = this.warehouse_part;
                        this.datatable.load();
                    }
                });

                this.sale.client_id = '';
                this.sale.client_name = '';
                this.sale.warehouse_document_type_id = '';
                this.sale.referral_serie_number = '';
                this.sale.referral_voucher_number = '';
                this.sale.details = [];
                this.sale.liquidations = [];
                this.sale.total = '';
                this.sale.perception = '';
                this.sale.total_perception = '';
                this.sale.payment_id = '';

                $('#client_id').val(null).trigger('change');
                $('#liquidation-modal').modal('hide');
            },
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
                    vm.sale.client_id = parseInt(selected_element.val());

                    if ( vm.edit_flag == 0 ) {
                        EventBus.$emit('loading', true);

                        let article_ids = vm.warehouse_part.map(element => element.article_id);
                        
                        axios.post(vm.url_get_client_price_list, {
                            client_id: vm.sale.client_id,
                            article_ids: article_ids
                        }).then(response => {
                            if ( response.data.items.length ) {
                                vm.sale.client_name = response.data.client.business_name;
                                vm.sale.payment_id = response.data.client.payment_id;

                                response.data.items.map(element => {
                                    vm.sale.details.find(item => {
                                        if ( element.article_id == item.article_id ) {
                                            item.price_igv = element.price_igv;
                                            item.perception = element.perception;
                                            item.igv_perception_percentage = element.igv_perception_percentage;
                                        }
                                    });
                                });

                                let articles = [];

                                vm.warehouse_part.filter(element => {
                                    if ( response.data.items.find(item => item.article_id == element.article_id) === undefined ) {
                                        articles.push(element.article_name);
                                    }
                                });
                                
                                if ( Array.isArray(articles) && articles.length ) {
                                    let text = '';
                                    vm.sale.payment_id = '';

                                    articles.forEach(element => {
                                        text += '<p>El Artículo ' + element + ' no tiene un precio para el Cliente seleccionado.</p>';
                                    });

                                    Swal.fire({
                                        title: '¡Cuidado!',
                                        html: text,
                                        type: "warning",
                                        heightAuto: false,
                                        showCancelButton: false,
                                        confirmButtonText: 'Ok',
                                    });
                                }
                            } else {
                                vm.sale.payment_id = '';
                                vm.sale.total = 0;
                                vm.sale.perception = 0;
                                vm.sale.total_perception = 0;
                                vm.sale.details.map(element => {
                                    element.price_igv = 0;
                                    element.quantity = 0;
                                    element.sale_value = 0;
                                    element.igv_perception = 0;
                                    element.total_perception = 0;
                                });

                                Swal.fire({
                                    title: '¡Error!',
                                    text: 'El Cliente no tiene precios para estos Artículos.',
                                    type: "error",
                                    heightAuto: false,
                                    showCancelButton: false,
                                    confirmButtonText: 'Ok',
                                });
                            }                        

                            EventBus.$emit('loading', false);
                        }).catch(error => {
                            console.log(error);
                            console.log(error.response);
                        });
                    } else if ( vm.edit_flag == 1 ) {
                        EventBus.$emit('loading', false);
                    }
                }).on('select2:unselect', function(e) {
                    vm.sale.client_id = '';
                    vm.sale.client_name = '';
                    vm.sale.payment_id = '';
                    vm.sale.total = 0;
                    vm.sale.perception = 0;
                    vm.sale.total_perception = 0;
                    vm.sale.details.map(element => {
                        element.price_igv = 0;
                        element.quantity = 0;
                        element.sale_value = 0;
                        element.igv_perception = 0;
                        element.total_perception = 0;
                    });
                });
            },
            fillTableX: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.datatable = $('.kt-datatable').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.warehouse_part,
                        pageSize: 10,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: false,

                    search: {
                        input: $('#generalSearch'),
                    },

                    translate: {
                        records: {
                            processing: 'Espere porfavor...',
                            noRecords: 'No hay registros'
                        },
                        toolbar: {
                            pagination: {
                                items: {
                                    default: {
                                        first: 'Primero',
                                        prev: 'Anterior',
                                        next: 'Siguiente',
                                        last: 'Último',
                                        more: 'Más páginas',
                                        input: 'Número de página',
                                        select: 'Seleccionar tamaño de página'
                                    },
                                    info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                                }
                            }
                        }
                    },

                    rows: {
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'article_code',
                            title: 'Código',
                            width: 60,
                            textAlign: 'center',
                        },
                        {
                            field: 'article_name',
                            title: 'Artículo',
                            width: 300,
                        },
                        {
                            field: 'presale_converted_amount',
                            title: 'Pre-Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'return_converted_amount',
                            title: 'Retorno',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'sale_converted_amount',
                            title: 'Venta',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'balance_converted_amount',
                            title: 'Saldo',
                            width: 120,
                            textAlign: 'right',
                        },
                        {
                            field: 'id',
                            title: 'ID',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        // {
                        //     field: 'options',
                        //     title: 'Opciones',
                        //     sortable: false,
                        //     width: 80,
                        //     overflow: 'visible',
                        //     autoHide: false,
                        //     textAlign: 'right',
                        //     template: function(row) {
                        //         let actions = '<div class="actions">';
                        //             actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                        //                 actions += '<i class="la la-edit"></i>';
                        //             actions += '</a>';
                        //             actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                        //                 actions += '<i class="la la-trash"></i>';
                        //             actions += '</a>';
                        //         actions += '</div>';

                        //         return actions;
                        //     },
                        // },
                    ]
                });

                this.datatable.columns('id').visible(false);
            },
            fillLiquidationTable: function() {
                let vm = this;
                let token = document.head.querySelector('meta[name="csrf-token"]').content;

                this.sale_datatable = $('.kt-datatable-liquidation').KTDatatable({
                    // datasource definition
                    data: {
                        type: 'local',
                        source: vm.sales,
                        pageSize: 10,
                    },

                    // layout definition
                    layout: {
                        scroll: true, // enable/disable datatable scroll both horizontal and vertical when needed.
                        height: 400,
                        footer: false // display/hide footer
                    },

                    // column sorting
                    sortable: true,
                    pagination: false,

                    search: {
                        input: $('#generalSearch'),
                    },

                    translate: {
                        records: {
                            processing: 'Espere porfavor...',
                            noRecords: 'No hay registros'
                        },
                        toolbar: {
                            pagination: {
                                items: {
                                    default: {
                                        first: 'Primero',
                                        prev: 'Anterior',
                                        next: 'Siguiente',
                                        last: 'Último',
                                        more: 'Más páginas',
                                        input: 'Número de página',
                                        select: 'Seleccionar tamaño de página'
                                    },
                                    info: 'Mostrando {{start}} - {{end}} de {{total}} registros'
                                }
                            }
                        }
                    },

                    rows: {
                        autoHide: true,
                    },

                    // columns definition
                    columns: [
                        {
                            field: 'client_name',
                            title: 'Cliente',
                            width: 300,
                            textAlign: 'left',
                        },
                        {
                            field: 'total',
                            title: 'Valor Venta',
                            width: 150,
                            textAlign: 'right',
                        },
                        {
                            field: 'perception',
                            title: 'Percepción',
                            width: 150,
                            textAlign: 'right',
                        },
                        {
                            field: 'total_perception',
                            title: 'Total',
                            width: 150,
                            textAlign: 'right',
                        },
                        {
                            field: 'client_id',
                            title: 'ID',
                            width: 0,
                            overflow: 'hidden',
                            responsive: {
                                hidden: 'sm',
                                hidden: 'md',
                                hidden: 'lg',
                                hidden: 'xl'
                            }
                        },
                        {
                            field: 'options',
                            title: 'Opciones',
                            sortable: false,
                            width: 120,
                            overflow: 'visible',
                            autoHide: false,
                            textAlign: 'right',
                            template: function(row) {
                                let actions = '<div class="actions">';
                                    actions += '<a href="#" class="view btn btn-sm btn-clean btn-icon btn-icon-md" title="Ver">';
                                        actions += '<i class="la la-eye"></i>';
                                    actions += '</a>';
                                    actions += '<a href="#" class="edit btn btn-sm btn-clean btn-icon btn-icon-md" title="Editar">';
                                        actions += '<i class="la la-edit"></i>';
                                    actions += '</a>';
                                    actions += '<a href="#" class="delete btn btn-sm btn-clean btn-icon btn-icon-md" title="Eliminar">';
                                        actions += '<i class="la la-trash"></i>';
                                    actions += '</a>';
                                actions += '</div>';

                                return actions;
                            },
                        }
                    ]
                });

                this.sale_datatable.columns('client_id').visible(false);
            },
            manageActions: function(event) {
                if ( $(event.target).hasClass('delete') ) {
                    event.preventDefault();
                    let client_id = $(event.target).parents('tr').find('td[data-field="client_id"] span').html();

                    Swal.fire({
                        title: '¡Cuidado!',
                        text: '¿Seguro que desea eliminar el registro?',
                        type: "warning",
                        heightAuto: false,
                        showCancelButton: true,
                        confirmButtonText: 'Sí',
                        cancelButtonText: 'No'
                    }).then(result => {
                        EventBus.$emit('loading', true);

                        if ( result.value ) {
                            
                            this.sales.find(element => {
                                if ( element.client_id == client_id ) {
                                    let index = this.sales.indexOf(element);
                                    if ( index > -1 ) {
                                        this.sales.splice(index, 1);

                                        this.sale_datatable.originalDataSet = this.sales;
                                        this.sale_datatable.load();

                                        Swal.fire({
                                            title: '¡Ok!',
                                            text: 'Se ha eliminado correctamente',
                                            type: "success",
                                            heightAuto: false,
                                        });

                                        EventBus.$emit('loading', false);
                                    }
                                }
                            });
                        } else if ( result.dismiss == Swal.DismissReason.cancel ) {
                            EventBus.$emit('loading', false);
                        }
                    });
                } else if ( $(event.target).hasClass('edit') ) {
                    event.preventDefault();
                    EventBus.$emit('loading', true);
                    let client_id = $(event.target).parents('tr').find('td[data-field="client_id"] span').html();

                    this.sales.find(element => {
                        if ( element.client_id == client_id ) {
                            this.sale = JSON.parse(JSON.stringify(element));
                            EventBus.$emit('edit_modal', this.sale);
                        }
                    })
                }
            },
        }
    };
</script>