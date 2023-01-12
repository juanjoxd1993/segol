<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    <a :href="url_sales_report" class="btn btn-clean btn-sm btn-icon btn-icon-md">
						<i class="fa fa-arrow-left"></i>
					</a>
					Administrar Presupuestos
                </h3>
            </div>
			<div class="kt-portlet__head-toolbar">
				<div class="kt-portlet__head-actions">
					
				</div>
			</div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event, model)">
            <div class="kt-portlet__body">
                <div class="row">
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
                            <label class="form-control-label">Mes / Año:</label>
                            <datetime
                                v-model="model.year_month"
                                placeholder="Selecciona un Mes y Año"
                                :format="'yyyy-LL'"
                                name="year_month"
                                value-zone="America/Lima"
                                zone="America/Lima"
                                class="form-control"
								:flow="['year', 'month']"
								:phrases="{ok: 'Siguiente', cancel: 'Cancel'}"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="year_month-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Días:</label>
							<input type="number" class="form-control" name="days" id="days" v-model="model.days" @focus="$parent.clearErrorMsg($event)">
                            <div id="days-error" class="error invalid-feedback"></div>
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
			business_units: {
				type: Array,
				defualt: '',
			},
            url: {
                type: String,
                default: ''
            },
			url_sales_report: {
                type: String,
                default: ''
            }
        },
        data() {
            return {
                model: {
                    business_unit_id: '',
                    year_month: '',
                    days: '',
                }
            }
        },
		mounted() {
			EventBus.$on('reset_form', function() {
				this.model = {
                    business_unit_id: '',
                    year_month: '',
                    days: '',
                };
			}.bind(this));
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
                    console.log(response);
                    EventBus.$emit('show_table', response.data);
                }).catch(error => {
                    EventBus.$emit('loading', false);
                    console.log(error.response);
                    var obj = error.response.data.errors;
                    $('html, body').animate({
                        scrollTop: 0
                    }, 500, 'swing');
                    $.each(obj, function(i, item) {
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