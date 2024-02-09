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
        <form class="kt-form" @submit.prevent="formController(url, $event)">
            <div class="kt-portlet__body">
                <div class="row">
                    <input type="hidden" name="warehouse_type_id" id="warehouse_type_id" v-model="model.warehouse_type_id">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Compañía:</label>
                            <select class="form-control" name="company_id" id="company_id" v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{ company.name }}</option>
                            </select>
                            <div id="company_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Nº de Parte:</label>
                            <select class="form-control" name="warehouse_movement_id" id="warehouse_movement_id" v-model="model.warehouse_movement_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="warehouse_movement in warehouse_movements" :value="warehouse_movement.id" v-bind:key="warehouse_movement.id">#{{ warehouse_movement.movement_number }} | {{ warehouse_movement.referral_guide_series }}-{{ warehouse_movement.referral_guide_number }} | {{ warehouse_movement.license_plate }} | {{ warehouse_movement.creation_date }}</option>
                            </select>
                            <div id="warehouse_movement_id-error" class="error invalid-feedback"></div>
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
            url_get_warehouse_movements: {
                type: String,
                default: ''
            },
        },
        data() {
            return {
                model: {
                    company_id: '',
                    warehouse_movement_id: '',
                    warehouse_type_id: 5,
                },
                warehouse_movements: [],
            }
        },
        created() {

        },
        mounted() {
            EventBus.$on('clear_form_sale', function() {
				$('.kt-form').find('input').prop('disabled', false);
				$('.kt-form').find('select').prop('disabled', false);
				$('.kt-form').find('button').prop('disabled', false);
				
                this.model = {
					company_id: '',
					warehouse_movement_id: '',
                    warehouse_type_id: 5,
				}
            }.bind(this));
        },
        watch: {
            'model.company_id': function(val) {
                if ( val != '' ) {
                    EventBus.$emit('loading', true);

                    axios.post(this.url_get_warehouse_movements, {
                        company_id: this.model.company_id
                    }).then(response => {
                        // console.log(response);
                        this.model.warehouse_movement_id = '';
                        this.warehouse_movements = response.data;

                        EventBus.$emit('loading', false);
                    }).catch(error => {
                        console.log(error);
                        console.log(error.response)
                    });
                } else {
                    this.model.warehouse_movement_id = '';
                    this.warehouse_movements = '';
                }
            }
        },
        computed: {

        },
        methods: {
            formController: function(url, event) {
                var vm = this;

                var target = $(event.target);
                var url = url;
                var fd = new FormData(event.target);

                EventBus.$emit('loading', true);
				this.$store.commit('resetState');

                axios.post(url, fd, { headers: {
                        'Content-type': 'application/x-www-form-urlencoded',
                    }
                }).then(response => {
                    // console.log(response);
					target.find('input').prop('disabled', true);
                    target.find('select').prop('disabled', true);
                    target.find('button').prop('disabled', true);
                    EventBus.$emit('show_table', response.data);
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
                        // console.log(target);
                        let c_target = target.find("#" + i + "-error");
                        let p = c_target.parents('.form-group').find('#' + i);
                        p.addClass('is-invalid');
                        c_target.css('display', 'block');
                        c_target.html(item);
                    });
                });
            },
        }
    };
</script>