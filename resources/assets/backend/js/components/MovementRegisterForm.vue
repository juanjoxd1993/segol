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
                            <label class="form-control-label">Ingreso/Salida:</label>
                            <select class="form-control" name="movement_class_id" id="movement_class_id"
                                v-model="model.movement_class_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="movement_class in movement_classes" :value="movement_class.id"
                                    v-bind:key="movement_class.id">{{ movement_class.name }}</option>
                            </select>
                            <div id="movement_class_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Tipo Movimiento:</label>
                            <select class="form-control" name="movement_type_id" id="movement_type_id"
                                v-model="model.movement_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="movement_type in movement_types" :value="movement_type.id"
                                    v-bind:key="movement_type.id">{{ movement_type.name }}</option>
                            </select>
                            <div id="movement_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Almacén:</label>
                            <select class="form-control" name="warehouse_type_id" id="warehouse_type_id"
                                v-model="model.warehouse_type_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="warehouse_type in warehouse_types" :value="warehouse_type.id"
                                    v-bind:key="warehouse_type.id">{{ warehouse_type.name }}</option>
                            </select>
                            <div id="warehouse_type_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Compañía:</label>
                            <select value="1" class="form-control" name="company_id" id="company_id"
                                v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="" selected disabled>Seleccionar</option>
                                <option v-for="company in companies" :value="company.id"
                                    v-bind:key="company.id">{{ company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha de Emisión:</label>
                            <datetime v-model="model.since_date" placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'" input-id="since_date" name="since_date" value-zone="America/Lima"
                                zone="America/Lima" class="form-control" :max-datetime="this.max_datetime"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="since_date-error" class="error invalid-feedback"></div>
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
        warehouse_types: {
            type: Array,
            default: ''
        },
        companies: {
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
            model: {
                movement_class_id: '2',
                movement_type_id: '5',
                warehouse_type_id: '75',
                company_id: '2',
                since_date: ''
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
        formController: function (url, event) {
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
                $.each(obj, function (i, item) {
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