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
							<label class="form-control-label">Fecha inicial:</label>
							<datetime
								v-model="model.initial_date"
								placeholder="Selecciona una Fecha"
								:format="'dd-LL-yyyy'"
								input-id="initial_date"
								name="initial_date"
								value-zone="America/Lima"
								zone="America/Lima"
								class="form-control"
								:max-datetime="today"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
							<div id="initial_date-error" class="error invalid-feedback"></div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label class="form-control-label">Fecha final:</label>
							<datetime
								v-model="model.final_date"
								placeholder="Selecciona una Fecha"
								:format="'dd-LL-yyyy'"
								input-id="final_date"
								name="final_date"
								value-zone="America/Lima"
								zone="America/Lima"
								class="form-control"
								:max-datetime="today"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
							<div id="final_date-error" class="error invalid-feedback"></div>
						</div>
					</div>
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
			business_units: {
				type: Array,
				default: ''
			},
			today: {
				type: String,
				default: ''
			},
			user_name: {
				type: String,
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
					company_id: '',
					initial_date: '',
					final_date: '',
					business_unit_id: '',
				},
			}
		},
		created() {

		},
		mounted() {
			EventBus.$on('clear_form', function() {
				$('.kt-form').find('input').prop('disabled', false);
				$('.kt-form').find('select').prop('disabled', false);
				$('.kt-form').find('button').prop('disabled', false);
				
				// this.model = {
				// 	company_id: '',
				// 	initial_date: '',
				// 	final_date: '',
				// 	business_unit_id: '',
				// }
			}.bind(this));
		},
		watch: {
			
		},
		computed: {

		},
		methods: {
			formController: function(url, event) {
				var vm = this;

				if ( vm.user_name === 'admin' || vm.user_name === 'contabilidad1' || vm.user_name === 'sistemas1' ) {
					var target = $(event.target);
					var url = url;
					var fd = new FormData(event.target);

					EventBus.$emit('loading', true);

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
				} else {
					this.$parent.alertMsg({
						type: 5,
						title: 'Error',
						msg: 'Usuario no autorizado.'
					});
				}
			},
		}
	};
</script>