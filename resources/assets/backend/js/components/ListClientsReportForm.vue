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
		<form class="kt-form" @submit.prevent="formController(url, $event)" id="first-step-form">
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
							<label class="form-control-label">Compañía:</label>
							<select class="form-control" name="company_id" id="company_id" v-model="model.company_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="company in companies" :value="company.id" v-bind:key="company.id">{{ company.name }}</option>
							</select>
							<div id="company_id-error" class="error invalid-feedback"></div>
						</div>
					</div>				
				</div>
			</div>
			<div class="kt-portlet__foot">
				<div class="kt-form__actions">
					<div class="row">
						<div class="col-12 kt-align-right">
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
			business_units: {
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
					business_unit_id: '',
					company_id: '',
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
				var vm = this;

				var target = $(event.target);
				var url = url;
				var fd = new FormData(event.target);

				EventBus.$emit('loading', true);

				axios.post(url, fd, { headers: {
						'Content-type': 'application/x-www-form-urlencoded',
					}
				}).then(response => {
					EventBus.$emit('loading', false);
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