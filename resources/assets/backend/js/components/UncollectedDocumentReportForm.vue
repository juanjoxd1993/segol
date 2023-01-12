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
								:max-datetime="max_datetime"
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
								:max-datetime="max_datetime"
								@focus="$parent.clearErrorMsg($event)">
							</datetime>
							<div id="final_date-error" class="error invalid-feedback"></div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label class="form-control-label">Cliente:</label>
							<select class="form-control kt-select2" name="client_id" id="client_id" v-model="model.client_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
							</select>
							<div id="client_id-error" class="error invalid-feedback"></div>
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group">
							<label class="form-control-label">Tipo de Documento:</label>
							<select class="form-control" name="warehouse_document_type_id" id="warehouse_document_type_id" v-model="model.warehouse_document_type_id" @focus="$parent.clearErrorMsg($event)">
								<option value="">Seleccionar</option>
								<option v-for="warehouse_document_type in warehouse_document_types" :value="warehouse_document_type.id" v-bind:key="warehouse_document_type.id">{{ warehouse_document_type.name }}</option>
							</select>
							<div id="warehouse_document_type_id-error" class="error invalid-feedback"></div>
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
			companies: {
				type: Array,
				default: ''
			},
			max_datetime: {
				type: String,
				default: ''
			},
			warehouse_document_types: {
				type: Array,
				default: ''
			},
			business_units: {
				type: Array,
				default: ''
			},
			url: {
				type: String,
				default: ''
			},
			url_get_clients: {
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
					client_id: '',
					warehouse_document_type_id: '',
					business_unit_id: ''
				},
			}
		},
		created() {

		},
		mounted() {
			let vm = this;
			vm.newSelect2();
		},
		watch: {

		},
		computed: {

		},
		methods: {
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
					vm.model.client_id = parseInt(selected_element.val());
				}).on('select2:unselect', function(e) {
					vm.model.client_id = '';
				});
			},
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