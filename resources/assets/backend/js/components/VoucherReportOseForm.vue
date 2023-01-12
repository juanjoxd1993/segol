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
        <form class="kt-form" @submit.prevent="$parent.formController(url, $event, model)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Compañía:</label>
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{ company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Documento:</label>
                            <select class="form-control" name="voucher_type" id="voucher_type" v-model="model.voucher_type" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Selecionar</option>
                                <option v-for="voucher_type in voucher_types" :value="voucher_type.id" v-bind:key="voucher_type.id">{{ voucher_type.name }}</option>
                            </select>
                            <div id="voucher_type-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Serie:</label>
                            <input type="text" class="form-control" name="serie" id="serie" v-model="model.serie" @focus="$parent.clearErrorMsg($event)">
                            <div id="serie-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Desde:</label>
                            <datetime
                                v-model="model.since_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                name="since_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="current_date"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Hasta:</label>
                            <datetime
                                v-model="model.to_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                name="to_date"
                                value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="current_date"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="to_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2" v-if="model.voucher_type == 2">
                        <div class="form-group">
                            <label class="form-control-label">Nº Inicial:</label>
                            <input type="text" class="form-control" name="initial_number" id="initial_number" v-model="model.initial_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="initial_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2" v-if="model.voucher_type == 2">
                        <div class="form-group">
                            <label class="form-control-label">Nº Final:</label>
                            <input type="text" class="form-control" name="final_number" id="final_number" v-model="model.final_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="final_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2" v-if="model.voucher_type == 2">
                        <div class="form-group">
                            <label class="form-control-label">Serie de Pedido:</label>
                            <input type="text" class="form-control" name="order_series" id="order_series" v-model="model.order_series" @focus="$parent.clearErrorMsg($event)">
                            <div id="order_series-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2" v-if="model.voucher_type == 2">
                        <div class="form-group">
                            <label class="form-control-label">Nº de Pedido:</label>
                            <input type="text" class="form-control" name="order_number" id="order_number" v-model="model.order_number" @focus="$parent.clearErrorMsg($event)">
                            <div id="order_number-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
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
            companies: {
                type: Array,
                default: ''
            },
            voucher_types: {
                type: Array,
                default: ''
            },
            current_date: {
                type: String,
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
                    flag: true,
                    company_id: '',
                    voucher_type: '',
                    since_date: '',
                    to_date: '',
                    serie: '',
                    initial_number: '',
                    final_number: '',
                    order_series: '',
                    order_number: '',
                }
            }
        },
    };
</script>