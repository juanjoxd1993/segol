<template>
    <!--begin::Portlet-->
    <div class="kt-portlet">
        <div class="kt-portlet__head">
            <div class="kt-portlet__head-label">
                <h3 class="kt-portlet__head-title">
                    Buscar
                </h3>
            </div>
			<div class="kt-portlet__head-toolbar">
				<div class="kt-portlet__head-actions">
					<a :href="url_budgets" class="btn btn-outline-brand btn-bold btn-sm">
						<i class="fa fa-chart-line"></i> Administrar Presupuestos
					</a>
				</div>
			</div>
        </div>

        <!--begin::Form-->
        <form class="kt-form" @submit.prevent="formController(url, $event, model)">
            <div class="kt-portlet__body">
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Fecha:</label>
                            <datetime
                                v-model="model.to_date"
                                placeholder="Selecciona una Fecha"
                                :format="'dd-LL-yyyy'"
                                name="to_date"
								value-zone="America/Lima"
								zone="America/Lima"
                                class="form-control"
                                :max-datetime="max_date"
                                @focus="$parent.clearErrorMsg($event)">
                            </datetime>
                            <div id="to_date-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Opción:</label>
							<select class="form-control" name="sale_option_id" id="sale_option_id" v-model="model.sale_option_id" @focus="$parent.clearErrorMsg($event)">
                                <option value="">Seleccionar</option>
                                <option v-for="sale_option in sale_options" :value="sale_option.id" v-bind:key="sale_option.id">{{ sale_option.name }}</option>
                            </select>
                            <div id="sale_option_id-error" class="error invalid-feedback"></div>
                        </div>
                    </div>
					<div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-control-label">Precio Glp:</label>
							<input type="number" class="form-control" name="price" id="price" step="any" min="0" v-model="model.price" @focus="$parent.clearErrorMsg($event)">
                            <div id="price-error" class="error invalid-feedback"></div>
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
	import { Settings } from 'luxon'
	Settings.defaultLocale = 'es'

    Vue.use(Datetime);

    export default {
        props: {
            companies: {
                type: Array,
                default: ''
            },
            current_date: {
                type: String,
                default: ''
            },
            max_date: {
                type: String,
                default: ''
            },
			sale_options: {
				type: Array,
				defualt: '',
			},
            url: {
                type: String,
                default: ''
            },
			url_budgets: {
				type: String,
				default: ''
			},
			url_get_current_price: {
				type: String,
				default: ''
			}
        },
        data() {
            return {
                model: {
                    to_date: '',
                    sale_option_id: '',
					price: 0,
                }
            }
        },
		watch: {
			'model.to_date': function(val) {
				EventBus.$emit('loading', true);
				let vm = this;

				axios.post(vm.url_get_current_price, {
						to_date: val
					}).then(response => {
					// console.log(response);
					vm.model.price = response.data;
					EventBus.$emit('loading', false);
				}).catch(error => {
					console.log(error);
					console.log(error.response);
					EventBus.$emit('loading', false);
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
                    // console.log(response);
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