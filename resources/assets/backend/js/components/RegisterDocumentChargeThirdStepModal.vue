<template>
	<!--begin::Modal-->
	<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
			<div class="modal-content">
				<!--begin::Form-->
				<form class="kt-form" @submit.prevent="formController(url, $event)">
					<input type="hidden" name="business_unit_id" id="business_unit_id" v-model="model.business_unit_id">
					<input type="hidden" name="igv_percentage" id="igv_percentage" v-model="model.igv_percentage">
					<input type="hidden" name="inaccurate_value" id="inaccurate_value" v-model="model.inaccurate_value">
					<input type="hidden" name="exonerated_value" id="exonerated_value" v-model="model.exonerated_value">
					<input type="hidden" name="unit_name" id="unit_name" v-model="model.unit_name">
					<div class="modal-header">
						<h5 class="modal-title" id="exampleModalLabel">Agregar item</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						</button>
					</div>
					<div class="modal-body">
						<div class="kt-portlet__body">
							<div class="row">
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Concepto:</label>
										<input type="text" class="form-control" name="concept" id="concept" v-model="model.concept" @focus="$parent.clearErrorMsg($event)">
										<div id="concept-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Unidad de Medida:</label>
										<select class="form-control" name="unit_id" id="unit_id" v-model="model.unit_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="unit in units" :value="unit.id" v-bind:key="unit.id">{{ unit.name }}</option>
										</select>
										<div id="unit_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Cantidad:</label>
										<input type="number" class="form-control" name="quantity" id="quantity" placeholder="0" step="any" min="0.0000" v-model="model.quantity" @focus="$parent.clearErrorMsg($event)">
										<div id="quantity-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Precio Unitario:</label>
										<input type="number" class="form-control" name="price_igv" id="price_igv" placeholder="0" step="any" min="0.0000" v-model="model.price_igv" @focus="$parent.clearErrorMsg($event)">
										<div id="price_igv-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Tipo de Venta:</label>
										<select class="form-control" name="value_type_id" id="value_type_id" v-model="model.value_type_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="value_type in value_types" :value="value_type.id" v-bind:key="value_type.id">{{ value_type.name }}</option>
										</select>
										<div id="value_type_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Valor:</label>
										<input type="number" class="form-control" name="sale_value" id="sale_value" placeholder="0" step="any" min="0.0000" v-model="model.sale_value" @focus="$parent.clearErrorMsg($event)">
										<div id="sale_value-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">IGV:</label>
										<input type="number" class="form-control" name="igv" id="igv" placeholder="0" step="any" min="0.00" v-model="model.igv" @focus="$parent.clearErrorMsg($event)">
										<div id="igv-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Total:</label>
										<input type="number" class="form-control" name="total" id="total" placeholder="0" step="any" min="0.00" v-model="model.total" @focus="$parent.clearErrorMsg($event)">
										<div id="total-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Serie Guía Remisión:</label>
										<input type="number" class="form-control" name="referral_guide_series" id="referral_guide_series" step="any" min="1" v-model="model.referral_guide_series" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_guide_series-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Nº Guía Remisión:</label>
										<input type="number" class="form-control" name="referral_guide_number" id="referral_guide_number" step="any" min="1" v-model="model.referral_guide_number" @focus="$parent.clearErrorMsg($event)">
										<div id="referral_guide_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Serie Guía Transportista:</label>
										<input type="number" class="form-control" name="carrier_series" id="carrier_series" step="any" min="1" v-model="model.carrier_series" @focus="$parent.clearErrorMsg($event)">
										<div id="carrier_series-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Nº Guía Transportista:</label>
										<input type="number" class="form-control" name="carrier_number" id="carrier_number" step="any" min="1" v-model="model.carrier_number" @focus="$parent.clearErrorMsg($event)">
										<div id="carrier_number-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Placa:</label>
										<input type="text" class="form-control" name="license_plate" id="license_plate" v-model="model.license_plate" @focus="$parent.clearErrorMsg($event)">
										<div id="license_plate-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Cantidad Referencial:</label>
										<input type="number" class="form-control" name="referential_quantity" id="referential_quantity" placeholder="0" step="any" min="0.0000" v-model="model.referential_quantity" @focus="$parent.clearErrorMsg($event)">
										<div id="referential_quantity-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Unidad de Medida Referencial:</label>
										<select class="form-control" name="referential_unit_id" id="referential_unit_id" v-model="model.referential_unit_id" @focus="$parent.clearErrorMsg($event)">
											<option value="">Seleccionar</option>
											<option v-for="unit in units" :value="unit.id" v-bind:key="unit.id">{{ unit.name }}</option>
										</select>
										<div id="referential_unit_id-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Factor Referencial:</label>
										<input type="number" class="form-control" name="referential_convertion" id="referential_convertion" placeholder="0" step="any" min="0.00" v-model="model.referential_convertion" @focus="$parent.clearErrorMsg($event)">
										<div id="referential_convertion-error" class="error invalid-feedback"></div>
									</div>
								</div>
								<div class="col-lg-4">
									<div class="form-group">
										<label class="form-control-label">Valor Referencial:</label>
										<input type="number" class="form-control" name="referential_sale_value" id="referential_sale_value" placeholder="0" step="any" min="0.000000" v-model="model.referential_sale_value" @focus="$parent.clearErrorMsg($event)">
										<div id="referential_sale_value-error" class="error invalid-feedback"></div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success">
							Agregar item
						</button>
						<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
					</div>
				</form>
				<!--end::Form-->
			</div>
		</div>
	</div>
	<!--end::Modal-->
</template>

<script>
	import EventBus from '../event-bus';

	export default {
		props: {
			units: {
				type: Array,
				default: ''
			},
			value_types: {
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
					item: '',
					business_unit_id: '',
					concept: '',
					unit_id: '',
					unit_name: '',
					quantity: 0,
					price_igv: 0,
					value_type_id: '',
					sale_value: 0,
					inaccurate_value: 0,
					exonerated_value: 0,
					igv: 0,
					igv_percentage: 0,
					total: 0,
					referral_guide_series: '',
					referral_guide_number: '',
					carrier_series: '',
					carrier_number: '',
					license_plate: '',
					referential_quantity: 0,
					referential_unit_id: '',
					referential_convertion: 1,
					referential_sale_value: 0,
				},
				form_model: {},
				button_text: '',
			}
		},
		watch: {
			'model.quantity': function(val) {
				this.calculateValues(val, this.model.price_igv, this.model.value_type_id);
			},
			'model.price_igv': function(val) {
				this.calculateValues(this.model.quantity, val, this.model.value_type_id);
			},
			'model.value_type_id': function(val) {
				this.calculateValues(this.model.quantity, this.model.price_igv, val);
			},
		},
		computed: {
			
		},
		created() {
			EventBus.$on('open_modal', function(business_unit_id, igv_percentage) {
				EventBus.$emit('loading', true);

				this.model = {
					item: '',
					business_unit_id: business_unit_id,
					concept: '',
					unit_id: '',
					unit_name: '',
					quantity: 0,
					price_igv: 0,
					value_type_id: '',
					sale_value: 0,
					inaccurate_value: 0,
					exonerated_value: 0,
					igv: 0,
					igv_percentage: igv_percentage,
					total: 0,
					referral_guide_series: '',
					referral_guide_number: '',
					carrier_series: '',
					carrier_number: '',
					license_plate: '',
					referential_quantity: 0,
					referential_unit_id: '',
					referential_convertion: 1,
					referential_sale_value: 0,
				};

				$('#modal').modal('show');
				EventBus.$emit('loading', false);
			}.bind(this));
		},
		mounted() {
			$('#modal').on('hide.bs.modal', function(e) {
				this.model = {
					item: '',
					business_unit_id: '',
					concept: '',
					unit_id: '',
					unit_name: '',
					quantity: 0,
					price_igv: 0,
					value_type_id: '',
					sale_value: 0,
					inaccurate_value: 0,
					exonerated_value: 0,
					igv: 0,
					igv_percentage: 0,
					total: 0,
					referral_guide_series: '',
					referral_guide_number: '',
					carrier_series: '',
					carrier_number: '',
					license_plate: '',
					referential_quantity: 0,
					referential_unit_id: '',
					referential_convertion: 1,
					referential_sale_value: 0,
				};
			}.bind(this));
		},
		methods: {
			calculateValues: function (quantity, price_igv, value_type_id) {
				let total = accounting.toFixed(quantity * price_igv, 2);
				let igv_percentage = ( this.model.igv_percentage / 100 ) + 1;
				let sale_value = accounting.toFixed(total / igv_percentage, 4);
				let igv = accounting.toFixed(total - sale_value, 2);

				if ( value_type_id == 2 || value_type_id == 3 ) {
					this.model.sale_value = total;
					this.model.igv = 0;
					this.model.total = total;
				} else {
					this.model.sale_value = sale_value;
					this.model.igv = igv;
					this.model.total = total;
				}
			},
			formController: function(url, event) {
				var vm = this;

				if ( this.model.value_type_id == 2 ) {
					this.model.inaccurate_value = this.model.sale_value;
					this.model.sale_value = 0;
				} else if ( this.model.value_type_id == 3 ) {
					this.model.exonerated_value = this.model.sale_value;
					this.model.sale_value = 0;
				}

				let unit = this.units.find( element => element.id == this.model.unit_id );
				this.model.unit_name = unit ? unit.name : '';

				var target = $(event.target);
				var url = url;
				var fd = new FormData(event.target);
				
				EventBus.$emit('loading', true);

				axios.post(url, fd, { headers: {
						'Content-type': 'application/x-www-form-urlencoded',
					}
				}).then(response => {
					// console.log(response.data);
					EventBus.$emit('loading', false);
					EventBus.$emit('update_table', this.model);

					$('#modal').modal('hide');
					this.$parent.alertMsg(response.data);

					this.model = {
						item: '',
						business_unit_id: '',
						concept: '',
						unit_id: '',
						unit_name: '',
						quantity: 0,
						price_igv: 0,
						value_type_id: '',
						sale_value: 0,
						inaccurate_value: 0,
						exonerated_value: 0,
						igv: 0,
						igv_percentage: 0,
						total: 0,
						referral_guide_series: '',
						referral_guide_number: '',
						carrier_series: '',
						carrier_number: '',
						license_plate: '',
						referential_quantity: 0,
						referential_unit_id: '',
						referential_convertion: 1,
						referential_sale_value: 0,
					};


				}).catch(error => {
					EventBus.$emit('loading', false);
					console.log(error.response);
					var obj = error.response.data.errors;
					$('.modal').animate({
						scrollTop: 0
					}, 500, 'swing');
					$.each(obj, function(i, item) {
						let c_target = target.find("#" + i + "-error");
						if ( !c_target.attr('data-required') ) {
							let p = c_target.parent().find('#' + i);
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