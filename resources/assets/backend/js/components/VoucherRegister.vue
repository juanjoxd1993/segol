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
                            <label class="form-control-label">Fecha de depósito:</label>
                            <datetime
                                v-model="model.bank_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                input-id="bank_date"
                                name="bank_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                this.current_date
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="bank_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                         <label class="form-control-label">Banco:</label>
                            <select class="form-control" name="bank" id="bank" v-model="model.bank" @focus="$parent.clearErrorMsg($event)">
                               <option disabled value="">Seleccionar</option>
                                <option v-for="bank in banks" :value="bank.id">{{ bank.name }}</option>
                            </select>
                            <div id="bank-error" class="error invalid-feedback"></div>
                      </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                         <label class="form-control-label">Cuenta:</label>
                            <select class="form-control" name="bank_account" id="bank_account" v-model="model.bank_account" @focus="$parent.clearErrorMsg($event)">
                               <option disabled value="">Seleccionar</option>
                                <option v-for="bank_account in bank_accounts" v-if="bank_account.bank_id == model.bank" :value="bank_account.id">{{ bank_account.account_number }}</option>
                            </select>
                            <div id="bank_account-error" class="error invalid-feedback"></div>
                      </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Número de operación:</label>
                            <input type="text" class="form-control" name="operation_number" id="operation_number" v-model="model.operation_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="operation_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Saldo total:</label>
                            <input type="text" class="form-control" name="total" id="total" v-model="model.total" @focus="$parent.clearErrorMsg($event)">
                            <div id="total-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kt-portlet__foot">
                <div class="kt-form__actions">
                    <div class="row">
                        <div class="col-12 kt-align-right">
                            <button type="submit" class="btn btn-primary">Guardar</button>
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
        name: 'VoucherRegister',
        props: {          
            banks: {
                type: Array,
                default: ''
            },
            bank_accounts: {
                type: Array,
                default: ''
            },
            url: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                model: {
                    bank: null,
                    bank_account: null,
                    bank_date: null,
                    operation_number: null,
                    total: null
                },
            }
        },
        created() {
        },
        mounted() {
        },
        watch: {
            
        },
        computed: {
        
        },
        methods: {
            formController: function(url, event) {
                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);

                axios.post(url, {
                  'model': this.model,
				 
                }).then(response => {

                    EventBus.$emit('loading', false);
                    Swal.fire({
							title: '¡Ok!',
							text: 'Se creó el registro correctamente.',
							type: "success",
							heightAuto: false,
						});

                        this.cleanFields();
					}).catch(error => {
                        $('.invalid-feedback').each(function() {
                            $(this).text('');
                            $(this).hide();
                        });

						EventBus.$emit('loading', false);

                        let errors = error.response.data.errors;

                        for (const field in errors) {
                            let formatted_field = field.replace('model.', '');
                            $('#' + formatted_field + '-error').show();
                            $('#' + formatted_field + '-error').text(errors[field][0]);
                        }
					});
            },
            cleanFields: function() {
                this.model.bank = null;
                this.model.bank_account = null;
                this.model.bank_date = null;
                this.model.operation_number = null;
                this.model.total = null;
            },
        }
    };
</script>