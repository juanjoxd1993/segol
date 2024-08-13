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
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{
            company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Flag Ose:</label>
                            <select class="form-control" name="flag_ose" id="flag_ose" v-model="model.flag_ose"
                                @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="0">No enviado</option>
                                <option value="1">Enviado</option>
                            </select>
                            <div id="flag_ose-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Tipo de Movimiento:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id"
                                v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option value="11">Pre-Venta</option>
                                <option value="12">Venta-Planta</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-control-label">Serie:</label>
                            <input type="text" class="form-control" name="serie" id="serie" v-model="model.serie"
                                @focus="$parent.clearErrorMsg($event)">
                            <div id="serie-error" class="error invalid-feedback"></div>
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
        url: {
            type: String,
            default: ''
        },
        user_name: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            model: {
                flag: true,
                company_id: '',
                serie: '',
                flag_ose: '',
                movement_type_id: ''
            }
        }
    },
};
</script>
